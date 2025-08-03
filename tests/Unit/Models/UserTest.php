<?php

use App\Models\User;
use App\Models\WeightHistory;
use Tests\TestCase;

uses(TestCase::class);

test('it correctly calculates total daily energy expenditure (tdee)', function () {
    // 1. Arrange: Erstelle einen User mit exakt definierten Werten
    $user = User::factory()->create([
        'height_cm' => 180,
        'date_of_birth' => now()->subYears(30),
        'gender' => 'male',
        'activity_level' => 2, // Leicht aktiv (Multiplier: 1.375)
    ]);

    // Erstelle einen Gewichtseintrag für diesen User
    WeightHistory::factory()->for($user)->create([
        'weight_kg' => 85,
    ]);

    // 2. Act: Führe die Berechnung durch (manuell und über den Accessor)
    // BMR = (10 * 85) + (6.25 * 180) - (5 * 30) + 5 = 850 + 1125 - 150 + 5 = 1830
    // TDEE = 1830 * 1.375 = 2516.25
    $expectedTdee = 2516.25;

    // Wir müssen den User neu laden, damit der Accessor die eben erstellte WeightHistory findet
    $user->refresh();
    $calculatedTdee = $user->tdee;

    // 3. Assert: Überprüfe, ob das Ergebnis korrekt ist
    expect($calculatedTdee)->toBe($expectedTdee);
});