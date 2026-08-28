<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingMember;
use App\Services\WeddingMembershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_workspace_and_admit_an_existing_editor(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();

        $response = $this->actingAs($owner)->post('/weddings', [
            'name' => 'Our Wedding',
        ]);

        $wedding = Wedding::firstOrFail();
        $response->assertRedirect("/weddings/{$wedding->id}");

        $this->actingAs($owner)->post("/weddings/{$wedding->id}/members", [
            'user_id' => $editor->id,
            'role' => 'editor',
        ])->assertRedirect();

        $this->assertDatabaseHas('wedding_members', [
            'wedding_id' => $wedding->id,
            'user_id' => $editor->id,
            'role' => 'editor',
        ]);
    }

    public function test_editor_can_read_workspace_but_outsider_gets_forbidden_without_disclosure(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $outsider = User::factory()->create();
        $wedding = Wedding::factory()->create(['owner_id' => $owner->id]);
        WeddingMember::factory()->create(['wedding_id' => $wedding->id, 'user_id' => $owner->id, 'role' => 'owner']);
        WeddingMember::factory()->create(['wedding_id' => $wedding->id, 'user_id' => $editor->id, 'role' => 'editor']);

        $this->actingAs($editor)
            ->getJson("/weddings/{$wedding->id}")
            ->assertOk()
            ->assertJsonPath('wedding.id', $wedding->id);

        $this->actingAs($outsider)
            ->get("/weddings/{$wedding->id}")
            ->assertForbidden();
    }

    public function test_membership_rejects_duplicate_and_nonexistent_users(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $wedding = Wedding::factory()->create(['owner_id' => $owner->id]);
        WeddingMember::factory()->create(['wedding_id' => $wedding->id, 'user_id' => $owner->id, 'role' => 'owner']);

        $this->actingAs($owner)->post("/weddings/{$wedding->id}/members", [
            'user_id' => $editor->id,
            'role' => 'editor',
        ])->assertRedirect();

        $this->actingAs($owner)->post("/weddings/{$wedding->id}/members", [
            'user_id' => $editor->id,
            'role' => 'editor',
        ])->assertSessionHasErrors('user_id');

        $this->actingAs($owner)->post("/weddings/{$wedding->id}/members", [
            'user_id' => 999999,
            'role' => 'editor',
        ])->assertSessionHasErrors('user_id');
    }

    public function test_legacy_records_are_mapped_without_changing_identifiers_or_values(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id, 'budget_limit' => 5000]);
        $expense = Expense::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'amount' => 1250,
            'status' => 'paid',
        ]);

        app(WeddingMembershipService::class)->backfillLegacyRecords();
        $wedding = Wedding::where('owner_id', $owner->id)->firstOrFail();
        app(WeddingMembershipService::class)->backfillLegacyRecords();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'wedding_id' => $wedding->id, 'budget_limit' => 5000]);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'wedding_id' => $wedding->id, 'amount' => 1250, 'status' => 'paid']);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'contracted_amount' => 1250]);
        $this->assertDatabaseCount('expense_payments', 1);
        $this->assertDatabaseHas('expense_payments', ['expense_id' => $expense->id, 'amount' => 1250, 'kind' => 'payment', 'origin' => 'legacy_paid']);
    }

    public function test_two_users_sharing_workspace_see_each_others_categories_and_expenses_in_dashboard(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();

        $wedding = Wedding::factory()->create(['owner_id' => $owner->id, 'name' => 'Boda Compartida']);
        WeddingMember::factory()->create(['wedding_id' => $wedding->id, 'user_id' => $owner->id, 'role' => 'owner']);
        WeddingMember::factory()->create(['wedding_id' => $wedding->id, 'user_id' => $collaborator->id, 'role' => 'editor']);

        // Owner creates a category and an expense
        $category = Category::factory()->create([
            'wedding_id' => $wedding->id,
            'user_id' => $owner->id,
            'name' => 'Fotografía',
            'budget_limit' => 3000,
        ]);

        Expense::factory()->create([
            'wedding_id' => $wedding->id,
            'category_id' => $category->id,
            'user_id' => $owner->id,
            'amount' => 1500,
            'status' => 'paid',
        ]);

        // Collaborator logs in and views Dashboard
        $response = $this->actingAs($collaborator)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->has('categories', 1)
                ->where('categories.0.name', 'Fotografía')
                ->where('categories.0.spent', 1500)
                ->where('totals.total_budget', 3000)
                ->where('totals.total_spent', 1500)
                ->where('totals.total_remaining', 1500)
        );
    }
}
