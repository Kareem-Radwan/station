<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_fuel_logs', function (Blueprint $table) {
            $table->boolean('deduct_from_inventory')->default(false)->after('total_cost');
            $table->foreignId('inventory_item_id')->nullable()->after('deduct_from_inventory')->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('inventory_movement_id')->nullable()->after('inventory_item_id')->constrained('inventory_movements')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipment_fuel_logs', function (Blueprint $table) {
            $table->dropForeign(['inventory_movement_id']);
            $table->dropForeign(['inventory_item_id']);
            $table->dropColumn(['deduct_from_inventory', 'inventory_item_id', 'inventory_movement_id']);
        });
    }
};
