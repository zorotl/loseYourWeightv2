<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureProfileIsComplete;

// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

// PUBLIC PAGES
Route::view('/', 'welcome')->name('home');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/imprint', 'pages.imprint')->name('imprint');

Route::middleware(['auth', 'verified', EnsureProfileIsComplete::class])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('setup', 'livewire.pages.setup')->name('pages.setup');

    Volt::route('meals', 'pages.meals.index')->name('pages.meals.index');
    Volt::route('meals/{meal}', 'pages.meals.show')->name('pages.meals.show');
    Volt::route('statistics', 'pages.statistics.index')->name('statistics.index');
    Volt::route('favorites', 'pages.favorites.index')->name('favorites.index');
    Volt::route('log/{date?}', 'pages.log.index')->name('log.index');
    Volt::route('feedback', 'pages.feedback.index')->name('feedback.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// ADMIN ROUTEN
Route::middleware(['auth', 'verified', 'can:view-admin-panel'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('/', 'pages.admin.index')->name('index');
    Volt::route('foods', 'pages.admin.foods.index')->name('foods.index');
    Volt::route('feedback', 'pages.admin.feedback.index')->name('feedback.index');
});

require __DIR__ . '/auth.php';
