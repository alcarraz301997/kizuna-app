<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpensePayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExpensePayment> */
class ExpensePaymentFactory extends Factory
{
    protected $model = ExpensePayment::class;

    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'amount' => fake()->randomFloat(2, 1, 5000),
            'paid_on' => fake()->optional()->date(),
            'kind' => 'payment',
            'origin' => 'manual',
        ];
    }
}
