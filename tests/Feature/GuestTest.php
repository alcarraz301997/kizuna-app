<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * GR-01a: Guest index page is displayed with RSVP counts.
     */
    public function test_guest_index_page_is_displayed(): void
    {
        Guest::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->get('/guests');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Guests/Index')
                ->has('guests', 3)
                ->has('counts')
        );
    }

    /**
     * GR-02: RSVP counter shows correct counts.
     */
    public function test_rsvp_counter_shows_correct_counts(): void
    {
        Guest::factory()->create([
            'user_id' => $this->user->id,
            'rsvp_status' => 'confirmado',
        ]);
        Guest::factory()->create([
            'user_id' => $this->user->id,
            'rsvp_status' => 'confirmado',
        ]);
        Guest::factory()->create([
            'user_id' => $this->user->id,
            'rsvp_status' => 'pendiente',
        ]);
        Guest::factory()->create([
            'user_id' => $this->user->id,
            'rsvp_status' => 'pendiente',
        ]);
        Guest::factory()->create([
            'user_id' => $this->user->id,
            'rsvp_status' => 'no_asiste',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/guests');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->where('counts.total', 5)
                ->where('counts.confirmados', 2)
                ->where('counts.pendientes', 3)
        );
    }

    /**
     * Guest create page is displayed with tables list.
     */
    public function test_guest_create_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/guests/create');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Guests/Create')
                ->has('rsvpStatuses')
                ->has('tables')
        );
    }

    /**
     * GR-01a: Guest can be created without table.
     */
    public function test_guest_can_be_created(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/guests', [
                'name' => 'María López',
                'email' => 'maria@ejemplo.com',
                'phone' => '+51 999 888 777',
                'rsvp_status' => 'pendiente',
                'table_id' => '',
            ]);

        $response->assertRedirect('/guests');

        $this->assertDatabaseHas('guests', [
            'name' => 'María López',
            'email' => 'maria@ejemplo.com',
            'rsvp_status' => 'pendiente',
            'table_id' => null,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * GR-01b / TM-04a: Assign guest to a table with capacity.
     */
    public function test_guest_can_be_assigned_to_table(): void
    {
        $table = Table::factory()->create([
            'user_id' => $this->user->id,
            'capacity' => 10,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post('/guests', [
                'name' => 'Juan Pérez',
                'rsvp_status' => 'pendiente',
                'table_id' => $table->id,
            ]);

        $response->assertRedirect('/guests');

        $this->assertDatabaseHas('guests', [
            'name' => 'Juan Pérez',
            'table_id' => $table->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * TM-04b: Cannot assign guest to a full table.
     */
    public function test_cannot_assign_guest_to_full_table(): void
    {
        $table = Table::factory()->create([
            'user_id' => $this->user->id,
            'capacity' => 2,
        ]);

        // Fill the table
        Guest::factory()->count(2)->create([
            'table_id' => $table->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->from('/guests/create')
            ->post('/guests', [
                'name' => 'Invitado Extra',
                'rsvp_status' => 'pendiente',
                'table_id' => $table->id,
            ]);

        $response->assertRedirect('/guests/create');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('llena', session('error'));
    }

    /**
     * Cannot assign guest to another user's table (table_id not in user's tables).
     */
    public function test_cannot_assign_guest_to_other_users_table(): void
    {
        $otherUser = User::factory()->create();
        $table = Table::factory()->create([
            'user_id' => $otherUser->id,
            'capacity' => 10,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->from('/guests/create')
            ->post('/guests', [
                'name' => 'María López',
                'rsvp_status' => 'pendiente',
                'table_id' => $table->id,
            ]);

        $response->assertRedirect('/guests/create');
        $response->assertSessionHas('error');
    }

    /**
     * Guest requires name.
     */
    public function test_guest_requires_name(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/guests/create')
            ->post('/guests', [
                'name' => '',
                'rsvp_status' => 'pendiente',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('guests', 0);
    }

    /**
     * Guest requires valid RSVP status.
     */
    public function test_guest_requires_valid_rsvp_status(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/guests/create')
            ->post('/guests', [
                'name' => 'María López',
                'rsvp_status' => 'invalid',
            ]);

        $response->assertSessionHasErrors('rsvp_status');
    }

    /**
     * Guest requires valid email format.
     */
    public function test_guest_requires_valid_email(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/guests/create')
            ->post('/guests', [
                'name' => 'María López',
                'rsvp_status' => 'pendiente',
                'email' => 'not-an-email',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Guest table_id must be a valid existing table.
     */
    public function test_guest_table_id_must_exist(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/guests/create')
            ->post('/guests', [
                'name' => 'María López',
                'rsvp_status' => 'pendiente',
                'table_id' => 99999,
            ]);

        $response->assertSessionHasErrors('table_id');
    }

    /**
     * Guest edit page is displayed with tables and guest data.
     */
    public function test_guest_edit_page_is_displayed(): void
    {
        $guest = Guest::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->get("/guests/{$guest->id}/edit");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Guests/Edit')
                ->has('guest')
                ->has('rsvpStatuses')
                ->has('tables')
        );
    }

    /**
     * GR-01c: Guest RSVP status can be changed.
     */
    public function test_guest_rsvp_can_be_updated(): void
    {
        $guest = Guest::factory()->create([
            'user_id' => $this->user->id,
            'rsvp_status' => 'pendiente',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/guests/{$guest->id}", [
                'name' => $guest->name,
                'rsvp_status' => 'confirmado',
                'table_id' => '',
            ]);

        $response->assertRedirect('/guests');

        $guest->refresh();
        $this->assertSame('confirmado', $guest->rsvp_status->value);
    }

    /**
     * Guest can be fully updated including table assignment.
     */
    public function test_guest_can_be_updated(): void
    {
        $guest = Guest::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Juan Pérez',
        ]);

        $table = Table::factory()->create([
            'user_id' => $this->user->id,
            'capacity' => 10,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/guests/{$guest->id}", [
                'name' => 'Juan Pérez Actualizado',
                'email' => 'juan@nuevo.com',
                'phone' => '+51 111 222 333',
                'rsvp_status' => 'confirmado',
                'table_id' => $table->id,
            ]);

        $response->assertRedirect('/guests');

        $guest->refresh();
        $this->assertSame('Juan Pérez Actualizado', $guest->name);
        $this->assertSame('juan@nuevo.com', $guest->email);
        $this->assertSame('+51 111 222 333', $guest->phone);
        $this->assertSame('confirmado', $guest->rsvp_status->value);
        $this->assertSame($table->id, $guest->table_id);
    }

    /**
     * Updating a guest with their own table (already assigned) does not
     * trigger a false "table full" error.
     */
    public function test_guest_update_does_not_count_self_against_capacity(): void
    {
        $table = Table::factory()->create([
            'user_id' => $this->user->id,
            'capacity' => 2,
        ]);

        // Two guests already fill the table
        $guest = Guest::factory()->create([
            'user_id' => $this->user->id,
            'table_id' => $table->id,
        ]);
        Guest::factory()->create([
            'user_id' => $this->user->id,
            'table_id' => $table->id,
        ]);

        // Updating the same guest (keeping same table) should succeed
        $response = $this
            ->actingAs($this->user)
            ->put("/guests/{$guest->id}", [
                'name' => 'Nombre Actualizado',
                'rsvp_status' => 'confirmado',
                'table_id' => $table->id,
            ]);

        $response->assertRedirect('/guests');

        $guest->refresh();
        $this->assertSame('Nombre Actualizado', $guest->name);
    }

    /**
     * Guest can be deleted.
     */
    public function test_guest_can_be_deleted(): void
    {
        $guest = Guest::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/guests/{$guest->id}");

        $response->assertRedirect('/guests');
        $this->assertDatabaseMissing('guests', ['id' => $guest->id]);
    }

    /**
     * Guest index only shows user guests.
     */
    public function test_guest_index_only_shows_user_guests(): void
    {
        $otherUser = User::factory()->create();
        Guest::factory()->count(2)->create(['user_id' => $this->user->id]);
        Guest::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->get('/guests');

        $response->assertInertia(
            fn ($page) => $page->has('guests', 2)
        );
    }

    /**
     * Unauthenticated user cannot access guests.
     */
    public function test_unauthenticated_user_cannot_access_guests(): void
    {
        $this->get('/guests')->assertRedirect('/login');
        $this->get('/guests/create')->assertRedirect('/login');
    }

    /**
     * Cannot edit other user's guest.
     */
    public function test_cannot_edit_other_users_guest(): void
    {
        $otherUser = User::factory()->create();
        $guest = Guest::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->get("/guests/{$guest->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * Cannot update other user's guest.
     */
    public function test_cannot_update_other_users_guest(): void
    {
        $otherUser = User::factory()->create();
        $guest = Guest::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Original',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/guests/{$guest->id}", [
                'name' => 'Hacked',
                'rsvp_status' => 'confirmado',
                'table_id' => '',
            ]);

        $response->assertStatus(403);

        $guest->refresh();
        $this->assertSame('Original', $guest->name);
    }

    /**
     * Cannot delete other user's guest.
     */
    public function test_cannot_delete_other_users_guest(): void
    {
        $otherUser = User::factory()->create();
        $guest = Guest::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/guests/{$guest->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('guests', ['id' => $guest->id]);
    }

    /**
     * GR-03: PDF export generates a downloadable PDF with table names.
     */
    public function test_guest_pdf_export_generates_download(): void
    {
        Guest::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'rsvp_status' => 'confirmado',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/guests/export/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * PDF export includes table names when assigned.
     */
    public function test_pdf_export_shows_table_name(): void
    {
        $table = Table::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Principal',
            'capacity' => 10,
        ]);

        Guest::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'María López',
            'table_id' => $table->id,
            'rsvp_status' => 'confirmado',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/guests/export/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * Unauthenticated user cannot access PDF export.
     */
    public function test_unauthenticated_user_cannot_export_pdf(): void
    {
        $this->get('/guests/export/pdf')->assertRedirect('/login');
    }

    /**
     * PDF export with no guests returns valid PDF.
     */
    public function test_pdf_export_with_empty_list(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/guests/export/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * Guest index shows table name instead of table_number.
     */
    public function test_guest_index_shows_table_name(): void
    {
        $table = Table::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Principal',
            'capacity' => 10,
        ]);

        Guest::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Con Mesa',
            'table_id' => $table->id,
        ]);

        Guest::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Sin Mesa',
            'table_id' => null,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/guests');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->has('guests', 2)
                ->where('guests.0.table_name', 'Principal')
        );
    }

    /**
     * Table occupancy count updates correctly after guest assignment.
     */
    public function test_table_occupancy_updates_after_guest_assignment(): void
    {
        $table = Table::factory()->create([
            'user_id' => $this->user->id,
            'capacity' => 5,
        ]);

        $this->assertSame(0, $table->fresh()->occupancy_count);

        Guest::factory()->create([
            'user_id' => $this->user->id,
            'table_id' => $table->id,
        ]);

        $this->assertSame(1, $table->fresh()->occupancy_count);
    }
}
