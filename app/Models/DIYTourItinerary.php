<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DIYTourItinerary extends Model
{
    use HasFactory;

    protected $table = 'diy_tour_itineraries';

    protected $fillable = [
        'session_id',
        'tour_name',
        'user_preferences',
        'itinerary_data',
        'map_data',
        'pricing_data',
        'validation_results',
        'version',
    ];

    protected $casts = [
        'user_preferences'   => 'array',
        'itinerary_data'     => 'array',
        'map_data'           => 'array',
        'pricing_data'       => 'array',
        'validation_results' => 'array',
    ];

    // Relationships

    public function session(): BelongsTo
    {
        return $this->belongsTo(DIYTourSession::class, 'session_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(DIYTourCity::class, 'itinerary_id')->orderBy('sequence');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(DIYTourVote::class, 'itinerary_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(DIYTourQuote::class, 'itinerary_id');
    }

    public function latestQuote(): HasOne
    {
        return $this->hasOne(DIYTourQuote::class, 'itinerary_id')->latestOfMany();
    }

    // Helpers

    public function getTotalDays(): int
    {
        $prefs = $this->user_preferences ?? [];
        return (int) ($prefs['duration_days'] ?? 0);
    }

    public function getEstimatedTotal(): float
    {
        $pricing = $this->pricing_data ?? [];
        return (float) ($pricing['total'] ?? 0);
    }
}
