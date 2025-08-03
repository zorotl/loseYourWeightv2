<?php

use App\Models\User;
use App\Models\WeightHistory;
use Tests\TestCase;

uses(TestCase::class);

test('it correctly calculates total daily energy expenditure (tdee)', function () {
    // 1. Arrange
    $user = User::factory()->create([
        'height_cm' => 180,
        'date_of_birth' => now()->subYears(30),
        'gender' => 'male',
        'activity_level' => 2, // Multiplier: 1.375
    ]);
    WeightHistory::factory()->for($user)->create(['weight_kg' => 85]);

    // 2. Act
    // BMR = (10 * 85) + (6.25 * 180) - (5 * 30) + 5 = 1830
    // TDEE = 1830 * 1.375 = 2516.25
    $expectedTdee = 2516.25;

    $user->refresh();
    $calculatedTdee = $user->tdee;

    // 3. Assert
    expect($calculatedTdee)->toBe($expectedTdee);
});

// NEUER TESTFALL
test('it correctly calculates bmi', function () {
    // Arrange
    $user = User::factory()->create(['height_cm' => 180]);
    WeightHistory::factory()->for($user)->create(['weight_kg' => 85]);

    // Act
    // BMI = 85 / (1.80 * 1.80) = 26.23... rounded to 26.2
    $expectedBmi = 26.2;
    $user->refresh();

    // Assert
    expect($user->bmi)->toBe($expectedBmi);
});

// NEUER TESTFALL
test('it correctly calculates the target bmi', function () {
    // Arrange
    $user = User::factory()->create([
        'height_cm' => 180,
        'target_weight_kg' => 75,
    ]);

    // Act
    // Target BMI = 75 / (1.80 * 1.80) = 23.14... rounded to 23.1
    $expectedTargetBmi = 23.1;

    // Assert
    expect($user->target_bmi)->toBe($expectedTargetBmi);
});

// NEUER TESTFALL
test('it returns a deficit of zero when the weight goal is reached', function () {
    // Arrange
    $user = User::factory()->create(['target_weight_kg' => 80]);
    WeightHistory::factory()->for($user)->create(['weight_kg' => 79.5]); // Current weight is below target

    // Act
    $user->refresh();

    // Assert
    expect($user->daily_deficit)->toBe(0);
});