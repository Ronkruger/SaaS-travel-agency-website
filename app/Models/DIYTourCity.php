<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DIYTourCity extends Model
{
    use HasFactory;

    protected $table = 'diy_tour_cities';

    protected $fillable = [
        'itinerary_id',
        'sequence',
        'city_name',
        'country',
        'latitude',
        'longitude',
        'day_start',
        'day_end',
        'duration_days',
        'hotel_tier',
        'estimated_cost_php',
    ];

    protected $casts = [
        'latitude'           => 'decimal:8',
        'longitude'          => 'decimal:8',
        'estimated_cost_php' => 'decimal:2',
    ];

    // Relationships

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(DIYTourItinerary::class, 'itinerary_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(DIYTourActivity::class, 'city_id')->orderBy('time_of_day');
    }
}
