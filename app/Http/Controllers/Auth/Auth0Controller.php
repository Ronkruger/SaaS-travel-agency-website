<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class Auth0Controller extends Controller
{
    /**
     * Redirect the user to the Auth0 login page.
     */
    public function redirect()
    {
        return Socialite::driver('auth0')->enablePKCE()->redirect();
    }

    /**
     * Handle the Auth0 callback and log the user in (or create their account).
     */
    public function callback()
    {
        // Auth0 can redirect back with ?error=... if auth failed on their end
        if (request()->has('error')) {
            $err = request()->input('error');
            $desc = request()->input('error_description', '');
            error_log("[Auth0] error redirect received: {$err} — {$desc}");
            return redirect()->route('login')
                ->withErrors(['email' => 'Auth0 login failed: ' . $desc]);
        }

        try {
            $socialUser = Socialite::driver('auth0')->enablePKCE()->user();
        } catch (\Exception $e) {
            $body = '';
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
            }
            error_log('[Auth0] callback exception: ' . get_class($e) . ' — ' . $e->getMessage() . ' | body: ' . $body);
            \Illuminate\Support\Facades\Log::error('Auth0 callback failed', [
                'message' => $e->getMessage(),
                'class'   => get_class($e),
                'body'    => $body,
                'redirect_uri' => config('services.auth0.redirect'),
                'base_url'     => config('services.auth0.base_url'),
                'client_id'    => config('services.auth0.client_id'),
            ]);
            return redirect()->route('login')
                ->withErrors(['email' => 'Auth0 login failed. Please try again.']);
        }

        // Find existing user by email or create a new one
        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'password'          => bcrypt(Str::random(32)), // unusable password — Auth0 handles auth
                'role'              => 'user',
                'auth0_id'          => $socialUser->getId(),
                'avatar'            => $socialUser->getAvatar(),
            ]
        );

        // Update Auth0 ID and avatar if user already existed but logged in via Auth0 for the first time
        if (!$user->auth0_id) {
            $user->update([
                'auth0_id' => $socialUser->getId(),
                'avatar'   => $socialUser->getAvatar(),
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('home'));
    }
}
