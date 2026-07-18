<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * TM-01a: Table can be created.
     */
    public function test_table_can_be_created(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/tables', [
                'name' => 'Principal',
                'capacity' => 10,
            ]);

        $response->assertRedirect('/tables');

        $this->assertDatabaseHas('tables', [
            'name' => 'Principal',
            'capacity' => 10,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * TM-01: Table requires name.
     */
    public function test_table_requires_name(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/tables/create')
            ->post('/tables', [
                'name' => '',
                'capacity' => 10,
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('tables', 0);
    }

    /**
     * TM-01: Table requires capacity.
     */
    public function test_table_requires_capacity(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/tables/create')
            ->post('/tables', [
                'name' => 'Principal',
                'capacity' => '',
            ]);

        $response->assertSessionHasErrors('capacity');
        $this->assertDatabaseCount('tables', 0);
    }

    /**
     * Table capacity must be at least 1.
     */
    public function test_table_capacity_must_be_positive(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/tables/create')
            ->post('/tables', [
                'name' => 'Principal',
                'capacity' => 0,
            ]);

        $response->assertSessionHasErrors('capacity');
        $this->assertDatabaseCount('tables', 0);
    }

    /**
     * TM-01b: Table name must be unique per user.
     */
    public function test_table_name_must_be_unique_per_user(): void
    {
        Table::factory()->create([
            'name' => 'Principal',
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->from('/tables/create')
            ->post('/tables', [
                'name' => 'Principal',
                'capacity' => 10,
            ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Table name can be duplicated across users.
     */
    public function test_table_name_can_be_duplicated_across_users(): void
    {
        $otherUser = User::factory()->create();
        Table::factory()->create([
            'name' => 'Principal',
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post('/tables', [
                'name' => 'Principal',
                'capacity' => 10,
            ]);

        $response->assertRedirect('/tables');
        $this->assertDatabaseCount('tables', 2);
    }

    /**
     * TM-01: Table can be updated.
     */
    public function test_table_can_be_updated(): void
    {
        $table = Table::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Principal',
            'capacity' => 10,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/tables/{$table->id}", [
                'name' => 'Jardín',
                'capacity' => 12,
            ]);

        $response->assertRedirect('/tables');

        $table->refresh();
        $this->assertSame('Jardín', $table->name);
        $this->assertSame(12, $table->capacity);
    }

    /**
     * TM-02b: Table can be deleted when no guests.
     */
    public function test_table_can_be_deleted_when_no_guests(): void
    {
        $table = Table::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/tables/{$table->id}");

        $response->assertRedirect('/tables');
        $this->assertDatabaseMissing('tables', ['id' => $table->id]);
    }

    /**
     * TM-02a: Table deletion blocked when guests exist.
     *
     * Uses table_number as the temporary FK (will become table_id in PR 2).
     */
    public function test_table_deletion_blocked_when_guests_exist(): void
    {
        $table = Table::factory()->create(['user_id' => $this->user->id]);
        Guest::factory()->create([
            'user_id' => $this->user->id,
            'table_number' => $table->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/tables/{$table->id}");

        $response->assertRedirect('/tables');
        $this->assertDatabaseHas('tables', ['id' => $table->id]);
    }

    /**
     * Table index only shows user tables (tested via redirect, no page visit needed).
     */
    public function test_table_store_scoped_to_user(): void
    {
        $otherUser = User::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->post('/tables', [
                'name' => 'Mi Mesa',
                'capacity' => 8,
            ]);

        $response->assertRedirect('/tables');

        $this->assertDatabaseHas('tables', [
            'name' => 'Mi Mesa',
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseMissing('tables', [
            'name' => 'Mi Mesa',
            'user_id' => $otherUser->id,
        ]);
    }

    /**
     * Unauthenticated user cannot access tables.
     */
    public function test_unauthenticated_user_cannot_access_tables(): void
    {
        $this->get('/tables')->assertRedirect('/login');
        $this->post('/tables', [
            'name' => 'Principal',
            'capacity' => 10,
        ])->assertRedirect('/login');
    }

    /**
     * Cannot update other user's table.
     */
    public function test_cannot_update_other_users_table(): void
    {
        $otherUser = User::factory()->create();
        $table = Table::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Original',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/tables/{$table->id}", [
                'name' => 'Hacked',
                'capacity' => 10,
            ]);

        $response->assertStatus(403);

        $table->refresh();
        $this->assertSame('Original', $table->name);
    }

    /**
     * Cannot delete other user's table.
     */
    public function test_cannot_delete_other_users_table(): void
    {
        $otherUser = User::factory()->create();
        $table = Table::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/tables/{$table->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tables', ['id' => $table->id]);
    }
}
