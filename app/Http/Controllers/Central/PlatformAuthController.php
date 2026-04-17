<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PlatformAuthController extends Controller
{
    public function showLogin()
    {
        return view('central.platform.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('platform')->attempt($validated, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('platform.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::guard('platform')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('platform.login');
    }
}
