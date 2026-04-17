<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AdminAuth0Controller extends Controller
{
    protected static array $allowedAvatarDomains = [
        'lh3.googleusercontent.com',
        'avatars.githubusercontent.com',
        's.gravatar.com',
        'platform-lookaside.fbsbx.com',
        'cdn.auth0.com',
        'secure.gravatar.com',
    ];

    public function redirect()
    {
        $driver = Socialite::driver('auth0')
            ->stateless()
            ->redirectUrl(route('admin.auth.auth0.callback'));

        if (request()->input('mode') === 'register') {
            $driver->with(['screen_hint' => 'signup']);
        }

        return $driver->redirect();
    }

    public function callback()
    {
        if (request()->has('error')) {
            Log::channel('security')->warning('Admin Auth0 authentication failed', [
                'error_code' => request()->input('error', 'unknown'),
                'ip'         => request()->ip(),
            ]);
            return redirect()->route('admin.auth.login')
                ->with('auth0_error', 'Authentication failed. Please try again.');
        }

        try {
            $socialUser = Socialite::driver('auth0')
                ->stateless()
                ->redirectUrl(route('admin.auth.auth0.callback'))
                ->user();
        } catch (\Exception $e) {
            Log::channel('security')->error('Admin Auth0 callback exception', [
                'exception_class' => get_class($e),
                'ip'              => request()->ip(),
            ]);
            return redirect()->route('admin.auth.login')
                ->with('auth0_error', 'Auth0 login failed. Please try again.');
        }

        $avatar = $this->sanitizeAvatarUrl($socialUser->getAvatar());

        $admin = AdminUser::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name'     => $this->sanitizeName(
                    $socialUser->getName() ?? $socialUser->getNickname() ?? 'Employee'
                ),
                'password' => Hash::make(Str::random(32)),
                'auth0_id' => $socialUser->getId(),
                'avatar'   => $avatar,
            ]
        );

        if (!$admin->auth0_id) {
            $admin->update([
                'auth0_id' => $socialUser->getId(),
                'avatar'   => $avatar,
            ]);
        }

        Auth::guard('admin')->login($admin, true);
        request()->session()->regenerate();

        if (!$admin->is_onboarded) {
            return redirect()->route('admin.onboarding');
        }

        return redirect()->route('admin.dashboard');
    }

    protected function sanitizeAvatarUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        if (!str_starts_with($url, 'https://')) {
            return null;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || !in_array($host, self::$allowedAvatarDomains, true)) {
            Log::channel('security')->info('Rejected admin avatar from unknown domain', [
                'domain' => $host,
            ]);
            return null;
        }
        return $url;
    }

    protected function sanitizeName(string $name): string
    {
        return Str::limit(trim(strip_tags($name)), 255, '');
    }
}
