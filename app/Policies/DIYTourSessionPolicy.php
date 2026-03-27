<?php

namespace App\Policies;

use App\Models\DIYTourSession;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DIYTourSessionPolicy
{
    use HandlesAuthorization;

    /**
     * Any authenticated or guest user can create a new DIY session.
     * (Guest access is controlled at the route level via session_token.)
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * The session owner, a collaborator, or an admin may view the session.
     */
    public function view(?User $user, DIYTourSession $session): bool
    {
        if ($user && $user->isAdmin()) return true;

        if ($user && $session->user_id === $user->id) return true;

        // Collaborator check
        if ($user && $session->collaborators()->where('user_id', $user->id)->exists()) return true;

        // Guest access via session token is handled by the controller
        return false;
    }

    /**
     * Only the owner or an admin may update.
     */
    public function update(?User $user, DIYTourSession $session): bool
    {
        if ($user && $user->isAdmin()) return true;
        if ($user && $session->user_id === $user->id) return true;

        // Editor collaborators may update
        if ($user) {
            return $session->collaborators()
                ->where('user_id', $user->id)
                ->where('permission_level', 'edit')
                ->exists();
        }

        return false;
    }

    /**
     * Only the owner or admin may delete.
     */
    public function delete(?User $user, DIYTourSession $session): bool
    {
        if ($user && $user->isAdmin()) return true;
        return $user && $session->user_id === $user->id;
    }
}
