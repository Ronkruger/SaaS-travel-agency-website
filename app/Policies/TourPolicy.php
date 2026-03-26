<?php

namespace App\Policies;

use App\Models\Tour;
use App\Models\User;

class TourPolicy
{
    /**
     * Determine if the user can view the tour listing (admin).
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view the tour.
     */
    public function view(?User $user, Tour $tour): bool
    {
        // Public tours can be viewed by anyone
        if ($tour->is_active) {
            return true;
        }

        // Only admins can view inactive tours
        return $user && $user->isAdmin();
    }

    /**
     * Determine if the user can create tours.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update the tour.
     */
    public function update(User $user, Tour $tour): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete the tour.
     */
    public function delete(User $user, Tour $tour): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can restore the tour.
     */
    public function restore(User $user, Tour $tour): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can toggle wishlist for the tour.
     */
    public function wishlist(User $user, Tour $tour): bool
    {
        // Can only wishlist active tours
        return $tour->is_active;
    }
}
