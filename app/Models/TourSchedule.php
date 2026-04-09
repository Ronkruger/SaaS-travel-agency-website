<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TourSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'departure_date',
        'return_date',
        'available_seats',
        'booked_seats',
        'price_override',
        'status',
        'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'price_override' => 'decimal:2',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }

    public function getRemainingSeatsAttribute(): int
    {
        return $this->available_seats - $this->booked_seats;
    }

    public function isSoldOut(): bool
    {
        return $this->remaining_seats <= 0;
    }
}
