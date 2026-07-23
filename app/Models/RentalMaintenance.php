<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalMaintenance extends Model
{
    protected $table = 'rental_maintenance';
    
    protected $fillable = [
        'rental_contract_id', 'maintenance_date', 'description', 'cost', 'deducted_from_rent', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'maintenance_date'   => 'date',
        'cost'               => 'decimal:2',
        'deducted_from_rent' => 'boolean',
    ];

    public function contract()   { return $this->belongsTo(RentalContract::class, 'rental_contract_id'); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
