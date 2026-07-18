<?php

namespace Database\Factories;

use App\Models\Guest;
use App\Models\Table;
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
            'table_id' => null,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Assign the guest to a specific table.
     */
    public function forTable(Table|int $table): static
    {
        $tableId = $table instanceof Table ? $table->id : $table;

        return $this->state(fn () => [
            'table_id' => $tableId,
        ]);
    }
}
