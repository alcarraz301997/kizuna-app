<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Wedding> */
class WeddingFactory extends Factory
{
    protected $model = Wedding::class;

    public function definition(): array
    {
        return ['owner_id' => User::factory(), 'name' => fake()->sentence(3)];
    }
}
