<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Guests assigned to this table.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class, 'table_id');
    }

    /**
     * Number of guests currently assigned to this table.
     */
    public function getOccupancyCountAttribute(): int
    {
        if ($this->relationLoaded('guests')) {
            return $this->guests->count();
        }

        return $this->guests()->count();
    }

    /**
     * How many spots are still available at this table.
     */
    public function getAvailableSpotsAttribute(): int
    {
        return max(0, $this->capacity - $this->occupancy_count);
    }

    /**
     * Whether another guest can be assigned to this table.
     */
    public function canAssignGuest(): bool
    {
        return $this->occupancy_count < $this->capacity;
    }
}
