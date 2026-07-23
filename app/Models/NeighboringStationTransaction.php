<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NeighboringStationTransaction extends Model
{
    protected $fillable = [
        'neighboring_station_id',
        'transaction_type',
        'direction',
        'transaction_date',
        'amount',
        'description',
        'reference_number',
        'notes',
        'payment_status',
        'paid_amount',
        'recorded_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function station()
    {
        return $this->belongsTo(NeighboringStation::class, 'neighboring_station_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getTransactionTypeLabelAttribute(): string
    {
        return match($this->transaction_type) {
            'rent_equipment' => 'تأجير معدات',
            'rent_vehicle' => 'تأجير مركبة',
            'borrow_material' => 'استعارة مواد',
            'borrow_inventory' => 'استعارة من المخزون',
            'sell_concrete' => 'بيع خرسانة',
            'service' => 'خدمة',
            default => $this->transaction_type,
        };
    }

    public function getDirectionLabelAttribute(): string
    {
        return $this->direction === 'incoming' ? 'وارد' : 'صادر';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'مدفوع',
            'pending' => 'معلق',
            'partial' => 'دفع جزئي',
            default => $this->payment_status,
        };
    }

    public function getRemainingAmount(): float
    {
        return (float)($this->amount - $this->paid_amount);
    }
}
