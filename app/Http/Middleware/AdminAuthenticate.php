<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        // Make the 'admin' guard the default for this entire request
        // so auth()->user() and @auth work correctly in all admin views
        Auth::shouldUse('admin');

        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('admin.auth.login')
                ->with('error', 'Please sign in to access the admin panel.');
        }

        // If authenticated but hasn't completed onboarding, redirect there
        $user = Auth::user();
        if (
            !$user->is_onboarded
            && !$request->routeIs('admin.onboarding')
            && !$request->routeIs('admin.onboarding.save')
            && !$request->routeIs('admin.logout')
        ) {
            return redirect()->route('admin.onboarding');
        }

        return $next($request);
    }
}
