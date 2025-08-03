<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),

            // Unsere neuen Felder mit realistischen Werten
            'height_cm' => fake()->numberBetween(150, 200),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female']),
            'activity_level' => fake()->numberBetween(1, 5),
            'target_weight_kg' => fake()->randomFloat(1, 60, 90),
            'target_date' => fake()->dateTimeBetween('+1 month', '+1 year'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
