<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributorPayment extends Model
{
    protected $fillable = [
        'contributor_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'treasury_transaction_id',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function contributor(): BelongsTo
    {
        return $this->belongsTo(Contributor::class);
    }

    public function treasuryTransaction(): BelongsTo
    {
        return $this->belongsTo(TreasuryTransaction::class);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getPaymentMethodLabelAttribute(): string
    {
        return Contributor::paymentMethodLabel($this->payment_method);
    }

    protected static function booted()
    {
        // Cascade delete treasury transactions when a contributor payment is deleted
        static::deleting(function ($payment) {
            TreasuryTransaction::where('reference_type', 'contributor_payment')
                ->where('reference_id', $payment->id)
                ->delete();
        });
    }
}
