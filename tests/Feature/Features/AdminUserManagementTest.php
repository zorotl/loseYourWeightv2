<?php

use App\Models\User;
use Livewire\Livewire;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\assertSoftDeleted;

test('an admin can ban a user', function () {
    // Arrange: Erstelle einen Admin und einen normalen User
    $admin = User::factory()->create(['is_admin' => true]);
    $userToBan = User::factory()->create();

    actingAs($admin);

    // Act: Simuliere den Klick auf den "Verbannen"-Button
    Livewire::test('pages.admin.index')
        ->call('deleteUser', $userToBan->id);

    // Assert: Prüfe, ob der User als "soft deleted" markiert ist
    assertSoftDeleted($userToBan);
});

test('a banned user cannot log in', function () {
    // Arrange: Erstelle einen User, der bereits verbannt ist
    $bannedUser = User::factory()->create([
        'deleted_at' => now(),
    ]);

    // Act: Versuche, den User direkt über das Auth-System zu authentifizieren
    $isAuthenticated = Auth::attempt([
        'email' => $bannedUser->email,
        'password' => 'password', // 'password' ist das Standardpasswort der Factory
    ]);

    // Assert: Der Authentifizierungsversuch muss fehlschlagen
    expect($isAuthenticated)->toBeFalse();
    assertGuest();
});