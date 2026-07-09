<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $categories = [
            ['name' => 'Venue', 'budget_limit' => 8000, 'color' => '#7c3aed'],
            ['name' => 'Catering', 'budget_limit' => 5000, 'color' => '#2563eb'],
            ['name' => 'Photography', 'budget_limit' => 3000, 'color' => '#059669'],
            ['name' => 'Flowers', 'budget_limit' => 1500, 'color' => '#dc2626'],
            ['name' => 'Music & DJ', 'budget_limit' => 2000, 'color' => '#d97706'],
            ['name' => 'Attire', 'budget_limit' => 2500, 'color' => '#ec4899'],
            ['name' => 'Invitations', 'budget_limit' => 500, 'color' => '#8b5cf6'],
            ['name' => 'Transportation', 'budget_limit' => 1000, 'color' => '#0891b2'],
        ];

        foreach ($categories as $catData) {
            $category = Category::factory()->create(array_merge($catData, [
                'user_id' => $user->id,
            ]));

            // Create 2-4 expenses per category with varied statuses
            $expenseCount = rand(2, 4);
            for ($i = 0; $i < $expenseCount; $i++) {
                Expense::factory()->create([
                    'category_id' => $category->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
