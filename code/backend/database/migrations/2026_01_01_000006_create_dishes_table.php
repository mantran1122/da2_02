<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->unsignedInteger('price');
            $table->string('unit', 30)->default('phần');
            $table->unsignedSmallInteger('estimated_prep_time')->default(10); // tính theo phút
            $table->enum('status', ['available', 'out_of_stock', 'hidden'])->default('available');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
