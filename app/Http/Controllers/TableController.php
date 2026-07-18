<?php

namespace App\Http\Controllers;

use App\Models\Table;
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
    public function index(Request $request): Response
    {
        $tables = $request->user()->tables()
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
        ]);
    }

    /**
     * Show the form for creating a new table.
     */
    public function create(): Response
    {
        return Inertia::render('Tables/Create');
    }

    /**
     * Store a newly created table.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tables,name,NULL,id,user_id,' . $request->user()->id],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        $request->user()->tables()->create($validated);

        return Redirect::route('tables.index');
    }

    /**
     * Display the table (redirect to index since Inertia doesn't use show).
     */
    public function show(): RedirectResponse
    {
        return Redirect::route('tables.index');
    }

    /**
     * Show the form for editing the table.
     */
    public function edit(Request $request, Table $table): Response
    {
        $this->authorizeTable($request, $table);

        return Inertia::render('Tables/Edit', [
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'capacity' => $table->capacity,
            ],
        ]);
    }

    /**
     * Update the table.
     */
    public function update(Request $request, Table $table): RedirectResponse
    {
        $this->authorizeTable($request, $table);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tables,name,' . $table->id . ',id,user_id,' . $request->user()->id],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        $table->update($validated);

        return Redirect::route('tables.index');
    }

    /**
     * Remove the table (blocked if guests exist).
     */
    public function destroy(Request $request, Table $table): RedirectResponse
    {
        $this->authorizeTable($request, $table);

        if ($table->guests()->exists()) {
            return Redirect::route('tables.index')
                ->with('error', 'No se puede eliminar una mesa con invitados asignados. Reasigna los invitados primero.');
        }

        $table->delete();

        return Redirect::route('tables.index');
    }

    /**
     * Ensure the table belongs to the authenticated user.
     */
    private function authorizeTable(Request $request, Table $table): void
    {
        if ($table->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
