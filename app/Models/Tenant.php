<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $casts = [
        'data' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'activated_at' => 'datetime',
        'trial_activated' => 'boolean',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'password',
            'activation_token',
            'activated_at',
            'trial_activated',
            'plan',
            'trial_ends_at',
            'subscription_ends_at',
            'stripe_customer_id',
            'stripe_subscription_id',
            'is_active',
            'logo_url',
            'company_name',
            'company_phone',
            'company_address',
        ];
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->isOnTrial()) {
            return true;
        }

        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    public function isOnPlan(string $plan): bool
    {
        return $this->plan === $plan;
    }

    public function subscriptionStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->isOnTrial()) {
            return 'trial';
        }

        if ($this->hasActiveSubscription()) {
            return 'active';
        }

        return 'expired';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('subscription_ends_at')
                    ->orWhere('subscription_ends_at', '<', now());
            })
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')
                    ->orWhere('trial_ends_at', '<', now());
            });
    }
}
