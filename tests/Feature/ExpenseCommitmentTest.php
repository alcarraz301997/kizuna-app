<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\ExpenseSplit;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wedding;
use App\Models\WeddingMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCommitmentTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Wedding $wedding;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->wedding = Wedding::factory()->create(['owner_id' => $this->owner->id]);
        WeddingMember::factory()->create(['wedding_id' => $this->wedding->id, 'user_id' => $this->owner->id, 'role' => 'owner']);
        $this->category = Category::factory()->create(['wedding_id' => $this->wedding->id, 'user_id' => $this->owner->id]);
    }

    public function test_planned_and_contracted_values_are_nullable_and_independent(): void
    {
        $expense = $this->expense(['status' => 'contracted', 'planned_amount' => null, 'contracted_amount' => null]);

        $this->actingAs($this->owner)->patchJson($this->commitmentPath($expense), [
            'planned_amount' => null,
            'contracted_amount' => 800,
            'due_date' => null,
        ])->assertOk()
            ->assertJsonPath('commitment.planned_amount', null)
            ->assertJsonPath('commitment.contracted_amount', 800)
            ->assertJsonPath('commitment.paid_to_date', 0)
            ->assertJsonPath('commitment.balance', 800);

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'planned_amount' => null, 'contracted_amount' => 800]);
    }

    public function test_negative_planning_and_payment_values_are_rejected_without_changing_valid_data(): void
    {
        $expense = $this->expense(['planned_amount' => 100, 'contracted_amount' => 500]);

        $commitmentResponse = $this->actingAs($this->owner)->patchJson($this->commitmentPath($expense), [
            'planned_amount' => -1,
            'contracted_amount' => 500,
        ]);
        $this->assertSame(302, $commitmentResponse->baseResponse->getStatusCode());
        $this->assertArrayHasKey('planned_amount', $commitmentResponse->baseResponse->getSession()->get('errors')['default']['messages']);

        $paymentResponse = $this->actingAs($this->owner)->postJson($this->paymentsPath($expense), [
            'amount' => -25,
            'kind' => 'payment',
        ]);
        $this->assertSame(302, $paymentResponse->baseResponse->getStatusCode());
        $this->assertArrayHasKey('amount', $paymentResponse->baseResponse->getSession()->get('errors')['default']['messages']);

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'planned_amount' => 100, 'contracted_amount' => 500]);
        $this->assertDatabaseCount('expense_payments', 0);
    }

    public function test_deposits_and_payments_accumulate_paid_to_date_without_replacing_contract(): void
    {
        $expense = $this->expense(['planned_amount' => 400, 'contracted_amount' => 500]);

        $this->actingAs($this->owner)->postJson($this->paymentsPath($expense), [
            'amount' => 100,
            'kind' => 'deposit',
            'paid_on' => '2026-08-01',
        ])->assertOk();
        $this->actingAs($this->owner)->postJson($this->paymentsPath($expense), [
            'amount' => 450,
            'kind' => 'payment',
            'paid_on' => '2026-08-02',
        ])->assertOk()
            ->assertJsonPath('commitment.contracted_amount', 500)
            ->assertJsonPath('commitment.paid_to_date', 550)
            ->assertJsonPath('commitment.balance', -50);

        $this->assertDatabaseCount('expense_payments', 2);
        $this->assertDatabaseHas('expense_payments', ['expense_id' => $expense->id, 'kind' => 'deposit', 'amount' => 100]);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'contracted_amount' => 500]);
    }

    public function test_summary_preserves_vendor_receipt_split_and_status_continuity(): void
    {
        $vendor = Vendor::factory()->create(['wedding_id' => $this->wedding->id, 'user_id' => $this->owner->id]);
        $expense = $this->expense(['vendor_id' => $vendor->id, 'status' => 'paid', 'planned_amount' => 200, 'contracted_amount' => 300]);
        Receipt::factory()->create(['expense_id' => $expense->id, 'user_id' => $this->owner->id]);
        ExpenseSplit::factory()->create(['expense_id' => $expense->id]);
        ExpensePayment::create(['expense_id' => $expense->id, 'amount' => 300, 'kind' => 'payment', 'origin' => 'manual']);

        $this->actingAs($this->owner)->getJson($this->commitmentPath($expense))
            ->assertOk()
            ->assertJsonPath('commitment.status', 'paid')
            ->assertJsonPath('commitment.vendor_id', $vendor->id)
            ->assertJsonPath('commitment.receipts_count', 1)
            ->assertJsonPath('commitment.has_split', true)
            ->assertJsonPath('commitment.planned_amount', 200)
            ->assertJsonPath('commitment.contracted_amount', 300)
            ->assertJsonPath('commitment.paid_to_date', 300);
    }

    public function test_outsider_cannot_read_or_write_an_expense_commitment(): void
    {
        $outsider = User::factory()->create();
        $expense = $this->expense(['planned_amount' => 100]);

        $this->actingAs($outsider)->getJson($this->commitmentPath($expense))->assertForbidden();
        $this->actingAs($outsider)->postJson($this->paymentsPath($expense), [
            'amount' => 50,
            'kind' => 'payment',
        ])->assertForbidden();
        $this->assertDatabaseCount('expense_payments', 0);
    }

    private function expense(array $attributes = []): Expense
    {
        return Expense::factory()->create(array_merge([
            'category_id' => $this->category->id,
            'user_id' => $this->owner->id,
            'wedding_id' => $this->wedding->id,
            'amount' => 500,
            'status' => 'planned',
        ], $attributes));
    }

    private function commitmentPath(Expense $expense): string
    {
        return "/weddings/{$this->wedding->id}/expenses/{$expense->id}/commitment";
    }

    private function paymentsPath(Expense $expense): string
    {
        return "/weddings/{$this->wedding->id}/expenses/{$expense->id}/payments";
    }
}
