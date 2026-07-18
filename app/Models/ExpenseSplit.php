<?php

namespace App\Models;

use App\Enums\SplitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'split_type',
        'person_a_label',
        'person_a_amount',
        'person_b_label',
        'person_b_amount',
    ];

    protected function casts(): array
    {
        return [
            'split_type' => SplitType::class,
            'person_a_amount' => 'decimal:2',
            'person_b_amount' => 'decimal:2',
        ];
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
