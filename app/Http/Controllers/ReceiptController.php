<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Receipt;
use App\Models\Wedding;
use App\Services\WeddingContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptController extends Controller
{
    /**
     * Store a newly uploaded receipt for an expense.
     */
    public function store(Request $request, Wedding $wedding, Expense $expense, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeExpense($wedding, $expense);

        // Check max 5 receipts per expense
        if ($expense->receipts()->count() >= 5) {
            return Redirect::back()->with('error', 'Máximo 5 adjuntos por gasto');
        }

        $validated = $request->validate([
            'receipt' => ['required', 'file', 'mimes:jpeg,png,gif,webp,pdf', 'max:10240'],
        ], [
            'receipt.mimes' => 'El archivo debe ser una imagen (JPEG, PNG, GIF, WebP) o PDF.',
            'receipt.max' => 'El archivo excede 10 MB.',
        ]);

        $file = $validated['receipt'];

        // Generate safe filename: timestamp + random + extension
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::random(8) . '.' . $extension;

        // Store on the receipts disk, organized by expense
        $path = $file->storeAs((string) $expense->id, $filename, 'receipts');

        $expense->receipts()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'user_id' => $request->user()->id,
        ]);

        return Redirect::back();
    }

    /**
     * Remove the receipt (delete DB record + physical file).
     */
    public function destroy(Request $request, Wedding $wedding, Receipt $receipt, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);

        // Authorization: receipt must belong to the wedding
        $expense = $receipt->expense;
        if (! $expense || $expense->wedding_id !== $wedding->id) {
            abort(403);
        }

        // Delete physical file
        if (Storage::disk('receipts')->exists($receipt->file_path)) {
            Storage::disk('receipts')->delete($receipt->file_path);
        }

        $receipt->delete();

        return Redirect::back();
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
