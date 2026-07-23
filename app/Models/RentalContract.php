<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalContract extends Model
{
    protected $fillable = [
        'equipment_name', 'description', 'supplier_id',
        'car_number', 'driver_name', 'hourly_price', 'driver_allowance',
        'start_date', 'end_date',
        'monthly_fee', 'total_fee', 'payment_type', 'status', 'notes',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'monthly_fee'      => 'decimal:2',
        'total_fee'        => 'decimal:2',
        'hourly_price'     => 'decimal:2',
        'driver_allowance' => 'decimal:2',
    ];

    public function supplier()    { return $this->belongsTo(Supplier::class); }
    public function maintenance() { return $this->hasMany(RentalMaintenance::class); }
    public function shifts()      { return $this->hasMany(RentalShift::class); }

    public function getTotalMaintenanceCost(): float
    {
        return (float)$this->maintenance()->sum('cost');
    }

    public function getDeductedMaintenanceCost(): float
    {
        return (float)$this->maintenance()->where('deducted_from_rent', true)->sum('cost');
    }

    public function getTotalShiftsCost(): float
    {
        return (float)$this->shifts()->sum('total_cost');
    }

    public function getTotalFuelCost(): float
    {
        return (float)$this->shifts()->whereNotNull('fuel_cost')->sum('fuel_cost');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active'    => 'نشط',
            'expired'   => 'منتهي',
            'cancelled' => 'ملغي',
            default     => $this->status,
        };
    }

    public function getDurationStringAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return 'مفتوح';
        }

        $start = \Carbon\Carbon::parse($this->start_date);
        $end = \Carbon\Carbon::parse($this->end_date);

        if ($end->lessThan($start)) {
            return 'تاريخ البدء بعد تاريخ الانتهاء';
        }

        $endInclusive = $end->copy()->addDay();
        $diff = $start->diff($endInclusive);

        $months = $diff->y * 12 + $diff->m;
        $days = $diff->d;

        $parts = [];
        if ($months > 0) {
            if ($months == 1) {
                $parts[] = 'شهر';
            } elseif ($months == 2) {
                $parts[] = 'شهران';
            } elseif ($months >= 3 && $months <= 10) {
                $parts[] = $months . ' أشهر';
            } else {
                $parts[] = $months . ' شهر';
            }
        }

        if ($days > 0) {
            if ($days == 1) {
                $parts[] = 'يوم';
            } elseif ($days == 2) {
                $parts[] = 'يومان';
            } elseif ($days >= 3 && $days <= 10) {
                $parts[] = $days . ' أيام';
            } else {
                $parts[] = $days . ' يوم';
            }
        }

        if (empty($parts)) {
            return '0 يوم';
        }

        return implode(' و ', $parts);
    }
}
