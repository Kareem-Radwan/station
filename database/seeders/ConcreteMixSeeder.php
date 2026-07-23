<?php

namespace Database\Seeders;

use App\Models\ConcreteMix;
use Illuminate\Database\Seeder;

class ConcreteMixSeeder extends Seeder
{
    public function run(): void
    {
        $mixes = [
            ['strength' => 180, 'cement_per_m3' => 250.0, 'description' => 'خرسانة 180 - 250 كغ/م³'],
            ['strength' => 250, 'cement_per_m3' => 350.0, 'description' => 'خرسانة 250 - 350 كغ/م³'],
            ['strength' => 300, 'cement_per_m3' => 350.0, 'description' => 'خرسانة 300 - 350 كغ/م³'],
        ];

        foreach ($mixes as $mix) {
            ConcreteMix::firstOrCreate(['strength' => $mix['strength']], $mix);
        }
    }
}
