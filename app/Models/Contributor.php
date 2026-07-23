<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contributor extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'share_percentage',
        'share_amount',
        'national_id',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'share_percentage' => 'decimal:2',
        'share_amount'     => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function payments(): HasMany
    {
        return $this->hasMany(ContributorPayment::class);
    }

    // ─── Computed Attributes ────────────────────────────────────────────────────

    /**
     * Total amount paid to this contributor.
     */
    public function getTotalPaid(): float
    {
        return (float) $this->payments()->whereNull('treasury_transaction_id')->sum('amount');
    }

    /**
     * Outstanding amount = share_amount (net share amount after payments out are decremented)
     */
    public function getOutstandingBalance(): float
    {
        return (float) $this->share_amount;
    }

    /**
     * How much has been paid as a percentage of original share_amount.
     */
    public function getPaidPercentage(): float
    {
        $originalShare = (float) $this->share_amount + $this->getTotalPaid();
        if ($originalShare <= 0) {
            return 0;
        }
        return min(100, ($this->getTotalPaid() / $originalShare) * 100);
    }

    /**
     * Human-readable payment method label.
     */
    public static function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash'          => 'نقدي',
            'bank_transfer' => 'تحويل بنكي',
            'check'         => 'شيك',
            default         => $method,
        };
    }
}
