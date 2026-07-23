<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    
    protected $fillable = ['employee_id', 'date', 'check_in', 'check_out', 'normal_hours', 'overtime_hours', 'status', 'notes', 'recorded_by'];

    protected $casts = [
        'date'           => 'date',
        'normal_hours'   => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'present'  => 'حاضر',
            'absent'   => 'غائب',
            'half_day' => 'نصف يوم',
            default    => $this->status,
        };
    }

    public function getTimeInAttribute()
    {
        return $this->check_in ? \Carbon\Carbon::parse($this->check_in)->format('H:i') : null;
    }

    public function getTimeOutAttribute()
    {
        return $this->check_out ? \Carbon\Carbon::parse($this->check_out)->format('H:i') : null;
    }

    public function getHoursWorkedAttribute()
    {
        return (float)$this->normal_hours + (float)$this->overtime_hours;
    }
}
