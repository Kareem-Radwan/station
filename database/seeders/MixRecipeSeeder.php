<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MixRecipeSeeder extends Seeder
{
    public function run(): void
    {
        $recipes = [
            [
                'cement_per_m3' => 350,
                'sand_kg' => 720,
                'gravel1_kg' => 440,
                'gravel2_kg' => 660,
                'cement_kg' => 350,
                'water_m3' => 0.2,
                'additives_liter' => 4.5,
                'notes' => 'خلطة قياسية 350',
            ],
            [
                'cement_per_m3' => 250,
                'sand_kg' => 820,
                'gravel1_kg' => 440,
                'gravel2_kg' => 660,
                'cement_kg' => 250,
                'water_m3' => 0.19,
                'additives_liter' => 3.0,
                'notes' => 'خلطة قياسية 250',
            ],
        ];

        foreach ($recipes as $recipe) {
            DB::table('mix_recipes')->updateOrInsert(
                ['cement_per_m3' => $recipe['cement_per_m3']],
                $recipe
            );
        }
    }
}
