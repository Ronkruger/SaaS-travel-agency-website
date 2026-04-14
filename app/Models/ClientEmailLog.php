<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientEmailLog extends Model
{
    protected $fillable = [
        'to_email',
        'to_name',
        'subject',
        'mail_class',
        'status',
        'booking_id',
        'booking_number',
        'error_message',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
