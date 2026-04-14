<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_spend',
        'usage_limit',
        'used_count',
        'expires_at',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'value'       => 'decimal:2',
        'min_spend'   => 'decimal:2',
        'expires_at'  => 'date',
        'is_active'   => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'coupon_code', 'code');
    }

    public function isValid(float $subtotal): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->lt(Carbon::today())) return false;
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) return false;
        if ($subtotal < (float) $this->min_spend) return false;
        return true;
    }

    /**
     * Calculate discount amount for a given subtotal.
     */
    public function discountFor(float $subtotal): float
    {
        if ($this->type === 'percent') {
            return round($subtotal * ($this->value / 100), 2);
        }
        return min((float) $this->value, $subtotal);
    }
}
