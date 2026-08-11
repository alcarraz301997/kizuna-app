<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Wedding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BudgetMetrics
{
    public function forecast(Wedding $wedding): array
    {
        $expenses = $wedding->expenses()
            ->whereNotNull('contracted_amount')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get(['id', 'contracted_amount', 'due_date']);
        $payments = $this->paymentTotals($expenses->pluck('id'));
        $dated = collect();
        $unscheduled = collect();

        foreach ($expenses as $expense) {
            $paid = (float) ($payments[$expense->id] ?? 0);
            $item = [
                'expense_id' => $expense->id,
                'due_date' => $expense->due_date?->format('Y-m-d'),
                'contracted' => (float) $expense->contracted_amount,
                'paid_to_date' => $paid,
                'balance' => round((float) $expense->contracted_amount - $paid, 2),
                'state' => $expense->due_date === null
                    ? 'unscheduled'
                    : ($expense->due_date->isPast() ? 'past_due' : 'scheduled'),
            ];

            ($expense->due_date === null ? $unscheduled : $dated)->push($item);
        }

        $contracted = (float) $expenses->sum(fn ($expense) => (float) $expense->contracted_amount);
        $paid = (float) $payments->sum();

        return [
            'dated' => $dated,
            'unscheduled' => $unscheduled,
            'totals' => [
                'contracted' => $contracted,
                'paid_to_date' => $paid,
                'balance' => round($contracted - $paid, 2),
            ],
        ];
    }

    public function variance(Wedding $wedding): Collection
    {
        $categories = Category::query()
            ->where('wedding_id', $wedding->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id', 'sort_order']);
        $expenses = $wedding->expenses()
            ->select('category_id', DB::raw('COUNT(*) as expense_count'), DB::raw('SUM(planned_amount) as planned'), DB::raw('SUM(contracted_amount) as contracted'))
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');
        $payments = $this->paymentTotalsForCategories($wedding);

        return $categories->map(function (Category $category) use ($expenses, $payments): array {
            $totals = $expenses->get($category->id);
            $planned = $totals?->planned === null ? null : (float) $totals->planned;
            $contracted = $totals?->contracted === null ? null : (float) $totals->contracted;
            $paid = $totals === null ? null : (float) ($payments[$category->id] ?? 0);
            $commitmentVariance = $planned === null || $contracted === null ? null : round($contracted - $planned, 2);
            $paidVariance = $planned === null || $paid === null ? null : round($paid - $planned, 2);
            $alerts = [];

            if ($commitmentVariance !== null && $commitmentVariance > 0) {
                $alerts[] = 'commitment_over_budget';
            }
            if ($paidVariance !== null && $paidVariance > 0) {
                $alerts[] = 'paid_over_budget';
            }

            return [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'sort_order' => $category->sort_order,
                'planned' => $planned,
                'contracted' => $contracted,
                'paid' => $paid,
                'commitment_variance' => $commitmentVariance,
                'paid_variance' => $paidVariance,
                'alerts' => $alerts,
            ];
        });
    }

    private function paymentTotals(Collection $expenseIds): Collection
    {
        if ($expenseIds->isEmpty()) {
            return collect();
        }

        return DB::table('expense_payments')
            ->whereIn('expense_id', $expenseIds)
            ->select('expense_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_id')
            ->pluck('total', 'expense_id')
            ->map(fn ($total) => (float) $total);
    }

    private function paymentTotalsForCategories(Wedding $wedding): Collection
    {
        return DB::table('expense_payments')
            ->join('expenses', 'expenses.id', '=', 'expense_payments.expense_id')
            ->where('expenses.wedding_id', $wedding->id)
            ->select('expenses.category_id', DB::raw('SUM(expense_payments.amount) as total'))
            ->groupBy('expenses.category_id')
            ->pluck('total', 'category_id')
            ->map(fn ($total) => (float) $total);
    }
}
