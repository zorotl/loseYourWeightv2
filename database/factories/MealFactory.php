<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Test-Mahlzeit ' . fake()->word(),
        ];
    }
}