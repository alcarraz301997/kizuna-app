<?php

namespace App\Http\Controllers;

use App\Enums\VendorPaymentStatus;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    /**
     * Display a listing of the vendors with optional service_category filter.
     */
    public function index(Request $request): Response
    {
        $query = $request->user()->vendors()->withCount('expenses')->orderBy('name');

        if ($request->filled('service_category')) {
            $query->where('service_category', $request->service_category);
        }

        $vendors = $query->get()->map(fn (Vendor $v) => [
            'id' => $v->id,
            'name' => $v->name,
            'service_category' => $v->service_category,
            'contact_phone' => $v->contact_phone,
            'contact_email' => $v->contact_email,
            'payment_status' => $v->payment_status->value,
            'notes' => $v->notes,
            'expenses_count' => $v->expenses_count,
        ]);

        $serviceCategories = $request->user()->vendors()
            ->select('service_category')
            ->distinct()
            ->orderBy('service_category')
            ->pluck('service_category');

        return Inertia::render('Vendors/Index', [
            'vendors' => $vendors,
            'serviceCategories' => $serviceCategories,
            'filters' => [
                'service_category' => $request->service_category,
            ],
        ]);
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create(): Response
    {
        return Inertia::render('Vendors/Create', [
            'paymentStatuses' => collect(VendorPaymentStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => match ($s) {
                    VendorPaymentStatus::NoIniciado => 'No iniciado',
                    VendorPaymentStatus::PagadoParcialmente => 'Pagado parcialmente',
                    VendorPaymentStatus::PagadoCompleto => 'Pagado completo',
                },
            ]),
        ]);
    }

    /**
     * Store a newly created vendor.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:vendors,name,NULL,id,user_id,' . $request->user()->id],
            'service_category' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'payment_status' => ['required', 'string', 'in:no_iniciado,pagado_parcialmente,pagado_completo'],
            'notes' => ['nullable', 'string'],
        ]);

        $request->user()->vendors()->create($validated);

        return Redirect::route('vendors.index');
    }

    /**
     * Display the vendor (redirect to index since Inertia doesn't use show).
     */
    public function show(): RedirectResponse
    {
        return Redirect::route('vendors.index');
    }

    /**
     * Show the form for editing the vendor.
     */
    public function edit(Request $request, Vendor $vendor): Response
    {
        $this->authorizeVendor($request, $vendor);

        return Inertia::render('Vendors/Edit', [
            'vendor' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'service_category' => $vendor->service_category,
                'contact_phone' => $vendor->contact_phone,
                'contact_email' => $vendor->contact_email,
                'payment_status' => $vendor->payment_status->value,
                'notes' => $vendor->notes,
            ],
            'paymentStatuses' => collect(VendorPaymentStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => match ($s) {
                    VendorPaymentStatus::NoIniciado => 'No iniciado',
                    VendorPaymentStatus::PagadoParcialmente => 'Pagado parcialmente',
                    VendorPaymentStatus::PagadoCompleto => 'Pagado completo',
                },
            ]),
        ]);
    }

    /**
     * Update the vendor.
     */
    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorizeVendor($request, $vendor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:vendors,name,' . $vendor->id . ',id,user_id,' . $request->user()->id],
            'service_category' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'payment_status' => ['required', 'string', 'in:no_iniciado,pagado_parcialmente,pagado_completo'],
            'notes' => ['nullable', 'string'],
        ]);

        $vendor->update($validated);

        return Redirect::route('vendors.index');
    }

    /**
     * Remove the vendor (blocked if expenses exist).
     */
    public function destroy(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorizeVendor($request, $vendor);

        if ($vendor->expenses()->exists()) {
            return Redirect::route('vendors.index')
                ->with('error', 'Existen gastos vinculados a este proveedor. Elimina o reasigna los gastos primero.');
        }

        $vendor->delete();

        return Redirect::route('vendors.index');
    }

    /**
     * Ensure the vendor belongs to the authenticated user.
     */
    private function authorizeVendor(Request $request, Vendor $vendor): void
    {
        if ($vendor->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
