<?php

namespace Tests\Unit;

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

    public function test_spent_returns_sum_of_contracted_and_paid_expenses(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'budget_limit' => 5000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 1000,
            'status' => 'paid',
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 500,
            'status' => 'contracted',
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 200,
            'status' => 'planned',
        ]);

        $this->assertSame(1500.0, $category->spent);
    }

    public function test_planned_returns_sum_of_planned_expenses(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'budget_limit' => 5000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 300,
            'status' => 'planned',
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 200,
            'status' => 'planned',
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 1000,
            'status' => 'paid',
        ]);

        $this->assertSame(500.0, $category->planned);
    }

    public function test_remaining_returns_budget_minus_spent(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'budget_limit' => 5000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 2000,
            'status' => 'paid',
        ]);

        $this->assertSame(3000.0, $category->remaining);
    }

    public function test_remaining_can_be_negative_when_over_budget(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'budget_limit' => 1000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 1500,
            'status' => 'paid',
        ]);

        $this->assertSame(-500.0, $category->remaining);
    }

    public function test_progress_returns_percentage_of_spent_vs_budget(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'budget_limit' => 1000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 400,
            'status' => 'paid',
        ]);

        $this->assertSame(40.0, $category->progress);
    }

    public function test_progress_can_exceed_100_when_over_budget(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'budget_limit' => 5000,
        ]);

        Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'amount' => 6000,
            'status' => 'paid',
        ]);

        $this->assertSame(120.0, $category->progress);
    }

    public function test_progress_returns_zero_when_budget_is_zero(): void
    {
        $category = new Category([
            'name' => 'Test',
            'budget_limit' => 0,
            'color' => '#000000',
            'user_id' => $this->user->id,
        ]);

        // Use a fresh model without saving to DB to test the accessor guard
        $this->assertSame(0.0, $category->progress);
    }

    public function test_spent_returns_zero_when_no_expenses(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'budget_limit' => 5000,
        ]);

        $this->assertSame(0.0, $category->spent);
    }
}
