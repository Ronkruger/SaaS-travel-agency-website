<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Basic Info
        'title', 'slug', 'summary', 'short_description', 'line', 'continent',
        'duration_days', 'guaranteed_departure', 'booking_pdf_url',
        'video_url', 'facebook_post_url',
        // Pricing
        'regular_price_per_person', 'promo_price_per_person', 'base_price_per_day',
        'is_sale_enabled', 'sale_end_date',
        // Travel Details
        'travel_window', 'departure_dates',
        // Content
        'highlights', 'main_image', 'gallery_images', 'related_images', 'video_file',
        // Itinerary
        'itinerary',
        // Stops & Geography
        'full_stops', 'additional_info',
        // Booking
        'booking_links', 'allows_downpayment', 'fixed_downpayment_amount',
        'balance_due_days_before_travel',
        // Optional Tours & Freebies
        'optional_tours', 'cash_freebies',
        // Status
        'is_active', 'is_featured', 'average_rating', 'total_reviews', 'total_bookings',
    ];

    protected $casts = [
        'travel_window'         => 'array',
        'departure_dates'       => 'array',
        'highlights'            => 'array',
        'gallery_images'        => 'array',
        'related_images'        => 'array',
        'itinerary'             => 'array',
        'full_stops'            => 'array',
        'additional_info'       => 'array',
        'booking_links'         => 'array',
        'optional_tours'        => 'array',
        'cash_freebies'         => 'array',
        'guaranteed_departure'  => 'boolean',
        'is_sale_enabled'       => 'boolean',
        'allows_downpayment'    => 'boolean',
        'is_featured'           => 'boolean',
        'is_active'             => 'boolean',
        'regular_price_per_person'     => 'decimal:2',
        'promo_price_per_person'       => 'decimal:2',
        'base_price_per_day'           => 'decimal:2',
        'fixed_downpayment_amount'     => 'decimal:2',
        'average_rating'               => 'decimal:2',
        'sale_end_date'                => 'date',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function allReviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    // ── Route model binding ──────────────────────────────────────────────────

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ── Computed attributes ──────────────────────────────────────────────────

    /** The display price: promo if available, else regular */
    public function getEffectivePriceAttribute(): ?float
    {
        return $this->promo_price_per_person ?? $this->regular_price_per_person;
    }

    /** Discount % between regular and promo */
    public function getDiscountPercentAttribute(): int
    {
        if ($this->promo_price_per_person && $this->regular_price_per_person > 0) {
            return (int) round((($this->regular_price_per_person - $this->promo_price_per_person) / $this->regular_price_per_person) * 100);
        }
        return 0;
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    public function updateRating(): void
    {
        $this->average_rating = $this->reviews()->avg('rating') ?? 0;
        $this->total_reviews  = $this->reviews()->count();
        $this->save();
    }
}
