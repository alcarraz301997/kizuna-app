<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'budget_limit' => fake()->randomFloat(2, 100, 10000),
            'color' => '#' . fake()->regexify('[0-9a-f]{6}'),
            'user_id' => User::factory(),
        ];
    }
}
