<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Wedding;
use App\Services\WeddingContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories for the wedding.
     */
    public function index(Request $request, Wedding $wedding, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);

        $categories = $wedding->categories()
            ->withSum(['expenses as spent' => fn ($q) => $q->whereIn('status', ['contracted', 'paid'])], 'amount')
            ->withCount('expenses')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'budget_limit' => $c->budget_limit,
                'color' => $c->color,
                'spent' => (float) ($c->spent ?? 0),
                'remaining' => (float) $c->budget_limit - (float) ($c->spent ?? 0),
                'expenses_count' => $c->expenses_count,
            ]);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'wedding' => $wedding,
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(Request $request, Wedding $wedding, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);

        return Inertia::render('Categories/Create', [
            'wedding' => $wedding,
        ]);
    }

    /**
     * Store a newly created category for the wedding.
     */
    public function store(Request $request, Wedding $wedding, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,NULL,id,wedding_id,' . $wedding->id],
            'budget_limit' => ['required', 'numeric', 'gt:0'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $wedding->categories()->create([
            'name' => $validated['name'],
            'budget_limit' => $validated['budget_limit'],
            'color' => $validated['color'],
            'user_id' => $request->user()->id,
        ]);

        return Redirect::route('weddings.categories.index', $wedding);
    }

    /**
     * Show the form for editing the category.
     */
    public function edit(Request $request, Wedding $wedding, Category $category, WeddingContext $context): Response
    {
        $context->authorize($request, $wedding);
        $this->authorizeCategory($wedding, $category);

        return Inertia::render('Categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'budget_limit' => $category->budget_limit,
                'color' => $category->color,
            ],
            'wedding' => $wedding,
        ]);
    }

    /**
     * Update the category.
     */
    public function update(Request $request, Wedding $wedding, Category $category, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeCategory($wedding, $category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id . ',id,wedding_id,' . $wedding->id],
            'budget_limit' => ['required', 'numeric', 'gt:0'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $category->update($validated);

        return Redirect::route('weddings.categories.index', $wedding);
    }

    /**
     * Remove the category (blocked if expenses exist).
     */
    public function destroy(Request $request, Wedding $wedding, Category $category, WeddingContext $context): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $this->authorizeCategory($wedding, $category);

        if ($category->expenses()->exists()) {
            return Redirect::route('weddings.categories.index', $wedding)
                ->with('error', 'No se puede eliminar una categoría con gastos existentes. Elimina o reasigna los gastos primero.');
        }

        $category->delete();

        return Redirect::route('weddings.categories.index', $wedding);
    }

    /**
     * Ensure the category belongs to the wedding.
     */
    private function authorizeCategory(Wedding $wedding, Category $category): void
    {
        if ($category->wedding_id !== $wedding->id) {
            abort(403);
        }
    }
}
