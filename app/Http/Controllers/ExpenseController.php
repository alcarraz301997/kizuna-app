<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseStatus;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Vendor;
use App\Services\ExpenseCommitmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

        $vendors = $request->user()->vendors()->orderBy('name')->get()->map(fn (Vendor $v) => [
            'id' => $v->id,
            'name' => $v->name,
        ]);

        return Inertia::render('Expenses/Create', [
            'categories' => $categories,
            'vendors' => $vendors,
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
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'status' => ['required', 'string', 'in:planned,contracted,paid'],
            'paid_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure category belongs to the authenticated user
        $category = $request->user()->categories()->find($validated['category_id']);
        if (! $category) {
            abort(403, 'Category does not belong to you.');
        }

        // If vendor_id provided, ensure vendor belongs to the user
        if (! empty($validated['vendor_id'])) {
            $vendor = $request->user()->vendors()->find($validated['vendor_id']);
            if (! $vendor) {
                abort(403, 'Vendor does not belong to you.');
            }
        }

        $validated['user_id'] = $request->user()->id;

        $expense = $request->user()->expenses()->create($validated);

        // Handle optional split data from create form
        if ($request->filled('split_type')) {
            $this->createSplit($request, $expense);
        }

        // Process file uploads (W2 fix)
        if ($request->hasFile('receipt_files')) {
            foreach ($request->file('receipt_files') as $file) {
                if ($expense->receipts()->count() >= 5) {
                    break;
                }
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . Str::random(8) . '.' . $extension;
                $path = $file->storeAs((string) $expense->id, $filename, 'receipts');
                $expense->receipts()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'user_id' => $request->user()->id,
                ]);
            }
        }

        return Redirect::route('expenses.index');
    }

    /**
     * Show the form for editing the expense.
     */
    public function edit(Request $request, Expense $expense, ExpenseCommitmentService $commitmentService): Response
    {
        $this->authorizeExpense($request, $expense);

        $expense->load('split');

        $categories = $request->user()->categories()->orderBy('name')->get()->map(fn (Category $c) => [
            'id' => $c->id,
            'name' => $c->name,
        ]);

        $vendors = $request->user()->vendors()->orderBy('name')->get()->map(fn (Vendor $v) => [
            'id' => $v->id,
            'name' => $v->name,
        ]);

        $receipts = $expense->receipts()->orderBy('created_at', 'desc')->get()->map(fn ($r) => [
            'id' => $r->id,
            'file_name' => $r->file_name,
            'file_type' => $r->file_type,
            'file_size' => $r->file_size,
            'file_url' => $r->file_url,
            'created_at' => $r->created_at->format('Y-m-d H:i'),
        ]);

        $split = null;
        if ($expense->split) {
            $split = [
                'id' => $expense->split->id,
                'split_type' => $expense->split->split_type->value,
                'person_a_label' => $expense->split->person_a_label,
                'person_a_amount' => (float) $expense->split->person_a_amount,
                'person_b_label' => $expense->split->person_b_label,
                'person_b_amount' => (float) $expense->split->person_b_amount,
            ];
        }

        return Inertia::render('Expenses/Edit', [
            'expense' => [
                'id' => $expense->id,
                'category_id' => $expense->category_id,
                'amount' => (float) $expense->amount,
                'vendor' => $expense->vendor,
                'vendor_id' => $expense->vendor_id,
                'status' => $expense->status->value,
                'paid_date' => $expense->paid_date?->format('Y-m-d'),
                'notes' => $expense->notes,
                'receipts_count' => $expense->receipts()->count(),
                'split' => $split,
                'wedding_id' => $expense->wedding_id,
            ],
            'categories' => $categories,
            'vendors' => $vendors,
            'receipts' => $receipts,
            'statuses' => collect(ExpenseStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => ucfirst($s->value),
            ]),
            'maxReceipts' => 5,
            'commitment' => $commitmentService->summary($expense),
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
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'status' => ['required', 'string', 'in:planned,contracted,paid'],
            'paid_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure category belongs to the authenticated user
        $category = $request->user()->categories()->find($validated['category_id']);
        if (! $category) {
            abort(403, 'Category does not belong to you.');
        }

        // If vendor_id provided, ensure vendor belongs to the user
        if (! empty($validated['vendor_id'])) {
            $vendor = $request->user()->vendors()->find($validated['vendor_id']);
            if (! $vendor) {
                abort(403, 'Vendor does not belong to you.');
            }
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

    /**
     * Create a split for a newly created expense from the create form.
     */
    private function createSplit(Request $request, Expense $expense): void
    {
        $splitType = $request->input('split_type');

        if (! in_array($splitType, ['50_50', 'percent', 'fixed'], true)) {
            return;
        }

        $amount = round((float) $expense->amount, 2);
        $personALabel = $request->input('person_a_label', 'Él');
        $personBLabel = $request->input('person_b_label', 'Ella');

        switch ($splitType) {
            case '50_50':
                $personA = round($amount / 2, 2);
                $personB = round($amount - $personA, 2);
                break;

            case 'percent':
                $percentA = (float) $request->input('percent_a', 50);
                $personA = round($amount * ($percentA / 100), 2);
                $personB = round($amount - $personA, 2);
                if ($personB < 0) {
                    $personB = 0.00;
                }
                break;

            case 'fixed':
                $personA = round((float) $request->input('person_a_amount', 0), 2);
                $personB = round((float) $request->input('person_b_amount', 0), 2);

                // Validate that fixed amounts sum to the expense total (tolerance 0.01)
                $sum = round($personA + $personB, 2);
                if (abs($sum - $amount) > 0.01) {
                    throw ValidationException::withMessages([
                        'person_a_amount' => "La suma de los montos (S/. {$sum}) no coincide con el total del gasto (S/. {$amount}).",
                    ]);
                }

                break;

            default:
                return;
        }

        $expense->split()->create([
            'split_type' => $splitType,
            'person_a_label' => $personALabel,
            'person_a_amount' => $personA,
            'person_b_label' => $personBLabel,
            'person_b_amount' => $personB,
        ]);
    }
}
