<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDeduction extends Model
{
    protected $fillable = ['employee_id', 'deduction_date', 'type', 'amount', 'reason', 'recorded_by'];

    protected $casts = [
        'deduction_date' => 'date',
        'amount'         => 'decimal:2',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'absence'     => 'غياب',
            'late_arrival'=> 'تأخر',
            'other'       => 'أخرى',
            default       => $this->type,
        };
    }
}
