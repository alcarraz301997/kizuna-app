<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Wedding;
use App\Services\WeddingContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class TableController extends Controller
{
    /**
     * Display a listing of the tables with occupancy information.
     */
    public function index(Request $request, Wedding $wedding, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);

        $tables = $wedding->tables()
            ->withCount('guests')
            ->orderBy('name')
            ->get()
            ->map(fn (Table $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'capacity' => $t->capacity,
                'guests_count' => $t->guests_count,
                'available_spots' => max(0, $t->capacity - $t->guests_count),
            ]);

        return Inertia::render('Tables/Index', [
            'tables' => $tables,
            'wedding' => $wedding,
        ]);
    }

    /**
     * Show the form for creating a new table.
     */
    public function create(Request $request, Wedding $wedding, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);

        return Inertia::render('Tables/Create', [
            'wedding' => $wedding,
        ]);
    }

    /**
     * Store a newly created table.
     */
    public function store(Request $request, Wedding $wedding, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tables,name,NULL,id,wedding_id,' . $wedding->id],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        $wedding->tables()->create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return Redirect::route('weddings.tables.index', $wedding);
    }

    /**
     * Display the table (redirect to index since Inertia doesn't use show).
     */
    public function show(Wedding $wedding): RedirectResponse
    {
        return Redirect::route('weddings.tables.index', $wedding);
    }

    /**
     * Show the form for editing the table.
     */
    public function edit(Request $request, Wedding $wedding, Table $table, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);
        $this->authorizeTable($wedding, $table);

        return Inertia::render('Tables/Edit', [
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'capacity' => $table->capacity,
            ],
            'wedding' => $wedding,
        ]);
    }

    /**
     * Update the table.
     */
    public function update(Request $request, Wedding $wedding, Table $table, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeTable($wedding, $table);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tables,name,' . $table->id . ',id,wedding_id,' . $wedding->id],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        $table->update($validated);

        return Redirect::route('weddings.tables.index', $wedding);
    }

    /**
     * Remove the table (blocked if guests exist).
     */
    public function destroy(Request $request, Wedding $wedding, Table $table, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeTable($wedding, $table);

        if ($table->guests()->exists()) {
            return Redirect::route('weddings.tables.index', $wedding)
                ->with('error', 'No se puede eliminar una mesa con invitados asignados. Reasigna los invitados primero.');
        }

        $table->delete();

        return Redirect::route('weddings.tables.index', $wedding);
    }

    /**
     * Ensure the table belongs to the wedding.
     */
    private function authorizeTable(Wedding $wedding, Table $table): void
    {
        if ($table->wedding_id !== $wedding->id) {
            abort(403);
        }
    }
}
