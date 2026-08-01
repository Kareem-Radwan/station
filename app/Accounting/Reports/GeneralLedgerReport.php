<?php

namespace App\Accounting\Reports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * General Ledger Report — running balance for one or all accounts.
 *
 * Reads ONLY from journal_entry_lines, journal_entries, accounts.
 */
class GeneralLedgerReport
{
    /**
     * Generate a running-balance ledger for a specific account.
     *
     * @param  int          $accountId
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @return array        { account, lines, opening_balance, closing_balance }
     */
    public function forAccount(int $accountId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $account = DB::table('accounts')->where('id', $accountId)->first();

        if (!$account) {
            return [];
        }

        // Opening balance: all postings before $fromDate
        $opening = 0.0;
        if ($fromDate) {
            $ob = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->where('je.status', 'posted')
                ->where('jel.account_id', $accountId)
                ->where('je.date', '<', $fromDate)
                ->selectRaw('COALESCE(SUM(jel.debit), 0) - COALESCE(SUM(jel.credit), 0) as net')
                ->value('net');

            $opening = $account->normal_balance === 'debit'
                ? (float) $ob
                : -(float) $ob;
        }

        // Lines within period
        $query = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->where('je.status', 'posted')
            ->where('jel.account_id', $accountId)
            ->select([
                'je.id as entry_id',
                'je.entry_no',
                'je.date',
                'je.description as entry_description',
                'je.reference_type',
                'je.reference_id',
                'jel.debit',
                'jel.credit',
                'jel.description as line_description',
            ])
            ->orderBy('je.date')
            ->orderBy('je.id');

        if ($fromDate) {
            $query->where('je.date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('je.date', '<=', $toDate);
        }

        $lines = collect($query->get())->map(function ($row) use (&$opening, $account) {
            $debit  = (float) $row->debit;
            $credit = (float) $row->credit;

            // Running balance
            if ($account->normal_balance === 'debit') {
                $opening += $debit - $credit;
            } else {
                $opening += $credit - $debit;
            }

            $row->running_balance = round($opening, 2);
            $row->debit           = round($debit,  2);
            $row->credit          = round($credit, 2);
            return $row;
        });

        $closingBalance = $lines->last()?->running_balance ?? $opening;

        return [
            'account'         => $account,
            'lines'           => $lines,
            'opening_balance' => round($opening - ($lines->last()?->running_balance - ($lines->first()?->running_balance ?? 0) ?? 0), 2),
            'closing_balance' => round($closingBalance, 2),
            'total_debit'     => round($lines->sum('debit'),  2),
            'total_credit'    => round($lines->sum('credit'), 2),
        ];
    }

    /**
     * List all postable accounts for the account selector dropdown.
     */
    public function accountList(): Collection
    {
        return collect(
            DB::table('accounts')
                ->where('is_postable', true)
                ->where('is_active', true)
                ->orderBy('account_number')
                ->get(['id', 'account_number', 'account_name', 'account_type'])
        );
    }
}
