<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'materials', 'payment_type', 'balance', 'notes', 'is_active'];

    protected $casts = [
        'materials' => 'array',
        'balance'   => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function purchases()          { return $this->hasMany(SupplierPurchase::class); }
    public function payments()           { return $this->hasMany(SupplierPayment::class); }
    public function inventoryMovements() { return $this->hasMany(InventoryMovement::class); }
    public function credits()            { return $this->morphMany(Credit::class, 'creditable'); }
    public function receipts()           { return $this->hasMany(Receipt::class); }
    public function rentalContracts()    { return $this->hasMany(RentalContract::class); }
    public function equipmentMaintenance(){ return $this->hasMany(EquipmentMaintenance::class); }

    public function scopeActive($query)  { return $query->where('is_active', true); }

    public function getPaymentTypeLabelAttribute(): string
    {
        return match($this->payment_type) {
            'cash'   => 'نقدي',
            'credit' => 'آجل',
            'mixed'  => 'مختلط',
            default  => $this->payment_type ?? '-',
        };
    }
}
