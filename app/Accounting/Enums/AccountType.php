<?php

namespace App\Accounting\Enums;

enum AccountType: string
{
    case Asset     = 'asset';
    case Liability = 'liability';
    case Equity    = 'equity';
    case Revenue   = 'revenue';
    case Expense   = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Asset     => 'أصول',
            self::Liability => 'خصوم',
            self::Equity    => 'حقوق الملكية',
            self::Revenue   => 'إيرادات',
            self::Expense   => 'مصروفات',
        };
    }

    public function normalBalance(): NormalBalance
    {
        return match ($this) {
            self::Asset, self::Expense   => NormalBalance::Debit,
            self::Liability, self::Equity, self::Revenue => NormalBalance::Credit,
        };
    }

    /** Types that appear on the Balance Sheet */
    public static function balanceSheetTypes(): array
    {
        return [self::Asset, self::Liability, self::Equity];
    }

    /** Types that appear on the Income Statement */
    public static function incomeStatementTypes(): array
    {
        return [self::Revenue, self::Expense];
    }
}
