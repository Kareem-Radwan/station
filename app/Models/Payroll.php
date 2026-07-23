<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $table = 'payroll';
    
    protected $fillable = [
        'employee_id', 'period_month', 'period_year', 'base_salary',
        'overtime_pay', 'total_deductions', 'borrow_deductions', 'net_salary',
        'payment_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'base_salary'      => 'decimal:2',
        'overtime_pay'     => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'borrow_deductions' => 'decimal:2',
        'net_salary'       => 'decimal:2',
        'payment_date'     => 'date',
    ];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function borrowDeductions() { return $this->hasMany(EmployeeBorrowDeduction::class, 'payroll_id'); }

    public function getMonthAttribute()
    {
        return $this->period_month;
    }

    public function getYearAttribute()
    {
        return $this->period_year;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'paid' ? 'مدفوع' : 'معلق';
    }

    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
        return ($months[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }

    public function getDaysAttendedAttribute(): int
    {
        return Attendance::where('employee_id', $this->employee_id)
            ->whereYear('date', $this->period_year)
            ->whereMonth('date', $this->period_month)
            ->whereIn('status', ['present', 'half_day'])
            ->count();
    }
}
