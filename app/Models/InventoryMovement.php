<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'inventory_item_id', 'type', 'quantity', 'balance_after',
        'unit_cost', 'total_cost', 'supplier_id', 'reference_type',
        'reference_id', 'invoice_number', 'notes', 'recorded_by', 'movement_date',
    ];

    protected $casts = [
        'quantity'      => 'decimal:3',
        'balance_after' => 'decimal:3',
        'movement_date' => 'date',
    ];

    public function item()       { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function supplier()   { return $this->belongsTo(Supplier::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
    public function purchase()   { return $this->belongsTo(SupplierPurchase::class, 'reference_id'); }

    public function getCustomerAttribute()
    {
        if ($this->reference_type === 'customer') {
            return Customer::find($this->reference_id);
        }
        return null;
    }

    public function getSupplierNameAttribute(): ?string
    {
        if ($this->reference_type === 'customer' && $this->customer) {
            return $this->customer->name;
        }
        return $this->supplier?->name;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'in' ? 'وارد' : 'صادر';
    }
}
