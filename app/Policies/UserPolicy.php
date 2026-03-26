<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view the target user's profile.
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        // Admin can view any user
        if ($currentUser->isAdmin()) {
            return true;
        }

        // Users can only view their own profile
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Determine if the user can update the target user.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        // Admin can update any user
        if ($currentUser->isAdmin()) {
            return true;
        }

        // Users can only update their own profile
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Determine if the user can delete the target user.
     */
    public function delete(User $currentUser, User $targetUser): bool
    {
        // Only admins can delete users
        if (!$currentUser->isAdmin()) {
            return false;
        }

        // Cannot delete yourself
        return $currentUser->id !== $targetUser->id;
    }

    /**
     * Determine if the user can view any user listing.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
