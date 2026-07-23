<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialDensity extends Model
{
    protected $fillable = [
        'material_name',
        'material_name_ar',
        'density_kg_per_m3',
        'notes',
    ];

    protected $casts = [
        'density_kg_per_m3' => 'decimal:3',
    ];
}
