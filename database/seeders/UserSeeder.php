<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inventory Manager
        User::firstOrCreate(
            ['email' => 'omar@newsolidup.com'],
            [
                'name' => 'Omar - Inventory Manager',
                'password' => Hash::make('inv2026!newsolidup'),
                'role' => 'inventory_manager',
                'is_active' => true,
            ]
        );

        // Accountant
        User::firstOrCreate(
            ['email' => 'mohamed@newsolidup.com'],
            [
                'name' => 'Mohamed - Accountant',
                'password' => Hash::make('acc2026!newsolidup'),
                'role' => 'accountant',
                'is_active' => true,
            ]
        );
    }
}
