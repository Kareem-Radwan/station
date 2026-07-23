<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'inventory_item_id', 'type', 'quantity', 'balance_after',
        'unit_cost', 'total_cost', 'supplier_id', 'reference_type',
        'reference_id', 'notes', 'recorded_by', 'movement_date',
    ];

    protected $casts = [
        'quantity'      => 'decimal:3',
        'balance_after' => 'decimal:3',
        'movement_date' => 'date',
    ];

    public function item()       { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function supplier()   { return $this->belongsTo(Supplier::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'in' ? 'وارد' : 'صادر';
    }
}
