<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_category_index_page_is_displayed(): void
    {
        Category::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->get('/categories');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Categories/Index')
                ->has('categories', 3)
        );
    }

    public function test_category_create_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/categories/create');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page->component('Categories/Create')
        );
    }

    public function test_category_can_be_created(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/categories', [
                'name' => 'Venue',
                'budget_limit' => 5000.00,
                'color' => '#7c3aed',
            ]);

        $response->assertRedirect('/categories');

        $this->assertDatabaseHas('categories', [
            'name' => 'Venue',
            'budget_limit' => 5000.00,
            'color' => '#7c3aed',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_category_requires_name(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/categories/create')
            ->post('/categories', [
                'name' => '',
                'budget_limit' => 5000,
                'color' => '#7c3aed',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_category_requires_positive_budget_limit(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/categories/create')
            ->post('/categories', [
                'name' => 'Venue',
                'budget_limit' => 0,
                'color' => '#7c3aed',
            ]);

        $response->assertSessionHasErrors('budget_limit');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_category_rejects_negative_budget_limit(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/categories/create')
            ->post('/categories', [
                'name' => 'Venue',
                'budget_limit' => -100,
                'color' => '#7c3aed',
            ]);

        $response->assertSessionHasErrors('budget_limit');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_category_requires_budget_limit(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/categories/create')
            ->post('/categories', [
                'name' => 'Venue',
                'color' => '#7c3aed',
            ]);

        $response->assertSessionHasErrors('budget_limit');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_category_requires_valid_color(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/categories/create')
            ->post('/categories', [
                'name' => 'Venue',
                'budget_limit' => 5000,
                'color' => 'red',
            ]);

        $response->assertSessionHasErrors('color');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_category_name_must_be_unique_per_user(): void
    {
        Category::factory()->create([
            'name' => 'Venue',
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->from('/categories/create')
            ->post('/categories', [
                'name' => 'Venue',
                'budget_limit' => 5000,
                'color' => '#7c3aed',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_name_can_be_duplicated_across_users(): void
    {
        $otherUser = User::factory()->create();
        Category::factory()->create([
            'name' => 'Venue',
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post('/categories', [
                'name' => 'Venue',
                'budget_limit' => 5000,
                'color' => '#7c3aed',
            ]);

        $response->assertRedirect('/categories');
        $this->assertDatabaseCount('categories', 2);
    }

    public function test_category_edit_page_is_displayed(): void
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->get("/categories/{$category->id}/edit");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Categories/Edit')
                ->has('category')
        );
    }

    public function test_category_can_be_updated(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Venue',
            'budget_limit' => 5000,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/categories/{$category->id}", [
                'name' => 'Venue Updated',
                'budget_limit' => 6000,
                'color' => '#ff0000',
            ]);

        $response->assertRedirect('/categories');

        $category->refresh();
        $this->assertSame('Venue Updated', $category->name);
        $this->assertSame('6000.00', $category->budget_limit);
        $this->assertSame('#ff0000', $category->color);
    }

    public function test_category_can_be_deleted_when_no_expenses(): void
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/categories/{$category->id}");

        $response->assertRedirect('/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_deletion_blocked_when_expenses_exist(): void
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/categories/{$category->id}");

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_index_only_shows_user_categories(): void
    {
        $otherUser = User::factory()->create();
        Category::factory()->count(2)->create(['user_id' => $this->user->id]);
        Category::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->get('/categories');

        $response->assertInertia(
            fn ($page) => $page->has('categories', 2)
        );
    }

    public function test_unauthenticated_user_cannot_access_categories(): void
    {
        $this->get('/categories')->assertRedirect('/login');
        $this->get('/categories/create')->assertRedirect('/login');
    }

    public function test_cannot_edit_other_users_category(): void
    {
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->get("/categories/{$category->id}/edit");

        $response->assertStatus(403);
    }
}
