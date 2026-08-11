<?php

namespace App\Models;

use Database\Factories\CategoryTemplateItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryTemplateItem extends Model
{
    /** @use HasFactory<CategoryTemplateItemFactory> */
    use HasFactory;

    protected $fillable = ['category_template_id', 'parent_id', 'name', 'budget_limit', 'color', 'sort_order'];

    protected function casts(): array
    {
        return ['budget_limit' => 'decimal:2', 'sort_order' => 'integer'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CategoryTemplate::class, 'category_template_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
