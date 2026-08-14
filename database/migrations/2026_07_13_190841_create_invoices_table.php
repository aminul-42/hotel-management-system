<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
        $table->string('invoice_number')->unique();
        $table->decimal('room_charge', 10, 2)->default(0);
        $table->decimal('extra_charges', 10, 2)->default(0);
        $table->decimal('vat_amount', 10, 2)->default(0);
        $table->decimal('service_charge_amount', 10, 2)->default(0);
        $table->decimal('discount_amount', 10, 2)->default(0);
        $table->decimal('deposit_paid', 10, 2)->default(0);
        $table->decimal('total_due', 10, 2)->default(0);
        $table->string('pdf_path')->nullable();
        $table->timestamp('generated_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('invoices');
}
};
