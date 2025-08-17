<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WeightHistory;
use Illuminate\Database\Seeder;

class MainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a main user you can log in with
        $mainUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'info@stws.ch',
        ]);

        // Add some weight history to the main user
        WeightHistory::factory(20)->for($mainUser)->create();

        // 2. Create a few other random users
        User::factory(5)->create()->each(function ($user) {
            WeightHistory::factory(10)->for($user)->create();
        });

        $this->command->info('Test users and weight histories created.');

        // 3. Call the Artisan command to import real base foods
        $this->command->info('Starting import of base foods from API...');
        $this->command->call('import:base-foods');
        $this->command->info('Base foods imported.');
    }
}