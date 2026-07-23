<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcreteMix extends Model
{
    protected $fillable = ['strength', 'cement_per_m3', 'description', 'is_active'];

    protected $casts = [
        'cement_per_m3' => 'decimal:3',
        'is_active'     => 'boolean',
    ];

    public function orders() { return $this->hasMany(Order::class); }
    public function scopeActive($query) { return $query->where('is_active', true); }

    public function getNameAttribute(): string
    {
        return "خرسانة {$this->strength} - " . (int)$this->cement_per_m3 . " كغ/م³";
    }
}


// ─────────────────────────────────────────────
