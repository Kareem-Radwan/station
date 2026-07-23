<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'address', 'location', 'notes',
        'concrete_type', 'payment_type', 'cement_balance',
        'concrete_strength', 'cement_content', 'is_active', 'created_by',
    ];

    protected $casts = [
        'cement_balance'    => 'decimal:3',
        'cement_content'    => 'decimal:3',
        'is_active'         => 'boolean',
    ];

    public function orders()   { return $this->hasMany(Order::class); }
    public function payments() { return $this->hasMany(CustomerPayment::class); }
    public function credits()  { return $this->morphMany(Credit::class, 'creditable'); }
    public function createdBy(){ return $this->belongsTo(User::class, 'created_by'); }

    public function isOperational(): bool { return $this->concrete_type === 'operational'; }
    public function isComplete(): bool    { return $this->concrete_type === 'complete'; }

    public function deductCement(float $tons): void  { $this->decrement('cement_balance', $tons); }
    public function addCement(float $tons): void     { $this->increment('cement_balance', $tons); }

    public function getConcreteTypeLabelAttribute(): string
    {
        return $this->concrete_type === 'operational' ? 'تشغيلية' : 'متكامل';
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        return match($this->payment_type) {
            'cash'   => 'نقدي',
            'credit' => 'آجل',
            'mixed'  => 'مختلط',
            default  => $this->payment_type,
        };
    }

    public function scopeActive($query)      { return $query->where('is_active', true); }
    public function scopeOperational($query) { return $query->where('concrete_type', 'operational'); }
    public function scopeComplete($query)    { return $query->where('concrete_type', 'complete'); }

    public function getTotalOrdersAmount(): float
    {
        return (float)$this->orders()->sum('total_amount');
    }

    public function getTotalPaid(): float
    {
        return (float)$this->payments()->sum('amount');
    }

    public function getOutstandingBalance(): float
    {
        return $this->getTotalOrdersAmount() - $this->getTotalPaid();
    }
}
