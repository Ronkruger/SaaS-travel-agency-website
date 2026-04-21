<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'xendit/webhook',
        'stripe/webhook',
        // Allow logout even when session/CSRF token has expired.
        'logout',
        '*/logout',
    ];
}
