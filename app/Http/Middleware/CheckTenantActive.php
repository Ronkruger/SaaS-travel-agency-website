<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTenantActive
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();

        if (!$tenant) {
            return $next($request);
        }

        if (!$tenant->is_active) {
            return response()->view('errors.tenant-inactive', [
                'message' => 'This agency account has been deactivated. Please contact support.',
            ], 403);
        }

        if (!$tenant->hasActiveSubscription()) {
            // Allow admin routes to still work (to let them subscribe/renew)
            if (!$request->is('admin/*') && !$request->is('billing/*')) {
                return response()->view('errors.subscription-expired', [
                    'tenant' => $tenant,
                    'message' => 'Your subscription has expired. Please renew to continue.',
                ], 402);
            }
        }

        // Share tenant with all views
        view()->share('currentTenant', $tenant);

        return $next($request);
    }
}
