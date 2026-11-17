<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('dining_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating_food')->default(5);
            $table->unsignedTinyInteger('rating_service')->default(5);
            $table->unsignedTinyInteger('rating_overall')->default(5);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
