<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBorrow extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'remaining_amount',
        'borrow_date',
        'reason',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'borrow_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function deductions()
    {
        return $this->hasMany(EmployeeBorrowDeduction::class, 'borrow_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'نشط' : 'مسدد';
    }

    public function getPaidAmountAttribute(): float
    {
        return (float)$this->amount - (float)$this->remaining_amount;
    }
}
