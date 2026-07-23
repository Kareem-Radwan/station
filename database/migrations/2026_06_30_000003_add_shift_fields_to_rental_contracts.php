<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->string('car_number')->nullable()->after('equipment_name');
            $table->string('driver_name')->nullable()->after('car_number');
            $table->decimal('hourly_price', 12, 2)->nullable()->after('driver_name');
            $table->decimal('driver_allowance', 12, 2)->nullable()->after('hourly_price');
            // Make start_date nullable for shift-based contracts
            $table->date('start_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn(['car_number', 'driver_name', 'hourly_price', 'driver_allowance']);
            $table->date('start_date')->nullable(false)->change();
        });
    }
};
