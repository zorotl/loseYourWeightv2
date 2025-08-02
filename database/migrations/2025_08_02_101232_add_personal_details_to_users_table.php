<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add columns for user's physical attributes and goals.
            // These are nullable because they are filled in after registration.
            $table->unsignedInteger('height_cm')->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('height_cm');
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_of_birth');
            $table->unsignedTinyInteger('activity_level')->nullable()->after('gender'); // 1-5 scale
            $table->decimal('target_weight_kg', 5, 2)->nullable()->after('activity_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop columns if the migration is rolled back.
            $table->dropColumn([
                'height_cm',
                'date_of_birth',
                'gender',
                'activity_level',
                'target_weight_kg',
            ]);
        });
    }
};