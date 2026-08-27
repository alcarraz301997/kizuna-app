<?php

namespace App\Http\Controllers;

use App\Enums\RsvpStatus;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\UpdateGuestRequest;
use App\Models\Guest;
use App\Models\Table;
use App\Models\Wedding;
use App\Services\WeddingContext;
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
    public function index(Request $request, Wedding $wedding, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);

        $guests = $wedding->guests()
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
            'wedding' => $wedding,
        ]);
    }

    /**
     * Show the form for creating a new guest.
     */
    public function create(Request $request, Wedding $wedding, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);

        return Inertia::render('Guests/Create', [
            'tables' => $this->tablesForSelect($wedding),
            'rsvpStatuses' => $this->rsvpStatusOptions(),
            'wedding' => $wedding,
        ]);
    }

    /**
     * Store a newly created guest.
     */
    public function store(StoreGuestRequest $request, Wedding $wedding, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);

        $validated = $request->validated();

        // If a table is selected, verify it belongs to the wedding and has capacity.
        if (! empty($validated['table_id'])) {
            $result = $this->authorizeAndCheckCapacity($wedding, $validated['table_id']);
            if ($result instanceof RedirectResponse) {
                return $result;
            }
        }

        $wedding->guests()->create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return Redirect::route('weddings.guests.index', $wedding);
    }

    /**
     * Display the guest (redirect to index since Inertia doesn't use show).
     */
    public function show(Wedding $wedding): RedirectResponse
    {
        return Redirect::route('weddings.guests.index', $wedding);
    }

    /**
     * Show the form for editing the guest.
     */
    public function edit(Request $request, Wedding $wedding, Guest $guest, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        return Inertia::render('Guests/Edit', [
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'rsvp_status' => $guest->rsvp_status->value,
                'table_id' => $guest->table_id,
            ],
            'tables' => $this->tablesForSelect($wedding),
            'rsvpStatuses' => $this->rsvpStatusOptions(),
            'wedding' => $wedding,
        ]);
    }

    /**
     * Update the guest.
     */
    public function update(UpdateGuestRequest $request, Wedding $wedding, Guest $guest, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        $validated = $request->validated();

        // If a table is selected, verify it belongs to the wedding and has capacity.
        if (! empty($validated['table_id'])) {
            $result = $this->authorizeAndCheckCapacity($wedding, $validated['table_id'], $guest);
            if ($result instanceof RedirectResponse) {
                return $result;
            }
        }

        $guest->update($validated);

        return Redirect::route('weddings.guests.index', $wedding);
    }

    /**
     * Remove the guest.
     */
    public function destroy(Request $request, Wedding $wedding, Guest $guest, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeGuest($wedding, $guest);
        $this->authorize('delete', $guest);

        $guest->delete();

        return Redirect::route('weddings.guests.index', $wedding);
    }

    /**
     * Export the guest list as PDF.
     */
    public function export(Request $request, Wedding $wedding, WeddingContext $context): HttpResponse
    {
        $context->authorize($request, $wedding);

        $guests = $wedding->guests()
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
     * Ensure the guest belongs to the wedding.
     */
    private function authorizeGuest(Wedding $wedding, Guest $guest): void
    {
        if ($guest->wedding_id !== $wedding->id) {
            abort(403);
        }
    }

    /**
     * Validate the table belongs to the wedding and has capacity.
     *
     * Returns the Table model on success, or a RedirectResponse on failure.
     */
    private function authorizeAndCheckCapacity(
        Wedding $wedding,
        int $tableId,
        ?Guest $excludeGuest = null,
    ): Table|RedirectResponse {
        $table = $wedding->tables()->withCount('guests')->find($tableId);

        if (! $table) {
            return Redirect::back()
                ->withInput()
                ->with('error', 'La mesa seleccionada no es válida para esta boda.');
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
     * Build the tables dropdown options for the wedding.
     */
    private function tablesForSelect(Wedding $wedding): array
    {
        return $wedding->tables()
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
