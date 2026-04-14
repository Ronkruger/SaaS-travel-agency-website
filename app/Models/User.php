<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'address',
        'city',
        'state',
        'country',
        'auth0_id',
    ];

    // Explicitly block mass assignment of privilege fields
    protected $guarded = ['role', 'email_verified_at'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishedTours()
    {
        return $this->belongsToMany(Tour::class, 'wishlists');
    }

    public function travelFunds()
    {
        return $this->hasMany(TravelFund::class);
    }

    public function travelFundBalance(): float
    {
        $credits = $this->travelFunds()->where('type', 'credit')->sum('amount');
        $debits  = $this->travelFunds()->where('type', 'debit')->sum('amount');
        return (float) bcsub($credits, $debits, 2);
    }
}
