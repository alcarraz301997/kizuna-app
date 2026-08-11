<?php

namespace Database\Factories;

use App\Models\CategoryTemplate;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CategoryTemplate> */
class CategoryTemplateFactory extends Factory
{
    protected $model = CategoryTemplate::class;

    public function definition(): array
    {
        return ['wedding_id' => Wedding::factory(), 'name' => fake()->unique()->words(2, true)];
    }
}
