<?php

namespace App\Http\Controllers;

use App\Models\CategoryTemplate;
use App\Models\Wedding;
use App\Services\CategoryTemplateService;
use App\Services\WeddingContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategoryTemplateController extends Controller
{
    public function store(Request $request, Wedding $wedding, WeddingContext $context, CategoryTemplateService $service): RedirectResponse
    {
        $context->authorize($request, $wedding);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.budget_limit' => ['nullable', 'numeric', 'gte:0'],
            'items.*.color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'items.*.parent_index' => ['nullable', 'integer', 'min:0'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $service->create($wedding, $validated['name'], $validated['items']);

        return redirect()->route('weddings.category-templates.index', $wedding);
    }

    public function index(Request $request, Wedding $wedding, WeddingContext $context): Response|JsonResponse
    {
        $context->authorize($request, $wedding);
        $templates = $wedding->categoryTemplates()->with('items')->orderBy('name')->get();
        $props = ['templates' => $templates];

        if ($request->expectsJson()) {
            return response()->json($props);
        }

        return Inertia::render('Weddings/CategoryTemplates', $props);
    }

    public function apply(Request $request, Wedding $wedding, CategoryTemplate $template, WeddingContext $context, CategoryTemplateService $service): RedirectResponse|JsonResponse
    {
        $context->authorize($request, $wedding);
        abort_unless($template->wedding_id === $wedding->id, 404);
        $categories = $service->apply($wedding, $template);

        if ($request->expectsJson()) {
            return response()->json(['categories' => $categories]);
        }

        return redirect()->route('weddings.category-rollups', $wedding);
    }

    public function rollups(Request $request, Wedding $wedding, WeddingContext $context, CategoryTemplateService $service): Response|JsonResponse
    {
        $context->authorize($request, $wedding);
        $props = ['categories' => $service->rollups($wedding)];

        if ($request->expectsJson()) {
            return response()->json($props);
        }

        return Inertia::render('Weddings/CategoryRollups', $props);
    }
}
