<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'concrete_mix_id', 'concrete_type',
        'quantity_m3', 'cement_deducted', 'location',
        'delivery_date', 'delivery_time', 'status', 'notes',
        'unit_price', 'total_amount', 'payment_type',
        'cash_amount', 'credit_amount', 'credit_due_date', 'material_prices', 'material_quantities', 'created_by',
    ];

    protected $casts = [
        'delivery_date'   => 'date',
        'credit_due_date' => 'date',
        'quantity_m3'     => 'decimal:3',
        'cement_deducted' => 'decimal:3',
        'total_amount'    => 'decimal:2',
        'cash_amount'     => 'decimal:2',
        'credit_amount'   => 'decimal:2',
        'material_prices' => 'array',
        'material_quantities' => 'array',
    ];

    public function customer()     { return $this->belongsTo(Customer::class); }
    public function concreteMix()  { return $this->belongsTo(ConcreteMix::class); }
    public function scheduleEntry(){ return $this->hasOne(WeeklyScheduleEntry::class); }
    public function payments()     { return $this->hasMany(CustomerPayment::class); }
    public function expenses()     { return $this->hasMany(OrderExpense::class); }
    public function createdBy()    { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'معلق',
            'scheduled' => 'مجدول',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'yellow',
            'scheduled' => 'blue',
            'delivered' => 'green',
            'cancelled' => 'red',
            default     => 'gray',
        };
    }

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
            default  => $this->payment_type ?? '-',
        };
    }

    public function scopePending($query)   { return $query->where('status', 'pending'); }
    public function scopeDelivered($query) { return $query->where('status', 'delivered'); }
    public function scopeToday($query)     { return $query->whereDate('delivery_date', today()); }
}
