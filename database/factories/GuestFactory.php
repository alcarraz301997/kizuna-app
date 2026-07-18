<?php

namespace Database\Factories;

use App\Models\Guest;
use App\Models\User;
use App\Enums\RsvpStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    protected $model = Guest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'rsvp_status' => fake()->randomElement(RsvpStatus::cases())->value,
            'table_number' => fake()->optional()->numberBetween(1, 20),
            'user_id' => User::factory(),
        ];
    }
}
