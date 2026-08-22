<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'wedding' => function () use ($request) {
                $user = $request->user();
                if (! $user) {
                    return null;
                }

                $wedding = $user->weddings()->first()
                    ?? $user->weddingMemberships()->with('wedding:id,name')->first()?->wedding
                    ?? app(\App\Services\WeddingMembershipService::class)->createForOwner($user, 'Mi Boda');

                return $wedding ? ['id' => $wedding->id, 'name' => $wedding->name] : null;
            },
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
