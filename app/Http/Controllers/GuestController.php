<?php

namespace App\Http\Controllers;

use App\Enums\RsvpStatus;
use App\Models\Guest;
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
        $guests = $request->user()->guests()->orderBy('name')->get()->map(fn (Guest $g) => [
            'id' => $g->id,
            'name' => $g->name,
            'email' => $g->email,
            'phone' => $g->phone,
            'rsvp_status' => $g->rsvp_status->value,
            'table_number' => $g->table_number,
        ]);

        $counts = [
            'total' => $guests->count(),
            'confirmados' => $guests->where('rsvp_status', 'confirmado')->count(),
            'pendientes' => $guests->where('rsvp_status', 'pendiente')->count(),
        ];

        return Inertia::render('Guests/Index', [
            'guests' => $guests,
            'counts' => $counts,
        ]);
    }

    /**
     * Show the form for creating a new guest.
     */
    public function create(): Response
    {
        return Inertia::render('Guests/Create', [
            'rsvpStatuses' => collect(RsvpStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => match ($s) {
                    RsvpStatus::Pendiente => 'Pendiente',
                    RsvpStatus::Confirmado => 'Confirmado',
                    RsvpStatus::NoAsiste => 'No Asiste',
                },
            ]),
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
            'table_number' => ['nullable', 'integer', 'min:1'],
        ]);

        $request->user()->guests()->create($validated);

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
                'table_number' => $guest->table_number,
            ],
            'rsvpStatuses' => collect(RsvpStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => match ($s) {
                    RsvpStatus::Pendiente => 'Pendiente',
                    RsvpStatus::Confirmado => 'Confirmado',
                    RsvpStatus::NoAsiste => 'No Asiste',
                },
            ]),
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
            'table_number' => ['nullable', 'integer', 'min:1'],
        ]);

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
        $guests = $request->user()->guests()->orderBy('name')->get();

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
}
