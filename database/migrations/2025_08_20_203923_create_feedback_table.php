<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // z.B. Bug-Report, Feature-Wunsch
            $table->integer('priority');
            $table->text('message');
            $table->string('status')->default('neu'); // neu, akzeptiert, abgelehnt
            $table->string('url_at_submission')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};