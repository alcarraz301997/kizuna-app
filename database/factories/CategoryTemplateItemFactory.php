<?php

namespace Database\Factories;

use App\Models\CategoryTemplate;
use App\Models\CategoryTemplateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CategoryTemplateItem> */
class CategoryTemplateItemFactory extends Factory
{
    protected $model = CategoryTemplateItem::class;

    public function definition(): array
    {
        return [
            'category_template_id' => CategoryTemplate::factory(),
            'name' => fake()->words(2, true),
            'budget_limit' => fake()->randomFloat(2, 100, 5000),
            'color' => '#6366f1',
            'sort_order' => 0,
        ];
    }
}
