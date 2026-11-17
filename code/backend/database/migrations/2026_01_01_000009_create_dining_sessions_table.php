<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dining_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->cascadeOnDelete();
            $table->string('session_token', 64)->unique();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->unsignedTinyInteger('customer_count')->default(1);
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('session_token');
            $table->index(['table_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dining_sessions');
    }
};
