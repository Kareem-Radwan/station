<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = [
        'name', 'type', 'model', 'serial_number', 'purchase_date', 'purchase_cost', 
        'status', 'tracking_type', 'maintenance_threshold', 'current_hours', 'current_days',
        'last_maintenance_at_hours', 'last_maintenance_at_days', 'notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_hours' => 'decimal:2',
        'last_maintenance_at_hours' => 'decimal:2',
    ];

    public function fuelLogs()    { return $this->hasMany(EquipmentFuelLog::class); }
    public function maintenance() { return $this->hasMany(EquipmentMaintenance::class); }

    public function getTotalFuelCost(): float
    {
        return (float)$this->fuelLogs()->sum('total_cost');
    }

    public function getTotalMaintenanceCost(): float
    {
        return (float)$this->maintenance()->sum('cost');
    }

    public function getTotalCost(): float
    {
        return $this->getTotalFuelCost() + $this->getTotalMaintenanceCost();
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'loader'          => 'رافعة (لودر)',
            'mixer'           => 'خلاط',
            'service_vehicle' => 'مركبة خدمة',
            'pump'            => 'مضخة',
            default           => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active'      => 'نشط',
            'maintenance' => 'في الصيانة',
            'inactive'    => 'متوقف',
            default       => $this->status,
        };
    }

    public function getTrackingTypeLabelAttribute(): string
    {
        return match($this->tracking_type) {
            'hours' => 'بالساعات',
            'days'  => 'بالأيام',
            default => $this->tracking_type,
        };
    }

    public function getCurrentValue(): float|int
    {
        return $this->tracking_type === 'hours' ? $this->current_hours : $this->current_days;
    }

    public function getLastMaintenanceValue(): ?float
    {
        return $this->tracking_type === 'hours' 
            ? $this->last_maintenance_at_hours 
            : $this->last_maintenance_at_days;
    }

    public function needsMaintenance(): bool
    {
        if (!$this->maintenance_threshold) {
            return false;
        }

        $lastMaintenance = $this->getLastMaintenanceValue() ?? 0;
        $currentValue = $this->getCurrentValue();
        $sinceLastMaintenance = $currentValue - $lastMaintenance;

        return $sinceLastMaintenance >= $this->maintenance_threshold;
    }

    public function getNextMaintenanceValue(): ?float
    {
        if (!$this->maintenance_threshold) {
            return null;
        }

        $lastMaintenance = $this->getLastMaintenanceValue() ?? 0;
        return $lastMaintenance + $this->maintenance_threshold;
    }
}


