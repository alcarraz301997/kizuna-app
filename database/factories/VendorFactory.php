<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\User;
use App\Enums\VendorPaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'service_category' => fake()->randomElement(['Fotografía', 'Catering', 'Música', 'Decoración', 'Transporte', 'Vestuario']),
            'contact_phone' => fake()->optional()->phoneNumber(),
            'contact_email' => fake()->optional()->email(),
            'payment_status' => fake()->randomElement(VendorPaymentStatus::cases())->value,
            'notes' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
