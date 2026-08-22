<?php

namespace App\Services;

use App\Models\Wedding;
use Illuminate\Http\Request;

class WeddingContext
{
    public function authorize(Request $request, Wedding $wedding): Wedding
    {
        $userId = $request->user()?->id;

        if (! $userId) {
            abort(403);
        }

        $isMember = $wedding->members()->where('user_id', $userId)->exists()
            || $wedding->owner_id === $userId;

        if ($wedding->owner_id === $userId && ! $wedding->members()->where('user_id', $userId)->exists()) {
            $wedding->members()->firstOrCreate(['user_id' => $userId], ['role' => 'owner']);
            $isMember = true;
        }

        abort_unless($isMember, 403);

        return $wedding;
    }

    public function isOwner(Request $request, Wedding $wedding): bool
    {
        $userId = $request->user()?->id;

        if (! $userId) {
            return false;
        }

        return $wedding->owner_id === $userId
            || $wedding->members()->where('user_id', $userId)->where('role', 'owner')->exists();
    }
}
