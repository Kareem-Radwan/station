<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDeduction extends Model
{
    protected $fillable = [
        'customer_id',
        'deduction_date',
        'amount',
        'reason',
        'notes',
        'recorded_by'
    ];

    protected $casts = [
        'deduction_date' => 'date',
        'amount'         => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected static function booted()
    {
        // Cascade delete treasury transactions when a customer deduction is deleted
        static::deleting(function ($deduction) {
            TreasuryTransaction::where('reference_type', 'customer_deduction')
                ->where('reference_id', $deduction->id)
                ->delete();
        });
    }
}
