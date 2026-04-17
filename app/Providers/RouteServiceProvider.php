<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\Tour;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    protected function centralDomains(): array
    {
        return config('tenancy.central_domains', ['localhost']);
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        // Allow admin routes to resolve soft-deleted tours by slug
        Route::bind('tour', function ($value) {
            $isAdminRoute = request()->is('admin/*');
            $query = $isAdminRoute ? Tour::withTrashed() : Tour::query();
            return $query->where('slug', $value)->firstOrFail();
        });

        $this->routes(function () {
            // Central domain routes (SaaS platform, billing, platform admin)
            foreach ($this->centralDomains() as $domain) {
                Route::middleware('web')
                    ->domain($domain)
                    ->group(base_path('routes/central.php'));
            }

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Tenant routes are handled by TenancyServiceProvider via routes/tenant.php
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Default API rate limit
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Stricter rate limit for authentication attempts
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Rate limit for sensitive resource access (bookings, checkouts)
        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'error' => 'Too many requests. Please slow down.',
                        'code' => 'RATE_LIMIT_EXCEEDED'
                    ], 429);
                });
        });

        // Rate limit for admin panel
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limit for password changes and sensitive operations
        RateLimiter::for('password', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });
    }
}
