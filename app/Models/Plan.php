<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'monthly_price',
        'yearly_price',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
        'max_tours',
        'max_bookings_per_month',
        'max_admin_users',
        'has_diy_builder',
        'has_custom_domain',
        'has_api_access',
        'has_advanced_reports',
        'has_email_marketing',
        'has_priority_support',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'max_tours' => 'integer',
        'max_bookings_per_month' => 'integer',
        'max_admin_users' => 'integer',
        'has_diy_builder' => 'boolean',
        'has_custom_domain' => 'boolean',
        'has_api_access' => 'boolean',
        'has_advanced_reports' => 'boolean',
        'has_email_marketing' => 'boolean',
        'has_priority_support' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'plan', 'slug');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // Helpers
    public function formattedMonthlyPrice(): string
    {
        return '$' . number_format($this->monthly_price, 2);
    }

    public function formattedYearlyPrice(): string
    {
        return '$' . number_format($this->yearly_price, 2);
    }
}
