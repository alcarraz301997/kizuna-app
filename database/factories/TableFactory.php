<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Table>
 */
class TableFactory extends Factory
{
    protected $model = Table::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Principal', 'Jardín', 'Terraza', 'VIP', 'Amigos', 'Familia'])
                . ' ' . fake()->randomNumber(1),
            'capacity' => fake()->numberBetween(6, 15),
            'user_id' => User::factory(),
        ];
    }
}
