<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'user_id',
        'tour_id',
        'schedule_id',
        'tour_date',
        'adults',
        'children',
        'infants',
        'total_guests',
        'price_per_adult',
        'price_per_child',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'coupon_code',
        'status',
        'payment_status',
        'xendit_invoice_id',
        'special_requests',
        'traveler_details',
        'contact_name',
        'contact_email',
        'contact_phone',
        'payment_method',
        'installment_months',
        'downpayment_amount',
        'installment_schedule',
        'second_payment_status',
    ];

    protected $casts = [
        'tour_date' => 'date',
        'traveler_details' => 'array',
        'subtotal'              => 'decimal:2',
        'discount_amount'       => 'decimal:2',
        'tax_amount'            => 'decimal:2',
        'total_amount'          => 'decimal:2',
        'downpayment_amount'    => 'decimal:2',
        'installment_schedule'  => 'array',
    ];

    public static function generateBookingNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'DG-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function schedule()
    {
        return $this->belongsTo(TourSchedule::class, 'schedule_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
}
