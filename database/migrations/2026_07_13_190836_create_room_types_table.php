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
    Schema::create('room_types', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->unsignedInteger('base_capacity')->default(1);
        $table->unsignedInteger('max_capacity')->default(2);
        $table->json('amenities')->nullable();
        $table->json('images')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('room_types');
}
};
