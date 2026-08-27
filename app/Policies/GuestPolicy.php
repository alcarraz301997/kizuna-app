<?php

namespace App\Policies;

use App\Models\Guest;
use App\Models\User;

class GuestPolicy
{
    /**
     * Owners and editors can create guests.
     */
    public function create(User $user, Guest $guest): bool
    {
        return $guest->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Owners and editors can update guests.
     */
    public function update(User $user, Guest $guest): bool
    {
        return $guest->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Only owners can delete guests.
     */
    public function delete(User $user, Guest $guest): bool
    {
        return $guest->wedding->members()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
