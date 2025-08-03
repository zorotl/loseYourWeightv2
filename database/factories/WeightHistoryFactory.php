<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WeightHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'weight_kg' => $this->faker->randomFloat(1, 60, 120),
            'weighed_on' => $this->faker->dateTimeThisYear(),
        ];
    }
}