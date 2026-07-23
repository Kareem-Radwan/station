<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBorrowDeduction extends Model
{
    protected $fillable = [
        'borrow_id',
        'payroll_id',
        'amount',
        'deduction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deduction_date' => 'date',
    ];

    public function borrow()
    {
        return $this->belongsTo(EmployeeBorrow::class, 'borrow_id');
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
