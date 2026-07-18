<?php

namespace Database\Factories;

use App\Enums\SplitType;
use App\Models\Expense;
use App\Models\ExpenseSplit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseSplit>
 */
class ExpenseSplitFactory extends Factory
{
    protected $model = ExpenseSplit::class;

    public function definition(): array
    {
        $amount = 1000.00;

        return [
            'expense_id' => Expense::factory(),
            'split_type' => SplitType::FiftyFifty,
            'person_a_label' => 'Él',
            'person_a_amount' => 500.00,
            'person_b_label' => 'Ella',
            'person_b_amount' => 500.00,
        ];
    }

    public function fiftyFifty(): static
    {
        return $this->state(fn (array $attributes) => [
            'split_type' => SplitType::FiftyFifty,
            'person_a_amount' => round($attributes['person_a_amount'] ?? 500.00 / 2, 2),
            'person_b_amount' => round($attributes['person_a_amount'] ?? 500.00 / 2, 2),
        ]);
    }

    public function percent(): static
    {
        return $this->state(fn (array $attributes) => [
            'split_type' => SplitType::Percent,
        ]);
    }

    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'split_type' => SplitType::Fixed,
        ]);
    }
}
