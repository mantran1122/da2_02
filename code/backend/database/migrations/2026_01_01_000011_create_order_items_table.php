<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('dining_sessions')->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedInteger('unit_price'); // Giá chốt snapshot tại thời điểm gọi
            $table->string('note', 255)->nullable();
            $table->enum('status', ['pending', 'cooking', 'ready', 'served', 'cancelled'])->default('pending');
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamp('cooking_started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
