<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderExpense extends Model
{
    protected $fillable = [
        'order_id',
        'expense_name',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
