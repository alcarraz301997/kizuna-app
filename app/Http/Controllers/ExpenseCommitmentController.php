<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseCommitmentRequest;
use App\Http\Requests\ExpensePaymentRequest;
use App\Models\Expense;
use App\Models\Wedding;
use App\Services\ExpenseCommitmentService;
use App\Services\WeddingContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExpenseCommitmentController extends Controller
{
    public function show(Request $request, Wedding $wedding, Expense $expense, WeddingContext $context, ExpenseCommitmentService $service): Response|JsonResponse
    {
        $context->authorize($request, $wedding);
        $expense = $service->forWedding($wedding, $expense);
        $props = ['commitment' => $service->summary($expense)];

        if ($request->expectsJson()) {
            return response()->json($props);
        }

        return Inertia::render('Expenses/Commitment', $props);
    }

    public function update(ExpenseCommitmentRequest $request, Wedding $wedding, Expense $expense, WeddingContext $context, ExpenseCommitmentService $service): JsonResponse|RedirectResponse
    {
        $context->authorize($request, $wedding);
        $expense = $service->forWedding($wedding, $expense);
        $expense = $service->update($expense, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['commitment' => $service->summary($expense)]);
        }

        return redirect()->route('expenses.edit', $expense);
    }

    public function payment(ExpensePaymentRequest $request, Wedding $wedding, Expense $expense, WeddingContext $context, ExpenseCommitmentService $service): JsonResponse|RedirectResponse
    {
        $context->authorize($request, $wedding);
        $expense = $service->forWedding($wedding, $expense);
        $expense = $service->addPayment($expense, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['commitment' => $service->summary($expense)]);
        }

        return redirect()->route('expenses.edit', $expense);
    }
}
