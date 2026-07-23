<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreasuryTransaction extends Model
{
    protected $fillable = [
        'type',
        'category',
        'amount',
        'balance_after',
        'transaction_date',
        'description',
        'reference_type',
        'reference_id',
        'recorded_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
        'balance_after'    => 'decimal:2',
    ];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'in' ? 'وارد' : 'صادر';
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'customer_payment'    => 'دفعة من عميل',
            'supplier_payment'    => 'دفعة لمورد',
            'inventory_purchase' => 'شراء مخزون',
            'inventory_sale'     => 'بيع مخزون',
            'receipt_in'          => 'سند قبض',
            'material_cost'         => 'تكلفة المواد',
            'neighboring_station_outgoing'         => 'دفعة لمحطة',
            'neighboring_station_incoming'         => 'دفعة من محطة',
            'order_expense'         => 'تكلفة طلب',
            'receipt_out'         => 'سند صرف',
            'rental'              => 'مصاريف إيجار',
            'expense'             => 'مصروفات عامة',
            'contributor_payment_out'             => 'دفعة لمساهم',
            'credit_payment'      => 'سداد ديون',
            'rental_maintenance'  => 'صيانة المعدات المستأجرة',
            'vehicle_equipment'   => 'مصاريف مركبات ومعدات',
            'plant_maintenance'   => 'صيانة المحطة وقطع الغيار',
            'salary'            => 'الرواتب',
            'overtime'            => 'العمل الإضافي',
            'employee_deductions' => 'خصومات الموظفين',
            'employee_borrow'     => 'سلفة موظف',
            'employee_borrow_repayment' => 'سداد سلفة موظف',
            'contributor_payment' => 'دفعة من مساهم',
            'employee_borrow_return' => 'إلغاء سلفة موظف',
            'land_rent'           => 'إيجار الأرض',
            default               => $this->category,
        };
    }

    public static function getCurrentBalance(): float
    {
        return (float)(static::latest('id')->value('balance_after') ?? 0);
    }
}
