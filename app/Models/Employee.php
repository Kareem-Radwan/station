<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['name', 'phone', 'position', 'hire_date', 'base_salary', 'overtime_rate', 'is_active', 'notes'];

    protected $casts = [
        'hire_date'    => 'date',
        'base_salary'  => 'decimal:2',
        'overtime_rate'=> 'decimal:2',
        'is_active'    => 'boolean',
    ];

    public function attendance()  { return $this->hasMany(Attendance::class); }
    public function deductions()  { return $this->hasMany(EmployeeDeduction::class); }
    public function payrolls()    { return $this->hasMany(Payroll::class); }
    public function borrows()     { return $this->hasMany(EmployeeBorrow::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }

    public function getMonthlyOvertimeRate(): float
    {
        if ($this->overtime_rate) return (float)$this->overtime_rate;
        return round((float)$this->base_salary / 26 / 10, 2);
    }
}
