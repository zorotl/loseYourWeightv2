<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Füge diese Zeile hinzu, um die Setup-Seite zu definieren und zu benennen.
Route::view('setup', 'livewire.pages.setup')
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureProfileIsComplete::class])
    ->name('pages.setup');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureProfileIsComplete::class])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
