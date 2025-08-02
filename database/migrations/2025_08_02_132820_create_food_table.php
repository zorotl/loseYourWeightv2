<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->unsignedInteger('calories'); // per 100g/ml
            $table->decimal('protein', 8, 2)->nullable();
            $table->decimal('carbohydrates', 8, 2)->nullable();
            $table->decimal('fat', 8, 2)->nullable();

            // Source tracking
            $table->string('source')->comment('z.B. user, openfoodfacts');
            $table->string('source_id')->nullable()->comment('Produkt-Code von der API');

            // Link to user if created manually
            $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Prevents duplicate entries from the same API source
            $table->unique(['source', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};