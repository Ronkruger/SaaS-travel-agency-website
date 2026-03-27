<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class DIYTourSession extends Model
{
    use HasFactory;

    protected $table = 'diy_tour_sessions';

    protected $fillable = [
        'user_id',
        'session_token',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function generateToken(): string
    {
        return 'diy_' . Str::random(64);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isOwnedBy(?int $userId, string $sessionToken): bool
    {
        if ($userId && $this->user_id === $userId) {
            return true;
        }

        return $this->session_token === $sessionToken;
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(DIYTourItinerary::class, 'session_id');
    }

    public function latestItinerary(): HasOne
    {
        return $this->hasOne(DIYTourItinerary::class, 'session_id')->latestOfMany();
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(DIYTourCollaborator::class, 'session_id');
    }
}
