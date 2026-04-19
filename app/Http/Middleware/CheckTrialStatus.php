<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialStatus
{
    /**
     * Handle an incoming request.
     *
     * Check if tenant's trial has expired and redirect to upgrade page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (!$tenant) {
            return $next($request);
        }

        // Skip check if tenant is on a paid plan
        if ($tenant->plan !== 'trial') {
            return $next($request);
        }

        // Check if trial has expired
        if ($tenant->trial_ends_at && $tenant->trial_ends_at->isPast()) {
            // Allow access to certain routes even with expired trial
            $allowedRoutes = [
                'admin.billing.plans',
                'admin.billing.subscribe',
                'admin.auth.logout',
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('admin.billing.plans')
                    ->with('error', 'Your trial has expired. Please upgrade to continue.');
            }
        }

        return $next($request);
    }
}

