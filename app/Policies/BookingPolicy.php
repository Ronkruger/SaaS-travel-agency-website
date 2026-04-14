<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    private function userOwnsBooking(User $user, Booking $booking): bool
    {
        if ($user->id === $booking->user_id) {
            return true;
        }

        // Allow access to imported bookings (no user_id) where the contact name matches
        if ($booking->user_id === null && $booking->contact_name !== null) {
            return strtolower(trim($booking->contact_name)) === strtolower(trim($user->name));
        }

        return false;
    }

    /**
     * Determine if the user can view the booking.
     */
    public function view(User|AdminUser $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->userOwnsBooking($user, $booking);
    }

    /**
     * Determine if the user can update the booking.
     */
    public function update(User|AdminUser $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->userOwnsBooking($user, $booking);
    }

    /**
     * Determine if the user can cancel the booking.
     */
    public function cancel(User|AdminUser $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->userOwnsBooking($user, $booking);
    }
}
