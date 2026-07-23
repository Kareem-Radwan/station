<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentFuelLog extends Model
{
    protected $fillable = [
        'equipment_id', 'log_date', 'liters', 'unit_cost', 'total_cost', 
        'hours_logged', 'days_logged',
        'deduct_from_inventory', 'inventory_item_id', 'inventory_movement_id',
        'notes', 'recorded_by'
    ];
    
    protected $casts = [
        'log_date' => 'date', 
        'total_cost' => 'decimal:2',
        'hours_logged' => 'decimal:2',
        'deduct_from_inventory' => 'boolean',
    ];

    public function equipment()  { return $this->belongsTo(Equipment::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
    public function inventoryMovement() { return $this->belongsTo(InventoryMovement::class); }
}
