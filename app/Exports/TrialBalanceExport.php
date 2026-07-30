<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Contributor;
use App\Models\Employee;
use App\Models\RentalContract;
use App\Models\InventoryItem;
use App\Models\NeighboringStation;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TrialBalanceExport implements WithMultipleSheets
{
    public function __construct(
        private ?string $fromDate = null,
        private ?string $toDate   = null
    ) {
        $this->fromDate = $fromDate ?? now()->startOfYear()->toDateString();
        $this->toDate   = $toDate   ?? now()->toDateString();
    }

    public function sheets(): array
    {
        return [
            new HierarchicalTrialBalanceSheet($this->fromDate, $this->toDate),
            new SectorialTrialBalanceMatrixSheet($this->fromDate, $this->toDate),
        ];
    }
}


// ─── Helper number formatter ──────────────────────────────────────────────────
function fmtTbVal($v): string
{
    return (float)$v != 0 ? number_format(abs((float)$v), 2) : '';
}


// ═══════════════════════════════════════════════════════════════════════════════
// ACCOUNT CATEGORY COLLECTORS
// ═══════════════════════════════════════════════════════════════════════════════

class TreasuryBankCollector
{
    public function getTitle(): string { return '1. الخزينة النقدية والحسابات البنكية'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $cashIn  = (float) DB::table('treasury_transactions')
            ->where('type', 'in')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->sum('amount');

        $cashOut = (float) DB::table('treasury_transactions')
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->sum('amount');

        $net = $cashIn - $cashOut;

        $rows = [
            [
                'code'       => '1010-001',
                'name'       => 'الخزينة النقدية الرئيسية',
                'debit'      => $cashIn,
                'credit'     => $cashOut,
                'net_debit'  => $net > 0 ? $net : 0,
                'net_credit' => $net < 0 ? abs($net) : 0,
            ]
        ];

        $bankReceipts = (float) DB::table('receipts')
            ->whereBetween('receipt_date', [$fromDate, $toDate])
            ->sum('total_amount');

        if ($bankReceipts > 0) {
            $rows[] = [
                'code'       => '1020-001',
                'name'       => 'أوراق القبض / البنوك',
                'debit'      => $bankReceipts,
                'credit'     => 0,
                'net_debit'  => $bankReceipts,
                'net_credit' => 0,
            ];
        }

        return $rows;
    }
}


class CustomerCollector
{
    public function getTitle(): string { return '2. حسابات العملاء (المدينون)'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $customers = Customer::orderBy('name')->get();
        $rows = [];
        $index = 1;

        foreach ($customers as $customer) {
            $ordersTotal = (float) DB::table('orders')
                ->where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('delivery_date', [$fromDate, $toDate])
                ->sum('total_amount');

            $cashOnOrders = (float) DB::table('orders')
                ->where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('delivery_date', [$fromDate, $toDate])
                ->sum('cash_amount');

            $paymentsReceived = (float) DB::table('customer_payments')
                ->where('customer_id', $customer->id)
                ->where('amount', '>', 0)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('amount');

            $outgoingPayments = (float) DB::table('customer_payments')
                ->where('customer_id', $customer->id)
                ->where('amount', '<', 0)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum(DB::raw('ABS(amount)'));

            // Treasury transactions - exclude customer_payment category to avoid double counting with customer_payments table
            $treasuryIn = (float) DB::table('treasury_transactions')
                ->where('reference_type', 'customer')
                ->where('reference_id', $customer->id)
                ->where('type', 'in')
                ->where('category', '!=', 'customer_payment')
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->sum('amount');

            $treasuryOut = (float) DB::table('treasury_transactions')
                ->where('reference_type', 'customer')
                ->where('reference_id', $customer->id)
                ->where('type', 'out')
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->sum('amount');

            // Do not include paid credits separately - they are already in customer_payments
            // $paidCredits is removed to prevent double counting

            $totalDebit  = $ordersTotal + $outgoingPayments + $treasuryOut;
            $totalCredit = $paymentsReceived + $treasuryIn + $cashOnOrders;

            if ($totalDebit > 0 || $totalCredit > 0) {
                $net = $totalDebit - $totalCredit;
                $rows[] = [
                    'code'       => '1110-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                    'name'       => $customer->name,
                    'debit'      => $totalDebit,
                    'credit'     => $totalCredit,
                    'net_debit'  => $net > 0 ? $net : 0,
                    'net_credit' => $net < 0 ? abs($net) : 0,
                ];
            }
        }

        return $rows;
    }
}


class SupplierCollector
{
    public function getTitle(): string { return '3. حسابات الموردين والمخازن (الدائنون)'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $suppliers = Supplier::orderBy('name')->get();
        $rows = [];
        $index = 1;

        foreach ($suppliers as $supplier) {
            $purchasesTotal = (float) DB::table('supplier_purchases')
                ->where('supplier_id', $supplier->id)
                ->whereBetween('purchase_date', [$fromDate, $toDate])
                ->sum('total_amount');

            $cashPaidOnPurchases = (float) DB::table('supplier_purchases')
                ->where('supplier_id', $supplier->id)
                ->whereBetween('purchase_date', [$fromDate, $toDate])
                ->sum('cash_amount');

            $paymentsMade = (float) DB::table('supplier_payments')
                ->where('supplier_id', $supplier->id)
                ->where('payment_type', '!=', 'deduction')
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('amount');

            $deductions = (float) DB::table('supplier_payments')
                ->where('supplier_id', $supplier->id)
                ->where('payment_type', 'deduction')
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('amount');

            // Do not include paid credits separately - they are already in supplier_payments
            // $paidCredits is removed to prevent double counting

            $totalDebit  = $paymentsMade + $cashPaidOnPurchases;
            $totalCredit = $purchasesTotal + $deductions;

            if ($totalDebit > 0 || $totalCredit > 0) {
                $net = $totalCredit - $totalDebit;
                $rows[] = [
                    'code'       => '2110-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                    'name'       => $supplier->name,
                    'debit'      => $totalDebit,
                    'credit'     => $totalCredit,
                    'net_debit'  => $net < 0 ? abs($net) : 0,
                    'net_credit' => $net > 0 ? $net : 0,
                ];
            }
        }

        return $rows;
    }
}


class RentalAgentCollector
{
    public function getTitle(): string { return '4. وكلاء النقل والسيارات المستأجرة'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $contracts = RentalContract::with('supplier')->get();
        $rows = [];
        $index = 1;

        foreach ($contracts as $contract) {
            $shiftCost = (float) DB::table('rental_shifts')
                ->where('rental_contract_id', $contract->id)
                ->whereBetween('shift_date', [$fromDate, $toDate])
                ->sum('total_cost');

            $maintCost = (float) DB::table('rental_maintenance')
                ->where('rental_contract_id', $contract->id)
                ->whereBetween('maintenance_date', [$fromDate, $toDate])
                ->sum('cost');

            $totalCredit = $shiftCost;
            $totalDebit  = $maintCost;

            if ($totalCredit > 0 || $totalDebit > 0) {
                $displayName = ($contract->supplier->name ?? 'وكيل نقل') . ' — ' . $contract->equipment_name . ($contract->car_number ? ' (' . $contract->car_number . ')' : '');
                $net = $totalCredit - $totalDebit;
                $rows[] = [
                    'code'       => '5430-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                    'name'       => $displayName,
                    'debit'      => $totalDebit,
                    'credit'     => $totalCredit,
                    'net_debit'  => $net < 0 ? abs($net) : 0,
                    'net_credit' => $net > 0 ? $net : 0,
                ];
            }
        }

        return $rows;
    }
}


class ContributorCollector
{
    public function getTitle(): string { return '5. المساهمين (حقوق الملكية)'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $contributors = Contributor::orderBy('name')->get();
        $rows = [];
        $index = 1;

        foreach ($contributors as $contributor) {
            $paymentsIn = (float) DB::table('contributor_payments')
                ->where('contributor_id', $contributor->id)
                ->whereNotNull('treasury_transaction_id')
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('amount');

            $paymentsOut = (float) DB::table('contributor_payments')
                ->where('contributor_id', $contributor->id)
                ->whereNull('treasury_transaction_id')
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->sum('amount');

            if ($paymentsIn == 0 && $paymentsOut == 0) {
                $paymentsIn = (float) DB::table('contributor_payments')
                    ->where('contributor_id', $contributor->id)
                    ->sum('amount');
            }

            $totalDebit  = $paymentsOut;
            $totalCredit = $paymentsIn;

            if ($totalDebit > 0 || $totalCredit > 0 || $contributor->share_amount > 0) {
                if ($totalCredit == 0 && $contributor->share_amount > 0) {
                    $totalCredit = (float)$contributor->share_amount;
                }
                $net = $totalCredit - $totalDebit;
                $rows[] = [
                    'code'       => '3110-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                    'name'       => $contributor->name,
                    'debit'      => $totalDebit,
                    'credit'     => $totalCredit,
                    'net_debit'  => $net < 0 ? abs($net) : 0,
                    'net_credit' => $net > 0 ? $net : 0,
                ];
            }
        }

        return $rows;
    }
}


class EmployeeBorrowCollector
{
    public function getTitle(): string { return '6. سلف الموظفين والأخرى'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $employees = Employee::orderBy('name')->get();
        $rows = [];
        $index = 1;

        foreach ($employees as $employee) {
            $borrowsTaken = (float) DB::table('employee_borrows')
                ->where('employee_id', $employee->id)
                ->whereBetween('borrow_date', [$fromDate, $toDate])
                ->sum('amount');

            $repaid = (float) DB::table('employee_borrows')
                ->where('employee_id', $employee->id)
                ->whereBetween('borrow_date', [$fromDate, $toDate])
                ->sum(DB::raw('amount - remaining_amount'));

            if ($borrowsTaken == 0 && $repaid == 0) {
                $borrowsTaken = (float) DB::table('employee_borrows')
                    ->where('employee_id', $employee->id)
                    ->sum('amount');
                $repaid = (float) DB::table('employee_borrows')
                    ->where('employee_id', $employee->id)
                    ->sum(DB::raw('amount - remaining_amount'));
            }

            if ($borrowsTaken > 0 || $repaid > 0) {
                $net = $borrowsTaken - $repaid;
                $rows[] = [
                    'code'       => '1210-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                    'name'       => 'سلفة ' . $employee->name,
                    'debit'      => $borrowsTaken,
                    'credit'     => $repaid,
                    'net_debit'  => $net > 0 ? $net : 0,
                    'net_credit' => $net < 0 ? abs($net) : 0,
                ];
            }
        }

        return $rows;
    }
}


class ExpenseCollector
{
    public function getTitle(): string { return '7. المصروفات والتكاليف التشغيلية'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $rows = [];
        $index = 1;

        $expensesByCategory = DB::table('expenses')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->groupBy('category')
            ->get();

        $categoryLabels = array_merge(
            \App\Models\Expense::categoryList(),
            \App\Models\ExpenseCategory::getAllCategories()
        );

        foreach ($expensesByCategory as $exp) {
            $label = $categoryLabels[$exp->category] ?? ($exp->category ? 'مصروفات ' . $exp->category : 'مصروفات متنوعة');
            $rows[] = [
                'code'       => '5310-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                'name'       => $label,
                'debit'      => (float)$exp->total,
                'credit'     => 0,
                'net_debit'  => (float)$exp->total,
                'net_credit' => 0,
            ];
        }

        $landRentPaid = (float) DB::table('land_rent_payments')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->sum('amount');
        if ($landRentPaid > 0) {
            $rows[] = [
                'code'       => '5320-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                'name'       => 'إيجار الأرض',
                'debit'      => $landRentPaid,
                'credit'     => 0,
                'net_debit'  => $landRentPaid,
                'net_credit' => 0,
            ];
        }

        $fuelCost = (float) DB::table('equipment_fuel_logs')
            ->whereBetween('log_date', [$fromDate, $toDate])
            ->sum('total_cost');
        if ($fuelCost > 0) {
            $rows[] = [
                'code'       => '5410-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                'name'       => 'وقود المعدات المملوكة',
                'debit'      => $fuelCost,
                'credit'     => 0,
                'net_debit'  => $fuelCost,
                'net_credit' => 0,
            ];
        }

        $ownedMaint = (float) DB::table('equipment_maintenance')
            ->whereBetween('maintenance_date', [$fromDate, $toDate])
            ->sum('cost');
        if ($ownedMaint > 0) {
            $rows[] = [
                'code'       => '5420-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                'name'       => 'صيانة المعدات المملوكة',
                'debit'      => $ownedMaint,
                'credit'     => 0,
                'net_debit'  => $ownedMaint,
                'net_credit' => 0,
            ];
        }

        return $rows;
    }
}


class InventoryCollector
{
    public function getTitle(): string { return '8. المخزون والمواد الخام'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $items = InventoryItem::orderBy('name_ar')->get();
        $rows = [];
        $index = 1;

        foreach ($items as $item) {
            $stockIn = (float) DB::table('inventory_movements')
                ->where('inventory_item_id', $item->id)
                ->where('type', 'in')
                ->whereBetween('movement_date', [$fromDate, $toDate])
                ->sum('total_cost');

            $stockOut = (float) DB::table('inventory_movements')
                ->where('inventory_item_id', $item->id)
                ->where('type', 'out')
                ->whereBetween('movement_date', [$fromDate, $toDate])
                ->sum('total_cost');

            if ($stockIn == 0 && $stockOut == 0 && $item->current_stock > 0) {
                $stockIn = (float)$item->current_stock * (float)$item->price_per_unit;
            }

            if ($stockIn > 0 || $stockOut > 0 || $item->current_stock > 0) {
                $net = $stockIn - $stockOut;
                
                // Determine stock status
                $stockStatus = $item->isBelowAlert() ? 'منخفض ⚠️' : 'عادي ✓';
                $currentQty = number_format((float)$item->current_stock, 3);
                
                $rows[] = [
                    'code'       => '1310-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                    'name'       => 'مخزن ' . $item->name_ar . ' — الكمية: ' . $currentQty . ' ' . $item->unit . ' (' . $stockStatus . ')',
                    'debit'      => $stockIn,
                    'credit'     => $stockOut,
                    'net_debit'  => $net > 0 ? $net : 0,
                    'net_credit' => $net < 0 ? abs($net) : 0,
                ];
            }
        }

        return $rows;
    }
}


class NeighboringStationCollector
{
    public function getTitle(): string { return '9. المحطات المجاورة'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $stations = NeighboringStation::orderBy('name')->get();
        $rows = [];
        $index = 1;

        foreach ($stations as $station) {
            $outgoing = (float) DB::table('neighboring_station_transactions')
                ->where('neighboring_station_id', $station->id)
                ->where('direction', 'outgoing')
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->sum('amount');

            $incoming = (float) DB::table('neighboring_station_transactions')
                ->where('neighboring_station_id', $station->id)
                ->where('direction', 'incoming')
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->sum('amount');

            $paidIncoming = (float) DB::table('neighboring_station_transactions')
                ->where('neighboring_station_id', $station->id)
                ->where('direction', 'incoming')
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->sum('paid_amount');

            $paidOutgoing = (float) DB::table('neighboring_station_transactions')
                ->where('neighboring_station_id', $station->id)
                ->where('direction', 'outgoing')
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->sum('paid_amount');

            $totalDebit  = $outgoing + $paidIncoming;
            $totalCredit = $incoming + $paidOutgoing;

            if ($totalDebit > 0 || $totalCredit > 0) {
                $net = $totalDebit - $totalCredit;
                $rows[] = [
                    'code'       => '1410-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                    'name'       => $station->name,
                    'debit'      => $totalDebit,
                    'credit'     => $totalCredit,
                    'net_debit'  => $net > 0 ? $net : 0,
                    'net_credit' => $net < 0 ? abs($net) : 0,
                ];
            }
        }

        return $rows;
    }
}


class PayrollCollector
{
    public function getTitle(): string { return '10. الرواتب والأجور'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $fromYear  = (int) date('Y', strtotime($fromDate));
        $fromMonth = (int) date('m', strtotime($fromDate));
        $toYear    = (int) date('Y', strtotime($toDate));
        $toMonth   = (int) date('m', strtotime($toDate));

        $payrolls = DB::table('payroll')
            ->join('employees', 'payroll.employee_id', '=', 'employees.id')
            ->select(
                'employees.name',
                DB::raw('SUM(payroll.base_salary) as total_base'),
                DB::raw('SUM(payroll.overtime_pay) as total_overtime'),
                DB::raw('SUM(payroll.total_deductions) as total_deductions')
            )
            ->where('payroll.status', 'paid')
            ->where(function ($q) use ($fromYear, $fromMonth, $toYear, $toMonth) {
                $q->whereRaw('(payroll.period_year * 100 + payroll.period_month) >= ?', [$fromYear * 100 + $fromMonth])
                  ->whereRaw('(payroll.period_year * 100 + payroll.period_month) <= ?', [$toYear * 100 + $toMonth]);
            })
            ->groupBy('employees.id', 'employees.name')
            ->get();

        $rows = [];
        $index = 1;

        foreach ($payrolls as $p) {
            $gross = (float)$p->total_base + (float)$p->total_overtime;
            $ded   = (float)$p->total_deductions;
            $net   = $gross - $ded;

            $rows[] = [
                'code'       => '5210-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                'name'       => 'راتب ' . $p->name,
                'debit'      => $gross,
                'credit'     => $ded,
                'net_debit'  => $net > 0 ? $net : 0,
                'net_credit' => $net < 0 ? abs($net) : 0,
            ];
        }

        return $rows;
    }
}


class RevenueCollector
{
    public function getTitle(): string { return '11. الإيرادات والمبيعات'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $concreteSales = (float) DB::table('orders')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('delivery_date', [$fromDate, $toDate])
            ->sum('total_amount');

        $otherIncome = (float) DB::table('treasury_transactions')
            ->where('type', 'in')
            ->whereIn('category', ['income', 'refund', 'other'])
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->sum('amount');

        $rows = [
            [
                'code'       => '4110-001',
                'name'       => 'إيرادات مبيعات الخرسانة الجاهزة',
                'debit'      => 0,
                'credit'     => $concreteSales,
                'net_debit'  => 0,
                'net_credit' => $concreteSales,
            ]
        ];

        if ($otherIncome > 0) {
            $rows[] = [
                'code'       => '4210-001',
                'name'       => 'إيرادات متنوعة وأخرى',
                'debit'      => 0,
                'credit'     => $otherIncome,
                'net_debit'  => 0,
                'net_credit' => $otherIncome,
            ];
        }

        return $rows;
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// SHEET 1 — HIERARCHICAL ANALYTICAL TRIAL BALANCE
// ═══════════════════════════════════════════════════════════════════════════════

class HierarchicalTrialBalanceSheet implements
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithColumnWidths,
    \Maatwebsite\Excel\Concerns\WithEvents,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    private array $rows     = [];
    private array $styleMap = [];

    public function __construct(private string $fromDate, private string $toDate) {}

    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function title(): string { return 'ميزان المراجعة التحليلي'; }

    public function columnWidths(): array
    {
        return ['A' => 12, 'B' => 45, 'C' => 20, 'D' => 20, 'E' => 20, 'F' => 20];
    }

    public function array(): array
    {
        $fd = $this->fromDate;
        $td = $this->toDate;

        $collectors = [
            new TreasuryBankCollector(),
            new CustomerCollector(),
            new SupplierCollector(),
            new RentalAgentCollector(),
            new ContributorCollector(),
            new EmployeeBorrowCollector(),
            new ExpenseCollector(),
            new InventoryCollector(),
            new NeighboringStationCollector(),
            new PayrollCollector(),
            new RevenueCollector(),
        ];

        $out = [];

        // Title Header
        $out[] = ['محطة الخرسانة الجاهزة (CBPMS)', '', '', '', '', ''];
        $out[] = ['ميزان المراجعة التحليلي الشامل (حسب الحسابات الفرعية)', '', '', '', '', ''];
        $out[] = ['الفترة المالية: ' . $fd . ' إلى ' . $td, '', '', '', '', ''];
        $out[] = ['تاريخ الإصدار: ' . now()->format('Y-m-d H:i'), '', '', '', '', ''];
        $out[] = ['', '', '', '', '', ''];

        // Table Headings
        $out[] = ['رقم الحساب', 'اسم الحساب / البيان', 'حركات مدينة', 'حركات دائنة', 'رصيد مدين', 'رصيد دائن'];

        $this->styleMap[1] = 'company';
        $this->styleMap[2] = 'title';
        $this->styleMap[3] = 'period';
        $this->styleMap[4] = 'period';
        $this->styleMap[6] = 'heading';

        $rowIdx = 7;

        $grandDebitMovements  = 0;
        $grandCreditMovements = 0;
        $grandNetDebit        = 0;
        $grandNetCredit       = 0;

        foreach ($collectors as $collector) {
            $catRows = $collector->getRows($fd, $td);
            if (empty($catRows)) {
                continue;
            }

            // Category Group Header
            $out[] = [$collector->getTitle(), '', '', '', '', ''];
            $this->styleMap[$rowIdx] = 'group_header';
            $rowIdx++;

            $subDebit  = 0;
            $subCredit = 0;
            $subNetDeb = 0;
            $subNetCre = 0;

            foreach ($catRows as $r) {
                $out[] = [
                    $r['code'],
                    $r['name'],
                    fmtTbVal($r['debit']),
                    fmtTbVal($r['credit']),
                    fmtTbVal($r['net_debit']),
                    fmtTbVal($r['net_credit']),
                ];
                $this->styleMap[$rowIdx] = 'data';
                $rowIdx++;

                $subDebit  += (float)$r['debit'];
                $subCredit += (float)$r['credit'];
                $subNetDeb += (float)$r['net_debit'];
                $subNetCre += (float)$r['net_credit'];
            }

            // Category Subtotal Row
            $out[] = [
                '',
                'إجمالي ' . $collector->getTitle(),
                number_format($subDebit, 2),
                number_format($subCredit, 2),
                number_format($subNetDeb, 2),
                number_format($subNetCre, 2),
            ];
            $this->styleMap[$rowIdx] = 'subtotal';
            $rowIdx++;

            // Accumulate Grand Totals
            $grandDebitMovements  += $subDebit;
            $grandCreditMovements += $subCredit;
            $grandNetDebit        += $subNetDeb;
            $grandNetCredit       += $subNetCre;
        }

        // Blank separator
        $out[] = ['', '', '', '', '', ''];
        $rowIdx++;

        // Grand Total Row
        $out[] = [
            '',
            'الإجمالي العام لميزان المراجعة',
            number_format($grandDebitMovements, 2),
            number_format($grandCreditMovements, 2),
            number_format($grandNetDebit, 2),
            number_format($grandNetCredit, 2),
        ];
        $this->styleMap[$rowIdx] = 'grand_total';
        $rowIdx++;

        // Balance Check Row
        $diff = abs($grandDebitMovements - $grandCreditMovements);
        $balanced = $diff < 0.01;
        $out[] = [
            '',
            'حالة توازن الميزان Accounting Balance Status',
            '',
            $balanced ? 'متوازن 100% ✓' : 'غير متوازن',
            $balanced ? 'الفرق: 0.00' : number_format($diff, 2),
            $balanced ? 'Total Debit == Total Credit' : 'يوجد فارق',
        ];
        $this->styleMap[$rowIdx] = $balanced ? 'balanced' : 'unbalanced';

        $this->rows = $out;
        return $out;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        if (empty($this->styleMap)) {
            $this->array();
        }

        foreach ($this->styleMap as $row => $type) {
            switch ($type) {
                case 'company':
                    $sheet->mergeCells("A{$row}:F{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 18, 'color' => ['rgb' => '1F3864']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(28);
                    break;

                case 'title':
                    $sheet->mergeCells("A{$row}:F{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '2E4057']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(22);
                    break;

                case 'period':
                    $sheet->mergeCells("A{$row}:F{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '555555']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    break;

                case 'heading':
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(24);
                    break;

                case 'group_header':
                    $sheet->mergeCells("A{$row}:F{$row}");
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'indent' => 1],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(20);
                    break;

                case 'data':
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]],
                    ]);
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:F{$row}")->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F9FAFB');
                    }
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    break;

                case 'subtotal':
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1F3864']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                        'borders' => [
                            'top'    => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '1F3864']],
                            'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE, 'color' => ['rgb' => '1F3864']],
                        ],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getRowDimension($row)->setRowHeight(22);
                    break;

                case 'grand_total':
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                        'borders' => [
                            'top'    => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                            'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE, 'color' => ['rgb' => '000000']],
                        ],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(26);
                    break;

                case 'balanced':
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '065F46']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    break;

                case 'unbalanced':
                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '991B1B']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    break;
            }
        }

        $lastRow = count($this->rows);
        $sheet->getStyle("A6:F{$lastRow}")->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '1F3864']]],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
            },
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// SHEET 2 — SECTORIAL MATRIX TRIAL BALANCE (MATCHING REFERENCE IMAGE LAYOUT)
// ═══════════════════════════════════════════════════════════════════════════════

class SectorialTrialBalanceMatrixSheet implements
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithColumnWidths,
    \Maatwebsite\Excel\Concerns\WithEvents,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private string $fromDate, private string $toDate) {}

    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function title(): string { return 'ميزان المراجعة القطاعي (الصورة)'; }

    public function columnWidths(): array
    {
        return [
            'A' => 26, 'B' => 16,
            'C' => 26, 'D' => 16,
            'E' => 26, 'F' => 16,
            'G' => 26, 'H' => 16,
            'I' => 26, 'J' => 16,
            'K' => 26, 'L' => 16,
        ];
    }

    public function array(): array
    {
        $fd = $this->fromDate;
        $td = $this->toDate;

        $contributors = (new ContributorCollector())->getRows($fd, $td);
        $expenses     = (new ExpenseCollector())->getRows($fd, $td);
        $suppliers    = (new SupplierCollector())->getRows($fd, $td);
        $customers    = (new CustomerCollector())->getRows($fd, $td);
        $rentals      = (new RentalAgentCollector())->getRows($fd, $td);
        $banks        = (new TreasuryBankCollector())->getRows($fd, $td);

        $out = [];

        // Title Header
        $out[] = ['شركة نيو سوليد اب / محطة الخرسانة الجاهزة', '', '', '', '', '', '', '', '', '', '', ''];
        $out[] = ['ميزان المراجعة القطاعي الإجمالي — ' . $fd . ' إلى ' . $td, '', '', '', '', '', '', '', '', '', '', ''];
        $out[] = ['', '', '', '', '', '', '', '', '', '', '', ''];

        // Category Headers Row
        $out[] = [
            'المساهمين', '',
            'أخرى / سلف ومصروفات', '',
            'موردين', '',
            'عملاء', '',
            'وكلاء نقل', '',
            'بنوك وخزينة', '',
        ];

        // Column Headings Row
        $out[] = [
            'اسم المساهم', 'صافي الرصيد',
            'اسم البيان / الحساب', 'صافي الرصيد',
            'اسم المورد', 'صافي الرصيد',
            'اسم العميل', 'صافي الرصيد',
            'وكيل النقل', 'صافي الرصيد',
            'الخزينة / البنك', 'صافي الرصيد',
        ];

        $maxCount = max(
            count($contributors),
            count($expenses),
            count($suppliers),
            count($customers),
            count($rentals),
            count($banks)
        );

        for ($i = 0; $i < $maxCount; $i++) {
            $cRow = $contributors[$i] ?? null;
            $eRow = $expenses[$i]     ?? null;
            $sRow = $suppliers[$i]    ?? null;
            $uRow = $customers[$i]    ?? null;
            $rRow = $rentals[$i]      ?? null;
            $bRow = $banks[$i]        ?? null;

            $out[] = [
                $cRow ? $cRow['name'] : '', $cRow ? fmtTbVal($cRow['net_credit'] - $cRow['net_debit']) : '',
                $eRow ? $eRow['name'] : '', $eRow ? fmtTbVal($eRow['net_debit'] - $eRow['net_credit']) : '',
                $sRow ? $sRow['name'] : '', $sRow ? fmtTbVal($sRow['net_credit'] - $sRow['net_debit']) : '',
                $uRow ? $uRow['name'] : '', $uRow ? fmtTbVal($uRow['net_debit'] - $uRow['net_credit']) : '',
                $rRow ? $rRow['name'] : '', $rRow ? fmtTbVal($rRow['net_credit'] - $rRow['net_debit']) : '',
                $bRow ? $bRow['name'] : '', $bRow ? fmtTbVal($bRow['net_debit'] - $bRow['net_credit']) : '',
            ];
        }

        // Grand Totals Row
        $out[] = [
            'Grand Total', number_format(array_sum(array_map(fn($r) => $r['net_credit'] - $r['net_debit'], $contributors)), 2),
            'Grand Total', number_format(array_sum(array_map(fn($r) => $r['net_debit'] - $r['net_credit'], $expenses)), 2),
            'Grand Total', number_format(array_sum(array_map(fn($r) => $r['net_credit'] - $r['net_debit'], $suppliers)), 2),
            'Grand Total', number_format(array_sum(array_map(fn($r) => $r['net_debit'] - $r['net_credit'], $customers)), 2),
            'Grand Total', number_format(array_sum(array_map(fn($r) => $r['net_credit'] - $r['net_debit'], $rentals)), 2),
            'Grand Total', number_format(array_sum(array_map(fn($r) => $r['net_debit'] - $r['net_credit'], $banks)), 2),
        ];

        return $out;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 11, 'color' => ['rgb' => '444444']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Merge sector top headers
        $sheet->mergeCells('A4:B4');
        $sheet->mergeCells('C4:D4');
        $sheet->mergeCells('E4:F4');
        $sheet->mergeCells('G4:H4');
        $sheet->mergeCells('I4:J4');
        $sheet->mergeCells('K4:L4');

        $sheet->getStyle('A4:L4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '1F3864']]],
        ]);

        // Table subheadings
        $sheet->getStyle('A5:L5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $highRow = $sheet->getHighestRow();

        // Data rows
        for ($r = 6; $r < $highRow; $r++) {
            $sheet->getStyle("A{$r}:L{$r}")->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
            ]);
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:L{$r}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F7FAFC');
            }
        }

        // Grand Totals row
        $sheet->getStyle("A{$highRow}:L{$highRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1F3864']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'borders' => [
                'top'    => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE, 'color' => ['rgb' => '1F3864']],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE, 'color' => ['rgb' => '1F3864']],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
            },
        ];
    }
}

