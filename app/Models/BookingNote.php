<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingNote extends Model
{
    protected $fillable = [
        'booking_id',
        'admin_user_id',
        'note',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }
}
