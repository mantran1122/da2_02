<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->unsignedTinyInteger('capacity')->default(4);
            $table->string('qr_token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('qr_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
