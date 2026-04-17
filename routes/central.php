<?php

use App\Http\Controllers\Central\HomeController;
use App\Http\Controllers\Central\TenantRegistrationController;
use App\Http\Controllers\Central\PlatformAuthController;
use App\Http\Controllers\Central\PlatformDashboardController;
use App\Http\Controllers\Central\PlatformTenantController;
use App\Http\Controllers\Central\PlatformPlanController;
use App\Http\Controllers\Central\BillingWebhookController;
use App\Http\Controllers\Central\TenantBillingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Domain Routes (SaaS Platform)
|--------------------------------------------------------------------------
| These routes are accessible on the central domain(s) only.
| They handle the SaaS landing page, tenant registration, billing,
| and platform administration.
*/

// Public pages
Route::get('/', [HomeController::class, 'index'])->name('central.home');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('central.pricing');
Route::get('/features', [HomeController::class, 'features'])->name('central.features');

// Tenant Registration & Onboarding
Route::middleware('guest')->group(function () {
    Route::get('/register', [TenantRegistrationController::class, 'showRegistrationForm'])->name('central.register');
    Route::post('/register', [TenantRegistrationController::class, 'register'])->middleware('throttle:5,1');
});

// Tenant Owner Login (to manage billing/subscription)
Route::middleware('guest')->group(function () {
    Route::get('/login', [TenantRegistrationController::class, 'showLoginForm'])->name('central.login');
    Route::post('/login', [TenantRegistrationController::class, 'login'])->middleware('throttle:5,1');
});
Route::post('/logout', [TenantRegistrationController::class, 'logout'])->name('central.logout');

// Tenant Owner Billing Dashboard
Route::middleware('auth.tenant_owner')->prefix('billing')->name('central.billing.')->group(function () {
    Route::get('/', [TenantBillingController::class, 'index'])->name('index');
    Route::get('/plans', [TenantBillingController::class, 'plans'])->name('plans');
    Route::post('/subscribe/{plan}', [TenantBillingController::class, 'subscribe'])->name('subscribe');
    Route::post('/cancel', [TenantBillingController::class, 'cancel'])->name('cancel');
    Route::get('/invoices', [TenantBillingController::class, 'invoices'])->name('invoices');
});

// Stripe Webhook (CSRF-exempt in VerifyCsrfToken middleware)
Route::post('/stripe/webhook', [BillingWebhookController::class, 'handle'])->name('central.stripe.webhook');

/*
|--------------------------------------------------------------------------
| Platform Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('platform')->name('platform.')->group(function () {
    // Auth
    Route::middleware('guest:platform')->group(function () {
        Route::get('/login', [PlatformAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [PlatformAuthController::class, 'login'])->middleware('throttle:5,1');
    });

    Route::middleware('auth:platform')->group(function () {
        Route::post('/logout', [PlatformAuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [PlatformDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [PlatformDashboardController::class, 'stats'])->name('dashboard.stats');

        // Tenant Management
        Route::get('/tenants', [PlatformTenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/{tenant}', [PlatformTenantController::class, 'show'])->name('tenants.show');
        Route::get('/tenants/{tenant}/edit', [PlatformTenantController::class, 'edit'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [PlatformTenantController::class, 'update'])->name('tenants.update');
        Route::post('/tenants/{tenant}/toggle-status', [PlatformTenantController::class, 'toggleStatus'])->name('tenants.toggle-status');
        Route::delete('/tenants/{tenant}', [PlatformTenantController::class, 'destroy'])->name('tenants.destroy');
        Route::post('/tenants/{tenant}/impersonate', [PlatformTenantController::class, 'impersonate'])->name('tenants.impersonate');

        // Plan Management
        Route::get('/plans', [PlatformPlanController::class, 'index'])->name('plans.index');
        Route::post('/plans', [PlatformPlanController::class, 'store'])->name('plans.store');
        Route::put('/plans/{plan}', [PlatformPlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [PlatformPlanController::class, 'destroy'])->name('plans.destroy');
    });
});
