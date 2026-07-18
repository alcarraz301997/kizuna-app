<?php

namespace App\Http\Controllers;

use App\Enums\RsvpStatus;
use App\Models\Guest;
use App\Models\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class GuestController extends Controller
{
    /**
     * Display a listing of the guests with RSVP counts.
     */
    public function index(Request $request): Response
    {
        $guests = $request->user()->guests()
            ->with('table')
            ->orderBy('name')
            ->get()
            ->map(fn (Guest $g) => [
                'id' => $g->id,
                'name' => $g->name,
                'email' => $g->email,
                'phone' => $g->phone,
                'rsvp_status' => $g->rsvp_status->value,
                'table_name' => $g->table?->name,
            ]);

        $total = $guests->count();
        $confirmados = $guests->where('rsvp_status', 'confirmado')->count();

        $counts = [
            'total' => $total,
            'confirmados' => $confirmados,
            'pendientes' => $total - $confirmados,
        ];

        return Inertia::render('Guests/Index', [
            'guests' => $guests,
            'counts' => $counts,
        ]);
    }

    /**
     * Show the form for creating a new guest.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Guests/Create', [
            'tables' => $this->tablesForSelect($request),
            'rsvpStatuses' => $this->rsvpStatusOptions(),
        ]);
    }

    /**
     * Store a newly created guest.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'rsvp_status' => ['required', 'string', 'in:pendiente,confirmado,no_asiste'],
            'table_id' => ['nullable', 'integer', 'exists:tables,id'],
        ]);

        // If a table is selected, verify ownership and capacity.
        if (! empty($validated['table_id'])) {
            $table = $this->authorizeAndCheckCapacity($request, $validated['table_id']);
            if ($table instanceof RedirectResponse) {
                return $table;
            }
        }

        $request->user()->guests()->create($validated);

        return Redirect::route('guests.index');
    }

    /**
     * Display the guest (redirect to index since Inertia doesn't use show).
     */
    public function show(): RedirectResponse
    {
        return Redirect::route('guests.index');
    }

    /**
     * Show the form for editing the guest.
     */
    public function edit(Request $request, Guest $guest): Response
    {
        $this->authorizeGuest($request, $guest);

        return Inertia::render('Guests/Edit', [
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'rsvp_status' => $guest->rsvp_status->value,
                'table_id' => $guest->table_id,
            ],
            'tables' => $this->tablesForSelect($request),
            'rsvpStatuses' => $this->rsvpStatusOptions(),
        ]);
    }

    /**
     * Update the guest.
     */
    public function update(Request $request, Guest $guest): RedirectResponse
    {
        $this->authorizeGuest($request, $guest);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'rsvp_status' => ['required', 'string', 'in:pendiente,confirmado,no_asiste'],
            'table_id' => ['nullable', 'integer', 'exists:tables,id'],
        ]);

        // If a table is selected, verify ownership and capacity.
        if (! empty($validated['table_id'])) {
            $table = $this->authorizeAndCheckCapacity($request, $validated['table_id'], $guest);
            if ($table instanceof RedirectResponse) {
                return $table;
            }
        }

        $guest->update($validated);

        return Redirect::route('guests.index');
    }

    /**
     * Remove the guest.
     */
    public function destroy(Request $request, Guest $guest): RedirectResponse
    {
        $this->authorizeGuest($request, $guest);

        $guest->delete();

        return Redirect::route('guests.index');
    }

    /**
     * Export the guest list as PDF.
     */
    public function export(Request $request): HttpResponse
    {
        $guests = $request->user()->guests()
            ->with('table')
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.guests-pdf', [
            'guests' => $guests,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('lista-invitados-boda.pdf');
    }

    /**
     * Ensure the guest belongs to the authenticated user.
     */
    private function authorizeGuest(Request $request, Guest $guest): void
    {
        if ($guest->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    /**
     * Validate the table belongs to the authenticated user and has capacity.
     *
     * Returns the Table model on success, or a RedirectResponse on failure.
     */
    private function authorizeAndCheckCapacity(
        Request $request,
        int $tableId,
        ?Guest $excludeGuest = null,
    ): Table|RedirectResponse {
        $table = Table::withCount('guests')->find($tableId);

        if (! $table || $table->user_id !== $request->user()->id) {
            return Redirect::back()
                ->withInput()
                ->with('error', 'La mesa seleccionada no es válida.');
        }

        // When updating a guest, don't count that guest's own spot against capacity.
        $occupancy = $table->guests_count;
        if ($excludeGuest && $excludeGuest->table_id === $table->id) {
            $occupancy = $occupancy - 1;
        }

        if ($occupancy >= $table->capacity) {
            return Redirect::back()
                ->withInput()
                ->with('error', "La mesa \"{$table->name}\" está llena. Capacidad máxima: {$table->capacity}.");
        }

        return $table;
    }

    /**
     * Build the tables dropdown options for the authenticated user.
     */
    private function tablesForSelect(Request $request): array
    {
        return $request->user()->tables()
            ->withCount('guests')
            ->orderBy('name')
            ->get()
            ->map(fn (Table $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'capacity' => $t->capacity,
                'guests_count' => $t->guests_count,
                'available_spots' => max(0, $t->capacity - $t->guests_count),
                'is_full' => $t->guests_count >= $t->capacity,
            ])
            ->toArray();
    }

    /**
     * RSVP status options for Inertia select.
     */
    private function rsvpStatusOptions(): array
    {
        return collect(RsvpStatus::cases())->map(fn ($s) => [
            'value' => $s->value,
            'label' => match ($s) {
                RsvpStatus::Pendiente => 'Pendiente',
                RsvpStatus::Confirmado => 'Confirmado',
                RsvpStatus::NoAsiste => 'No Asiste',
            },
        ])->toArray();
    }
}
