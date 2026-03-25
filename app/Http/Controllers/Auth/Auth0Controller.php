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
            $desc = request()->input('error_description', 'Unknown error');
            error_log('[Auth0] error redirect: ' . $desc);
            return redirect()->route('login')
                ->with('auth0_error', 'Auth0: ' . $desc);
        }

        try {
            $socialUser = Socialite::driver('auth0')->stateless()->user();
        } catch (\Exception $e) {
            $body = '';
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
            }
            error_log('[Auth0] callback exception: ' . get_class($e) . ' — ' . $e->getMessage() . ' | body: ' . $body);
            return redirect()->route('login')
                ->with('auth0_error', 'Auth0 login failed. Please try again.');
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
