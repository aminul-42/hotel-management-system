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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
        $table->string('tran_id')->unique();
        $table->string('val_id')->nullable();
        $table->decimal('amount', 10, 2);
        $table->enum('payment_type', ['deposit', 'full', 'remaining', 'refund'])->default('deposit');
        $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
        $table->json('gateway_response')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('payments');
}
};
