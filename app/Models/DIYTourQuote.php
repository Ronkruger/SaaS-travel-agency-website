<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DIYTourQuote extends Model
{
    use HasFactory;

    protected $table = 'diy_tour_quotes';

    protected $fillable = [
        'itinerary_id',
        'quoted_price_php',
        'valid_until',
        'terms_conditions',
        'generated_by',
        'status',
    ];

    protected $casts = [
        'quoted_price_php' => 'decimal:2',
        'valid_until'      => 'date',
    ];

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(DIYTourItinerary::class, 'itinerary_id');
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }
}
