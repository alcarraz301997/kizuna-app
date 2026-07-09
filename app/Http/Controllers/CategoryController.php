<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): Response
    {
        $categories = $request->user()->categories()
            ->withSum(['expenses as spent' => fn ($q) => $q->whereIn('status', ['contracted', 'paid'])], 'amount')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'budget_limit' => $c->budget_limit,
                'color' => $c->color,
                'spent' => (float) ($c->spent ?? 0),
                'remaining' => (float) $c->budget_limit - (float) ($c->spent ?? 0),
                'expenses_count' => $c->expenses()->count(),
            ]);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        return Inertia::render('Categories/Create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,NULL,id,user_id,' . $request->user()->id],
            'budget_limit' => ['required', 'numeric', 'gt:0'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $request->user()->categories()->create($validated);

        return Redirect::route('categories.index');
    }

    /**
     * Show the form for editing the category.
     */
    public function edit(Request $request, Category $category): Response
    {
        $this->authorizeCategory($request, $category);

        return Inertia::render('Categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'budget_limit' => $category->budget_limit,
                'color' => $category->color,
            ],
        ]);
    }

    /**
     * Update the category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($request, $category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id . ',id,user_id,' . $request->user()->id],
            'budget_limit' => ['required', 'numeric', 'gt:0'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $category->update($validated);

        return Redirect::route('categories.index');
    }

    /**
     * Remove the category (blocked if expenses exist).
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($request, $category);

        if ($category->expenses()->exists()) {
            return Redirect::route('categories.index')
                ->with('error', 'Cannot delete category with existing expenses. Remove or reassign expenses first.');
        }

        $category->delete();

        return Redirect::route('categories.index');
    }

    /**
     * Ensure the category belongs the authenticated user.
     */
    private function authorizeCategory(Request $request, Category $category): void
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
