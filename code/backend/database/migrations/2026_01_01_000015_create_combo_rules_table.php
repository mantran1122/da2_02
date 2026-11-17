<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('antecedent_dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->foreignId('consequent_dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->decimal('support', 6, 4)->default(0.0000);
            $table->decimal('confidence', 6, 4)->default(0.0000);
            $table->decimal('lift', 6, 4)->default(1.0000);
            $table->enum('source', ['manual', 'fp_growth'])->default('manual');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['antecedent_dish_id', 'is_active', 'confidence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_rules');
    }
};
