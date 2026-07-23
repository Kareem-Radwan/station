<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = ['name', 'name_ar', 'unit', 'alert_threshold', 'current_stock', 'price_per_unit'];

    protected $casts = [
        'current_stock'   => 'decimal:3',
        'alert_threshold' => 'decimal:3',
        'price_per_unit'  => 'decimal:2',
    ];

    public function movements() { return $this->hasMany(InventoryMovement::class); }

    public function isBelowAlert(): bool { return (float)$this->current_stock <= (float)$this->alert_threshold; }
    public function addStock(float $qty): void    { $this->increment('current_stock', $qty); }
    public function deductStock(float $qty): void { $this->decrement('current_stock', $qty); }

    public function getStatusColorAttribute(): string
    {
        return $this->isBelowAlert() ? 'red' : 'green';
    }
}

