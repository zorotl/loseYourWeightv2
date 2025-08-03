<?php

use App\Models\Food;
use App\Models\Meal;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

test('a user can create a meal and add an ingredient', function () {
    // 1. Arrange
    $user = User::factory()->create();
    $foodToAdd = Food::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    // 2. Act & Assert: Erstelle eine neue Mahlzeit
    // HIER DIE ÄNDERUNG: Wir verwenden den Namen der Komponente als String
    Livewire::test('pages.meals.index')
        ->set('newMealName', 'Mein Test-Mittagessen')
        ->call('createMeal');

    assertDatabaseHas('meals', [
        'user_id' => $user->id,
        'name' => 'Mein Test-Mittagessen'
    ]);

    // 3. Act & Assert: Füge eine Zutat zur eben erstellten Mahlzeit hinzu
    $meal = Meal::where('name', 'Mein Test-Mittagessen')->first();

    // HIER DIE ÄNDERUNG: Wir verwenden den Namen der Komponente als String
    Livewire::test('pages.meals.show', ['meal' => $meal])
        ->set('selectedFood', [ // Simuliere die Auswahl eines Lebensmittels
            'source' => 'user', // In diesem Testfall ist die Quelle 'user'
            'source_id' => null,
            'name' => $foodToAdd->name,
            'brand' => $foodToAdd->brand,
            'calories' => $foodToAdd->calories,
        ])
        ->set('quantity', 150)
        ->call('addIngredient');

    assertDatabaseHas('food_meal', [
        'meal_id' => $meal->id,
        'food_id' => $foodToAdd->id,
        'quantity_grams' => 150,
    ]);
});