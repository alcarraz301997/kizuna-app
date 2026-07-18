<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Expense;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_vendor_index_page_is_displayed(): void
    {
        Vendor::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->get('/vendors');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Vendors/Index')
                ->has('vendors', 3)
                ->has('serviceCategories')
                ->has('filters')
        );
    }

    public function test_vendor_index_filters_by_service_category(): void
    {
        Vendor::factory()->create([
            'user_id' => $this->user->id,
            'service_category' => 'Fotografía',
        ]);
        Vendor::factory()->create([
            'user_id' => $this->user->id,
            'service_category' => 'Catering',
        ]);
        Vendor::factory()->create([
            'user_id' => $this->user->id,
            'service_category' => 'Música',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/vendors?service_category=' . urlencode('Fotografía'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page->has('vendors', 1)
        );
    }

    public function test_vendor_create_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/vendors/create');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Vendors/Create')
                ->has('paymentStatuses')
        );
    }

    public function test_vendor_can_be_created(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/vendors', [
                'name' => 'Fotógrafo Martínez',
                'service_category' => 'Fotografía',
                'contact_phone' => '+51 999 888 777',
                'contact_email' => 'foto@ejemplo.com',
                'payment_status' => 'no_iniciado',
                'notes' => 'Recomendado por amigos',
            ]);

        $response->assertRedirect('/vendors');

        $this->assertDatabaseHas('vendors', [
            'name' => 'Fotógrafo Martínez',
            'service_category' => 'Fotografía',
            'contact_phone' => '+51 999 888 777',
            'contact_email' => 'foto@ejemplo.com',
            'payment_status' => 'no_iniciado',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_vendor_requires_name(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/vendors/create')
            ->post('/vendors', [
                'name' => '',
                'service_category' => 'Fotografía',
                'payment_status' => 'no_iniciado',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('vendors', 0);
    }

    public function test_vendor_requires_service_category(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/vendors/create')
            ->post('/vendors', [
                'name' => 'Fotógrafo Martínez',
                'service_category' => '',
                'payment_status' => 'no_iniciado',
            ]);

        $response->assertSessionHasErrors('service_category');
        $this->assertDatabaseCount('vendors', 0);
    }

    public function test_vendor_requires_valid_payment_status(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/vendors/create')
            ->post('/vendors', [
                'name' => 'Fotógrafo Martínez',
                'service_category' => 'Fotografía',
                'payment_status' => 'invalid',
            ]);

        $response->assertSessionHasErrors('payment_status');
    }

    public function test_vendor_requires_valid_email(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/vendors/create')
            ->post('/vendors', [
                'name' => 'Fotógrafo Martínez',
                'service_category' => 'Fotografía',
                'payment_status' => 'no_iniciado',
                'contact_email' => 'not-an-email',
            ]);

        $response->assertSessionHasErrors('contact_email');
    }

    public function test_vendor_name_must_be_unique_per_user(): void
    {
        Vendor::factory()->create([
            'name' => 'Fotógrafo Martínez',
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->from('/vendors/create')
            ->post('/vendors', [
                'name' => 'Fotógrafo Martínez',
                'service_category' => 'Fotografía',
                'payment_status' => 'no_iniciado',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_vendor_name_can_be_duplicated_across_users(): void
    {
        $otherUser = User::factory()->create();
        Vendor::factory()->create([
            'name' => 'Fotógrafo Martínez',
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post('/vendors', [
                'name' => 'Fotógrafo Martínez',
                'service_category' => 'Fotografía',
                'payment_status' => 'no_iniciado',
            ]);

        $response->assertRedirect('/vendors');
        $this->assertDatabaseCount('vendors', 2);
    }

    public function test_vendor_edit_page_is_displayed(): void
    {
        $vendor = Vendor::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->get("/vendors/{$vendor->id}/edit");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Vendors/Edit')
                ->has('vendor')
                ->has('paymentStatuses')
        );
    }

    public function test_vendor_can_be_updated(): void
    {
        $vendor = Vendor::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Fotógrafo Martínez',
            'payment_status' => 'no_iniciado',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/vendors/{$vendor->id}", [
                'name' => 'Fotógrafo Martínez Updated',
                'service_category' => 'Fotografía',
                'payment_status' => 'pagado_completo',
                'contact_email' => 'nuevo@email.com',
            ]);

        $response->assertRedirect('/vendors');

        $vendor->refresh();
        $this->assertSame('Fotógrafo Martínez Updated', $vendor->name);
        $this->assertSame('pagado_completo', $vendor->payment_status->value);
        $this->assertSame('nuevo@email.com', $vendor->contact_email);
    }

    public function test_vendor_can_be_deleted_when_no_expenses(): void
    {
        $vendor = Vendor::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/vendors/{$vendor->id}");

        $response->assertRedirect('/vendors');
        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    public function test_vendor_deletion_blocked_when_expenses_exist(): void
    {
        $vendor = Vendor::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        Expense::factory()->create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/vendors/{$vendor->id}");

        $response->assertRedirect('/vendors');
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);
    }

    public function test_vendor_index_only_shows_user_vendors(): void
    {
        $otherUser = User::factory()->create();
        Vendor::factory()->count(2)->create(['user_id' => $this->user->id]);
        Vendor::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->get('/vendors');

        $response->assertInertia(
            fn ($page) => $page->has('vendors', 2)
        );
    }

    public function test_unauthenticated_user_cannot_access_vendors(): void
    {
        $this->get('/vendors')->assertRedirect('/login');
        $this->get('/vendors/create')->assertRedirect('/login');
    }

    public function test_cannot_edit_other_users_vendor(): void
    {
        $otherUser = User::factory()->create();
        $vendor = Vendor::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->get("/vendors/{$vendor->id}/edit");

        $response->assertStatus(403);
    }

    public function test_cannot_delete_other_users_vendor(): void
    {
        $otherUser = User::factory()->create();
        $vendor = Vendor::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/vendors/{$vendor->id}");

        $response->assertStatus(403);
    }
}
