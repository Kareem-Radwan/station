<?php

namespace App\Http\Controllers;

use App\Accounting\Exports\BalanceSheetExport;
use App\Accounting\Exports\GeneralLedgerExport;
use App\Accounting\Exports\JournalBookExport;
use App\Accounting\Exports\TrialBalanceExportAccounting;
use App\Accounting\Models\JournalEntry;
use App\Accounting\Reports\BalanceSheetReport;
use App\Accounting\Reports\GeneralLedgerReport;
use App\Accounting\Reports\IncomeStatementReport;
use App\Accounting\Reports\TrialBalanceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Accounting Controller — thin, only orchestrates reports and views.
 *
 * All routes protected by role:admin,accountant middleware (set in routes/web.php).
 * Reports read exclusively from journal_entry_lines — never from business tables.
 */
class AccountingController extends Controller
{
    public function __construct(
        private readonly TrialBalanceReport    $trialBalance,
        private readonly GeneralLedgerReport   $generalLedger,
        private readonly BalanceSheetReport    $balanceSheet,
        private readonly IncomeStatementReport $incomeStatement,
    ) {}

    // ─── Trial Balance ───────────────────────────────────────────────────────────

    public function trialBalance(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $rows    = $this->trialBalance->generate($fromDate, $toDate);
        $totals  = $this->trialBalance->totals($fromDate, $toDate);
        $grouped = $rows->groupBy('account_type');

        return view('accounting.trial-balance', compact('rows', 'totals', 'grouped', 'fromDate', 'toDate'));
    }

    public function exportTrialBalance(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $filename = 'ميزان-المراجعة-' . $fromDate . '-' . $toDate . '.xlsx';

        return Excel::download(
            new TrialBalanceExportAccounting($this->trialBalance, $fromDate, $toDate),
            $filename
        );
    }

    // ─── General Ledger ──────────────────────────────────────────────────────────

    public function generalLedger(Request $request)
    {
        $fromDate  = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate    = $request->input('to_date',   now()->toDateString());
        $accountId = $request->input('account_id');

        $accounts = $this->generalLedger->accountList();
        $ledger   = $accountId ? $this->generalLedger->forAccount((int) $accountId, $fromDate, $toDate) : null;

        return view('accounting.general-ledger', compact('accounts', 'ledger', 'fromDate', 'toDate', 'accountId'));
    }

    public function exportGeneralLedger(Request $request)
    {
        $fromDate  = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate    = $request->input('to_date',   now()->toDateString());
        $accountId = (int) $request->input('account_id', 0);

        if (!$accountId) {
            return redirect()->route('accounting.general-ledger')->with('error', 'يرجى اختيار حساب أولاً');
        }

        $account  = \App\Accounting\Models\Account::find($accountId);
        $filename = 'استاذ-' . ($account?->account_number ?? $accountId) . '-' . $fromDate . '-' . $toDate . '.xlsx';

        return Excel::download(
            new GeneralLedgerExport($this->generalLedger, $accountId, $fromDate, $toDate),
            $filename
        );
    }

    // ─── Balance Sheet ───────────────────────────────────────────────────────────

    public function balanceSheet(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $data = $this->balanceSheet->generate($fromDate, $toDate);

        return view('accounting.balance-sheet', array_merge($data, compact('fromDate', 'toDate')));
    }

    public function exportBalanceSheet(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $filename = 'الميزانية-العمومية-' . $fromDate . '-' . $toDate . '.xlsx';

        return Excel::download(
            new BalanceSheetExport($this->balanceSheet, $this->incomeStatement, $fromDate, $toDate),
            $filename
        );
    }

    // ─── Income Statement ────────────────────────────────────────────────────────

    public function incomeStatement(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $data = $this->incomeStatement->generate($fromDate, $toDate);

        return view('accounting.income-statement', array_merge($data, compact('fromDate', 'toDate')));
    }

    public function exportIncomeStatement(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $filename = 'قائمة-الدخل-' . $fromDate . '-' . $toDate . '.xlsx';

        // Reuse the BalanceSheetExport multi-sheet — income statement is Sheet 2
        return Excel::download(
            new BalanceSheetExport($this->balanceSheet, $this->incomeStatement, $fromDate, $toDate),
            $filename
        );
    }

    // ─── Journal Book ────────────────────────────────────────────────────────────

    public function journalBook(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $query = JournalEntry::with('lines.account')
            ->where('status', 'posted')
            ->when($fromDate, fn($q) => $q->where('date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('date', '<=', $toDate))
            ->orderBy('date')
            ->orderBy('entry_no');

        $entries = $query->paginate(50)->withQueryString();

        return view('accounting.journal-book', compact('entries', 'fromDate', 'toDate'));
    }

    public function exportJournalBook(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $filename = 'دفتر-اليومية-' . $fromDate . '-' . $toDate . '.xlsx';

        return Excel::download(
            new JournalBookExport($fromDate, $toDate),
            $filename
        );
    }

    // ─── Rebuild Accounting ──────────────────────────────────────────────────────

    public function rebuild()
    {
        try {
            // Run the rebuild command and capture output
            $exitCode = Artisan::call('accounting:rebuild');
            $output = Artisan::output();

            // Parse output to extract statistics
            $posted = 0;
            $skipped = 0;
            $errors = 0;

            if (preg_match('/مرحّل:\s+(\d+)/', $output, $matches)) {
                $posted = (int) $matches[1];
            }
            if (preg_match('/متجاوز:\s+(\d+)/', $output, $matches)) {
                $skipped = (int) $matches[1];
            }
            if (preg_match('/أخطاء:\s+(\d+)/', $output, $matches)) {
                $errors = (int) $matches[1];
            }

            return response()->json([
                'success' => $exitCode === 0,
                'posted' => $posted,
                'skipped' => $skipped,
                'errors' => $errors,
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
