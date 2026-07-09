<?php

namespace Tests\Feature;

use App\Enums\ExpenseStatus;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_expense_index_page_is_displayed(): void
    {
        Expense::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/expenses');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Expenses/Index')
                ->has('expenses', 3)
                ->has('categories')
                ->has('filters')
        );
    }

    public function test_expense_index_filters_by_category(): void
    {
        $otherCategory = Category::factory()->create(['user_id' => $this->user->id]);
        Expense::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);
        Expense::factory()->count(3)->create([
            'category_id' => $otherCategory->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/expenses?category_id=' . $this->category->id);

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page->has('expenses', 2)
        );
    }

    public function test_expense_create_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/expenses/create');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Expenses/Create')
                ->has('categories')
                ->has('statuses')
        );
    }

    public function test_expense_can_be_created(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/expenses', [
                'category_id' => $this->category->id,
                'amount' => 1500.00,
                'vendor' => 'Floristry Co',
                'status' => 'planned',
                'paid_date' => '2026-09-01',
                'notes' => 'Wedding flowers',
            ]);

        $response->assertRedirect('/expenses');

        $this->assertDatabaseHas('expenses', [
            'category_id' => $this->category->id,
            'amount' => 1500.00,
            'vendor' => 'Floristry Co',
            'status' => 'planned',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_expense_requires_positive_amount(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/expenses/create')
            ->post('/expenses', [
                'category_id' => $this->category->id,
                'amount' => 0,
                'status' => 'planned',
            ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_expense_rejects_negative_amount(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/expenses/create')
            ->post('/expenses', [
                'category_id' => $this->category->id,
                'amount' => -100,
                'status' => 'planned',
            ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_expense_requires_valid_status(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/expenses/create')
            ->post('/expenses', [
                'category_id' => $this->category->id,
                'amount' => 500,
                'status' => 'partially-paid',
            ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_expense_requires_category(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from('/expenses/create')
            ->post('/expenses', [
                'amount' => 500,
                'status' => 'planned',
            ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_expense_rejects_other_users_category(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->actingAs($this->user)
            ->post('/expenses', [
                'category_id' => $otherCategory->id,
                'amount' => 500,
                'status' => 'planned',
            ]);

        $response->assertStatus(403);
    }

    public function test_expense_edit_page_is_displayed(): void
    {
        $expense = Expense::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get("/expenses/{$expense->id}/edit");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Expenses/Edit')
                ->has('expense')
                ->has('categories')
                ->has('statuses')
        );
    }

    public function test_expense_can_be_updated(): void
    {
        $expense = Expense::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'amount' => 1500,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/expenses/{$expense->id}", [
                'category_id' => $this->category->id,
                'amount' => 1600,
                'vendor' => 'Updated Vendor',
                'status' => 'contracted',
                'notes' => 'includes delivery',
            ]);

        $response->assertRedirect('/expenses');

        $expense->refresh();
        $this->assertSame('1600.00', $expense->amount);
        $this->assertSame('Updated Vendor', $expense->vendor);
        $this->assertSame(ExpenseStatus::Contracted, $expense->status);
    }

    public function test_expense_can_be_deleted(): void
    {
        $expense = Expense::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'status' => 'paid',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/expenses/{$expense->id}");

        $response->assertRedirect('/expenses');
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_expense_index_only_shows_user_expenses(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        Expense::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);
        Expense::factory()->count(3)->create([
            'category_id' => $otherCategory->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get('/expenses');

        $response->assertInertia(
            fn ($page) => $page->has('expenses', 2)
        );
    }

    public function test_unauthenticated_user_cannot_access_expenses(): void
    {
        $this->get('/expenses')->assertRedirect('/login');
        $this->get('/expenses/create')->assertRedirect('/login');
    }

    public function test_cannot_edit_other_users_expense(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        $expense = Expense::factory()->create([
            'category_id' => $otherCategory->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get("/expenses/{$expense->id}/edit");

        $response->assertStatus(403);
    }

    public function test_expense_status_can_transition_to_paid(): void
    {
        $expense = Expense::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'status' => 'planned',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/expenses/{$expense->id}", [
                'category_id' => $this->category->id,
                'amount' => $expense->amount,
                'status' => 'paid',
                'paid_date' => '2026-09-15',
            ]);

        $response->assertRedirect('/expenses');

        $expense->refresh();
        $this->assertSame(ExpenseStatus::Paid, $expense->status);
        $this->assertNotNull($expense->paid_date);
    }
}
