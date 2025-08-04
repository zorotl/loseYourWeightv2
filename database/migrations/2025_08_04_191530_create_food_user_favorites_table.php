<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('food_user_favorites', function (Blueprint $table) {
            $table->primary(['user_id', 'food_id']); // Prevents duplicate entries
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_user_favorites');
    }
};