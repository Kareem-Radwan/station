<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = ['supplier_id', 'supplier_purchase_id', 'payment_date', 'amount', 'payment_method', 'payment_type', 'notes', 'recorded_by'];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function supplier()         { return $this->belongsTo(Supplier::class); }
    public function purchase()         { return $this->belongsTo(SupplierPurchase::class, 'supplier_purchase_id'); }
    public function recordedBy()       { return $this->belongsTo(User::class, 'recorded_by'); }

    protected static function booted()
    {
        // Cascade delete treasury transactions when a supplier payment is deleted
        static::deleting(function ($payment) {
            TreasuryTransaction::where('reference_type', 'supplier_payment')
                ->where('reference_id', $payment->id)
                ->delete();
        });
    }
}
