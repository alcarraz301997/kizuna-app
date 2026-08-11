<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Wedding;
use Illuminate\Support\Facades\DB;

class ExpenseCommitmentService
{
    public function forWedding(Wedding $wedding, Expense $expense): Expense
    {
        abort_unless($expense->wedding_id === $wedding->id, 404);

        return $expense;
    }

    public function update(Expense $expense, array $attributes): Expense
    {
        return DB::transaction(function () use ($expense, $attributes): Expense {
            $expense->update($attributes);

            return $expense->refresh();
        });
    }

    public function addPayment(Expense $expense, array $attributes): Expense
    {
        return DB::transaction(function () use ($expense, $attributes): Expense {
            $expense->payments()->create([
                'amount' => $attributes['amount'],
                'paid_on' => $attributes['paid_on'] ?? null,
                'kind' => $attributes['kind'],
                'origin' => 'manual',
                'legacy_key' => null,
            ]);

            return $expense->refresh();
        });
    }

    public function summary(Expense $expense): array
    {
        $paid = (float) $expense->payments()->sum('amount');
        $contracted = $expense->contracted_amount === null ? null : (float) $expense->contracted_amount;

        return [
            'id' => $expense->id,
            'planned_amount' => $expense->planned_amount === null ? null : (float) $expense->planned_amount,
            'contracted_amount' => $contracted,
            'paid_to_date' => $paid,
            'balance' => $contracted === null ? null : round($contracted - $paid, 2),
            'due_date' => $expense->due_date?->format('Y-m-d'),
            'status' => $expense->status->value,
            'vendor_id' => $expense->vendor_id,
            'receipts_count' => $expense->receipts()->count(),
            'has_split' => $expense->split()->exists(),
        ];
    }
}
