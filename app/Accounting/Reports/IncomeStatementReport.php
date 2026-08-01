<?php

namespace App\Accounting\Reports;

use Illuminate\Support\Collection;

/**
 * Income Statement (P&L) Report.
 *
 * Revenue - Expenses = Net Income
 *
 * Data source: TrialBalanceReport (which reads only from journal_entry_lines).
 */
class IncomeStatementReport
{
    public function __construct(
        private readonly TrialBalanceReport $trialBalance,
    ) {}

    /**
     * @return array {
     *   revenue:         Collection,
     *   expenses:        Collection,
     *   total_revenue:   float,
     *   total_expenses:  float,
     *   net_income:      float,
     *   is_profitable:   bool,
     *   period:          string,
     * }
     */
    public function generate(?string $fromDate = null, ?string $toDate = null): array
    {
        $rows = $this->trialBalance->generate($fromDate, $toDate);

        $revenue  = $rows->where('account_type', 'revenue');
        $expenses = $rows->where('account_type', 'expense');

        $totalRevenue  = round($revenue->sum('net_balance'),  2);
        $totalExpenses = round($expenses->sum('net_balance'), 2);
        $netIncome     = round($totalRevenue - $totalExpenses, 2);

        $period = match (true) {
            $fromDate && $toDate => $fromDate . ' → ' . $toDate,
            $fromDate            => 'من ' . $fromDate,
            $toDate              => 'حتى ' . $toDate,
            default              => 'كامل الفترة',
        };

        return [
            'revenue'        => $revenue->values(),
            'expenses'       => $expenses->values(),
            'total_revenue'  => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income'     => $netIncome,
            'is_profitable'  => $netIncome >= 0,
            'period'         => $period,
        ];
    }
}
