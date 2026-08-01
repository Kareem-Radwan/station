<?php

namespace App\Accounting\Reports;

use Illuminate\Support\Collection;

/**
 * Balance Sheet Report.
 *
 * Assets  =  Liabilities + Equity
 *
 * Data source: TrialBalanceReport (which reads only from journal_entry_lines).
 */
class BalanceSheetReport
{
    public function __construct(
        private readonly TrialBalanceReport $trialBalance,
    ) {}

    /**
     * @return array {
     *   assets:           Collection,
     *   liabilities:      Collection,
     *   equity:           Collection,
     *   total_assets:     float,
     *   total_liabilities:float,
     *   total_equity:     float,
     *   is_balanced:      bool,
     * }
     */
    public function generate(?string $fromDate = null, ?string $toDate = null): array
    {
        $rows = $this->trialBalance->generate($fromDate, $toDate);

        $assets      = $rows->where('account_type', 'asset');
        $liabilities = $rows->where('account_type', 'liability');
        $equity      = $rows->where('account_type', 'equity');

        $totalAssets      = round($assets->sum('net_balance'),      2);
        $totalLiabilities = round($liabilities->sum('net_balance'), 2);
        $totalEquity      = round($equity->sum('net_balance'),      2);

        // Net income for the period (from income statement) adds to equity
        $incomeStmt      = app(IncomeStatementReport::class)->generate($fromDate, $toDate);
        $netIncome       = $incomeStmt['net_income'];

        $totalEquityWithIncome = round($totalEquity + $netIncome, 2);

        return [
            'assets'                   => $assets->values(),
            'liabilities'              => $liabilities->values(),
            'equity'                   => $equity->values(),
            'total_assets'             => $totalAssets,
            'total_liabilities'        => $totalLiabilities,
            'total_equity'             => $totalEquity,
            'net_income'               => $netIncome,
            'total_equity_with_income' => $totalEquityWithIncome,
            'total_liabilities_equity' => round($totalLiabilities + $totalEquityWithIncome, 2),
            'is_balanced'              => abs($totalAssets - ($totalLiabilities + $totalEquityWithIncome)) < 1.0,
        ];
    }
}
