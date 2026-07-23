<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandRent extends Model
{
    protected $fillable = ['description', 'annual_amount', 'due_date', 'notes'];

    protected $casts = [
        'due_date'      => 'date',
        'annual_amount' => 'decimal:2',
    ];

    public function payments() { return $this->hasMany(LandRentPayment::class); }

    public function getTotalPaid(): float
    {
        return (float)$this->payments()->sum('amount');
    }

    public function getRemainingAmount(): float
    {
        return (float)$this->annual_amount - $this->getTotalPaid();
    }

    public function getMonthAttribute(): ?int
    {
        if (preg_match('/شهر\s+(\d+)/u', $this->description, $matches)) {
            return (int)$matches[1];
        }
        return $this->due_date ? $this->due_date->month : null;
    }

    public function getYearAttribute(): ?int
    {
        if (preg_match('/\/\s*(\d{4})/u', $this->description, $matches)) {
            return (int)$matches[1];
        }
        return $this->due_date ? $this->due_date->year : null;
    }

    public function getAmountAttribute(): float
    {
        return (float)$this->annual_amount;
    }

    public function getPaymentDateAttribute()
    {
        $firstPayment = $this->payments->sortByDesc('payment_date')->first();
        return $firstPayment ? $firstPayment->payment_date : null;
    }

    public function getStatusAttribute(): string
    {
        $totalPaid = $this->relationLoaded('payments') || array_key_exists('payments_sum_amount', $this->attributes) 
            ? (float)($this->payments_sum_amount ?? $this->payments->sum('amount'))
            : $this->getTotalPaid();

        return $totalPaid >= (float)$this->annual_amount ? 'paid' : 'pending';
    }
}


class LandRentPayment extends Model
{
    protected $fillable = ['land_rent_id', 'payment_date', 'amount', 'notes', 'recorded_by'];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function landRent()   { return $this->belongsTo(LandRent::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
