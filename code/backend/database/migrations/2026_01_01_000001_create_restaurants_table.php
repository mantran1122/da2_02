<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('address', 255);
            $table->string('phone', 15)->nullable();
            $table->decimal('vat_percent', 5, 2)->default(8.00);
            $table->decimal('service_charge_percent', 5, 2)->default(0.00);
            $table->string('open_hours', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
