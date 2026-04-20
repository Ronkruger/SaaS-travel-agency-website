<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Platform admin routes → platform login
        if ($request->is('platform/*')) {
            return route('platform.login');
        }

        // Central/billing routes → central (tenant-owner) login
        if ($request->is('billing/*') || $request->is('login') || $request->is('register')) {
            return route('central.login');
        }

        // Tenant routes → tenant admin login (extract tenant from path)
        $tenant = $request->route('tenant');
        if ($tenant) {
            return route('admin.auth.login', ['tenant' => $tenant]);
        }

        return route('central.login');
    }
}
