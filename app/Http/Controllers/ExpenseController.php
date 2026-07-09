<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseStatus;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the expenses with optional category filter.
     */
    public function index(Request $request): Response
    {
        $query = $request->user()->expenses()->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $expenses = $query->orderByDesc('created_at')->get()->map(fn (Expense $e) => [
            'id' => $e->id,
            'amount' => (float) $e->amount,
            'vendor' => $e->vendor,
            'status' => $e->status->value,
            'paid_date' => $e->paid_date?->format('Y-m-d'),
            'notes' => $e->notes,
            'category' => [
                'id' => $e->category->id,
                'name' => $e->category->name,
                'color' => $e->category->color,
            ],
            'created_at' => $e->created_at->format('Y-m-d'),
        ]);

        $categories = $request->user()->categories()->orderBy('name')->get()->map(fn (Category $c) => [
            'id' => $c->id,
            'name' => $c->name,
        ]);

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'categories' => $categories,
            'filters' => [
                'category_id' => $request->category_id,
            ],
        ]);
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create(Request $request): Response
    {
        $categories = $request->user()->categories()->orderBy('name')->get()->map(fn (Category $c) => [
            'id' => $c->id,
            'name' => $c->name,
        ]);

        return Inertia::render('Expenses/Create', [
            'categories' => $categories,
            'statuses' => collect(ExpenseStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => ucfirst($s->value),
            ]),
        ]);
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:planned,contracted,paid'],
            'paid_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure category belongs to the authenticated user
        $category = $request->user()->categories()->find($validated['category_id']);
        if (! $category) {
            abort(403, 'Category does not belong to you.');
        }

        $validated['user_id'] = $request->user()->id;

        $request->user()->expenses()->create($validated);

        return Redirect::route('expenses.index');
    }

    /**
     * Show the form for editing the expense.
     */
    public function edit(Request $request, Expense $expense): Response
    {
        $this->authorizeExpense($request, $expense);

        $categories = $request->user()->categories()->orderBy('name')->get()->map(fn (Category $c) => [
            'id' => $c->id,
            'name' => $c->name,
        ]);

        return Inertia::render('Expenses/Edit', [
            'expense' => [
                'id' => $expense->id,
                'category_id' => $expense->category_id,
                'amount' => (float) $expense->amount,
                'vendor' => $expense->vendor,
                'status' => $expense->status->value,
                'paid_date' => $expense->paid_date?->format('Y-m-d'),
                'notes' => $expense->notes,
            ],
            'categories' => $categories,
            'statuses' => collect(ExpenseStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => ucfirst($s->value),
            ]),
        ]);
    }

    /**
     * Update the expense.
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($request, $expense);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:planned,contracted,paid'],
            'paid_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure category belongs to the authenticated user
        $category = $request->user()->categories()->find($validated['category_id']);
        if (! $category) {
            abort(403, 'Category does not belong to you.');
        }

        $expense->update($validated);

        return Redirect::route('expenses.index');
    }

    /**
     * Remove the expense.
     */
    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($request, $expense);

        $expense->delete();

        return Redirect::route('expenses.index');
    }

    /**
     * Ensure the expense belongs to the authenticated user.
     */
    private function authorizeExpense(Request $request, Expense $expense): void
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
