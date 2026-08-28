<?php

namespace App\Services;

use App\Models\Wedding;
use Illuminate\Http\Request;

class WeddingContext
{
    public function authorize(Request $request, Wedding $wedding): Wedding
    {
        $userId = $request->user()?->id;

        if (! $userId) {
            abort(403);
        }

        $isMember = $wedding->members()->where('user_id', $userId)->exists()
            || $wedding->owner_id === $userId;

        if ($wedding->owner_id === $userId && ! $wedding->members()->where('user_id', $userId)->exists()) {
            $wedding->members()->firstOrCreate(['user_id' => $userId], ['role' => 'owner']);
            $isMember = true;
        }

        abort_unless($isMember, 403);

        return $wedding;
    }

    public function isOwner(Request $request, Wedding $wedding): bool
    {
        $userId = $request->user()?->id;

        if (! $userId) {
            return false;
        }

        return $wedding->owner_id === $userId
            || $wedding->members()->where('user_id', $userId)->where('role', 'owner')->exists();
    }

    public function current(Request $request): ?Wedding
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        // Si hay una boda activa guardada en sesión, verificar que el usuario tenga acceso
        if ($activeId = $request->session()->get('active_wedding_id')) {
            $wedding = Wedding::find($activeId);
            if ($wedding && ($wedding->owner_id === $user->id || $wedding->members()->where('user_id', $user->id)->exists())) {
                return $wedding;
            }
        }

        // Si no, tomar la primera boda a la que pertenezca (membresía o dueño)
        $wedding = $user->weddings()->first()
            ?? $user->weddingMemberships()->with('wedding')->first()?->wedding;

        if ($wedding) {
            $request->session()->put('active_wedding_id', $wedding->id);
        }

        return $wedding;
    }

    public function setActive(Request $request, Wedding $wedding): void
    {
        $this->authorize($request, $wedding);
        $request->session()->put('active_wedding_id', $wedding->id);
    }

    public function getAvailableWeddings(Request $request): array
    {
        $user = $request->user();
        if (! $user) {
            return [];
        }

        $owned = $user->weddings()->select('id', 'name', 'owner_id')->get()->map(fn ($w) => [
            'id' => $w->id,
            'name' => $w->name,
            'role' => 'owner',
        ]);

        $memberships = $user->weddingMemberships()->with('wedding:id,name,owner_id')->get()
            ->filter(fn ($m) => $m->wedding !== null)
            ->map(fn ($m) => [
                'id' => $m->wedding->id,
                'name' => $m->wedding->name,
                'role' => $m->role,
            ]);

        return $owned->concat($memberships)->unique('id')->values()->all();
    }
}
