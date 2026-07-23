<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaultCategories = ExpenseCategory::getDefaultCategories();

        foreach ($defaultCategories as $name) {
            ExpenseCategory::firstOrCreate(
                ['name' => $name],
                ['type' => 'default', 'is_active' => true]
            );
        }
    }
}
