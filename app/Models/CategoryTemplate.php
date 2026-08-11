<?php

namespace App\Models;

use Database\Factories\CategoryTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryTemplate extends Model
{
    /** @use HasFactory<CategoryTemplateFactory> */
    use HasFactory;

    protected $fillable = ['wedding_id', 'name'];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CategoryTemplateItem::class);
    }
}
