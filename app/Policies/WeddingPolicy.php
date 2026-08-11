<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wedding;

class WeddingPolicy
{
    public function view(User $user, Wedding $wedding): bool
    {
        return $wedding->members()->where('user_id', $user->id)->exists();
    }

    public function manageMembers(User $user, Wedding $wedding): bool
    {
        return $wedding->members()->where('user_id', $user->id)->where('role', 'owner')->exists();
    }
}
