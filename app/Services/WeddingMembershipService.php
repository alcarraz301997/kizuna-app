<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingMember;
use Illuminate\Support\Facades\DB;

class WeddingMembershipService
{
    public function createForOwner(User $owner, string $name): Wedding
    {
        return DB::transaction(function () use ($owner, $name): Wedding {
            $wedding = Wedding::create(['owner_id' => $owner->id, 'name' => $name]);
            $wedding->members()->create(['user_id' => $owner->id, 'role' => 'owner']);

            return $wedding;
        });
    }

    public function addMember(Wedding $wedding, int|User|string $userOrIdOrEmail, string $role): WeddingMember
    {
        $userId = match (true) {
            $userOrIdOrEmail instanceof User => $userOrIdOrEmail->id,
            is_numeric($userOrIdOrEmail) => (int) $userOrIdOrEmail,
            is_string($userOrIdOrEmail) => User::where('email', $userOrIdOrEmail)->firstOrFail()->id,
        };

        return DB::transaction(fn (): WeddingMember => $wedding->members()->firstOrCreate(
            ['user_id' => $userId],
            ['role' => $role]
        ));
    }

    public function backfillLegacyRecords(): void
    {
        DB::transaction(function (): void {
            User::query()->orderBy('id')->each(function (User $user): void {
                $wedding = Wedding::firstOrCreate(['owner_id' => $user->id], ['name' => 'Wedding Workspace']);
                WeddingMember::firstOrCreate(
                    ['wedding_id' => $wedding->id, 'user_id' => $user->id],
                    ['role' => 'owner'],
                );

                foreach (['categories', 'expenses', 'vendors', 'guests', 'tables'] as $table) {
                    DB::table($table)->where('user_id', $user->id)->whereNull('wedding_id')->update(['wedding_id' => $wedding->id]);
                }

                DB::table('expenses')->where('wedding_id', $wedding->id)->where('status', 'planned')->whereNull('planned_amount')->update(['planned_amount' => DB::raw('amount')]);
                DB::table('expenses')->where('wedding_id', $wedding->id)->where('status', 'contracted')->whereNull('contracted_amount')->update(['contracted_amount' => DB::raw('amount')]);
                DB::table('expenses')->where('wedding_id', $wedding->id)->where('status', 'paid')->whereNull('contracted_amount')->update(['contracted_amount' => DB::raw('amount')]);

                DB::table('expenses')->where('wedding_id', $wedding->id)->where('status', 'paid')->orderBy('id')->each(function (object $expense): void {
                    DB::table('expense_payments')->updateOrInsert(
                        ['legacy_key' => 'legacy_paid:'.$expense->id],
                        ['expense_id' => $expense->id, 'origin' => 'legacy_paid', 'amount' => $expense->amount, 'paid_on' => $expense->paid_date, 'kind' => 'payment', 'updated_at' => now(), 'created_at' => now()],
                    );
                });
            });
        });
    }
}
