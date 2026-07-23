<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ConcreteMixSeeder::class,
            ExpenseCategorySeeder::class,
            InventoryItemSeeder::class,
            UserSeeder::class,
            MixRecipeSeeder::class,
            MaterialDensitySeeder::class,
        ]);
    }
}
