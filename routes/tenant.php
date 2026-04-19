<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
| These routes are accessible via /t/{tenant}/...
| All existing application functionality runs in the tenant context.
*/

Route::prefix('/t/{tenant}')
    ->middleware([
        'web',
        InitializeTenancyByPath::class,
        \App\Http\Middleware\CheckTenantActive::class,
        \App\Http\Middleware\CheckTrialStatus::class,
    ])
    ->group(base_path('routes/web.php'));
