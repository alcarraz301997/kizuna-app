<?php

namespace Tests\Feature;

use App\Models\Guest;
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
     * GR-01a: Guest create page is displayed.
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
        );
    }

    /**
     * GR-01a: Guest can be created.
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
                'table_number' => null,
            ]);

        $response->assertRedirect('/guests');

        $this->assertDatabaseHas('guests', [
            'name' => 'María López',
            'email' => 'maria@ejemplo.com',
            'rsvp_status' => 'pendiente',
            'table_number' => null,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * GR-01b: Assign table number to guest.
     */
    public function test_guest_can_be_created_with_table_number(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/guests', [
                'name' => 'Juan Pérez',
                'rsvp_status' => 'pendiente',
                'table_number' => 3,
            ]);

        $response->assertRedirect('/guests');

        $this->assertDatabaseHas('guests', [
            'name' => 'Juan Pérez',
            'table_number' => 3,
            'user_id' => $this->user->id,
        ]);
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
     * Guest requires table_number to be integer.
     */
    public function test_guest_table_number_must_be_integer(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/guests/create')
            ->post('/guests', [
                'name' => 'María López',
                'rsvp_status' => 'pendiente',
                'table_number' => 'abc',
            ]);

        $response->assertSessionHasErrors('table_number');
    }

    /**
     * Guest edit page is displayed.
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
            ]);

        $response->assertRedirect('/guests');

        $guest->refresh();
        $this->assertSame('confirmado', $guest->rsvp_status->value);
    }

    /**
     * Guest can be fully updated.
     */
    public function test_guest_can_be_updated(): void
    {
        $guest = Guest::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Juan Pérez',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/guests/{$guest->id}", [
                'name' => 'Juan Pérez Actualizado',
                'email' => 'juan@nuevo.com',
                'phone' => '+51 111 222 333',
                'rsvp_status' => 'confirmado',
                'table_number' => 5,
            ]);

        $response->assertRedirect('/guests');

        $guest->refresh();
        $this->assertSame('Juan Pérez Actualizado', $guest->name);
        $this->assertSame('juan@nuevo.com', $guest->email);
        $this->assertSame('+51 111 222 333', $guest->phone);
        $this->assertSame('confirmado', $guest->rsvp_status->value);
        $this->assertSame(5, $guest->table_number);
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
     * GR-03: PDF export generates a downloadable PDF.
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
}
