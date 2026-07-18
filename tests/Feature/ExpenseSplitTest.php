<?php

namespace Tests\Feature;

use App\Enums\SplitType;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseSplit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseSplitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;
    private Expense $expense;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        $this->expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 1000.00,
        ]);
    }

    /**
     * ES-01a: Split 50_50 for a 1000 expense → A=500, B=500.
     */
    public function test_split_fifty_fifty_calculates_correctly(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => '50_50',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
            ]);

        $response->assertRedirect("/expenses/{$this->expense->id}/edit");

        $this->assertDatabaseHas('expense_splits', [
            'expense_id' => $this->expense->id,
            'split_type' => '50_50',
            'person_a_label' => 'Él',
            'person_a_amount' => 500.00,
            'person_b_label' => 'Ella',
            'person_b_amount' => 500.00,
        ]);
    }

    /**
     * ES-01a variant: odd amount rounding (1001 → A=500.50, B=500.50).
     */
    public function test_split_fifty_fifty_handles_odd_amount(): void
    {
        $this->expense->update(['amount' => 1001.00]);

        $this
            ->actingAs($this->user)
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => '50_50',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
            ]);

        $split = $this->expense->split()->first();
        $this->assertEquals(500.50, (float) $split->person_a_amount);
        $this->assertEquals(500.50, (float) $split->person_b_amount);
    }

    /**
     * ES-01b: Split percent 60/40 → A=600, B=400.
     */
    public function test_split_percent_calculates_correctly(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => 'percent',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
                'percent_a' => 60,
            ]);

        $response->assertRedirect();

        $split = $this->expense->split()->first();
        $this->assertEquals(600.00, (float) $split->person_a_amount);
        $this->assertEquals(400.00, (float) $split->person_b_amount);
    }

    /**
     * ES-01c: Split fixed 700/300 → persists ok.
     */
    public function test_split_fixed_persists_correctly(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => 'fixed',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
                'person_a_amount' => 700.00,
                'person_b_amount' => 300.00,
            ]);

        $response->assertRedirect();

        $split = $this->expense->split()->first();
        $this->assertEquals(700.00, (float) $split->person_a_amount);
        $this->assertEquals(300.00, (float) $split->person_b_amount);
    }

    /**
     * ES-01d: Labels are editable (defaults "Él"/"Ella", change to custom).
     */
    public function test_split_labels_are_editable(): void
    {
        $this
            ->actingAs($this->user)
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => '50_50',
                'person_a_label' => 'Juan',
                'person_b_label' => 'María',
            ]);

        $this->assertDatabaseHas('expense_splits', [
            'expense_id' => $this->expense->id,
            'person_a_label' => 'Juan',
            'person_b_label' => 'María',
        ]);
    }

    /**
     * ES-02: Split fixed 600/300 (sum 900, expense 1000) → rejected.
     */
    public function test_split_fixed_rejects_invalid_sum(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from("/expenses/{$this->expense->id}/edit")
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => 'fixed',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
                'person_a_amount' => 600.00,
                'person_b_amount' => 300.00,
            ]);

        $response->assertSessionHasErrors(['person_a_amount', 'person_b_amount']);
        $this->assertDatabaseCount('expense_splits', 0);
    }

    /**
     * Split sum within tolerance (0.01) is accepted.
     */
    public function test_split_fixed_accepts_sum_within_tolerance(): void
    {
        $this->expense->update(['amount' => 1000.00]);

        $response = $this
            ->actingAs($this->user)
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => 'fixed',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
                'person_a_amount' => 999.99,
                'person_b_amount' => 0.01,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('expense_splits', 1);
    }

    /**
     * Split sum outside tolerance is rejected.
     */
    public function test_split_fixed_rejects_sum_outside_tolerance(): void
    {
        $this->expense->update(['amount' => 1000.00]);

        $response = $this
            ->actingAs($this->user)
            ->from("/expenses/{$this->expense->id}/edit")
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => 'fixed',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
                'person_a_amount' => 999.98,
                'person_b_amount' => 0.00,
            ]);

        $response->assertSessionHasErrors(['person_a_amount', 'person_b_amount']);
        $this->assertDatabaseCount('expense_splits', 0);
    }

    /**
     * ES-03: Expense without split — model returns null.
     */
    public function test_expense_without_split_returns_null(): void
    {
        $this->assertNull($this->expense->split);
    }

    /**
     * Split cannot be created twice for the same expense.
     */
    public function test_split_cannot_be_created_twice(): void
    {
        ExpenseSplit::factory()->create([
            'expense_id' => $this->expense->id,
            'split_type' => SplitType::FiftyFifty,
            'person_a_label' => 'Él',
            'person_a_amount' => 500.00,
            'person_b_label' => 'Ella',
            'person_b_amount' => 500.00,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->from("/expenses/{$this->expense->id}/edit")
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => '50_50',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
            ]);

        $response->assertRedirect("/expenses/{$this->expense->id}/edit");
        $this->assertDatabaseCount('expense_splits', 1);
    }

    /**
     * Existing split can be updated.
     */
    public function test_split_can_be_updated(): void
    {
        $split = ExpenseSplit::factory()->create([
            'expense_id' => $this->expense->id,
            'split_type' => SplitType::FiftyFifty,
            'person_a_label' => 'Él',
            'person_a_amount' => 500.00,
            'person_b_label' => 'Ella',
            'person_b_amount' => 500.00,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put("/expenses/{$this->expense->id}/split", [
                'split_type' => 'percent',
                'person_a_label' => 'Juan',
                'person_b_label' => 'María',
                'percent_a' => 70,
            ]);

        $response->assertRedirect("/expenses/{$this->expense->id}/edit");

        $split->refresh();
        $this->assertEquals(SplitType::Percent, $split->split_type);
        $this->assertEquals(700.00, (float) $split->person_a_amount);
        $this->assertEquals(300.00, (float) $split->person_b_amount);
        $this->assertEquals('Juan', $split->person_a_label);
        $this->assertEquals('María', $split->person_b_label);
    }

    /**
     * Split requires split_type.
     */
    public function test_split_requires_split_type(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from("/expenses/{$this->expense->id}/edit")
            ->post("/expenses/{$this->expense->id}/split", [
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
            ]);

        $response->assertSessionHasErrors('split_type');
        $this->assertDatabaseCount('expense_splits', 0);
    }

    /**
     * Split requires labels.
     */
    public function test_split_requires_labels(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from("/expenses/{$this->expense->id}/edit")
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => '50_50',
                'person_a_label' => '',
                'person_b_label' => '',
            ]);

        $response->assertSessionHasErrors('person_a_label');
        $response->assertSessionHasErrors('person_b_label');
        $this->assertDatabaseCount('expense_splits', 0);
    }

    /**
     * Percent split requires percent_a.
     */
    public function test_percent_split_requires_percent_a(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from("/expenses/{$this->expense->id}/edit")
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => 'percent',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
            ]);

        $response->assertSessionHasErrors('percent_a');
        $this->assertDatabaseCount('expense_splits', 0);
    }

    /**
     * Fixed split requires both amounts.
     */
    public function test_fixed_split_requires_amounts(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->from("/expenses/{$this->expense->id}/edit")
            ->post("/expenses/{$this->expense->id}/split", [
                'split_type' => 'fixed',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
            ]);

        $response->assertSessionHasErrors('person_a_amount');
        $response->assertSessionHasErrors('person_b_amount');
        $this->assertDatabaseCount('expense_splits', 0);
    }

    /**
     * Cannot create split for other user's expense.
     */
    public function test_cannot_create_split_for_other_users_expense(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        $otherExpense = Expense::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
            'amount' => 1000.00,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post("/expenses/{$otherExpense->id}/split", [
                'split_type' => '50_50',
                'person_a_label' => 'Él',
                'person_b_label' => 'Ella',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('expense_splits', 0);
    }

    /**
     * Unauthenticated user cannot access split routes.
     */
    public function test_unauthenticated_user_cannot_access_split_routes(): void
    {
        $this->post("/expenses/{$this->expense->id}/split", [
            'split_type' => '50_50',
            'person_a_label' => 'Él',
            'person_b_label' => 'Ella',
        ])->assertRedirect('/login');

        $this->put("/expenses/{$this->expense->id}/split", [
            'split_type' => '50_50',
            'person_a_label' => 'Él',
            'person_b_label' => 'Ella',
        ])->assertRedirect('/login');
    }
}
