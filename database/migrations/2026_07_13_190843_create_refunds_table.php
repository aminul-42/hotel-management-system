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
    Schema::create('refunds', function (Blueprint $table) {
        $table->id();
        $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
        $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('amount', 10, 2);
        $table->enum('reason', ['cancellation', 'no_show', 'early_checkout', 'room_downgrade', 'other']);
        $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
        $table->string('gateway_refund_ref')->nullable();
        $table->timestamp('processed_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('refunds');
}
};
