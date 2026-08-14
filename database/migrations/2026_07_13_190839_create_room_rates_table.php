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
    Schema::create('room_rates', function (Blueprint $table) {
        $table->id();
        $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->enum('rate_type', ['base', 'weekend', 'seasonal', 'occupancy'])->default('base');
        $table->decimal('price', 10, 2);
        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();
        $table->unsignedTinyInteger('day_of_week')->nullable();
        $table->unsignedInteger('priority')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('room_rates');
}
};
