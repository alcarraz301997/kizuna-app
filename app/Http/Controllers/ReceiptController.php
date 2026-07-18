<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Receipt;
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
    public function store(Request $request, Expense $expense): RedirectResponse
    {
        // Authorization: expense must belong to the user
        if ($expense->user_id !== $request->user()->id) {
            abort(403);
        }

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

        // Store on the receipts disk
        $path = $file->storeAs('/', $filename, 'receipts');

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
    public function destroy(Request $request, Receipt $receipt): RedirectResponse
    {
        // Authorization: receipt must belong to the user
        if ($receipt->user_id !== $request->user()->id) {
            abort(403);
        }

        // Delete physical file
        if (Storage::disk('receipts')->exists($receipt->file_path)) {
            Storage::disk('receipts')->delete($receipt->file_path);
        }

        $receipt->delete();

        return Redirect::back();
    }
}
