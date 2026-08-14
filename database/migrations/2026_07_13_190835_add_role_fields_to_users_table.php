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
    Schema::table('users', function (Blueprint $table) {
        $table->enum('role', ['admin', 'front_desk', 'customer'])->default('customer')->after('email');
        $table->string('phone')->nullable()->after('role');
        $table->string('nid_passport_number')->nullable()->after('phone');
        $table->string('nid_passport_image')->nullable()->after('nid_passport_number');
        $table->boolean('is_active')->default(true)->after('nid_passport_image');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['role', 'phone', 'nid_passport_number', 'nid_passport_image', 'is_active']);
    });
}
};
