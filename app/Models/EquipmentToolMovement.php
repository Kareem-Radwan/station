<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentToolMovement extends Model
{
    protected $fillable = [
        'equipment_tool_id',
        'type',
        'quantity',
        'price_per_unit',
        'total_cost',
        'balance_after',
        'treasury_transaction_id',
        'notes',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    public function tool()
    {
        return $this->belongsTo(EquipmentTool::class, 'equipment_tool_id');
    }

    public function treasuryTransaction()
    {
        return $this->belongsTo(TreasuryTransaction::class);
    }
}
