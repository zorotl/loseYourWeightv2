<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('food_log_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('calories');
            $table->timestamp('consumed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_log_entries');
    }
};