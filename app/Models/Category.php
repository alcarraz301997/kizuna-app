<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'budget_limit',
        'color',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'budget_limit' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Sum of contracted + paid expense amounts.
     */
    public function getSpentAttribute(): float
    {
        return (float) $this->expenses()
            ->whereIn('status', ['contracted', 'paid'])
            ->sum('amount');
    }

    /**
     * Sum of planned expense amounts.
     */
    public function getPlannedAttribute(): float
    {
        return (float) $this->expenses()
            ->where('status', 'planned')
            ->sum('amount');
    }

    /**
     * Budget remaining (budget_limit - spent).
     */
    public function getRemainingAttribute(): float
    {
        return (float) $this->budget_limit - $this->spent;
    }

    /**
     * Progress percentage (spent / budget_limit * 100).
     */
    public function getProgressAttribute(): float
    {
        if ((float) $this->budget_limit === 0.0) {
            return 0.0;
        }

        return round(($this->spent / (float) $this->budget_limit) * 100, 1);
    }
}
