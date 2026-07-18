<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseSplitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\VendorController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', CategoryController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('vendors', VendorController::class);

    Route::post('expenses/{expense}/receipts', [ReceiptController::class, 'store'])->name('expenses.receipts.store');
    Route::delete('receipts/{receipt}', [ReceiptController::class, 'destroy'])->name('receipts.destroy');

    Route::post('expenses/{expense}/split', [ExpenseSplitController::class, 'store'])->name('expenses.split.store');
    Route::put('expenses/{expense}/split', [ExpenseSplitController::class, 'update'])->name('expenses.split.update');

    Route::resource('tables', TableController::class);
    Route::resource('guests', GuestController::class);
    Route::get('guests/export/pdf', [GuestController::class, 'export'])->name('guests.export.pdf');
});

require __DIR__.'/auth.php';
