<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('dining_sessions')->cascadeOnDelete();
            $table->unsignedInteger('order_no')->default(1);
            $table->enum('status', ['received', 'processing', 'completed', 'cancelled'])->default('received');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
