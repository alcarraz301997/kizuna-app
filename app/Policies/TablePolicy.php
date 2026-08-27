<?php

namespace App\Policies;

use App\Models\Table;
use App\Models\User;

class TablePolicy
{
    /**
     * Owners and editors can create tables.
     */
    public function create(User $user, Table $table): bool
    {
        return $table->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Owners and editors can update tables.
     */
    public function update(User $user, Table $table): bool
    {
        return $table->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Only owners can delete tables.
     */
    public function delete(User $user, Table $table): bool
    {
        return $table->wedding->members()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
