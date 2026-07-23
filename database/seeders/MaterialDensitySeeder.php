<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialDensitySeeder extends Seeder
{
    public function run(): void
    {
        $densities = [
            [
                'material_name' => 'Sand',
                'material_name_ar' => 'رمل',
                'density_kg_per_m3' => 1378, // 720 kg ÷ 0.45 m³
                'notes' => 'كثافة الرمل المستخدمة في التحويل من كجم إلى م³',
            ],
            [
                'material_name' => 'Gravel1',
                'material_name_ar' => 'سن 1',
                'density_kg_per_m3' => 1258, // 440 kg ÷ 0.26 m³
                'notes' => 'كثافة الحصى 1 المستخدمة في التحويل من كجم إلى م³',
            ],
            [
                'material_name' => 'Gravel2',
                'material_name_ar' => 'سن 2',
                'density_kg_per_m3' => 1254, // 660 kg ÷ 0.42 m³
                'notes' => 'كثافة الحصى 2 المستخدمة في التحويل من كجم إلى م³',
            ],
        ];

        foreach ($densities as $density) {
            DB::table('material_densities')->updateOrInsert(
                ['material_name' => $density['material_name']],
                $density
            );
        }
    }
}
