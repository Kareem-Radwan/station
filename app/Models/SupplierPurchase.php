<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPurchase extends Model
{
    protected $fillable = [
        'supplier_id', 'invoice_number', 'purchase_date', 'total_amount',
        'payment_type', 'cash_amount', 'credit_amount', 'due_date',
        'status', 'notes', 'created_by', 'invoice_image_path',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'due_date'      => 'date',
        'total_amount'  => 'decimal:2',
        'cash_amount'   => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function items()     { return $this->hasMany(SupplierPurchaseItem::class); }
    public function payments()  { return $this->hasMany(SupplierPayment::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'معلق',
            'partial' => 'جزئي',
            'paid'    => 'مدفوع',
            default   => $this->status,
        };
    }
}
