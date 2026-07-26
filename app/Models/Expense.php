<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['category', 'amount', 'expense_date', 'description', 'notes', 'reference_type', 'reference_id', 'recorded_by'];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }

    public function contributor()
    {
        return $this->belongsTo(Contributor::class, 'reference_id');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if ($this->reference_type === 'contributor' && $this->reference_id) {
            return 'مساهم';
        }
        return 'نقدي من الخزينة';
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'rental'              => 'مصاريف إيجار',
            'rental_maintenance'  => 'صيانة المعدات المستأجرة',
            'vehicle_equipment'   => 'مصاريف مركبات ومعدات',
            'plant_maintenance'   => 'صيانة المحطة وقطع الغيار',
            'salary', 'salaries'  => 'الرواتب',
            'overtime'            => 'العمل الإضافي',
            'employee_deductions' => 'خصومات الموظفين',
            'land_rent'           => 'إيجار الأرض',
            default               => $this->category, // Return the category as-is for custom categories
        };
    }

    public static function categoryList(): array
    {
        return [
            'rental'              => 'مصاريف إيجار',
            'rental_maintenance'  => 'صيانة المعدات المستأجرة',
            'vehicle_equipment'   => 'مصاريف مركبات ومعدات',
            'plant_maintenance'   => 'صيانة المحطة وقطع الغيار',
            'salary'            => 'الرواتب',
            'overtime'            => 'العمل الإضافي',
            'employee_deductions' => 'خصومات الموظفين',
            'land_rent'           => 'إيجار الأرض',
        ];
    }

    public static function getArabicCategoryMapping(): array
    {
        return [
            'وقود' => 'vehicle_equipment',
            'صيانة' => 'plant_maintenance',
            'مواد' => 'plant_maintenance',
            'رواتب' => 'salaries',
            'إداري' => 'plant_maintenance',
            'أخرى' => 'plant_maintenance',
        ];
    }

    public static function getReverseCategoryMapping(): array
    {
        return [
            'rental'              => 'إداري',
            'rental_maintenance'  => 'صيانة',
            'vehicle_equipment'   => 'وقود',
            'plant_maintenance'   => 'صيانة',
            'salaries'            => 'رواتب',
            'salary'              => 'رواتب',
            'overtime'            => 'رواتب',
            'employee_deductions' => 'رواتب',
            'land_rent'           => 'إداري',
        ];
    }

    protected static function booted()
    {
        // Cascade delete treasury transactions when an expense is deleted
        static::deleting(function ($expense) {
            TreasuryTransaction::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->delete();
        });
    }
}
