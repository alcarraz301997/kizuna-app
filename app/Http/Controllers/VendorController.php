<?php

namespace App\Http\Controllers;

use App\Enums\VendorPaymentStatus;
use App\Models\Vendor;
use App\Models\Wedding;
use App\Services\WeddingContext;
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
    public function index(Request $request, Wedding $wedding, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);

        $query = $wedding->vendors()->withCount('expenses')->orderBy('name');

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

        $serviceCategories = $wedding->vendors()
            ->select('service_category')
            ->distinct()
            ->orderBy('service_category')
            ->pluck('service_category');

        return Inertia::render('Vendors/Index', [
            'vendors' => $vendors,
            'serviceCategories' => $serviceCategories,
            'wedding' => $wedding,
            'filters' => [
                'service_category' => $request->service_category,
            ],
        ]);
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create(Request $request, Wedding $wedding, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);

        return Inertia::render('Vendors/Create', [
            'wedding' => $wedding,
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
    public function store(Request $request, Wedding $wedding, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:vendors,name,NULL,id,wedding_id,' . $wedding->id],
            'service_category' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'payment_status' => ['required', 'string', 'in:no_iniciado,pagado_parcialmente,pagado_completo'],
            'notes' => ['nullable', 'string'],
        ]);

        $wedding->vendors()->create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return Redirect::route('weddings.vendors.index', $wedding);
    }

    /**
     * Display the vendor (redirect to index since Inertia doesn't use show).
     */
    public function show(Wedding $wedding): RedirectResponse
    {
        return Redirect::route('weddings.vendors.index', $wedding);
    }

    /**
     * Show the form for editing the vendor.
     */
    public function edit(Request $request, Wedding $wedding, Vendor $vendor, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);
        $this->authorizeVendor($wedding, $vendor);

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
            'wedding' => $wedding,
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
    public function update(Request $request, Wedding $wedding, Vendor $vendor, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeVendor($wedding, $vendor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:vendors,name,' . $vendor->id . ',id,wedding_id,' . $wedding->id],
            'service_category' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'payment_status' => ['required', 'string', 'in:no_iniciado,pagado_parcialmente,pagado_completo'],
            'notes' => ['nullable', 'string'],
        ]);

        $vendor->update($validated);

        return Redirect::route('weddings.vendors.index', $wedding);
    }

    /**
     * Remove the vendor (blocked if expenses exist).
     */
    public function destroy(Request $request, Wedding $wedding, Vendor $vendor, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeVendor($wedding, $vendor);

        if ($vendor->expenses()->exists()) {
            return Redirect::route('weddings.vendors.index', $wedding)
                ->with('error', 'Existen gastos vinculados a este proveedor. Elimina o reasigna los gastos primero.');
        }

        $vendor->delete();

        return Redirect::route('weddings.vendors.index', $wedding);
    }

    /**
     * Ensure the vendor belongs to the wedding.
     */
    private function authorizeVendor(Wedding $wedding, Vendor $vendor): void
    {
        if ($vendor->wedding_id !== $wedding->id) {
            abort(403);
        }
    }
}
