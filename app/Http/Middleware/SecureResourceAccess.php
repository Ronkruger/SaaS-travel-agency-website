<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SecureResourceAccess
{
    /**
     * Rate limit key prefix for sensitive resource access.
     */
    protected string $rateLimitPrefix = 'secure_resource';

    /**
     * Maximum attempts per minute.
     */
    protected int $maxAttempts = 30;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $resourceType = 'resource'): Response
    {
        $ip = $request->ip();
        $key = $this->getRateLimitKey($request, $resourceType);

        // Check if IP is already flagged as potentially malicious
        if (SecurityLogger::isPotentiallyMalicious($ip, $resourceType)) {
            return $this->tooManyAttemptsResponse($request, $resourceType);
        }

        // Validate ID format if present in route
        $id = $this->extractResourceId($request);
        if ($id !== null && !$this->isValidIdFormat($id)) {
            SecurityLogger::logSuspiciousAccess(
                $request,
                $resourceType,
                $id,
                'invalid_id_format'
            );
            return response()->json([
                'error' => 'Invalid resource identifier format.',
                'code' => 'INVALID_ID_FORMAT'
            ], 400);
        }

        // Rate limiting check
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            SecurityLogger::logEnumerationAttempt($request, $resourceType);
            return $this->tooManyAttemptsResponse($request, $resourceType);
        }

        RateLimiter::hit($key, 60); // 1 minute decay

        return $next($request);
    }

    /**
     * Extract resource ID from the request.
     */
    protected function extractResourceId(Request $request): ?string
    {
        // Check route parameters first
        foreach ($request->route()->parameters() as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                return (string) $value;
            }
        }

        // Check query parameter 'id'
        if ($request->has('id')) {
            return (string) $request->input('id');
        }

        return null;
    }

    /**
     * Validate that the ID is in a valid format.
     */
    protected function isValidIdFormat(string $id): bool
    {
        // Accept positive integers
        if (ctype_digit($id) && (int) $id > 0) {
            return true;
        }

        // Accept UUIDs (for future migration)
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $id)) {
            return true;
        }

        return false;
    }

    /**
     * Get rate limit key for the request.
     */
    protected function getRateLimitKey(Request $request, string $resourceType): string
    {
        $userId = auth()->id() ?? 'guest';
        return "{$this->rateLimitPrefix}:{$resourceType}:{$request->ip()}:{$userId}";
    }

    /**
     * Return a 429 Too Many Requests response.
     */
    protected function tooManyAttemptsResponse(Request $request, string $resourceType): Response
    {
        $message = 'Too many requests. Please try again later.';
        
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $message,
                'code' => 'RATE_LIMIT_EXCEEDED'
            ], 429);
        }

        abort(429, $message);
    }
}
