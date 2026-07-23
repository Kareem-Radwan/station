<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Inventory Item 'Additives'
        $item = DB::table('inventory_items')->where('name', 'Additives')->first();
        if ($item) {
            DB::table('inventory_items')
                ->where('id', $item->id)
                ->update([
                    'unit' => 'لتر',
                    'current_stock' => $item->current_stock * 1000,
                    'alert_threshold' => $item->alert_threshold * 1000,
                    'price_per_unit' => $item->price_per_unit / 1000,
                ]);

            // 2. Update Inventory Movements for Additives
            DB::table('inventory_movements')
                ->where('inventory_item_id', $item->id)
                ->get()
                ->each(function ($movement) {
                    DB::table('inventory_movements')
                        ->where('id', $movement->id)
                        ->update([
                            'quantity' => $movement->quantity * 1000,
                            'balance_after' => $movement->balance_after * 1000,
                            'unit_cost' => $movement->unit_cost ? ($movement->unit_cost / 1000) : null,
                        ]);
                });

            // 3. Update Supplier Purchase Items for Additives
            DB::table('supplier_purchase_items')
                ->where('inventory_item_id', $item->id)
                ->get()
                ->each(function ($purchaseItem) {
                    DB::table('supplier_purchase_items')
                        ->where('id', $purchaseItem->id)
                        ->update([
                            'unit' => 'لتر',
                            'quantity' => $purchaseItem->quantity * 1000,
                            'unit_price' => $purchaseItem->unit_price / 1000,
                        ]);
                });
        }
    }

    public function down(): void
    {
        $item = DB::table('inventory_items')->where('name', 'Additives')->first();
        if ($item) {
            DB::table('inventory_items')
                ->where('id', $item->id)
                ->update([
                    'unit' => 'م³',
                    'current_stock' => $item->current_stock / 1000,
                    'alert_threshold' => $item->alert_threshold / 1000,
                    'price_per_unit' => $item->price_per_unit * 1000,
                ]);

            DB::table('inventory_movements')
                ->where('inventory_item_id', $item->id)
                ->get()
                ->each(function ($movement) {
                    DB::table('inventory_movements')
                        ->where('id', $movement->id)
                        ->update([
                            'quantity' => $movement->quantity / 1000,
                            'balance_after' => $movement->balance_after / 1000,
                            'unit_cost' => $movement->unit_cost ? ($movement->unit_cost * 1000) : null,
                        ]);
                });

            DB::table('supplier_purchase_items')
                ->where('inventory_item_id', $item->id)
                ->get()
                ->each(function ($purchaseItem) {
                    DB::table('supplier_purchase_items')
                        ->where('id', $purchaseItem->id)
                        ->update([
                            'unit' => 'م³',
                            'quantity' => $purchaseItem->quantity / 1000,
                            'unit_price' => $purchaseItem->unit_price * 1000,
                        ]);
                });
        }
    }
};
