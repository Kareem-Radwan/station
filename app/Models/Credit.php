<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $fillable = [
        'creditable_type', 'creditable_id', 'reference_type', 'reference_id',
        'amount', 'due_date', 'paid_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'due_date'  => 'date',
        'paid_date' => 'date',
        'amount'    => 'decimal:2',
    ];

    public function creditable() { return $this->morphTo(); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }

    public static function checkAndMarkOverdue(): int
    {
        return self::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }

    public function getPaidAmountAttribute(): float
    {
        return $this->status === 'paid' ? (float)$this->amount : 0.0;
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->status === 'paid' ? 0.0 : (float)$this->amount;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'معلق',
            'paid'    => 'مدفوع',
            'overdue' => 'متأخر',
            default   => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'paid'    => 'green',
            'overdue' => 'red',
            default   => 'gray',
        };
    }

    public function getCreditorLabelAttribute(): string
    {
        return $this->creditable_type === 'customer' ? 'عميل' : 'مورد';
    }

    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeOverdue($query) { return $query->where('status', 'overdue'); }
    public function scopeDueSoon($query, int $days = 3)
    {
        return $query->where('status', 'pending')
                     ->whereBetween('due_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }
}
