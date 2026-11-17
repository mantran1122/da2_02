<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->unique()->constrained('dining_sessions')->cascadeOnDelete();
            $table->string('invoice_code', 30)->unique();
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('discount_amount')->default(0);
            $table->string('discount_note', 150)->nullable();
            $table->unsignedInteger('service_charge')->default(0);
            $table->unsignedInteger('vat_amount')->default(0);
            $table->unsignedInteger('total_amount')->default(0);
            $table->enum('status', ['draft', 'paid', 'cancelled'])->default('draft');
            $table->foreignId('issued_by_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
