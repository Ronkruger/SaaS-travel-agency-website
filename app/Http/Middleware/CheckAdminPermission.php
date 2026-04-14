<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('admin.can:manage_tours')
 *
 * Checks that the authenticated admin user has the given permission key.
 * Super admins are always allowed. Unauthenticated users are redirected to login.
 */
class CheckAdminPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $admin = auth('admin')->user();

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        if (! $admin->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            abort(403, "You don't have permission to access this page.");
        }

        return $next($request);
    }
}
