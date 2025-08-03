<?php

namespace Database\Seeders;

use App\Models\Food;
use App\Models\Meal;
use App\Models\User;
use App\Models\WeightHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class MainSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create all users first
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'info@stws.ch',
        ]);

        User::factory(5)->create();

        // 2. Then get a clean collection of all created users
        $users = User::all();

        // Prepare data arrays in memory
        $weightHistories = [];
        $meals = [];
        $foods = [];
        $foodMealPivots = [];

        foreach ($users as $user) {
            // Prepare weight histories for this user
            for ($i = 0; $i < 20; $i++) {
                $weightHistories[] = WeightHistory::factory()->make(['user_id' => $user->id])->toArray();
            }

            // Prepare meals and their ingredients
            $userMeals = Meal::factory(3)->make(['user_id' => $user->id]);
            foreach ($userMeals as $meal) {
                // We have to save the meal to get an ID for the pivot table
                $meal->save();

                // Create foods for this meal
                $mealFoods = Food::factory(5)->create(['creator_id' => $user->id]);

                // Prepare pivot data
                foreach ($mealFoods as $food) {
                    $foodMealPivots[] = [
                        'meal_id' => $meal->id,
                        'food_id' => $food->id,
                        'quantity_grams' => rand(50, 250)
                    ];
                }
            }
        }

        // Bulk insert all prepared data in single queries
        WeightHistory::insert($weightHistories);
        \DB::table('food_meal')->insert($foodMealPivots);
    }
}