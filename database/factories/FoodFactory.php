<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FoodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'brand' => fake()->company(),
            'calories' => fake()->numberBetween(50, 500),
            'source' => 'user',
            'creator_id' => null, // We'll set this when we create the user
        ];
    }
}