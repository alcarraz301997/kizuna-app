<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Wedding;
use App\Services\WeddingContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class ExpenseSplitController extends Controller
{
    /**
     * Store a new split for the given expense.
     */
    public function store(Request $request, Wedding $wedding, Expense $expense, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeExpense($wedding, $expense);

        if ($expense->split()->exists()) {
            return Redirect::route('weddings.expenses.edit', [$wedding, $expense])
                ->with('error', 'Este gasto ya tiene un split asignado.');
        }

        $validated = $this->validateSplit($request, $expense);
        $amounts = $this->calculateSplitAmounts($validated['split_type'], (float) $expense->amount, $validated);

        $expense->split()->create([
            'split_type' => $validated['split_type'],
            'person_a_label' => $validated['person_a_label'],
            'person_a_amount' => $amounts['person_a_amount'],
            'person_b_label' => $validated['person_b_label'],
            'person_b_amount' => $amounts['person_b_amount'],
        ]);

        return Redirect::route('weddings.expenses.edit', [$wedding, $expense])
            ->with('success', 'Split creado correctamente.');
    }

    /**
     * Update the existing split for the given expense.
     */
    public function update(Request $request, Wedding $wedding, Expense $expense, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeExpense($wedding, $expense);

        $split = $expense->split;
        if (! $split) {
            abort(404);
        }

        $validated = $this->validateSplit($request, $expense);
        $amounts = $this->calculateSplitAmounts($validated['split_type'], (float) $expense->amount, $validated);

        $split->update([
            'split_type' => $validated['split_type'],
            'person_a_label' => $validated['person_a_label'],
            'person_a_amount' => $amounts['person_a_amount'],
            'person_b_label' => $validated['person_b_label'],
            'person_b_amount' => $amounts['person_b_amount'],
        ]);

        return Redirect::route('weddings.expenses.edit', [$wedding, $expense])
            ->with('success', 'Split actualizado correctamente.');
    }

    /**
     * Validate the split request data.
     */
    private function validateSplit(Request $request, Expense $expense): array
    {
        $rules = [
            'split_type' => ['required', 'string', 'in:50_50,percent,fixed'],
            'person_a_label' => ['required', 'string', 'max:255'],
            'person_b_label' => ['required', 'string', 'max:255'],
        ];

        if ($request->input('split_type') === 'percent') {
            $rules['percent_a'] = ['required', 'numeric', 'min:0', 'max:100'];
        }

        if ($request->input('split_type') === 'fixed') {
            $rules['person_a_amount'] = ['required', 'numeric', 'min:0'];
            $rules['person_b_amount'] = ['required', 'numeric', 'min:0'];
        }

        $validated = $request->validate($rules);

        // Validate sum equals expense amount for fixed type (tolerance 0.01)
        if ($validated['split_type'] === 'fixed') {
            $sum = round((float) $validated['person_a_amount'] + (float) $validated['person_b_amount'], 2);
            $expected = round((float) $expense->amount, 2);

            if (abs($sum - $expected) > 0.01) {
                throw ValidationException::withMessages([
                    'person_a_amount' => ['Los montos no suman el total del gasto.'],
                    'person_b_amount' => ['Los montos no suman el total del gasto.'],
                ]);
            }
        }

        return $validated;
    }

    /**
     * Calculate split amounts based on type.
     */
    private function calculateSplitAmounts(string $splitType, float $amount, array $validated): array
    {
        $amount = round($amount, 2);

        switch ($splitType) {
            case '50_50':
                $personA = round($amount / 2, 2);
                $personB = round($amount - $personA, 2);
                break;

            case 'percent':
                $percentA = (float) ($validated['percent_a'] ?? 50);
                $personA = round($amount * ($percentA / 100), 2);
                $personB = round($amount - $personA, 2);
                break;

            case 'fixed':
                $personA = round((float) $validated['person_a_amount'], 2);
                $personB = round((float) $validated['person_b_amount'], 2);
                break;

            default:
                $personA = round($amount / 2, 2);
                $personB = round($amount - $personA, 2);
        }

        // Ensure non-negative
        if ($personB < 0) {
            $personB = 0.00;
        }

        return [
            'person_a_amount' => $personA,
            'person_b_amount' => $personB,
        ];
    }

    /**
     * Ensure the expense belongs to the wedding.
     */
    private function authorizeExpense(Wedding $wedding, Expense $expense): void
    {
        if ($expense->wedding_id !== $wedding->id) {
            abort(403);
        }
    }
}
