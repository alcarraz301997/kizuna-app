<?php

namespace App\Http\Controllers;

use App\Services\WeddingMembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LegacyRedirectController extends Controller
{
    public function __construct(private readonly WeddingMembershipService $membershipService) {}

    /**
     * Redirect legacy flat resource URLs (e.g. /categories) to the wedding-scoped URL.
     * If the user has no wedding, a default one is created automatically.
     */
    public function index(Request $request, string $resource): RedirectResponse
    {
        $wedding = $this->resolveWedding($request);

        return redirect()->route("weddings.{$resource}.index", $wedding);
    }

    /**
     * Redirect legacy flat create URLs (e.g. /categories/create) to the wedding-scoped URL.
     */
    public function create(Request $request, string $resource): RedirectResponse
    {
        $wedding = $this->resolveWedding($request);

        return redirect()->route("weddings.{$resource}.create", $wedding);
    }

    /**
     * Resolve the user's wedding: owned first, then membership, then create a new one.
     */
    private function resolveWedding(Request $request): \App\Models\Wedding
    {
        $user = $request->user();

        return $user->weddings()->first()
            ?? $user->weddingMemberships()->with('wedding')->first()?->wedding
            ?? $this->membershipService->createForOwner($user, 'Mi Boda');
    }
}
