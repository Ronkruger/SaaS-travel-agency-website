<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Determine if the user can delete the review.
     */
    public function delete(User|AdminUser $user, Review $review): bool
    {
        // Admin can delete any review
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only delete their own reviews
        return $user->id === $review->user_id;
    }

    /**
     * Determine if the user can approve the review.
     */
    public function approve(User|AdminUser $user, Review $review): bool
    {
        return $user->isAdmin();
    }
}
