<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Owners and editors can create expenses.
     */
    public function create(User $user, Expense $expense): bool
    {
        return $expense->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Owners and editors can update expenses.
     */
    public function update(User $user, Expense $expense): bool
    {
        return $expense->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Only owners can delete expenses.
     */
    public function delete(User $user, Expense $expense): bool
    {
        return $expense->wedding->members()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
