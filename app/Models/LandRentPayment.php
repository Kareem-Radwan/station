<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandRentPayment extends Model
{
    protected $fillable = ['land_rent_id', 'payment_date', 'amount', 'notes', 'recorded_by'];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function landRent()   { return $this->belongsTo(LandRent::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }

    protected static function booted()
    {
        // Cascade delete treasury transactions when a land rent payment is deleted
        static::deleting(function ($payment) {
            TreasuryTransaction::where('reference_type', 'land_rent_payment')
                ->where('reference_id', $payment->id)
                ->delete();
        });
    }
}
