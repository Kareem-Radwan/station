<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NeighboringStation extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(NeighboringStationTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalIncoming(): float
    {
        return (float)$this->transactions()->where('direction', 'incoming')->sum('amount');
    }

    public function getTotalOutgoing(): float
    {
        return (float)$this->transactions()->where('direction', 'outgoing')->sum('amount');
    }

    public function getTotalPaidIncoming(): float
    {
        return (float)$this->transactions()->where('direction', 'incoming')->sum('paid_amount');
    }

    public function getTotalPaidOutgoing(): float
    {
        return (float)$this->transactions()->where('direction', 'outgoing')->sum('paid_amount');
    }

    public function getBalance(): float
    {
        // Positive = they owe us, Negative = we owe them
        $incoming = $this->getTotalIncoming() - $this->getTotalPaidIncoming();
        $outgoing = $this->getTotalOutgoing() - $this->getTotalPaidOutgoing();
        return $incoming - $outgoing;
    }
}
