<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Dashboard')
                ->has('categories')
                ->has('totals')
                ->has('totals.total_budget')
                ->has('totals.total_spent')
                ->has('totals.total_planned')
                ->has('totals.total_remaining')
        );
    }

    public function test_dashboard_shows_correct_totals(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'budget_limit' => 10000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 4000,
            'status' => 'paid',
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 1000,
            'status' => 'contracted',
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 500,
            'status' => 'planned',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page
                ->where('totals.total_budget', 10000)
                ->where('totals.total_spent', 5000)
                ->where('totals.total_planned', 500)
                ->where('totals.total_remaining', 5000)
        );
    }

    public function test_dashboard_shows_per_category_progress(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Flowers',
            'budget_limit' => 1000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 400,
            'status' => 'paid',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page
                ->has('categories', 1)
                ->where('categories.0.name', 'Flowers')
                ->where('categories.0.budget_limit', 1000)
                ->where('categories.0.spent', 400)
                ->where('categories.0.remaining', 600)
                ->where('categories.0.progress', 40)
        );
    }

    public function test_dashboard_shows_over_budget_category(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Venue',
            'budget_limit' => 5000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 6000,
            'status' => 'paid',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page
                ->where('categories.0.progress', 120)
                ->where('categories.0.remaining', -1000)
        );
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_only_shows_user_categories(): void
    {
        $otherUser = User::factory()->create();

        Category::factory()->count(2)->create(['user_id' => $this->user->id]);
        Category::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page->has('categories', 2)
        );
    }

    public function test_dashboard_totals_update_after_new_expense(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'budget_limit' => 10000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 5000,
            'status' => 'paid',
        ]);

        // Add another contracted expense
        $this->actingAs($this->user)
            ->post('/expenses', [
                'category_id' => $category->id,
                'amount' => 1000,
                'status' => 'contracted',
            ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page
                ->where('totals.total_spent', 6000)
                ->where('totals.total_remaining', 4000)
        );
    }
}
