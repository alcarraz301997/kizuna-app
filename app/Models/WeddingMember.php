<?php

namespace App\Models;

use Database\Factories\WeddingMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingMember extends Model
{
    /** @use HasFactory<WeddingMemberFactory> */
    use HasFactory;

    protected $fillable = ['wedding_id', 'user_id', 'role'];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
