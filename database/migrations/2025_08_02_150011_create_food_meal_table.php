<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('food_meal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity_grams');
            // No timestamps needed for a simple pivot table
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_meal');
    }
};