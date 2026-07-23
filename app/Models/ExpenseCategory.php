<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = ['name', 'type', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getAllCategories(): array
    {
        // Get custom categories from database
        $customCategories = static::where('is_active', true)
            ->where('type', 'custom')
            ->pluck('name', 'name')
            ->toArray();

        // Merge with default categories
        return array_merge(static::getDefaultCategories(), $customCategories);
    }

    public static function getDefaultCategories(): array
    {
        return [
            'وقود' => 'وقود',
            'صيانة' => 'صيانة',
            'مواد' => 'مواد',
            'رواتب' => 'رواتب',
            'إداري' => 'إداري',
            '(أخرى) مخصص ضرائب' => '(أخرى) مخصص ضرائب',
            '(أخرى) مساهمين' => '(أخرى) مساهمين',
            '(أخرى) توزيع ارباح' => '(أخرى) توزيع ارباح',
            '(أخرى) الصدقه' => '(أخرى) الصدقه',
            'تأمين للغير' => 'تأمين للغير',
            'تكاليف عمليات' => 'تكاليف عمليات',
            'مخصص طوارئ' => 'مخصص طوارئ',
            'مصاريف تشغيل' => 'مصاريف تشغيل',
            'مشروعات تحت التنفيذ ( محطه )' => 'مشروعات تحت التنفيذ ( محطه )',
            'مصروفات عمومية' => 'مصروفات عمومية',
            'اصول ثابتة' => 'اصول ثابتة',
            'ايرادات اخري' => 'ايرادات اخري',
        ];
    }
}
