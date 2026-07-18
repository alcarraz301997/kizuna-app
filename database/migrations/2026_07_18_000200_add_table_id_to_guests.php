<?php

use App\Models\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add table_id nullable FK (idempotent)
        if (! Schema::hasColumn('guests', 'table_id')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            });
        }

        // Step 2: Data migration — only if table_number column still exists
        if (Schema::hasColumn('guests', 'table_number')) {
            // Get distinct table_number values with their guest counts
            $groups = DB::table('guests')
                ->select('user_id', 'table_number', DB::raw('COUNT(*) as guest_count'))
                ->whereNotNull('table_number')
                ->groupBy('user_id', 'table_number')
                ->get();

            foreach ($groups as $group) {
                // Create or find the table
                $table = Table::firstOrCreate(
                    [
                        'user_id' => $group->user_id,
                        'name' => "Mesa {$group->table_number}",
                    ],
                    [
                        'capacity' => $group->guest_count,
                    ]
                );

                // Update guests with this table_number to point to the table
                DB::table('guests')
                    ->where('user_id', $group->user_id)
                    ->where('table_number', $group->table_number)
                    ->update(['table_id' => $table->id]);
            }

            // Step 3: Drop table_number column
            Schema::table('guests', function (Blueprint $table) {
                $table->dropColumn('table_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add table_number column
        if (! Schema::hasColumn('guests', 'table_number')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->integer('table_number')->nullable()->after('table_id');
            });
        }

        // Drop table_id FK and column
        if (Schema::hasColumn('guests', 'table_id')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->dropForeign(['table_id']);
                $table->dropColumn('table_id');
            });
        }
    }
};
