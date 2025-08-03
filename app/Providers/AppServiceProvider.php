<?php

namespace App\Providers;

use App\Models\Meal;
use App\Models\User;
use App\Policies\MealPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Meal::class, MealPolicy::class);
        Gate::define('view-admin-panel', fn(User $user) => $user->is_admin);
    }
}
