<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddWeddingMemberRequest;
use App\Models\Wedding;
use App\Services\WeddingMembershipService;
use Illuminate\Http\RedirectResponse;

class WeddingMemberController extends Controller
{
    public function store(AddWeddingMemberRequest $request, Wedding $wedding, WeddingMembershipService $service): RedirectResponse
    {
        $service->addMember($wedding, (int) $request->validated('user_id'), $request->validated('role'));

        return redirect()->route('weddings.show', $wedding);
    }
}
