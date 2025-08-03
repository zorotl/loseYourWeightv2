<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureProfileIsComplete;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified', EnsureProfileIsComplete::class])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('setup', 'livewire.pages.setup')->name('pages.setup');

    Volt::route('meals', 'pages.meals.index')->name('pages.meals.index');
    Volt::route('meals/{meal}', 'pages.meals.show')->name('pages.meals.show');
    Volt::route('statistics', 'pages.statistics.index')->name('statistics.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// ADMIN ROUTEN
Route::middleware(['auth', 'verified', 'can:view-admin-panel'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('/', 'pages.admin.index')->name('index');
    Volt::route('foods', 'pages.admin.foods.index')->name('foods.index');
});

require __DIR__ . '/auth.php';
