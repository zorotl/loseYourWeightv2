<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('food_log_entries', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn('name');

            // Add new columns
            $table->foreignId('food_id')->after('user_id')->constrained('foods')->cascadeOnDelete();
            $table->unsignedInteger('quantity_grams')->after('food_id');
        });
    }

    public function down(): void
    {
        Schema::table('food_log_entries', function (Blueprint $table) {
            // Rollback changes if something goes wrong
            $table->string('name')->after('user_id');
            $table->dropConstrainedForeignId('food_id');
            $table->dropColumn('quantity_grams');
        });
    }
};