<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalShift extends Model
{
    protected $fillable = [
        'rental_contract_id',
        'shift_date',
        'hours',
        'hourly_price',
        'hours_cost',
        'gratuities',
        'cards_cost',
        'driver_allowance',
        'total_cost',
        'fuel_liters',
        'fuel_inventory_item_id',
        'fuel_inventory_movement_id',
        'fuel_cost',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'shift_date'       => 'date',
        'hours'            => 'decimal:2',
        'hourly_price'     => 'decimal:2',
        'hours_cost'       => 'decimal:2',
        'gratuities'       => 'decimal:2',
        'cards_cost'       => 'decimal:2',
        'driver_allowance' => 'decimal:2',
        'total_cost'       => 'decimal:2',
        'fuel_liters'      => 'decimal:3',
        'fuel_cost'        => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(RentalContract::class, 'rental_contract_id');
    }

    public function fuelInventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'fuel_inventory_item_id');
    }

    public function fuelInventoryMovement()
    {
        return $this->belongsTo(InventoryMovement::class, 'fuel_inventory_movement_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
