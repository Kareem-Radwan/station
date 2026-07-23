<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Cement',    'name_ar' => 'اسمنت',     'unit' => 'طن',  'alert_threshold' => 40, 'price_per_unit' => 150.00],
            ['name' => 'Sand',      'name_ar' => 'رمل',       'unit' => 'م³',  'alert_threshold' => 60, 'price_per_unit' => 25.00],
            ['name' => 'Gravel1',   'name_ar' => 'سن 1',      'unit' => 'م³',  'alert_threshold' => 60, 'price_per_unit' => 30.00],
            ['name' => 'Gravel2',   'name_ar' => 'سن 2',      'unit' => 'م³',  'alert_threshold' => 60, 'price_per_unit' => 30.00],
            ['name' => 'Additives', 'name_ar' => 'مادة',      'unit' => 'لتر', 'alert_threshold' => 0,  'price_per_unit' => 5.00],
            ['name' => 'Water',     'name_ar' => 'ماء',       'unit' => 'م³',  'alert_threshold' => 50, 'price_per_unit' => 2.00],
        ];

        foreach ($items as $item) {
            InventoryItem::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
