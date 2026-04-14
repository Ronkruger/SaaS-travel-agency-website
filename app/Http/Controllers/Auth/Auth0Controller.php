<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class Auth0Controller extends Controller
{
    /**
     * Allowed avatar URL domains from OAuth providers.
     */
    protected static array $allowedAvatarDomains = [
        'lh3.googleusercontent.com',    // Google
        'avatars.githubusercontent.com', // GitHub
        's.gravatar.com',                // Gravatar
        'platform-lookaside.fbsbx.com', // Facebook
        'cdn.auth0.com',                // Auth0 default
        'secure.gravatar.com',          // Gravatar secure
    ];

    /**
     * Redirect to Auth0. Pass ?mode=register for the sign-up screen.
     */
    public function redirect()
    {
        $driver = Socialite::driver('auth0')->stateless();

        if (request()->input('mode') === 'register') {
            $driver->with(['screen_hint' => 'signup']);
        }

        return $driver->redirect();
    }

    /**
     * Handle the Auth0 callback — works for both login and registration.
     */
    public function callback()
    {
        // Auth0 can redirect back with ?error=... if auth failed on its end
        if (request()->has('error')) {
            $errorCode = request()->input('error', 'unknown');
            // SECURITY: Don't log full error description, only code
            Log::channel('security')->warning('Auth0 authentication failed', [
                'error_code' => $errorCode,
                'ip' => request()->ip(),
            ]);
            return redirect()->route('login')
                ->with('auth0_error', 'Authentication failed. Please try again.');
        }

        try {
            $socialUser = Socialite::driver('auth0')->stateless()->user();
        } catch (\Exception $e) {
            // SECURITY: Log only exception class, not full message which may contain tokens
            Log::channel('security')->error('Auth0 callback exception', [
                'exception_class' => get_class($e),
                'ip' => request()->ip(),
            ]);
            return redirect()->route('login')
                ->with('auth0_error', 'Auth0 login failed. Please try again.');
        }

        // SECURITY: Validate and sanitize avatar URL
        $avatar = $this->sanitizeAvatarUrl($socialUser->getAvatar());

        // Find existing user by email or create a new one
        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name'              => $this->sanitizeName($socialUser->getName() ?? $socialUser->getNickname() ?? 'User'),
                'password'          => bcrypt(Str::random(32)), // unusable password — Auth0 handles auth
                'auth0_id'          => $socialUser->getId(),
                'avatar'            => $avatar,
            ]
        );
        $user->role = 'user';
        $user->save();

        // Update Auth0 ID and avatar if user already existed but logged in via Auth0 for the first time
        if (!$user->auth0_id) {
            $user->update([
                'auth0_id' => $socialUser->getId(),
                'avatar'   => $avatar,
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Validate and sanitize avatar URL from OAuth provider.
     * Returns null if URL is invalid or from untrusted domain.
     */
    protected function sanitizeAvatarUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Must be valid URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Must be HTTPS
        if (!str_starts_with($url, 'https://')) {
            return null;
        }

        // Must be from allowed domain
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || !in_array($host, self::$allowedAvatarDomains)) {
            Log::channel('security')->info('Rejected avatar from unknown domain', [
                'domain' => $host,
            ]);
            return null;
        }

        // URL-encode the path to prevent injection
        return $url;
    }

    /**
     * Sanitize user name from OAuth provider.
     */
    protected function sanitizeName(string $name): string
    {
        // Remove any HTML/script tags and limit length
        $name = strip_tags($name);
        $name = trim($name);
        return Str::limit($name, 255, '');
    }
}
