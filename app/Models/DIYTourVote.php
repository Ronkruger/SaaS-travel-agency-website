<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DIYTourVote extends Model
{
    use HasFactory;

    protected $table = 'diy_tour_votes';

    protected $fillable = [
        'itinerary_id',
        'user_id',
        'vote_type',
        'item_id',
        'vote_value',
    ];

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(DIYTourItinerary::class, 'itinerary_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
