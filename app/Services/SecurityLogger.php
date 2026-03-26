<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SecurityLogger
{
    /**
     * Log a suspicious access attempt.
     */
    public static function logSuspiciousAccess(
        Request $request,
        string $resourceType,
        string|int $resourceId,
        string $reason
    ): void {
        $userId = auth()->id() ?? 'guest';
        $ip = $request->ip();
        
        Log::channel('security')->warning('Suspicious access attempt', [
            'reason'        => $reason,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'user_id'       => $userId,
            'ip'            => $ip,
            'user_agent'    => $request->userAgent(),
            'url'           => $request->fullUrl(),
            'method'        => $request->method(),
            'timestamp'     => now()->toIso8601String(),
        ]);

        // Track failed attempts for rate limiting detection
        self::incrementFailedAttempts($ip, $resourceType);
    }

    /**
     * Log an unauthorized access attempt (403).
     */
    public static function logUnauthorizedAccess(
        Request $request,
        string $resourceType,
        string|int $resourceId
    ): void {
        self::logSuspiciousAccess($request, $resourceType, $resourceId, 'unauthorized_access');
    }

    /**
     * Log access to non-existent resource (404).
     */
    public static function logNotFoundAccess(
        Request $request,
        string $resourceType,
        string|int $resourceId
    ): void {
        self::logSuspiciousAccess($request, $resourceType, $resourceId, 'resource_not_found');
    }

    /**
     * Log potential enumeration attack.
     */
    public static function logEnumerationAttempt(
        Request $request,
        string $resourceType
    ): void {
        Log::channel('security')->alert('Potential enumeration attack detected', [
            'resource_type' => $resourceType,
            'user_id'       => auth()->id() ?? 'guest',
            'ip'            => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'url'           => $request->fullUrl(),
            'timestamp'     => now()->toIso8601String(),
        ]);
    }

    /**
     * Track failed attempts per IP/resource type for enumeration detection.
     */
    private static function incrementFailedAttempts(string $ip, string $resourceType): void
    {
        $key = "security:failed_attempts:{$ip}:{$resourceType}";
        $attempts = Cache::increment($key);
        
        // Set expiry on first attempt
        if ($attempts === 1) {
            Cache::put($key, 1, now()->addMinutes(15));
        }

        // Alert if threshold exceeded (potential enumeration)
        if ($attempts >= 10) {
            request() && self::logEnumerationAttempt(request(), $resourceType);
        }
    }

    /**
     * Get the number of failed attempts for an IP/resource type.
     */
    public static function getFailedAttempts(string $ip, string $resourceType): int
    {
        return (int) Cache::get("security:failed_attempts:{$ip}:{$resourceType}", 0);
    }

    /**
     * Check if IP is potentially malicious based on failed attempts.
     */
    public static function isPotentiallyMalicious(string $ip, string $resourceType): bool
    {
        return self::getFailedAttempts($ip, $resourceType) >= 10;
    }
}
