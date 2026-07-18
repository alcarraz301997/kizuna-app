<?php

namespace Tests\Feature;

use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuestTableMigrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * GR-04: Data migration from table_number to table_id.
     *
     * Simulates the old schema (table_number column exists, table_id does not),
     * inserts guests with table_number values, runs the migration logic,
     * and verifies tables are auto-created and guests are reassigned.
     */
    public function test_data_migration_creates_tables_and_reassigns_guests(): void
    {
        // Simulate old schema: drop the new FK column, add the old column back
        if (Schema::hasColumn('guests', 'table_id')) {
            Schema::table('guests', function ($table) {
                $table->dropForeign(['table_id']);
                $table->dropColumn('table_id');
            });
        }

        if (! Schema::hasColumn('guests', 'table_number')) {
            Schema::table('guests', function ($table) {
                $table->integer('table_number')->nullable()->after('user_id');
            });
        }

        // Insert guests with table_number values (old v2 pattern)
        DB::table('guests')->insert([
            ['user_id' => $this->user->id, 'name' => 'Invitado 1', 'rsvp_status' => 'pendiente', 'table_number' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->user->id, 'name' => 'Invitado 2', 'rsvp_status' => 'confirmado', 'table_number' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->user->id, 'name' => 'Invitado 3', 'rsvp_status' => 'pendiente', 'table_number' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->user->id, 'name' => 'Invitado 4', 'rsvp_status' => 'confirmado', 'table_number' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->user->id, 'name' => 'Invitado 5', 'rsvp_status' => 'pendiente', 'table_number' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->user->id, 'name' => 'Invitado 6', 'rsvp_status' => 'no_asiste', 'table_number' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Run data migration logic (same as in the migration's up())
        if (! Schema::hasColumn('guests', 'table_id')) {
            Schema::table('guests', function ($table) {
                $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            });
        }

        $groups = DB::table('guests')
            ->select('user_id', 'table_number', DB::raw('COUNT(*) as guest_count'))
            ->whereNotNull('table_number')
            ->groupBy('user_id', 'table_number')
            ->get();

        foreach ($groups as $group) {
            $t = Table::firstOrCreate(
                [
                    'user_id' => $group->user_id,
                    'name' => "Mesa {$group->table_number}",
                ],
                [
                    'capacity' => $group->guest_count,
                ]
            );

            DB::table('guests')
                ->where('user_id', $group->user_id)
                ->where('table_number', $group->table_number)
                ->update(['table_id' => $t->id]);
        }

        // Assertions
        // 1. Two tables were auto-created
        $this->assertDatabaseHas('tables', [
            'user_id' => $this->user->id,
            'name' => 'Mesa 1',
            'capacity' => 3,
        ]);
        $this->assertDatabaseHas('tables', [
            'user_id' => $this->user->id,
            'name' => 'Mesa 2',
            'capacity' => 2,
        ]);

        // 2. Guests with table_number 1 are assigned to Mesa 1
        $mesa1 = Table::where('name', 'Mesa 1')->first();
        $this->assertEquals(3, DB::table('guests')->where('table_id', $mesa1->id)->count());

        // 3. Guests with table_number 2 are assigned to Mesa 2
        $mesa2 = Table::where('name', 'Mesa 2')->first();
        $this->assertEquals(2, DB::table('guests')->where('table_id', $mesa2->id)->count());

        // 4. Guest without table_number still has null table_id
        $this->assertEquals(1, DB::table('guests')->whereNull('table_id')->count());
    }

    /**
     * Data migration is idempotent: running it twice does not duplicate data.
     */
    public function test_data_migration_is_idempotent(): void
    {
        // Set up old schema
        if (Schema::hasColumn('guests', 'table_id')) {
            Schema::table('guests', function ($table) {
                $table->dropForeign(['table_id']);
                $table->dropColumn('table_id');
            });
        }
        if (! Schema::hasColumn('guests', 'table_number')) {
            Schema::table('guests', function ($table) {
                $table->integer('table_number')->nullable()->after('user_id');
            });
        }

        DB::table('guests')->insert([
            ['user_id' => $this->user->id, 'name' => 'Invitado A', 'rsvp_status' => 'pendiente', 'table_number' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // First run
        if (! Schema::hasColumn('guests', 'table_id')) {
            Schema::table('guests', function ($table) {
                $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            });
        }

        $groups = DB::table('guests')
            ->select('user_id', 'table_number', DB::raw('COUNT(*) as guest_count'))
            ->whereNotNull('table_number')
            ->groupBy('user_id', 'table_number')
            ->get();

        foreach ($groups as $group) {
            Table::firstOrCreate(
                ['user_id' => $group->user_id, 'name' => "Mesa {$group->table_number}"],
                ['capacity' => $group->guest_count]
            );
        }

        // Second run (simulated — firstOrCreate should not duplicate)
        $groups2 = DB::table('guests')
            ->select('user_id', 'table_number', DB::raw('COUNT(*) as guest_count'))
            ->whereNotNull('table_number')
            ->groupBy('user_id', 'table_number')
            ->get();

        foreach ($groups2 as $group) {
            Table::firstOrCreate(
                ['user_id' => $group->user_id, 'name' => "Mesa {$group->table_number}"],
                ['capacity' => $group->guest_count]
            );
        }

        // Should still have exactly 1 table named "Mesa 1"
        $this->assertEquals(1, Table::where('name', 'Mesa 1')->count());
    }
}
