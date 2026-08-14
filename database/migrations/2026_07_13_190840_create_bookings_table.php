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
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->string('booking_reference')->unique();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
        $table->date('check_in');
        $table->date('check_out');
        $table->unsignedInteger('guests_count')->default(1);
        $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('pending');
        $table->decimal('total_amount', 10, 2);
        $table->decimal('deposit_percentage', 5, 2)->default(0);
        $table->decimal('deposit_amount', 10, 2)->default(0);
        $table->decimal('due_amount', 10, 2)->default(0);
        $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('discount_amount', 10, 2)->default(0);
        $table->text('special_requests')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('bookings');
}
};
