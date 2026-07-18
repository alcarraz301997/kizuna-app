<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'user_id' => User::factory(),
            'file_path' => fake()->uuid() . '.pdf',
            'file_name' => fake()->word() . '.pdf',
            'file_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1000, 1024000),
        ];
    }
}
