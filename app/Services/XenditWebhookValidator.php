<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditWebhookValidator
{
    /**
     * Xendit webhook IP ranges (should be updated periodically from Xendit docs).
     * Reference: https://developers.xendit.co/api-reference/#webhooks
     */
    protected static array $allowedIps = [
        // Xendit production IPs (update from Xendit documentation)
        '52.74.184.0/24',
        '52.76.206.0/24', 
        '13.228.68.0/24',
        '18.136.100.0/24',
        '18.139.10.0/24',
        // Allow localhost for testing
        '127.0.0.1',
        '::1',
    ];

    /**
     * Validate the webhook request.
     */
    public static function validate(
        string $token,
        string $ip,
        array $data
    ): array {
        $errors = [];

        // 1. Token validation
        if ($token !== config('xendit.webhook_token')) {
            $errors[] = 'invalid_token';
        }

        // 2. IP whitelist validation (optional but recommended)
        if (!self::isIpAllowed($ip)) {
            Log::channel('security')->warning('Xendit webhook from unwhitelisted IP', [
                'ip' => $ip,
                'external_id' => $data['external_id'] ?? null,
            ]);
            // Don't reject, just log - Xendit might add new IPs
        }

        // 3. Required fields validation
        $requiredFields = ['id', 'external_id', 'status'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "missing_{$field}";
            }
        }

        // 4. External ID format validation
        // Accepts: BOOKING-{id}-{ts}  or  INSTALLMENT-{id}-{term}-{ts}
        if (!empty($data['external_id'])) {
            if (!preg_match('/^(BOOKING|INSTALLMENT)-\d+/', $data['external_id'])) {
                $errors[] = 'invalid_external_id_format';
            }
        }

        return $errors;
    }

    /**
     * Check if IP is in the allowed list.
     */
    protected static function isIpAllowed(string $ip): bool
    {
        foreach (self::$allowedIps as $allowed) {
            if (self::ipInRange($ip, $allowed)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if IP is in CIDR range.
     */
    protected static function ipInRange(string $ip, string $cidr): bool
    {
        // Exact match
        if ($ip === $cidr) {
            return true;
        }

        // CIDR notation
        if (strpos($cidr, '/') !== false) {
            [$subnet, $bits] = explode('/', $cidr);
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - (int) $bits);
            $subnet &= $mask;
            return ($ip & $mask) === $subnet;
        }

        return false;
    }

    /**
     * Sanitize gateway response for storage.
     * Only keep necessary fields, remove sensitive data.
     */
    public static function sanitizeGatewayResponse(array $data): array
    {
        // Whitelist of safe fields to store
        $safeFields = [
            'id',
            'external_id',
            'status',
            'merchant_name',
            'amount',
            'payer_email',
            'description',
            'payment_method',
            'payment_channel',
            'paid_at',
            'created',
            'updated',
            'currency',
        ];

        $sanitized = [];
        foreach ($safeFields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = $data[$field];
            }
        }

        return $sanitized;
    }
}
