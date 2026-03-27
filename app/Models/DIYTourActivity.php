<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DIYTourActivity extends Model
{
    use HasFactory;

    protected $table = 'diy_tour_activities';

    protected $fillable = [
        'city_id',
        'day_number',
        'activity_name',
        'category',
        'duration_hours',
        'is_included',
        'cost_php',
        'time_of_day',
        'booking_required',
    ];

    protected $casts = [
        'duration_hours'  => 'decimal:2',
        'is_included'     => 'boolean',
        'cost_php'        => 'decimal:2',
        'booking_required'=> 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(DIYTourCity::class, 'city_id');
    }
}
