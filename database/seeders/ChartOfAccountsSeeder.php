<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Chart of Accounts seeder — idempotent (uses updateOrCreate on account_number).
 *
 * 35 accounts across 5 types in a 3-level hierarchy.
 * Run with: php artisan db:seed --class=ChartOfAccountsSeeder
 */
class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily for clean seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $accounts = $this->getChartOfAccounts();

        // First pass: create all top-level and group accounts (parent_id = null)
        foreach ($accounts as $account) {
            if ($account['parent_number'] === null) {
                $this->upsertAccount($account, null);
            }
        }

        // Second pass: create level-2 accounts
        foreach ($accounts as $account) {
            if ($account['parent_number'] !== null && $account['level'] === 2) {
                $parentId = DB::table('accounts')->where('account_number', $account['parent_number'])->value('id');
                $this->upsertAccount($account, $parentId);
            }
        }

        // Third pass: create level-3 (postable) accounts
        foreach ($accounts as $account) {
            if ($account['parent_number'] !== null && $account['level'] === 3) {
                $parentId = DB::table('accounts')->where('account_number', $account['parent_number'])->value('id');
                $this->upsertAccount($account, $parentId);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $total = DB::table('accounts')->count();
        $this->command->info("✅  دليل الحسابات: {$total} حساب تم إنشاؤه/تحديثه");
    }

    private function upsertAccount(array $account, ?int $parentId): void
    {
        DB::table('accounts')->updateOrInsert(
            ['account_number' => $account['account_number']],
            [
                'parent_id'      => $parentId,
                'level'          => $account['level'],
                'account_number' => $account['account_number'],
                'account_name'   => $account['account_name'],
                'account_type'   => $account['account_type'],
                'normal_balance' => $account['normal_balance'],
                'is_postable'    => $account['is_postable'],
                'is_active'      => true,
                'updated_at'     => now(),
                'created_at'     => now(),
            ]
        );
    }

    private function getChartOfAccounts(): array
    {
        return [
            // ═══════════ ASSETS ═══════════
            [
                'account_number' => '1000',
                'account_name'   => 'الأصول',
                'account_type'   => 'asset',
                'normal_balance' => 'debit',
                'is_postable'    => false,
                'level'          => 1,
                'parent_number'  => null,
            ],
            [
                'account_number' => '1100',
                'account_name'   => 'الأصول المتداولة',
                'account_type'   => 'asset',
                'normal_balance' => 'debit',
                'is_postable'    => false,
                'level'          => 2,
                'parent_number'  => '1000',
            ],
            [
                'account_number' => '1110',
                'account_name'   => 'الصندوق (الخزينة النقدية)',
                'account_type'   => 'asset',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '1100',
            ],
            [
                'account_number' => '1120',
                'account_name'   => 'البنك',
                'account_type'   => 'asset',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '1100',
            ],
            [
                'account_number' => '1130',
                'account_name'   => 'ذمم مدينة - عملاء',
                'account_type'   => 'asset',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '1100',
            ],
            [
                'account_number' => '1140',
                'account_name'   => 'مخزون',
                'account_type'   => 'asset',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '1100',
            ],
            [
                'account_number' => '1150',
                'account_name'   => 'سلف الموظفين',
                'account_type'   => 'asset',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '1100',
            ],
            [
                'account_number' => '1160',
                'account_name'   => 'ذمم مدينة أخرى',
                'account_type'   => 'asset',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '1100',
            ],
            [
                'account_number' => '1170',
                'account_name'   => 'محطات مجاورة - ذمم مدينة',
                'account_type'   => 'asset',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '1100',
            ],

            // ═══════════ LIABILITIES ═══════════
            [
                'account_number' => '2000',
                'account_name'   => 'الخصوم',
                'account_type'   => 'liability',
                'normal_balance' => 'credit',
                'is_postable'    => false,
                'level'          => 1,
                'parent_number'  => null,
            ],
            [
                'account_number' => '2100',
                'account_name'   => 'الخصوم المتداولة',
                'account_type'   => 'liability',
                'normal_balance' => 'credit',
                'is_postable'    => false,
                'level'          => 2,
                'parent_number'  => '2000',
            ],
            [
                'account_number' => '2110',
                'account_name'   => 'ذمم دائنة - موردون',
                'account_type'   => 'liability',
                'normal_balance' => 'credit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '2100',
            ],
            [
                'account_number' => '2120',
                'account_name'   => 'إيجار معدات مستحق',
                'account_type'   => 'liability',
                'normal_balance' => 'credit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '2100',
            ],
            [
                'account_number' => '2130',
                'account_name'   => 'رواتب مستحقة',
                'account_type'   => 'liability',
                'normal_balance' => 'credit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '2100',
            ],
            [
                'account_number' => '2140',
                'account_name'   => 'محطات مجاورة - ذمم دائنة',
                'account_type'   => 'liability',
                'normal_balance' => 'credit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '2100',
            ],
            [
                'account_number' => '2150',
                'account_name'   => 'إيجار أرض مستحق',
                'account_type'   => 'liability',
                'normal_balance' => 'credit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '2100',
            ],

            // ═══════════ EQUITY ═══════════
            [
                'account_number' => '3000',
                'account_name'   => 'حقوق الملكية',
                'account_type'   => 'equity',
                'normal_balance' => 'credit',
                'is_postable'    => false,
                'level'          => 1,
                'parent_number'  => null,
            ],
            [
                'account_number' => '3100',
                'account_name'   => 'رأس المال',
                'account_type'   => 'equity',
                'normal_balance' => 'credit',
                'is_postable'    => false,
                'level'          => 2,
                'parent_number'  => '3000',
            ],
            [
                'account_number' => '3110',
                'account_name'   => 'رأس مال المساهمين',
                'account_type'   => 'equity',
                'normal_balance' => 'credit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '3100',
            ],
            [
                'account_number' => '3120',
                'account_name'   => 'سحوبات المساهمين',
                'account_type'   => 'equity',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 3,
                'parent_number'  => '3100',
            ],

            // ═══════════ REVENUE ═══════════
            [
                'account_number' => '4000',
                'account_name'   => 'الإيرادات',
                'account_type'   => 'revenue',
                'normal_balance' => 'credit',
                'is_postable'    => false,
                'level'          => 1,
                'parent_number'  => null,
            ],
            [
                'account_number' => '4100',
                'account_name'   => 'إيرادات مبيعات الخرسانة',
                'account_type'   => 'revenue',
                'normal_balance' => 'credit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '4000',
            ],
            [
                'account_number' => '4200',
                'account_name'   => 'إيرادات أخرى',
                'account_type'   => 'revenue',
                'normal_balance' => 'credit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '4000',
            ],
            [
                'account_number' => '4300',
                'account_name'   => 'إيرادات المحطات المجاورة',
                'account_type'   => 'revenue',
                'normal_balance' => 'credit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '4000',
            ],

            // ═══════════ EXPENSES ═══════════
            [
                'account_number' => '5000',
                'account_name'   => 'المصروفات',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => false,
                'level'          => 1,
                'parent_number'  => null,
            ],
            [
                'account_number' => '5100',
                'account_name'   => 'تكلفة الوقود',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
            [
                'account_number' => '5200',
                'account_name'   => 'تكلفة صيانة المعدات المملوكة',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
            [
                'account_number' => '5300',
                'account_name'   => 'الرواتب والأجور',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
            [
                'account_number' => '5310',
                'account_name'   => 'العمل الإضافي',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
            [
                'account_number' => '5400',
                'account_name'   => 'مصاريف إيجار المعدات',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
            [
                'account_number' => '5500',
                'account_name'   => 'مصاريف إيجار الأرض',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
            [
                'account_number' => '5600',
                'account_name'   => 'مصاريف تشغيلية عامة',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
            [
                'account_number' => '5700',
                'account_name'   => 'صيانة المحطة وقطع الغيار',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
            [
                'account_number' => '5800',
                'account_name'   => 'مصاريف المركبات والمعدات',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
            [
                'account_number' => '5900',
                'account_name'   => 'صيانة المعدات المستأجرة',
                'account_type'   => 'expense',
                'normal_balance' => 'debit',
                'is_postable'    => true,
                'level'          => 2,
                'parent_number'  => '5000',
            ],
        ];
    }
}
