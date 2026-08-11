<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WeddingForecastTest extends TestCase
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

    public function test_forecast_separates_past_due_dated_commitments_and_paid_balance(): void
    {
        $expense = $this->expense(['contracted_amount' => 1000, 'due_date' => today()->subDay()]);
        ExpensePayment::factory()->create(['expense_id' => $expense->id, 'amount' => 250, 'kind' => 'payment']);

        $this->actingAs($this->owner)->getJson($this->forecastPath())
            ->assertOk()
            ->assertJsonPath('forecast.dated.0.state', 'past_due')
            ->assertJsonPath('forecast.dated.0.contracted', 1000)
            ->assertJsonPath('forecast.dated.0.paid_to_date', 250)
            ->assertJsonPath('forecast.dated.0.balance', 750)
            ->assertJsonPath('forecast.totals.contracted', 1000)
            ->assertJsonPath('forecast.totals.paid_to_date', 250);
    }

    public function test_forecast_keeps_undated_commitments_unscheduled_and_empty_forecasts_empty(): void
    {
        $this->expense(['contracted_amount' => 300, 'due_date' => null]);

        $this->actingAs($this->owner)->getJson($this->forecastPath())
            ->assertOk()
            ->assertJsonCount(0, 'forecast.dated')
            ->assertJsonPath('forecast.unscheduled.0.contracted', 300)
            ->assertJsonPath('forecast.totals.contracted', 300);

        Expense::query()->delete();
        $this->actingAs($this->owner)->getJson($this->forecastPath())
            ->assertOk()
            ->assertJsonCount(0, 'forecast.dated')
            ->assertJsonCount(0, 'forecast.unscheduled')
            ->assertJsonPath('forecast.totals.contracted', 0)
            ->assertJsonPath('forecast.totals.paid_to_date', 0);
    }

    public function test_variance_reports_distinct_commitment_and_paid_alerts(): void
    {
        $this->expense(['planned_amount' => 100, 'contracted_amount' => 150]);
        $paidCategory = Category::factory()->create(['wedding_id' => $this->wedding->id, 'user_id' => $this->owner->id, 'sort_order' => 1]);
        $paidExpense = $this->expense(['category_id' => $paidCategory->id, 'planned_amount' => 100, 'contracted_amount' => 100]);
        ExpensePayment::factory()->create(['expense_id' => $paidExpense->id, 'amount' => 120, 'kind' => 'payment']);
        $withinCategory = Category::factory()->create(['wedding_id' => $this->wedding->id, 'user_id' => $this->owner->id, 'sort_order' => 2]);
        $withinExpense = $this->expense(['category_id' => $withinCategory->id, 'planned_amount' => 300, 'contracted_amount' => 200]);
        ExpensePayment::factory()->create(['expense_id' => $withinExpense->id, 'amount' => 100, 'kind' => 'payment']);

        $this->actingAs($this->owner)->getJson($this->variancePath())
            ->assertOk()
            ->assertJsonPath('categories.0.commitment_variance', 50)
            ->assertJsonPath('categories.0.paid_variance', -100)
            ->assertJsonPath('categories.0.alerts.0', 'commitment_over_budget')
            ->assertJsonPath('categories.1.commitment_variance', 0)
            ->assertJsonPath('categories.1.paid_variance', 20)
            ->assertJsonPath('categories.1.alerts.0', 'paid_over_budget')
            ->assertJsonPath('categories.2.commitment_variance', -100)
            ->assertJsonPath('categories.2.paid_variance', -200)
            ->assertJsonPath('categories.2.alerts', []);
    }

    public function test_missing_plan_has_unavailable_variances_and_empty_category_has_no_alert(): void
    {
        $this->expense(['planned_amount' => null, 'contracted_amount' => 200]);
        $empty = Category::factory()->create(['wedding_id' => $this->wedding->id, 'user_id' => $this->owner->id, 'name' => 'Empty']);

        $this->actingAs($this->owner)->getJson($this->variancePath())
            ->assertOk()
            ->assertJsonPath('categories.0.planned', null)
            ->assertJsonPath('categories.0.commitment_variance', null)
            ->assertJsonPath('categories.0.paid_variance', null)
            ->assertJsonPath('categories.0.alerts', [])
            ->assertJsonPath('categories.1.id', $empty->id)
            ->assertJsonPath('categories.1.planned', null)
            ->assertJsonPath('categories.1.alerts', []);
    }

    public function test_forecast_and_variance_use_bounded_aggregate_queries_and_isolate_outsiders(): void
    {
        Expense::factory()->count(10)->create([
            'category_id' => $this->category->id,
            'wedding_id' => $this->wedding->id,
            'user_id' => $this->owner->id,
            'contracted_amount' => 100,
        ]);
        $queries = 0;
        DB::listen(static function () use (&$queries): void {
            $queries++;
        });

        $this->actingAs($this->owner)->getJson($this->forecastPath())->assertOk();
        $this->assertSame(4, $queries);

        $queries = 0;
        $this->actingAs($this->owner)->getJson($this->variancePath())->assertOk();
        $this->assertSame(5, $queries);

        $outsider = User::factory()->create();
        $this->actingAs($outsider)->getJson($this->variancePath())->assertForbidden();
    }

    private function expense(array $attributes = []): Expense
    {
        return Expense::factory()->create(array_merge([
            'category_id' => $this->category->id,
            'wedding_id' => $this->wedding->id,
            'user_id' => $this->owner->id,
            'amount' => 500,
            'status' => 'contracted',
        ], $attributes));
    }

    private function forecastPath(): string
    {
        return "/weddings/{$this->wedding->id}/forecast";
    }

    private function variancePath(): string
    {
        return "/weddings/{$this->wedding->id}/variance";
    }
}
