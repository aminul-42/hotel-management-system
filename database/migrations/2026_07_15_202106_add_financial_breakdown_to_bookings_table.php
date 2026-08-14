<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('total_amount');
            $table->decimal('service_charge_percentage', 5, 2)->default(0)->after('subtotal');
            $table->decimal('service_charge_amount', 10, 2)->default(0)->after('service_charge_percentage');
            $table->decimal('vat_percentage', 5, 2)->default(0)->after('service_charge_amount');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('vat_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'service_charge_percentage', 'service_charge_amount', 'vat_percentage', 'vat_amount']);
        });
    }
};