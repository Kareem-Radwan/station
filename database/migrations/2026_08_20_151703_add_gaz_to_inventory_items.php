<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Gaz (جاز) as an inventory item
        DB::table('inventory_items')->insert([
            'name' => 'Gaz',
            'name_ar' => 'جاز',
            'unit' => 'لتر',
            'current_stock' => 0,
            'alert_threshold' => 50,
            'price_per_unit' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('inventory_items')->where('name', 'Gaz')->delete();
    }
};
