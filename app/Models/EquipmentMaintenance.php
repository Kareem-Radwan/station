<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentMaintenance extends Model
{
    protected $table = 'equipment_maintenance';
    
    protected $fillable = [
        'equipment_id', 'maintenance_date', 'type', 'description', 'cost', 
        'hours_at_maintenance', 'days_at_maintenance',
        'supplier_id', 'notes', 'recorded_by'
    ];
    protected $casts = [
        'maintenance_date' => 'date', 
        'cost' => 'decimal:2',
        'hours_at_maintenance' => 'decimal:2',
    ];

    public function equipment()  { return $this->belongsTo(Equipment::class); }
    public function supplier()   { return $this->belongsTo(Supplier::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'routine'    => 'صيانة دورية',
            'repair'     => 'إصلاح',
            'spare_part' => 'قطع غيار',
            default      => $this->type,
        };
    }
}
