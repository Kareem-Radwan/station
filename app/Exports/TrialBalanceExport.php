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
        // Calculate movements within the period (for debit/credit columns)
        $cashIn  = (float) DB::table('treasury_transactions')
            ->where('type', 'in')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->sum('amount');

        $cashOut = (float) DB::table('treasury_transactions')
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->sum('amount');

        // Calculate the CURRENT cumulative balance (all time) - this matches the treasury page
        // This shows the actual cash position at the end of the period
        $currentBalance = (float) DB::table('treasury_transactions')
            ->where('type', 'in')
            ->sum('amount') 
            - (float) DB::table('treasury_transactions')
            ->where('type', 'out')
            ->sum('amount');

        $rows = [
            [
                'code'       => '1010-001',
                'name'       => 'الخزينة النقدية الرئيسية',
                'debit'      => $cashIn,
                'credit'     => $cashOut,
                'net_debit'  => $currentBalance > 0 ? $currentBalance : 0,
                'net_credit' => $currentBalance < 0 ? abs($currentBalance) : 0,
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

            $totalDebit  = 0; // Payments to agent (if tracked separately)
            $totalCredit = $shiftCost + $maintCost; // Total costs owed

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
    public function getTitle(): string { return '8. المخزون والمواد الخام (بيان كمي فقط)'; }

    public function getRows(string $fromDate, string $toDate): array
    {
        $items = InventoryItem::orderBy('name_ar')->get();
        $rows = [];
        $index = 1;

        foreach ($items as $item) {
            // Get quantity movements (not prices)
            $qtyIn = (float) DB::table('inventory_movements')
                ->where('inventory_item_id', $item->id)
                ->where('type', 'in')
                ->whereBetween('movement_date', [$fromDate, $toDate])
                ->sum('quantity');

            $qtyOut = (float) DB::table('inventory_movements')
                ->where('inventory_item_id', $item->id)
                ->where('type', 'out')
                ->whereBetween('movement_date', [$fromDate, $toDate])
                ->sum('quantity');

            // If no movements in period, show current stock as opening balance
            if ($qtyIn == 0 && $qtyOut == 0 && $item->current_stock > 0) {
                $qtyIn = (float)$item->current_stock;
            }

            if ($qtyIn > 0 || $qtyOut > 0 || $item->current_stock > 0) {
                $netQty = $qtyIn - $qtyOut;
                
                // Determine stock status
                $stockStatus = $item->isBelowAlert() ? 'منخفض ⚠️' : 'عادي ✓';
                $currentQty = number_format((float)$item->current_stock, 3);
                
                $rows[] = [
                    'code'       => '1310-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                    'name'       => 'مخزن ' . $item->name_ar . ' — الرصيد: ' . $currentQty . ' ' . $item->unit . ' (' . $stockStatus . ')',
                    'debit'      => $qtyIn > 0 ? number_format($qtyIn, 3) . ' ' . $item->unit : '',
                    'credit'     => $qtyOut > 0 ? number_format($qtyOut, 3) . ' ' . $item->unit : '',
                    'net_debit'  => $netQty > 0 ? number_format($netQty, 3) . ' ' . $item->unit : '',
                    'net_credit' => $netQty < 0 ? number_format(abs($netQty), 3) . ' ' . $item->unit : '',
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
                'employees.id as employee_id',
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
            
            // Get paid borrows for this employee (all time)
            $paidBorrows = (float) DB::table('employee_borrows')
                ->where('employee_id', $p->employee_id)
                ->sum(DB::raw('amount - remaining_amount'));
            
            // Net payroll = gross - deductions - paid borrows
            $netAfterDeductions = $gross - $ded;
            $netAfterBorrows = $netAfterDeductions - $paidBorrows;

            $rows[] = [
                'code'       => '5210-' . str_pad($index++, 3, '0', STR_PAD_LEFT),
                'name'       => 'راتب ' . $p->name,
                'debit'      => $gross,
                'credit'     => $ded + $paidBorrows,  // Total deductions including paid borrows
                'net_debit'  => $netAfterBorrows > 0 ? $netAfterBorrows : 0,
                'net_credit' => $netAfterBorrows < 0 ? abs($netAfterBorrows) : 0,
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
            
            // Check if this is the inventory collector (quantities only, not included in totals)
            $isInventoryCollector = ($collector instanceof InventoryCollector);

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

                // Only add to totals if NOT inventory (inventory shows quantities, not amounts)
                if (!$isInventoryCollector) {
                    $subDebit  += (float)$r['debit'];
                    $subCredit += (float)$r['credit'];
                    $subNetDeb += (float)$r['net_debit'];
                    $subNetCre += (float)$r['net_credit'];
                }
            }

            // Category Subtotal Row - show note for inventory
            if ($isInventoryCollector) {
                $out[] = [
                    '',
                    'إجمالي ' . $collector->getTitle() . ' — بيان كمي فقط (غير مدرج في الإجمالي المالي)',
                    '—',
                    '—',
                    '—',
                    '—',
                ];
            } else {
                $out[] = [
                    '',
                    'إجمالي ' . $collector->getTitle(),
                    number_format($subDebit, 2),
                    number_format($subCredit, 2),
                    number_format($subNetDeb, 2),
                    number_format($subNetCre, 2),
                ];
            }
            $this->styleMap[$rowIdx] = 'subtotal';
            $rowIdx++;

            // Accumulate Grand Totals (excluding inventory)
            if (!$isInventoryCollector) {
                $grandDebitMovements  += $subDebit;
                $grandCreditMovements += $subCredit;
                $grandNetDebit        += $subNetDeb;
                $grandNetCredit       += $subNetCre;
            }
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
    private array $styleMap = [];
    
    public function __construct(private string $fromDate, private string $toDate) {}

    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function title(): string { return 'ميزان المراجعة القطاعي (الصورة)'; }

    public function columnWidths(): array
    {
        return [
            'A' => 26, 'B' => 16,
            'C' => 26, 'D' => 14, 'E' => 14, 'F' => 14,
            'G' => 26, 'H' => 16,
            'I' => 26, 'J' => 16, 'K' => 16,
            'L' => 26, 'M' => 16, 'N' => 16,
            'O' => 26, 'P' => 16,
            'Q' => 26, 'R' => 16,
            'S' => 26, 'T' => 16,
            'U' => 26, 'V' => 16,
        ];
    }

    private function getDetailedCustomerRows(string $fd, string $td): array
    {
        $customers = Customer::orderBy('name')->get();
        $rows = [];
        
        foreach ($customers as $customer) {
            // Get total orders (sales - what they owe us - increases receivables)
            $orders = (float) DB::table('orders')
                ->where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('delivery_date', [$fd, $td])
                ->sum('total_amount');
            
            // Get cash paid on orders (reduces debt immediately)
            $cashOnOrders = (float) DB::table('orders')
                ->where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('delivery_date', [$fd, $td])
                ->sum('cash_amount');
            
            // Get all payments received from customer (reduces their debt)
            $paymentsReceived = (float) DB::table('customer_payments')
                ->where('customer_id', $customer->id)
                ->where('amount', '>', 0)
                ->whereBetween('payment_date', [$fd, $td])
                ->sum('amount');
            
            // Total payments = cash on orders + direct payments
            $totalPayments = $cashOnOrders + $paymentsReceived;
            
            // Net orders = orders - cash paid immediately
            $netOrders = $orders - $cashOnOrders;
            
            // Remaining debt = net orders - payments received
            $remainingDebt = $netOrders - $paymentsReceived;
            
            // If no activity in period, calculate from all-time data
            if ($orders == 0 && $paymentsReceived == 0) {
                // Get outstanding balance using the model method
                $outstandingBalance = $customer->getOutstandingBalance();
                $remainingDebt = max(0, $outstandingBalance);
                $totalPayments = 0; // No payments in this period
            }
            
            // Always show all customers
            $rows[] = [
                'name' => $customer->name,
                'payments' => $totalPayments,        // صافي الرصيد (what they paid)
                'credits' => max(0, $remainingDebt)  // ديون عملاء (what they still owe)
            ];
        }
        
        return $rows;
    }
    
    private function getDetailedSupplierRows(string $fd, string $td): array
    {
        $suppliers = Supplier::orderBy('name')->get();
        $rows = [];
        
        foreach ($suppliers as $supplier) {
            // Get total purchases (what we owe them - increases debt)
            $purchases = (float) DB::table('supplier_purchases')
                ->where('supplier_id', $supplier->id)
                ->whereBetween('purchase_date', [$fd, $td])
                ->sum('total_amount');
            
            // Get cash paid on purchases (reduces debt immediately)
            $cashOnPurchases = (float) DB::table('supplier_purchases')
                ->where('supplier_id', $supplier->id)
                ->whereBetween('purchase_date', [$fd, $td])
                ->sum('cash_amount');
            
            // Get payments made to supplier (reduces debt)
            $paymentsMade = (float) DB::table('supplier_payments')
                ->where('supplier_id', $supplier->id)
                ->where('payment_type', 'payment')
                ->whereBetween('payment_date', [$fd, $td])
                ->sum('amount');
            
            // Get deductions (we take back from them - increases our credit/their debt to us)
            $deductions = (float) DB::table('supplier_payments')
                ->where('supplier_id', $supplier->id)
                ->where('payment_type', 'deduction')
                ->whereBetween('payment_date', [$fd, $td])
                ->sum('amount');
            
            // Calculate current debt from database (what we currently owe them)
            $currentDebt = (float) $supplier->balance;
            
            // Total payments = cash on purchases + direct payments
            $totalPayments = $cashOnPurchases + $paymentsMade;
            
            // Net purchases = purchases - cash paid immediately
            $netPurchases = $purchases - $cashOnPurchases;
            
            // Remaining debt = net purchases - payments made + deductions
            // (deductions increase what they owe us, so we add them)
            $remainingDebt = $netPurchases - $paymentsMade + $deductions;
            
            // If no activity in period, use current balance from database
            if ($purchases == 0 && $paymentsMade == 0 && $deductions == 0 && $currentDebt != 0) {
                $remainingDebt = $currentDebt;
                $totalPayments = 0; // We'll show the debt but no payments in this period
            }
            
            // Always show all suppliers
            $rows[] = [
                'name' => $supplier->name,
                'payments' => $totalPayments,     // صافي الرصيد (what we paid)
                'credits' => max(0, $remainingDebt) // ديون موردين (what we still owe)
            ];
        }
        
        return $rows;
    }
    
    private function getEmployeeBorrowAndPayrollRows(string $fd, string $td): array
    {
        $employees = Employee::orderBy('name')->get();
        $borrowRows = [];
        $payrollRows = [];
        
        foreach ($employees as $employee) {
            // Get total borrows amount
            $totalBorrows = (float) DB::table('employee_borrows')
                ->where('employee_id', $employee->id)
                ->sum('amount');
            
            // Get remaining (unpaid) borrows
            $remainingBorrows = (float) DB::table('employee_borrows')
                ->where('employee_id', $employee->id)
                ->sum('remaining_amount');
            
            // Paid amount = total - remaining
            $paidBorrows = $totalBorrows - $remainingBorrows;
            
            if ($totalBorrows > 0) {
                $borrowRows[] = [
                    'name' => 'سلفة: ' . $employee->name,
                    'employee_id' => $employee->id,
                    'total' => $totalBorrows,
                    'paid' => $paidBorrows,
                    'remaining' => $remainingBorrows
                ];
            }
        }
        
        // Get payroll separately (all employees)
        // Build a map of employee_id => paid_borrows for quick lookup
        $employeePaidBorrows = [];
        foreach ($borrowRows as $borrow) {
            $employeePaidBorrows[$borrow['employee_id']] = $borrow['paid'];
        }
        
        foreach ($employees as $employee) {
            $fromYear  = (int) date('Y', strtotime($fd));
            $fromMonth = (int) date('m', strtotime($fd));
            $toYear    = (int) date('Y', strtotime($td));
            $toMonth   = (int) date('m', strtotime($td));
            
            $grossPayroll = (float) DB::table('payroll')
                ->where('employee_id', $employee->id)
                ->where('status', 'paid')
                ->whereRaw('(period_year * 100 + period_month) >= ?', [$fromYear * 100 + $fromMonth])
                ->whereRaw('(period_year * 100 + period_month) <= ?', [$toYear * 100 + $toMonth])
                ->sum(DB::raw('base_salary + overtime_pay - total_deductions'));
            
            // Net payroll = gross payroll - paid borrows for this employee
            $paidBorrowsForEmployee = $employeePaidBorrows[$employee->id] ?? 0;
            $netPayroll = $grossPayroll - $paidBorrowsForEmployee;
            
            if ($grossPayroll > 0) {
                $payrollRows[] = [
                    'name' => 'راتب: ' . $employee->name,
                    'payroll' => $netPayroll
                ];
            }
        }
        
        return [
            'borrows' => $borrowRows,
            'payrolls' => $payrollRows
        ];
    }
    
    private function getInventoryRows(string $fd, string $td): array
    {
        $items = InventoryItem::orderBy('name_ar')->get();
        $rows = [];
        
        foreach ($items as $item) {
            if ($item->current_stock > 0) {
                $stockStatus = $item->isBelowAlert() ? ' ⚠️' : ' ✓';
                $rows[] = [
                    'name' => $item->name_ar,
                    'detail' => number_format((float)$item->current_stock, 3) . ' ' . $item->unit . $stockStatus,
                    'unit' => $item->unit,
                    'qty' => (float)$item->current_stock,
                ];
            }
        }
        
        return $rows;
    }
    
    private function getNeighboringStationRows(string $fd, string $td): array
    {
        $stations = NeighboringStation::orderBy('name')->get();
        $rows = [];
        
        foreach ($stations as $station) {
            $outgoing = (float) DB::table('neighboring_station_transactions')
                ->where('neighboring_station_id', $station->id)
                ->where('direction', 'outgoing')
                ->whereBetween('transaction_date', [$fd, $td])
                ->sum('amount');

            $incoming = (float) DB::table('neighboring_station_transactions')
                ->where('neighboring_station_id', $station->id)
                ->where('direction', 'incoming')
                ->whereBetween('transaction_date', [$fd, $td])
                ->sum('amount');

            $paidIncoming = (float) DB::table('neighboring_station_transactions')
                ->where('neighboring_station_id', $station->id)
                ->where('direction', 'incoming')
                ->whereBetween('transaction_date', [$fd, $td])
                ->sum('paid_amount');

            $paidOutgoing = (float) DB::table('neighboring_station_transactions')
                ->where('neighboring_station_id', $station->id)
                ->where('direction', 'outgoing')
                ->whereBetween('transaction_date', [$fd, $td])
                ->sum('paid_amount');

            $netBalance = ($outgoing + $paidIncoming) - ($incoming + $paidOutgoing);
            
            $rows[] = [
                'name' => $station->name,
                'balance' => $netBalance
            ];
        }
        
        return $rows;
    }
    
    private function getRevenueRows(string $fd, string $td): array
    {
        $concreteSales = (float) DB::table('orders')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('delivery_date', [$fd, $td])
            ->sum('total_amount');

        $otherIncome = (float) DB::table('treasury_transactions')
            ->where('type', 'in')
            ->whereIn('category', ['income', 'refund', 'other'])
            ->whereBetween('transaction_date', [$fd, $td])
            ->sum('amount');

        return [
            ['name' => 'مبيعات الخرسانة', 'amount' => $concreteSales],
            ['name' => 'إيرادات أخرى', 'amount' => $otherIncome],
        ];
    }
    
    private function getTreasuryNetBalance(string $fd, string $td): float
    {
        // Get the CURRENT cumulative treasury balance (all time) - matches treasury page
        $currentBalance = (float) DB::table('treasury_transactions')
            ->where('type', 'in')
            ->sum('amount') 
            - (float) DB::table('treasury_transactions')
            ->where('type', 'out')
            ->sum('amount');

        return $currentBalance;
    }

    public function array(): array
    {
        $fd = $this->fromDate;
        $td = $this->toDate;

        $contributors = (new ContributorCollector())->getRows($fd, $td);
        $expensesBase = (new ExpenseCollector())->getRows($fd, $td);
        $employeeData = $this->getEmployeeBorrowAndPayrollRows($fd, $td);
        $employeeBorrows = $employeeData['borrows'];
        $employeePayrolls = $employeeData['payrolls'];
        $expenses = $expensesBase; // Only expenses in أخرى section now
        $suppliers = $this->getDetailedSupplierRows($fd, $td);
        $customers = $this->getDetailedCustomerRows($fd, $td);
        $rentals = (new RentalAgentCollector())->getRows($fd, $td);
        $banks = (new TreasuryBankCollector())->getRows($fd, $td);
        $inventory = $this->getInventoryRows($fd, $td);
        $neighboringStations = $this->getNeighboringStationRows($fd, $td);
        $revenueRows = $this->getRevenueRows($fd, $td);
        
        // Get treasury net balance using the helper method for consistency
        $treasuryNetBalance = $this->getTreasuryNetBalance($fd, $td);

        $out = [];
        $rowIdx = 1;

        // Title Header
        $out[] = ['شركة نيو سوليد اب / محطة الخرسانة الجاهزة', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->styleMap[$rowIdx++] = 'title';
        
        $out[] = ['ميزان المراجعة القطاعي الإجمالي — ' . $fd . ' إلى ' . $td, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->styleMap[$rowIdx++] = 'subtitle';
        
        $out[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rowIdx++;

        // Category Headers Row
        $out[] = [
            'المساهمين', '',
            'أخرى / سلف ومصروفات', '', '', '',
            'رواتب الموظفين', '',
            'موردين', '', '',
            'عملاء', '', '',
            'وكلاء نقل', '',
            'بنوك وخزينة', '',
            'محطات مجاورة', '',
            'إيرادات', '',
        ];
        $this->styleMap[$rowIdx++] = 'category_header';

        // Column Headings Row
        $out[] = [
            'اسم المساهم', 'صافي الرصيد',
            'الموظف / البيان', 'إجمالي السلفة', 'المدفوع', 'الباقي',
            'اسم الموظف', 'الراتب المدفوع',
            'اسم المورد', 'صافي الرصيد', 'ديون موردين',
            'اسم العميل', 'صافي الرصيد', 'ديون عملاء',
            'وكيل النقل', 'صافي الرصيد',
            'الخزينة / البنك', 'صافي الرصيد',
            'المحطة', 'صافي الرصيد',
            'نوع الإيراد', 'المبلغ',
        ];
        $this->styleMap[$rowIdx++] = 'column_header';

        $maxCount = max(
            count($contributors),
            count($expenses),
            count($employeeBorrows),
            count($employeePayrolls),
            count($suppliers),
            count($customers),
            count($rentals),
            count($banks),
            count($neighboringStations),
            count($revenueRows)
        );

        for ($i = 0; $i < $maxCount; $i++) {
            $cRow = $contributors[$i] ?? null;
            $eRow = $expenses[$i] ?? null;
            $bRow = $employeeBorrows[$i] ?? null;
            $pRow = $employeePayrolls[$i] ?? null;
            $sRow = $suppliers[$i] ?? null;
            $uRow = $customers[$i] ?? null;
            $rRow = $rentals[$i] ?? null;
            $tRow = $banks[$i] ?? null;
            $nRow = $neighboringStations[$i] ?? null;
            $revRow = $revenueRows[$i] ?? null;

            // Handle expenses - check if it's from ExpenseCollector
            if ($eRow) {
                if (isset($eRow['net_debit']) && isset($eRow['net_credit'])) {
                    $eName = $eRow['name'];
                    $eTotal = $eRow['net_debit'];
                    $ePaid = $eRow['net_credit'];
                    $eRemaining = $eRow['net_debit'] - $eRow['net_credit'];
                } else {
                    $eName = '';
                    $eTotal = 0;
                    $ePaid = 0;
                    $eRemaining = 0;
                }
            } else {
                $eName = '';
                $eTotal = 0;
                $ePaid = 0;
                $eRemaining = 0;
            }

            $out[] = [
                $cRow ? $cRow['name'] : '', 
                $cRow ? number_format($cRow['net_credit'] - $cRow['net_debit'], 2) : '',
                $bRow ? $bRow['name'] : $eName,
                $bRow ? number_format($bRow['total'], 2) : number_format($eTotal, 2),
                $bRow ? number_format($bRow['paid'], 2) : number_format($ePaid, 2),
                $bRow ? number_format($bRow['remaining'], 2) : number_format($eRemaining, 2),
                $pRow ? $pRow['name'] : '',
                $pRow ? number_format($pRow['payroll'], 2) : '',
                $sRow ? $sRow['name'] : '',
                $sRow ? number_format($sRow['payments'], 2) : '',
                $sRow ? number_format($sRow['credits'], 2) : '',
                $uRow ? $uRow['name'] : '',
                $uRow ? number_format($uRow['payments'], 2) : '',
                $uRow ? number_format($uRow['credits'], 2) : '',
                $rRow ? $rRow['name'] : '',
                $rRow ? number_format($rRow['net_credit'] - $rRow['net_debit'], 2) : '',
                $tRow ? $tRow['name'] : '',
                $tRow ? number_format($tRow['net_debit'] - $tRow['net_credit'], 2) : '',
                $nRow ? $nRow['name'] : '',
                $nRow ? number_format($nRow['balance'], 2) : '',
                $revRow ? $revRow['name'] : '',
                $revRow ? number_format($revRow['amount'], 2) : '',
            ];
            $this->styleMap[$rowIdx++] = 'data';
        }

        // Blank separator
        $out[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rowIdx++;

        // Grand Totals Row
        $contribTotal = array_sum(array_map(fn($r) => $r['net_credit'] - $r['net_debit'], $contributors));
        
        $borrowsTotal = array_sum(array_map(fn($r) => $r['total'], $employeeBorrows));
        $borrowsPaid = array_sum(array_map(fn($r) => $r['paid'], $employeeBorrows));
        $borrowsRemaining = array_sum(array_map(fn($r) => $r['remaining'], $employeeBorrows));
        
        $payrollsTotal = array_sum(array_map(fn($r) => $r['payroll'], $employeePayrolls));
        
        $expensesTotal = 0;
        foreach ($expenses as $e) {
            if (isset($e['net_debit']) && isset($e['net_credit'])) {
                $expensesTotal += $e['net_debit'] - $e['net_credit'];
            }
        }
        
        // Total for أخرى / سلف ومصروفات section - should include expenses in "الباقي"
        $otherSectionTotal = $borrowsTotal + $expensesTotal;
        $otherSectionPaid = $borrowsPaid;
        $otherSectionRemaining = $borrowsRemaining + $expensesTotal;
        
        $suppliersPayments = array_sum(array_map(fn($r) => $r['payments'], $suppliers));
        $suppliersCredits = array_sum(array_map(fn($r) => $r['credits'], $suppliers));
        $customersPayments = array_sum(array_map(fn($r) => $r['payments'], $customers));
        $customersCredits = array_sum(array_map(fn($r) => $r['credits'], $customers));
        $rentalsTotal = array_sum(array_map(fn($r) => $r['net_credit'] - $r['net_debit'], (new RentalAgentCollector())->getRows($fd, $td)));
        
        // Treasury: Get the current cumulative balance (already calculated correctly in TreasuryBankCollector)
        $treasuryRows = (new TreasuryBankCollector())->getRows($fd, $td);
        $banksTotal = array_sum(array_map(fn($r) => $r['net_debit'] - $r['net_credit'], $treasuryRows));
        
        $neighboringTotal = array_sum(array_map(fn($r) => $r['balance'], $neighboringStations));
        $revenueTotal = array_sum(array_map(fn($r) => $r['amount'], $revenueRows));

        $out[] = [
            'الإجمالي', number_format($contribTotal, 2),
            'الإجمالي', number_format($otherSectionTotal, 2), number_format($otherSectionPaid, 2), number_format($otherSectionRemaining, 2),
            'الإجمالي', number_format($payrollsTotal, 2),
            'الإجمالي', number_format($suppliersPayments, 2), number_format($suppliersCredits, 2),
            'الإجمالي', number_format($customersPayments, 2), number_format($customersCredits, 2),
            'الإجمالي', number_format($rentalsTotal, 2),
            'الإجمالي', number_format($banksTotal, 2),
            'الإجمالي', number_format($neighboringTotal, 2),
            'الإجمالي', number_format($revenueTotal, 2),
        ];
        $this->styleMap[$rowIdx++] = 'grand_total';

        // Add spacing
        $out[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $out[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rowIdx += 2;

        // Summary Section (like the image)
        $out[] = ['الملخص المالي (الصافي)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->styleMap[$rowIdx++] = 'summary_title';
        
        $out[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rowIdx++;

        // Calculate summary values
        $treasuryNet = $treasuryNetBalance; // Use consistent treasury calculation
        $customerPaymentsNet = $customersPayments;
        $customerDebtsNet = $customersCredits;
        $receiptsNet = 0;
        $banksAccountsNet = 0;
        $supplierPaymentsNet = $suppliersPayments;
        $supplierDebtsNet = $suppliersCredits;
        $rentalAgentsNet = $rentalsTotal;
        $contributorsNet = $contribTotal;
        $borrowsNet = $borrowsRemaining;
        $payrollsNet = $payrollsTotal;
        $otherNet = $expensesTotal;
        $neighboringStationsNet = $neighboringTotal;
        $revenueNet = $revenueTotal;
        
        $grandNet = $treasuryNet + $customerPaymentsNet - $customerDebtsNet + $receiptsNet + $banksAccountsNet 
                    - $supplierPaymentsNet - $supplierDebtsNet - $rentalAgentsNet + $contributorsNet 
                    - $borrowsNet - $payrollsNet - $otherNet + $neighboringStationsNet + $revenueNet;

        $summaryData = [
            ['النقدية', $treasuryNet],
            ['العملاء (دفعات)', $customerPaymentsNet],
            ['العملاء (ديون)', -$customerDebtsNet],
            ['أوراق قبض', $receiptsNet],
            ['البنوك', $banksAccountsNet],
            ['الموردين (دفعات)', -$supplierPaymentsNet],
            ['الموردين (ديون)', -$supplierDebtsNet],
            ['وكلاء النقل', -$rentalAgentsNet],
            ['المحطات المجاورة', $neighboringStationsNet],
            ['المساهمين', $contributorsNet],
            ['سلف الموظفين (الباقي)', -$borrowsNet],
            ['رواتب الموظفين', -$payrollsNet],
            ['أخرى', -$otherNet],
            ['الإيرادات', $revenueNet],
        ];

        foreach ($summaryData as $item) {
            $value = $item[1];
            if ($value == 0) continue;
            $formatted = 'ج.م ' . number_format(abs($value), 2) . ($value < 0 ? '-' : '');
            $out[] = [$formatted, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $item[0], ''];
            $this->styleMap[$rowIdx++] = $value < 0 ? 'summary_negative' : 'summary_positive';
        }

        // Grand total line
        $out[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rowIdx++;
        
        $netFormatted = 'ج.م ' . number_format(abs($grandNet), 2) . ($grandNet < 0 ? '-' : '');
        $out[] = [$netFormatted, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'الصافي', ''];
        $this->styleMap[$rowIdx++] = 'summary_grand';

        // Inventory Section
        $out[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $out[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rowIdx += 2;
        
        $out[] = ['المخزون — حالة المواد (بيان كمي فقط)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->styleMap[$rowIdx++] = 'inventory_title';
        
        $out[] = ['الحالة', '', 'اسم المادة', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->styleMap[$rowIdx++] = 'inventory_header';

        foreach ($inventory as $inv) {
            $out[] = [$inv['detail'], '', $inv['name'], '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
            $this->styleMap[$rowIdx++] = 'inventory_data';
        }

        $totalItems = count($inventory);
        $out[] = ['إجمالي عدد المواد: ' . $totalItems . ' مادة', '', 'بيان كمي فقط (غير مدرج في الإجمالي المالي)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->styleMap[$rowIdx++] = 'inventory_total';

        return $out;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        foreach ($this->styleMap as $row => $type) {
            switch ($type) {
                case 'title':
                    $sheet->mergeCells("A{$row}:V{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F3864']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    break;

                case 'subtitle':
                    $sheet->mergeCells("A{$row}:V{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font'      => ['italic' => true, 'size' => 11, 'color' => ['rgb' => '444444']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    break;

                case 'category_header':
                    // Merge sector top headers
                    $sheet->mergeCells("A{$row}:B{$row}");      // المساهمين
                    $sheet->mergeCells("C{$row}:F{$row}");      // أخرى / سلف ومصروفات
                    $sheet->mergeCells("G{$row}:H{$row}");      // رواتب الموظفين
                    $sheet->mergeCells("I{$row}:K{$row}");      // موردين
                    $sheet->mergeCells("L{$row}:N{$row}");      // عملاء
                    $sheet->mergeCells("O{$row}:P{$row}");      // وكلاء نقل
                    $sheet->mergeCells("Q{$row}:R{$row}");      // بنوك وخزينة
                    $sheet->mergeCells("S{$row}:T{$row}");      // محطات مجاورة
                    $sheet->mergeCells("U{$row}:V{$row}");      // إيرادات
                    
                    $sheet->getStyle("A{$row}:V{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1F3864']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                        'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '1F3864']]],
                    ]);
                    break;

                case 'column_header':
                    $sheet->getStyle("A{$row}:V{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    break;

                case 'data':
                    $sheet->getStyle("A{$row}:V{$row}")->applyFromArray([
                        'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:V{$row}")->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F7FAFC');
                    }
                    $sheet->getRowDimension($row)->setRowHeight(40);
                    break;

                case 'grand_total':
                    $sheet->getStyle("A{$row}:V{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1F3864']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                        'borders' => [
                            'top'    => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE, 'color' => ['rgb' => '1F3864']],
                            'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE, 'color' => ['rgb' => '1F3864']],
                        ],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    break;

                case 'summary_title':
                    $sheet->mergeCells("U{$row}:V{$row}");
                    $sheet->getStyle("U{$row}:V{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F3864']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
                        'borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '1F3864']]],
                    ]);
                    break;

                case 'summary_positive':
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '000000']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
                        'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    ]);
                    $sheet->getStyle("U{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
                        'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(22);
                    break;

                case 'summary_negative':
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FF0000']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
                        'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    ]);
                    $sheet->getStyle("U{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
                        'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(22);
                    break;

                case 'summary_grand':
                    $sheet->getStyle("A{$row}:V{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FF0000']],
                        'borders' => [
                            'top'    => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE, 'color' => ['rgb' => '000000']],
                            'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE, 'color' => ['rgb' => '000000']],
                        ],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("U{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getRowDimension($row)->setRowHeight(26);
                    break;

                case 'inventory_title':
                    $sheet->mergeCells("A{$row}:V{$row}");
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F3864']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF4E6']],
                    ]);
                    break;

                case 'inventory_header':
                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ED6C02']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    break;

                case 'inventory_data':
                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
                    ]);
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:C{$row}")->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FFFBF5');
                    }
                    break;

                case 'inventory_total':
                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1F3864']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE0B2']],
                        'borders' => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE, 'color' => ['rgb' => 'ED6C02']]],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    break;
            }
        }

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

