<?php

namespace App\Policies;

use App\Models\Vendor;
use App\Models\User;

class VendorPolicy
{
    /**
     * Owners and editors can create vendors.
     */
    public function create(User $user, Vendor $vendor): bool
    {
        return $vendor->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Owners and editors can update vendors.
     */
    public function update(User $user, Vendor $vendor): bool
    {
        return $vendor->wedding->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists();
    }

    /**
     * Only owners can delete vendors.
     */
    public function delete(User $user, Vendor $vendor): bool
    {
        return $vendor->wedding->members()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
