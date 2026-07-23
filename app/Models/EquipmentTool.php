<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentTool extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'quantity',
        'price_per_unit',
        'total_value',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function movements()
    {
        return $this->hasMany(EquipmentToolMovement::class);
    }

    public function updateTotalValue(): void
    {
        $this->total_value = $this->quantity * $this->price_per_unit;
        $this->save();
    }
}
