<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('users')->orderBy('id')->each(function (object $user): void {
                $wedding = DB::table('weddings')->where('owner_id', $user->id)->first();
                $weddingId = $wedding?->id ?? DB::table('weddings')->insertGetId([
                    'owner_id' => $user->id,
                    'name' => 'Wedding Workspace',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('wedding_members')->updateOrInsert(
                    ['wedding_id' => $weddingId, 'user_id' => $user->id],
                    ['role' => 'owner', 'updated_at' => now(), 'created_at' => now()],
                );

                foreach (['categories', 'expenses', 'vendors', 'guests', 'tables'] as $table) {
                    DB::table($table)->where('user_id', $user->id)->whereNull('wedding_id')->update(['wedding_id' => $weddingId]);
                }

                DB::table('expenses')->where('wedding_id', $weddingId)->where('status', 'planned')->whereNull('planned_amount')->update(['planned_amount' => DB::raw('amount')]);
                DB::table('expenses')->where('wedding_id', $weddingId)->where('status', 'contracted')->whereNull('contracted_amount')->update(['contracted_amount' => DB::raw('amount')]);
                DB::table('expenses')->where('wedding_id', $weddingId)->where('status', 'paid')->whereNull('contracted_amount')->update(['contracted_amount' => DB::raw('amount')]);

                DB::table('expenses')->where('wedding_id', $weddingId)->where('status', 'paid')->orderBy('id')->each(function (object $expense): void {
                    DB::table('expense_payments')->updateOrInsert(
                        ['legacy_key' => 'legacy_paid:'.$expense->id],
                        ['expense_id' => $expense->id, 'origin' => 'legacy_paid', 'amount' => $expense->amount, 'paid_on' => $expense->paid_date, 'kind' => 'payment', 'updated_at' => now(), 'created_at' => now()],
                    );
                });
            });
        });
    }

    public function down(): void
    {
        // Legacy rows and their additive workspace mapping are intentionally retained.
    }
};
