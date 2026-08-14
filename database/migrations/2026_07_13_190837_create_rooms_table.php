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
    Schema::create('rooms', function (Blueprint $table) {
        $table->id();
        $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
        $table->string('room_number')->unique();
        $table->string('floor')->nullable();
        $table->enum('status', ['clean', 'dirty', 'occupied', 'blocked', 'maintenance'])->default('clean');
        $table->json('images')->nullable(); // per-room photo gallery (optional overrides/extra shots)
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('rooms');
}
};
