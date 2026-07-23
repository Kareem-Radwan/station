<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MixRecipe extends Model
{
    protected $fillable = [
        'cement_per_m3',
        'sand_kg',
        'gravel1_kg',
        'gravel2_kg',
        'cement_kg',
        'water_m3',
        'additives_liter',
        'notes',
    ];

    protected $casts = [
        'cement_per_m3' => 'integer',
        'sand_kg' => 'decimal:3',
        'gravel1_kg' => 'decimal:3',
        'gravel2_kg' => 'decimal:3',
        'cement_kg' => 'decimal:3',
        'water_m3' => 'decimal:3',
        'additives_liter' => 'decimal:3',
    ];

    /**
     * Get the recipe as an array with converted m³ values
     */
    public function getRecipeArray(): array
    {
        $densities = MaterialDensity::pluck('density_kg_per_m3', 'material_name');

        return [
            'Sand' => $this->sand_kg / ($densities['Sand'] ?? 1600),
            'Gravel1' => $this->gravel1_kg / ($densities['Gravel1'] ?? 1692),
            'Gravel2' => $this->gravel2_kg / ($densities['Gravel2'] ?? 1571),
            'Cement' => $this->cement_kg,
            'Water' => $this->water_m3,
            'Additives' => $this->additives_liter,
        ];
    }
}
