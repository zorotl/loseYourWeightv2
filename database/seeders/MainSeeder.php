<?php

namespace Database\Seeders;

use App\Models\Food;
use App\Models\Meal;
use App\Models\User;
use App\Models\WeightHistory;
use Illuminate\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a main user you can log in with
        $mainUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'info@stws.ch',
        ]);

        // 2. Create some data for the main user
        $this->seedUserData($mainUser);

        // 3. Create a few other random users with their own data
        User::factory(5)->create()->each(function ($user) {
            $this->seedUserData($user);
        });
    }

    protected function seedUserData(User $user): void
    {
        // Add 20 random weight entries for the last year
        WeightHistory::factory(20)->for($user)->create();

        // Create 3 meals for the user
        Meal::factory(3)
            ->for($user)
            ->create()
            ->each(function ($meal) use ($user) {
                // Create 5 random food items for this user
                $foods = Food::factory(5)->for($user, 'creator')->create();

                // Attach these foods to the meal with a random quantity
                foreach ($foods as $food) {
                    $meal->foods()->attach($food->id, ['quantity_grams' => rand(50, 250)]);
                }
            });
    }
}