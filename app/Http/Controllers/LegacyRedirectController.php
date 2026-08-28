<?php

namespace App\Http\Controllers;

use App\Services\WeddingContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LegacyRedirectController extends Controller
{
    public function __construct(private readonly WeddingContext $weddingContext) {}

    /**
     * Redirect legacy flat resource URLs (e.g. /categories) to the wedding-scoped URL.
     * If the user has no wedding, redirect to dashboard.
     */
    public function index(Request $request, string $resource): RedirectResponse
    {
        $wedding = $this->weddingContext->current($request);

        if (! $wedding) {
            return redirect()->route('dashboard')->with('error', 'Crea o únete a un espacio de trabajo primero.');
        }

        return redirect()->route("weddings.{$resource}.index", $wedding);
    }

    /**
     * Redirect legacy flat create URLs (e.g. /categories/create) to the wedding-scoped URL.
     */
    public function create(Request $request, string $resource): RedirectResponse
    {
        $wedding = $this->weddingContext->current($request);

        if (! $wedding) {
            return redirect()->route('dashboard')->with('error', 'Crea o únete a un espacio de trabajo primero.');
        }

        return redirect()->route("weddings.{$resource}.create", $wedding);
    }
}
