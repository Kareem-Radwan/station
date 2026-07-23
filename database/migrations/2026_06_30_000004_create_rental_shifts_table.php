<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_contract_id')->constrained()->cascadeOnDelete();
            $table->date('shift_date');
            $table->decimal('hours', 8, 2)->default(0);
            $table->decimal('hourly_price', 12, 2)->default(0);
            $table->decimal('hours_cost', 12, 2)->default(0);       // hours × hourly_price
            $table->decimal('gratuities', 12, 2)->default(0);        // اكراميات
            $table->decimal('cards_cost', 12, 2)->default(0);         // كارتات
            $table->decimal('driver_allowance', 12, 2)->default(0);  // معيشة سواق
            $table->decimal('total_cost', 14, 2)->default(0);         // sum of all
            // Fuel (Gas)
            $table->decimal('fuel_liters', 8, 3)->nullable();
            $table->foreignId('fuel_inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('fuel_inventory_movement_id')->nullable()->constrained('inventory_movements')->nullOnDelete();
            $table->decimal('fuel_cost', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_shifts');
    }
};
