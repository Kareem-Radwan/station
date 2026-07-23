<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPurchaseItem extends Model
{
    protected $fillable = ['supplier_purchase_id', 'inventory_item_id', 'description', 'quantity', 'unit', 'unit_price', 'total_price'];

    protected $casts = [
        'quantity'    => 'decimal:3',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function purchase()       { return $this->belongsTo(SupplierPurchase::class, 'supplier_purchase_id'); }
    public function inventoryItem()  { return $this->belongsTo(InventoryItem::class); }
}
