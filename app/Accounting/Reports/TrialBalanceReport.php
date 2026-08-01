<?php

namespace App\Accounting\Reports;

use App\Accounting\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Trial Balance Report.
 *
 * Reads EXCLUSIVELY from journal_entry_lines and accounts.
 * Never touches orders, treasury_transactions, or any business table.
 */
class TrialBalanceReport
{
    /**
     * Generate a flat trial balance for all postable accounts.
     *
     * @param  string|null $fromDate  Y-m-d
     * @param  string|null $toDate    Y-m-d
     * @return Collection  Each item: { account, total_debit, total_credit, net_balance, balance_side }
     */
    public function generate(?string $fromDate = null, ?string $toDate = null): Collection
    {
        $query = DB::table('accounts as a')
            ->select([
                'a.id',
                'a.account_number',
                'a.account_name',
                'a.account_type',
                'a.normal_balance',
                'a.level',
                'a.parent_id',
                DB::raw('COALESCE(SUM(jel.debit), 0)  AS total_debit'),
                DB::raw('COALESCE(SUM(jel.credit), 0) AS total_credit'),
                DB::raw(
                    'CASE
                        WHEN a.normal_balance = \'debit\'
                            THEN COALESCE(SUM(jel.debit), 0) - COALESCE(SUM(jel.credit), 0)
                        ELSE
                            COALESCE(SUM(jel.credit), 0) - COALESCE(SUM(jel.debit), 0)
                     END AS net_balance'
                ),
            ])
            ->leftJoin('journal_entry_lines as jel', 'jel.account_id', '=', 'a.id')
            ->leftJoin('journal_entries as je', function ($join) use ($fromDate, $toDate) {
                $join->on('je.id', '=', 'jel.journal_entry_id')
                     ->where('je.status', 'posted');

                if ($fromDate) {
                    $join->where('je.date', '>=', $fromDate);
                }
                if ($toDate) {
                    $join->where('je.date', '<=', $toDate);
                }
            })
            ->where('a.is_postable', true)
            ->where('a.is_active', true)
            ->groupBy('a.id', 'a.account_number', 'a.account_name', 'a.account_type', 'a.normal_balance', 'a.level', 'a.parent_id')
            ->orderBy('a.account_number');

        return collect($query->get())->map(function ($row) {
            $row->net_balance   = (float) $row->net_balance;
            $row->total_debit   = (float) $row->total_debit;
            $row->total_credit  = (float) $row->total_credit;
            $row->balance_side  = $row->net_balance >= 0
                ? $row->normal_balance   // on normal side
                : ($row->normal_balance === 'debit' ? 'credit' : 'debit'); // contra
            return $row;
        });
    }

    /**
     * Verify the trial balance is mathematically balanced.
     * sum(debit) must equal sum(credit) across all accounts.
     */
    public function isBalanced(?string $fromDate = null, ?string $toDate = null): bool
    {
        $rows        = $this->generate($fromDate, $toDate);
        $totalDebit  = $rows->sum('total_debit');
        $totalCredit = $rows->sum('total_credit');

        return abs($totalDebit - $totalCredit) < 0.01;
    }

    /**
     * Total debits across all accounts (for display in the header).
     */
    public function totals(?string $fromDate = null, ?string $toDate = null): array
    {
        $rows = $this->generate($fromDate, $toDate);

        return [
            'total_debit'  => round($rows->sum('total_debit'), 2),
            'total_credit' => round($rows->sum('total_credit'), 2),
            'balanced'     => $this->isBalanced($fromDate, $toDate),
        ];
    }
}
