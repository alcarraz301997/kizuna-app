<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wedding;

class WeddingPolicy
{
    /**
     * Check if user is a member of the wedding (any role).
     */
    public function view(User $user, Wedding $wedding): bool
    {
        return $wedding->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Only the owner can manage members (invite, remove).
     */
    public function manageMembers(User $user, Wedding $wedding): bool
    {
        return $wedding->members()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }

    /**
     * Only the owner can delete the wedding workspace.
     */
    public function delete(User $user, Wedding $wedding): bool
    {
        return $wedding->members()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
