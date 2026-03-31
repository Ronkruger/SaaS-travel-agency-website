<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine if the user can view the booking.
     */
    public function view(User|AdminUser $user, Booking $booking): bool
    {
        // Admin can view any booking
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only view their own bookings
        return $user->id === $booking->user_id;
    }

    /**
     * Determine if the user can update the booking.
     */
    public function update(User|AdminUser $user, Booking $booking): bool
    {
        // Admin can update any booking
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only update their own bookings
        return $user->id === $booking->user_id;
    }

    /**
     * Determine if the user can cancel the booking.
     */
    public function cancel(User|AdminUser $user, Booking $booking): bool
    {
        // Admin can cancel any booking
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only cancel their own bookings
        return $user->id === $booking->user_id;
    }
}
