<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PlatformSystemController extends Controller
{
    public function index()
    {
        $checks = $this->runAllChecks();
        $summary = $this->summarize($checks);

        return view('central.platform.system.index', compact('checks', 'summary'));
    }

    public function check()
    {
        $checks = $this->runAllChecks();
        $summary = $this->summarize($checks);

        return response()->json(compact('checks', 'summary'));
    }

    protected function runAllChecks(): array
    {
        return [
            'database'   => $this->checkDatabase(),
            'cloudinary' => $this->checkCloudinary(),
            'xendit'     => $this->checkXendit(),
            'stripe'     => $this->checkStripe(),
            'resend'     => $this->checkResend(),
            'openai'     => $this->checkOpenAI(),
            'mapbox'     => $this->checkMapbox(),
            'app'        => $this->checkApp(),
        ];
    }

    protected function summarize(array $checks): array
    {
        $total = count($checks);
        $connected = count(array_filter($checks, fn($c) => $c['status'] === 'connected'));
        $errors = count(array_filter($checks, fn($c) => $c['status'] === 'error'));
        $notConfigured = count(array_filter($checks, fn($c) => $c['status'] === 'not_configured'));

        return compact('total', 'connected', 'errors', 'notConfigured');
    }

    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection('central')->getPdo();
            $ms = round((microtime(true) - $start) * 1000);
            $version = DB::connection('central')->selectOne('SELECT VERSION() as v')->v ?? 'Unknown';

            return [
                'name'    => 'MySQL Database',
                'icon'    => 'fas fa-database',
                'status'  => 'connected',
                'message' => "Connected ({$ms}ms)",
                'details' => [
                    'Host'    => config('database.connections.central.host'),
                    'DB'      => config('database.connections.central.database'),
                    'Version' => $version,
                    'Latency' => "{$ms}ms",
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'name'    => 'MySQL Database',
                'icon'    => 'fas fa-database',
                'status'  => 'error',
                'message' => 'Connection failed',
                'details' => ['Error' => $e->getMessage()],
            ];
        }
    }

    protected function checkCloudinary(): array
    {
        $url = config('cloudinary.cloud_url') ?: env('CLOUDINARY_URL');

        if (empty($url)) {
            return [
                'name'    => 'Cloudinary',
                'icon'    => 'fas fa-cloud-upload-alt',
                'status'  => 'not_configured',
                'message' => 'CLOUDINARY_URL not set',
                'details' => [],
            ];
        }

        try {
            $parsed = parse_url($url);
            $cloudName = $parsed['host'] ?? 'unknown';
            $apiKey = $parsed['user'] ?? '';
            $apiSecret = $parsed['pass'] ?? '';

            $start = microtime(true);
            $response = Http::timeout(8)
                ->withBasicAuth($apiKey, $apiSecret)
                ->get("https://api.cloudinary.com/v1_1/{$cloudName}/usage");
            $ms = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $usedPct = isset($data['credits']['usage'], $data['credits']['limit'])
                    ? round(($data['credits']['usage'] / $data['credits']['limit']) * 100, 1)
                    : null;

                return [
                    'name'    => 'Cloudinary',
                    'icon'    => 'fas fa-cloud-upload-alt',
                    'status'  => 'connected',
                    'message' => "Connected ({$ms}ms)",
                    'details' => [
                        'Cloud Name'   => $cloudName,
                        'Plan'         => $data['plan'] ?? 'N/A',
                        'Credits Used' => $usedPct !== null ? "{$usedPct}%" : 'N/A',
                        'Latency'      => "{$ms}ms",
                    ],
                ];
            }

            return [
                'name'    => 'Cloudinary',
                'icon'    => 'fas fa-cloud-upload-alt',
                'status'  => 'error',
                'message' => 'API returned ' . $response->status(),
                'details' => ['Cloud' => $cloudName, 'HTTP Status' => (string) $response->status()],
            ];
        } catch (\Throwable $e) {
            return [
                'name'    => 'Cloudinary',
                'icon'    => 'fas fa-cloud-upload-alt',
                'status'  => 'error',
                'message' => 'Connection failed',
                'details' => ['Error' => $e->getMessage()],
            ];
        }
    }

    protected function checkXendit(): array
    {
        $key = config('xendit.secret_key') ?: env('XENDIT_SECRET_KEY');

        if (empty($key)) {
            return [
                'name'    => 'Xendit',
                'icon'    => 'fas fa-money-bill-wave',
                'status'  => 'not_configured',
                'message' => 'XENDIT_SECRET_KEY not set (platform default)',
                'details' => ['Note' => 'Tenants configure their own keys'],
            ];
        }

        try {
            $start = microtime(true);
            $response = Http::timeout(8)
                ->withBasicAuth($key, '')
                ->get('https://api.xendit.co/balance');
            $ms = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return [
                    'name'    => 'Xendit',
                    'icon'    => 'fas fa-money-bill-wave',
                    'status'  => 'connected',
                    'message' => "Connected ({$ms}ms)",
                    'details' => [
                        'Mode'    => str_contains($key, 'development') ? 'Test / Development' : 'Production',
                        'Latency' => "{$ms}ms",
                    ],
                ];
            }

            return [
                'name'    => 'Xendit',
                'icon'    => 'fas fa-money-bill-wave',
                'status'  => 'error',
                'message' => 'Auth failed (HTTP ' . $response->status() . ')',
                'details' => ['HTTP Status' => (string) $response->status()],
            ];
        } catch (\Throwable $e) {
            return [
                'name'    => 'Xendit',
                'icon'    => 'fas fa-money-bill-wave',
                'status'  => 'error',
                'message' => 'Connection failed',
                'details' => ['Error' => $e->getMessage()],
            ];
        }
    }

    protected function checkStripe(): array
    {
        $key = config('services.stripe.secret') ?: env('STRIPE_SECRET');

        if (empty($key)) {
            return [
                'name'    => 'Stripe',
                'icon'    => 'fab fa-stripe',
                'status'  => 'not_configured',
                'message' => 'STRIPE_SECRET not set',
                'details' => [],
            ];
        }

        try {
            $start = microtime(true);
            $response = Http::timeout(8)
                ->withToken($key, 'Bearer')
                ->get('https://api.stripe.com/v1/balance');
            $ms = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'name'    => 'Stripe',
                    'icon'    => 'fab fa-stripe',
                    'status'  => 'connected',
                    'message' => "Connected ({$ms}ms)",
                    'details' => [
                        'Mode'    => ($data['livemode'] ?? false) ? 'Live' : 'Test',
                        'Latency' => "{$ms}ms",
                    ],
                ];
            }

            return [
                'name'    => 'Stripe',
                'icon'    => 'fab fa-stripe',
                'status'  => 'error',
                'message' => 'Auth failed (HTTP ' . $response->status() . ')',
                'details' => ['HTTP Status' => (string) $response->status()],
            ];
        } catch (\Throwable $e) {
            return [
                'name'    => 'Stripe',
                'icon'    => 'fab fa-stripe',
                'status'  => 'error',
                'message' => 'Connection failed',
                'details' => ['Error' => $e->getMessage()],
            ];
        }
    }

    protected function checkResend(): array
    {
        $key = config('services.resend.key') ?: env('RESEND_API_KEY');

        if (empty($key)) {
            return [
                'name'    => 'Resend (Email)',
                'icon'    => 'fas fa-envelope',
                'status'  => 'not_configured',
                'message' => 'RESEND_API_KEY not set',
                'details' => [],
            ];
        }

        try {
            $start = microtime(true);
            $response = Http::timeout(8)
                ->withToken($key, 'Bearer')
                ->get('https://api.resend.com/domains');
            $ms = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $domains = $response->json('data') ?? [];
                return [
                    'name'    => 'Resend (Email)',
                    'icon'    => 'fas fa-envelope',
                    'status'  => 'connected',
                    'message' => "Connected ({$ms}ms)",
                    'details' => [
                        'Domains' => (string) count($domains),
                        'Mailer'  => config('mail.default'),
                        'Latency' => "{$ms}ms",
                    ],
                ];
            }

            return [
                'name'    => 'Resend (Email)',
                'icon'    => 'fas fa-envelope',
                'status'  => 'error',
                'message' => 'Auth failed (HTTP ' . $response->status() . ')',
                'details' => ['HTTP Status' => (string) $response->status()],
            ];
        } catch (\Throwable $e) {
            return [
                'name'    => 'Resend (Email)',
                'icon'    => 'fas fa-envelope',
                'status'  => 'error',
                'message' => 'Connection failed',
                'details' => ['Error' => $e->getMessage()],
            ];
        }
    }

    protected function checkOpenAI(): array
    {
        $key = config('ai.openai_api_key') ?: env('OPENAI_API_KEY');

        if (empty($key)) {
            return [
                'name'    => 'AI Provider',
                'icon'    => 'fas fa-robot',
                'status'  => 'not_configured',
                'message' => 'OPENAI_API_KEY not set',
                'details' => [],
            ];
        }

        try {
            $baseUrl = rtrim(config('ai.openai_base_url', 'https://api.openai.com'), '/');
            $start = microtime(true);
            $response = Http::timeout(8)
                ->withToken($key, 'Bearer')
                ->get("{$baseUrl}/v1/models");
            $ms = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return [
                    'name'    => 'AI Provider',
                    'icon'    => 'fas fa-robot',
                    'status'  => 'connected',
                    'message' => "Connected ({$ms}ms)",
                    'details' => [
                        'Model'    => config('ai.openai_model', 'N/A'),
                        'Base URL' => $baseUrl,
                        'Latency'  => "{$ms}ms",
                    ],
                ];
            }

            return [
                'name'    => 'AI Provider',
                'icon'    => 'fas fa-robot',
                'status'  => 'error',
                'message' => 'Auth failed (HTTP ' . $response->status() . ')',
                'details' => ['HTTP Status' => (string) $response->status()],
            ];
        } catch (\Throwable $e) {
            return [
                'name'    => 'AI Provider',
                'icon'    => 'fas fa-robot',
                'status'  => 'error',
                'message' => 'Connection failed',
                'details' => ['Error' => $e->getMessage()],
            ];
        }
    }

    protected function checkMapbox(): array
    {
        $token = config('ai.mapbox_token') ?: env('MAPBOX_ACCESS_TOKEN');

        if (empty($token)) {
            return [
                'name'    => 'Mapbox',
                'icon'    => 'fas fa-map-marked-alt',
                'status'  => 'not_configured',
                'message' => 'MAPBOX_ACCESS_TOKEN not set',
                'details' => [],
            ];
        }

        try {
            $start = microtime(true);
            $response = Http::timeout(8)
                ->get("https://api.mapbox.com/tokens/v2?access_token={$token}");
            $ms = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return [
                    'name'    => 'Mapbox',
                    'icon'    => 'fas fa-map-marked-alt',
                    'status'  => 'connected',
                    'message' => "Connected ({$ms}ms)",
                    'details' => [
                        'Latency' => "{$ms}ms",
                    ],
                ];
            }

            return [
                'name'    => 'Mapbox',
                'icon'    => 'fas fa-map-marked-alt',
                'status'  => 'error',
                'message' => 'Token invalid (HTTP ' . $response->status() . ')',
                'details' => ['HTTP Status' => (string) $response->status()],
            ];
        } catch (\Throwable $e) {
            return [
                'name'    => 'Mapbox',
                'icon'    => 'fas fa-map-marked-alt',
                'status'  => 'error',
                'message' => 'Connection failed',
                'details' => ['Error' => $e->getMessage()],
            ];
        }
    }

    protected function checkApp(): array
    {
        return [
            'name'    => 'Application',
            'icon'    => 'fas fa-cogs',
            'status'  => 'connected',
            'message' => 'Running',
            'details' => [
                'Environment' => config('app.env'),
                'Debug'       => config('app.debug') ? 'ON ⚠️' : 'OFF',
                'URL'         => config('app.url'),
                'PHP'         => PHP_VERSION,
                'Laravel'     => app()->version(),
            ],
        ];
    }
}
