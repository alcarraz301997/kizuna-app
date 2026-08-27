<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Owners and editors can create categories.
     */
    public function create(User $user, Category $category): bool
    {
        return $category->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Owners and editors can update categories.
     */
    public function update(User $user, Category $category): bool
    {
        return $category->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Only owners can delete categories.
     */
    public function delete(User $user, Category $category): bool
    {
        return $category->wedding->members()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
