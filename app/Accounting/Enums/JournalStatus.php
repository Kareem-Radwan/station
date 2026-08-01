<?php

namespace App\Accounting\Enums;

enum JournalStatus: string
{
    case Draft  = 'draft';
    case Posted = 'posted';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Draft  => 'مسودة',
            self::Posted => 'مرحّل',
            self::Voided => 'ملغي',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft  => 'yellow',
            self::Posted => 'green',
            self::Voided => 'red',
        };
    }
}
