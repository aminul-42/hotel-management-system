<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tran_id')->unique();
            $table->json('payload'); // full booking snapshot needed to create the Booking row on success
            $table->decimal('amount', 10, 2);
            $table->enum('payment_type', ['deposit', 'full'])->default('deposit');
            $table->enum('status', ['initiated', 'success', 'failed', 'cancelled'])->default('initiated');
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_transactions');
    }
};