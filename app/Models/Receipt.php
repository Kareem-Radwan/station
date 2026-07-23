<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = ['type', 'amount', 'recipient_name', 'description', 'supplier_id', 'receipt_number', 'receipt_date', 'total_amount', 'image_path', 'notes', 'recorded_by', 'status', 'signed_image_path'];

    protected $casts = [
        'receipt_date' => 'date',
        'amount'       => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isDone(): bool    { return $this->status === 'done'; }

    public function supplier()   { return $this->belongsTo(Supplier::class); }
    public function items()      { return $this->hasMany(ReceiptItem::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
