<?php

namespace App\Exceptions;

use App\Services\SecurityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Log 403 Forbidden errors
        $this->renderable(function (AuthorizationException $e, $request) {
            SecurityLogger::logUnauthorizedAccess(
                $request,
                $this->getResourceTypeFromRequest($request),
                $this->getResourceIdFromRequest($request)
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'You are not authorized to access this resource.',
                    'code' => 'FORBIDDEN'
                ], 403);
            }

            return response()->view('errors.403', [], 403);
        });

        // Log 404 Not Found errors that may indicate enumeration attempts
        $this->renderable(function (ModelNotFoundException|NotFoundHttpException $e, $request) {
            SecurityLogger::logNotFoundAccess(
                $request,
                $this->getResourceTypeFromRequest($request),
                $this->getResourceIdFromRequest($request)
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'The requested resource was not found.',
                    'code' => 'NOT_FOUND'
                ], 404);
            }

            // Let Laravel handle the default 404 view
            return null;
        });

        // Log 401 Unauthenticated errors
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'You must be logged in to access this resource.',
                    'code' => 'UNAUTHENTICATED'
                ], 401);
            }

            // Determine the correct login route based on request context
            if ($request->is('platform/*')) {
                return redirect()->guest(route('platform.login'));
            }

            $tenant = $request->route('tenant');
            if ($tenant) {
                return redirect()->guest(route('admin.auth.login', ['tenant' => $tenant]));
            }

            return redirect()->guest(route('central.login'));
        });
    }

    /**
     * Extract resource type from request path.
     */
    protected function getResourceTypeFromRequest($request): string
    {
        $path = $request->path();
        $segments = explode('/', $path);
        
        // Common resource types
        $resources = ['bookings', 'checkout', 'users', 'tours', 'reviews', 'payments'];
        
        foreach ($segments as $segment) {
            if (in_array($segment, $resources)) {
                return rtrim($segment, 's'); // bookings -> booking
            }
        }
        
        return 'resource';
    }

    /**
     * Extract resource ID from request.
     */
    protected function getResourceIdFromRequest($request): string
    {
        // Try route parameters
        foreach ($request->route()?->parameters() ?? [] as $value) {
            if (is_string($value) || is_numeric($value)) {
                return (string) $value;
            }
        }

        // Try query parameter
        if ($request->has('id')) {
            return (string) $request->input('id');
        }

        return 'unknown';
    }
}
