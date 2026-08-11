<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Services\WeddingContext;
use App\Services\WeddingMembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class WeddingController extends Controller
{
    public function store(Request $request, WeddingMembershipService $service): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $wedding = $service->createForOwner($request->user(), $validated['name']);

        return redirect()->route('weddings.show', $wedding);
    }

    public function show(Request $request, Wedding $wedding, WeddingContext $context): Response|JsonResponse
    {
        $context->authorize($request, $wedding);

        $props = [
            'wedding' => ['id' => $wedding->id, 'name' => $wedding->name],
            'role' => $wedding->members()->where('user_id', $request->user()->id)->value('role'),
            'members' => $wedding->members()->with('user:id,name,email')->get()->map(fn ($member) => [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'name' => $member->user->name,
                'role' => $member->role,
            ]),
        ];

        if ($request->expectsJson()) {
            return response()->json($props);
        }

        return Inertia::render('Weddings/Show', $props);
    }
}
