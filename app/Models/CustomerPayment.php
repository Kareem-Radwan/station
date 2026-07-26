<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    protected $fillable = ['customer_id', 'order_id', 'payment_date', 'amount', 'payment_method', 'notes', 'recorded_by'];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function customer()   { return $this->belongsTo(Customer::class); }
    public function order()      { return $this->belongsTo(Order::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }

    protected static function booted()
    {
        // Cascade delete treasury transactions when a customer payment is deleted
        static::deleting(function ($payment) {
            TreasuryTransaction::where('reference_type', 'customer_payment')
                ->where('reference_id', $payment->id)
                ->delete();
        });
    }
}
