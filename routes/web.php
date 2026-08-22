<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryTemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCommitmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseSplitController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WeddingController;
use App\Http\Controllers\WeddingMemberController;
use App\Http\Controllers\WeddingMetricsController;
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

    Route::post('/weddings', [WeddingController::class, 'store'])->name('weddings.store');
    Route::get('/weddings/{wedding}', [WeddingController::class, 'show'])->name('weddings.show');
    Route::post('/weddings/{wedding}/members', [WeddingMemberController::class, 'store'])->name('weddings.members.store');
    Route::get('/weddings/{wedding}/category-templates', [CategoryTemplateController::class, 'index'])->name('weddings.category-templates.index');
    Route::post('/weddings/{wedding}/category-templates', [CategoryTemplateController::class, 'store'])->name('weddings.category-templates.store');
    Route::post('/weddings/{wedding}/category-templates/{template}/apply', [CategoryTemplateController::class, 'apply'])->name('weddings.category-templates.apply');
    Route::get('/weddings/{wedding}/category-rollups', [CategoryTemplateController::class, 'rollups'])->name('weddings.category-rollups');
    Route::get('/weddings/{wedding}/expenses/{expense}/commitment', [ExpenseCommitmentController::class, 'show'])->name('weddings.expenses.commitment.show');
    Route::patch('/weddings/{wedding}/expenses/{expense}/commitment', [ExpenseCommitmentController::class, 'update'])->name('weddings.expenses.commitment.update');
    Route::post('/weddings/{wedding}/expenses/{expense}/payments', [ExpenseCommitmentController::class, 'payment'])->name('weddings.expenses.payments.store');
    Route::get('/weddings/{wedding}/forecast', [WeddingMetricsController::class, 'forecast'])->name('weddings.forecast');
    Route::get('/weddings/{wedding}/variance', [WeddingMetricsController::class, 'variance'])->name('weddings.variance');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resources nested under wedding
    Route::resource('weddings/{wedding}/categories', CategoryController::class)->names('weddings.categories');
    Route::resource('weddings/{wedding}/expenses', ExpenseController::class)->names('weddings.expenses');
    Route::resource('weddings/{wedding}/vendors', VendorController::class)->names('weddings.vendors');
    Route::resource('weddings/{wedding}/tables', TableController::class)->names('weddings.tables');
    Route::resource('weddings/{wedding}/guests', GuestController::class)->names('weddings.guests');
    Route::get('weddings/{wedding}/guests/export/pdf', [GuestController::class, 'export'])->name('weddings.guests.export.pdf');

    Route::post('weddings/{wedding}/expenses/{expense}/receipts', [ReceiptController::class, 'store'])->name('weddings.expenses.receipts.store');
    Route::delete('weddings/{wedding}/receipts/{receipt}', [ReceiptController::class, 'destroy'])->name('weddings.receipts.destroy');

    Route::post('weddings/{wedding}/expenses/{expense}/split', [ExpenseSplitController::class, 'store'])->name('weddings.expenses.split.store');
    Route::put('weddings/{wedding}/expenses/{expense}/split', [ExpenseSplitController::class, 'update'])->name('weddings.expenses.split.update');
});

require __DIR__.'/auth.php';
