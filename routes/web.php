<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureProfileIsComplete;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified', EnsureProfileIsComplete::class])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Für Volt-Komponenten verwende Volt::route statt Route::get
    Volt::route('setup', 'pages.setup')->name('pages.setup');
    Volt::route('meals', 'pages.meals.index')->name('pages.meals.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
