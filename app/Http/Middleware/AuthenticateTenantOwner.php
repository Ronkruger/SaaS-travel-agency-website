<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class AuthenticateTenantOwner
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = session('tenant_owner');

        if (!$tenantId) {
            return redirect()->route('central.login');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_owner');
            return redirect()->route('central.login');
        }

        view()->share('tenantOwner', $tenant);

        return $next($request);
    }
}
