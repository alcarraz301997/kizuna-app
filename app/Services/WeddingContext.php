<?php

namespace App\Services;

use App\Models\Wedding;
use Illuminate\Http\Request;

class WeddingContext
{
    public function authorize(Request $request, Wedding $wedding): Wedding
    {
        abort_unless($wedding->members()->where('user_id', $request->user()->id)->exists(), 403);

        return $wedding;
    }

    public function isOwner(Request $request, Wedding $wedding): bool
    {
        return $wedding->members()->where('user_id', $request->user()->id)->where('role', 'owner')->exists();
    }
}
