<?php

namespace App\Accounting\Services;

use App\Accounting\DTO\JournalLineDTO;
use App\Accounting\Models\Account;
use App\Models\ContributorPayment;
use App\Models\CustomerPayment;
use App\Models\EmployeeBorrow;
use App\Models\EmployeeBorrowDeduction;
use App\Models\EquipmentFuelLog;
use App\Models\EquipmentMaintenance;
use App\Models\Expense;
use App\Models\LandRentPayment;
use App\Models\NeighboringStationTransaction;
use App\Models\Order;
use App\Models\Payroll;
use App\Models\RentalMaintenance;
use App\Models\RentalShift;
use App\Models\SupplierPayment;
use App\Models\SupplierPurchase;
use Illuminate\Support\Facades\Cache;

/**
 * High-level accounting posting façade.
 *
 * Each method:
 *   1. Resolves the relevant account IDs from the COA.
 *   2. Builds balanced JournalLineDTO[] arrays.
 *   3. Delegates to JournalEntryService::create().
 *   4. Is idempotent — skips if a journal entry already exists for the reference.
 *
 * Account codes are resolved once per request via a simple in-memory cache.
 *
 * NEVER call this from a controller directly.
 * Call it AFTER the business transaction has been committed.
 */
class AccountingPostingService
{
    public function __construct(
        private readonly JournalEntryService $journal,
    ) {}

    // ═══════════════════════════════════════════════════════════════════════════
    // 1. CUSTOMER INVOICE  (Order delivered)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Customers (1130) | Credit Revenue (4100)
     * For cash orders:   DR Cash (1110) | CR Revenue (4100)
     * For mixed orders:  DR Cash (1110) + DR Customers (1130) | CR Revenue (4100)
     */
    public function postCustomerInvoice(Order $order): void
    {
        $refType = 'order';
        if ($this->journal->existsForReference($refType, $order->id)) {
            return;
        }

        $amount       = (float) $order->total_amount;
        $cashAmount   = (float) ($order->cash_amount  ?? 0);
        $creditAmount = (float) ($order->credit_amount ?? 0);
        $date         = $order->delivery_date ?? $order->created_at->toDateString();

        $cashAccountId     = $this->account('1110');
        $customerAccountId = $this->account('1130');
        $revenueAccountId  = $this->account('4100');

        $lines = [];

        // Debit side
        if ($cashAmount > 0) {
            $lines[] = JournalLineDTO::debit($cashAccountId, $cashAmount, 'نقداً - ' . $order->customer?->name);
        }
        if ($creditAmount > 0) {
            $lines[] = JournalLineDTO::debit($customerAccountId, $creditAmount, 'آجل - ' . $order->customer?->name);
        }
        // Edge: pure cash order with no explicit credit_amount
        if ($cashAmount === 0.0 && $creditAmount === 0.0) {
            $lines[] = JournalLineDTO::debit($customerAccountId, $amount, $order->customer?->name ?? '');
        }

        // Credit side — always Revenue
        $lines[] = JournalLineDTO::credit($revenueAccountId, $amount, 'مبيعات خرسانة - طلب #' . $order->id);

        $this->journal->create(
            description:   'فاتورة عميل - ' . ($order->customer?->name ?? 'عميل') . ' - طلب #' . $order->id,
            date:          $date,
            lines:         $lines,
            referenceType: $refType,
            referenceId:   $order->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 2. CUSTOMER PAYMENT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Cash (1110) | CR Customers (1130)
     */
    public function postCustomerPayment(CustomerPayment $payment): void
    {
        $refType = 'customer_payment';
        if ($this->journal->existsForReference($refType, $payment->id)) {
            return;
        }

        $amount = (float) $payment->amount;
        if ($amount <= 0) {
            return;
        }

        $this->journal->create(
            description:   'دفعة من عميل - ' . ($payment->customer?->name ?? ''),
            date:          $payment->payment_date->toDateString(),
            lines:         [
                JournalLineDTO::debit($this->account('1110'),  $amount, 'استلام نقدي'),
                JournalLineDTO::credit($this->account('1130'), $amount, $payment->customer?->name ?? ''),
            ],
            referenceType: $refType,
            referenceId:   $payment->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 3. SUPPLIER PURCHASE
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Inventory (1140) | CR Supplier (2110) and/or CR Cash (1110)
     */
    public function postSupplierPurchase(SupplierPurchase $purchase): void
    {
        $refType = 'supplier_purchase';
        if ($this->journal->existsForReference($refType, $purchase->id)) {
            return;
        }

        $total        = (float) $purchase->total_amount;
        $cashAmount   = (float) ($purchase->cash_amount   ?? 0);
        $creditAmount = (float) ($purchase->credit_amount ?? 0);

        $inventoryAccountId = $this->account('1140');
        $supplierAccountId  = $this->account('2110');
        $cashAccountId      = $this->account('1110');

        $lines = [
            JournalLineDTO::debit($inventoryAccountId, $total, 'شراء مخزون - ' . $purchase->supplier?->name),
        ];

        if ($cashAmount > 0) {
            $lines[] = JournalLineDTO::credit($cashAccountId, $cashAmount, 'مدفوع نقداً');
        }
        if ($creditAmount > 0) {
            $lines[] = JournalLineDTO::credit($supplierAccountId, $creditAmount, 'مستحق للمورد');
        }
        // Pure cash purchase
        if ($cashAmount === 0.0 && $creditAmount === 0.0) {
            $lines[] = JournalLineDTO::credit($supplierAccountId, $total, $purchase->supplier?->name ?? '');
        }

        $this->journal->create(
            description:   'شراء من مورد - ' . ($purchase->supplier?->name ?? '') . ' - فاتورة #' . ($purchase->invoice_number ?? $purchase->id),
            date:          $purchase->purchase_date->toDateString(),
            lines:         $lines,
            referenceType: $refType,
            referenceId:   $purchase->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 4. SUPPLIER PAYMENT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Suppliers (2110) | CR Cash (1110)
     */
    public function postSupplierPayment(SupplierPayment $payment): void
    {
        $refType = 'supplier_payment';
        if ($this->journal->existsForReference($refType, $payment->id)) {
            return;
        }

        $amount = (float) $payment->amount;
        if ($amount <= 0) {
            return;
        }

        $this->journal->create(
            description:   'دفعة لمورد - ' . ($payment->supplier?->name ?? ''),
            date:          $payment->payment_date->toDateString(),
            lines:         [
                JournalLineDTO::debit($this->account('2110'),  $amount, $payment->supplier?->name ?? ''),
                JournalLineDTO::credit($this->account('1110'), $amount, 'صرف نقدي'),
            ],
            referenceType: $refType,
            referenceId:   $payment->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 5. EXPENSE
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Expense Account | CR Cash (1110)
     *
     * The expense category is mapped to the correct expense account code.
     */
    public function postExpense(Expense $expense): void
    {
        $refType = 'expense';
        if ($this->journal->existsForReference($refType, $expense->id)) {
            return;
        }

        $amount          = (float) $expense->amount;
        $expenseAcctCode = $this->mapExpenseCategory($expense->category);

        $this->journal->create(
            description:   'مصروف: ' . ($expense->description ?? $expense->category),
            date:          $expense->expense_date->toDateString(),
            lines:         [
                JournalLineDTO::debit($this->account($expenseAcctCode), $amount, $expense->category),
                JournalLineDTO::credit($this->account('1110'),          $amount, 'صرف نقدي'),
            ],
            referenceType: $refType,
            referenceId:   $expense->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 6. PAYROLL
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Salaries (5300) + DR Overtime (5310) | CR Cash (1110) + CR Employee Loans (1150)
     *
     * Balance formula:
     *   Gross = net_salary + borrow_deductions + other_deductions
     *   DR Salaries = Gross - overtime
     *   DR Overtime = overtime
     *   CR Cash     = net_salary
     *   CR Loans    = borrow_deductions
     *   CR Salaries (contra deductions) = other_deductions
     */
    public function postPayroll(Payroll $payroll): void
    {
        $refType = 'payroll';
        if ($this->journal->existsForReference($refType, $payroll->id)) {
            return;
        }

        $netSalary        = round((float) $payroll->net_salary, 2);
        $overtimePay      = round((float) ($payroll->overtime_pay ?? 0), 2);
        $borrowDeductions = round((float) ($payroll->borrow_deductions ?? 0), 2);
        $otherDeductions  = round((float) ($payroll->total_deductions ?? 0) - $borrowDeductions, 2);

        // Gross = everything that comes out of the expense bucket
        $gross     = round($netSalary + $borrowDeductions + $otherDeductions, 2);
        $basePart  = round($gross - $overtimePay, 2);

        $lines = [];

        // Debit side
        if ($basePart > 0) {
            $lines[] = JournalLineDTO::debit($this->account('5300'), $basePart,    'راتب أساسي - ' . $payroll->employee?->name);
        }
        if ($overtimePay > 0) {
            $lines[] = JournalLineDTO::debit($this->account('5310'), $overtimePay, 'عمل إضافي - ' . $payroll->employee?->name);
        }

        // Credit side — all components must sum to gross
        $lines[] = JournalLineDTO::credit($this->account('1110'), $netSalary,    'صرف راتب - ' . $payroll->employee?->name);

        if ($borrowDeductions > 0) {
            $lines[] = JournalLineDTO::credit($this->account('1150'), $borrowDeductions, 'خصم سلفة');
        }

        // Other non-borrow deductions (fines, absences) — reduce salary expense contra
        if ($otherDeductions > 0) {
            $lines[] = JournalLineDTO::credit($this->account('5300'), $otherDeductions, 'خصومات أخرى');
        }

        $this->journal->create(
            description:   'رواتب - ' . ($payroll->employee?->name ?? '') . ' - ' . $payroll->period_label,
            date:          $payroll->payment_date?->toDateString() ?? now()->toDateString(),
            lines:         $lines,
            referenceType: $refType,
            referenceId:   $payroll->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 7. EMPLOYEE BORROW (Loan)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Employee Loans (1150) | CR Cash (1110)
     */
    public function postEmployeeBorrow(EmployeeBorrow $borrow): void
    {
        $refType = 'employee_borrow';
        if ($this->journal->existsForReference($refType, $borrow->id)) {
            return;
        }

        $amount = (float) $borrow->amount;

        $this->journal->create(
            description:   'سلفة موظف - ' . ($borrow->employee?->name ?? ''),
            date:          $borrow->borrow_date->toDateString(),
            lines:         [
                JournalLineDTO::debit($this->account('1150'),  $amount, $borrow->employee?->name ?? ''),
                JournalLineDTO::credit($this->account('1110'), $amount, 'صرف سلفة'),
            ],
            referenceType: $refType,
            referenceId:   $borrow->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 8. CONTRIBUTOR DEPOSIT (Capital injection)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Cash (1110) | CR Capital (3110)
     *
     * For withdrawals/distributions: DR Drawings (3120) | CR Cash (1110)
     * We distinguish by checking the linked TreasuryTransaction type.
     */
    public function postContributorPayment(ContributorPayment $payment): void
    {
        $refType = 'contributor_payment';
        if ($this->journal->existsForReference($refType, $payment->id)) {
            return;
        }

        $amount = (float) $payment->amount;

        // Determine direction from the linked treasury transaction
        $treasuryType = $payment->treasuryTransaction?->type ?? 'in'; // default: deposit

        if ($treasuryType === 'in') {
            // Deposit: cash in from contributor
            $lines = [
                JournalLineDTO::debit($this->account('1110'),  $amount, 'إيداع من مساهم'),
                JournalLineDTO::credit($this->account('3110'), $amount, $payment->contributor?->name ?? ''),
            ];
            $desc = 'إيداع رأس مال - ' . ($payment->contributor?->name ?? '');
        } else {
            // Withdrawal: cash out to contributor
            $lines = [
                JournalLineDTO::debit($this->account('3120'),  $amount, $payment->contributor?->name ?? ''),
                JournalLineDTO::credit($this->account('1110'), $amount, 'سحب مساهم'),
            ];
            $desc = 'سحب رأس مال - ' . ($payment->contributor?->name ?? '');
        }

        $this->journal->create(
            description:   $desc,
            date:          $payment->payment_date->toDateString(),
            lines:         $lines,
            referenceType: $refType,
            referenceId:   $payment->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 9. EQUIPMENT FUEL LOG
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Fuel Expense (5100) | CR Cash (1110)
     */
    public function postFuelExpense(EquipmentFuelLog $log): void
    {
        $refType = 'fuel_log';
        if ($this->journal->existsForReference($refType, $log->id)) {
            return;
        }

        $amount = (float) ($log->total_cost ?? 0);
        if ($amount <= 0) {
            return;
        }

        $this->journal->create(
            description:   'وقود - ' . ($log->equipment?->name ?? ''),
            date:          $log->log_date->toDateString(),
            lines:         [
                JournalLineDTO::debit($this->account('5100'),  $amount, $log->equipment?->name ?? ''),
                JournalLineDTO::credit($this->account('1110'), $amount, 'صرف نقدي'),
            ],
            referenceType: $refType,
            referenceId:   $log->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 10. EQUIPMENT MAINTENANCE
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Maintenance Expense (5200) | CR Cash (1110)
     */
    public function postEquipmentMaintenance(EquipmentMaintenance $maintenance): void
    {
        $refType = 'equipment_maintenance';
        if ($this->journal->existsForReference($refType, $maintenance->id)) {
            return;
        }

        $amount = (float) $maintenance->cost;
        if ($amount <= 0) {
            return;
        }

        $this->journal->create(
            description:   'صيانة معدة - ' . ($maintenance->equipment?->name ?? ''),
            date:          $maintenance->maintenance_date->toDateString(),
            lines:         [
                JournalLineDTO::debit($this->account('5200'),  $amount, $maintenance->description ?? ''),
                JournalLineDTO::credit($this->account('1110'), $amount, 'صرف نقدي'),
            ],
            referenceType: $refType,
            referenceId:   $maintenance->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 11. RENTAL SHIFT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Rental Expense (5400) | CR Cash (1110)
     */
    public function postRentalShift(RentalShift $shift): void
    {
        $refType = 'rental_shift';
        if ($this->journal->existsForReference($refType, $shift->id)) {
            return;
        }

        $amount = (float) ($shift->total_cost ?? 0);
        if ($amount <= 0) {
            return;
        }

        $this->journal->create(
            description:   'وردية إيجار - ' . ($shift->contract?->equipment_name ?? ''),
            date:          $shift->shift_date?->toDateString() ?? now()->toDateString(),
            lines:         [
                JournalLineDTO::debit($this->account('5400'),  $amount, $shift->contract?->equipment_name ?? ''),
                JournalLineDTO::credit($this->account('1110'), $amount, 'صرف نقدي'),
            ],
            referenceType: $refType,
            referenceId:   $shift->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 12. RENTAL MAINTENANCE
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Rental Maintenance (5900) | CR Cash (1110)
     */
    public function postRentalMaintenance(RentalMaintenance $maintenance): void
    {
        $refType = 'rental_maintenance';
        if ($this->journal->existsForReference($refType, $maintenance->id)) {
            return;
        }

        $amount = (float) $maintenance->cost;
        if ($amount <= 0) {
            return;
        }

        $this->journal->create(
            description:   'صيانة معدة مستأجرة - ' . ($maintenance->contract?->equipment_name ?? ''),
            date:          $maintenance->maintenance_date?->toDateString() ?? now()->toDateString(),
            lines:         [
                JournalLineDTO::debit($this->account('5900'),  $amount, $maintenance->description ?? ''),
                JournalLineDTO::credit($this->account('1110'), $amount, 'صرف نقدي'),
            ],
            referenceType: $refType,
            referenceId:   $maintenance->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 13. LAND RENT PAYMENT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * DR Land Rent Expense (5500) | CR Cash (1110)
     */
    public function postLandRentPayment(LandRentPayment $payment): void
    {
        $refType = 'land_rent_payment';
        if ($this->journal->existsForReference($refType, $payment->id)) {
            return;
        }

        $amount = (float) $payment->amount;

        $this->journal->create(
            description:   'إيجار أرض',
            date:          $payment->payment_date->toDateString(),
            lines:         [
                JournalLineDTO::debit($this->account('5500'),  $amount, 'إيجار أرض'),
                JournalLineDTO::credit($this->account('1110'), $amount, 'صرف نقدي'),
            ],
            referenceType: $refType,
            referenceId:   $payment->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 14. NEIGHBORING STATION TRANSACTION
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Incoming (we provide service/goods to neighbor):
     *   DR NeighborReceivable (1170) | CR OtherRevenue/NeighboringRevenue (4300)
     *   When paid: DR Cash (1110) | CR NeighborReceivable (1170)
     *
     * Outgoing (neighbor provides service/goods to us):
     *   DR OperatingExpense (5600) | CR NeighborPayable (2140)
     *   When we pay: DR NeighborPayable (2140) | CR Cash (1110)
     */
    public function postNeighboringTransaction(NeighboringStationTransaction $txn): void
    {
        $refType = 'neighboring_station_transaction';
        if ($this->journal->existsForReference($refType, $txn->id)) {
            return;
        }

        $amount    = (float) $txn->amount;
        $stationName = $txn->station?->name ?? '';
        $date      = $txn->transaction_date->toDateString();

        if ($txn->direction === 'incoming') {
            // We earn from the neighbor
            $lines = [
                JournalLineDTO::debit($this->account('1170'),  $amount, 'مستحق من ' . $stationName),
                JournalLineDTO::credit($this->account('4300'), $amount, 'إيراد محطة مجاورة - ' . $stationName),
            ];
            $desc = 'إيراد من محطة - ' . $stationName;
        } else {
            // We owe the neighbor
            $lines = [
                JournalLineDTO::debit($this->account('5600'),  $amount, 'خدمة من ' . $stationName),
                JournalLineDTO::credit($this->account('2140'), $amount, 'مستحق لمحطة - ' . $stationName),
            ];
            $desc = 'مصروف لمحطة - ' . $stationName;
        }

        $this->journal->create(
            description:   $desc,
            date:          $date,
            lines:         $lines,
            referenceType: $refType,
            referenceId:   $txn->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 15. STANDALONE TREASURY TRANSACTION
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Post standalone treasury transactions that don't have domain records.
     * These are manual cash-in/cash-out entries made directly in the treasury.
     *
     * For IN transactions:  DR Cash (1110) | CR Other Income (4300)
     * For OUT transactions: DR Operating Expense (5600) | CR Cash (1110)
     */
    public function postTreasuryTransaction(\App\Models\TreasuryTransaction $transaction): void
    {
        $refType = 'treasury_transaction';
        if ($this->journal->existsForReference($refType, $transaction->id)) {
            return;
        }

        // Skip if this transaction is already linked to a domain record
        // (those should be posted via their domain-specific methods)
        if ($transaction->reference_type && $transaction->reference_id) {
            // Check if reference_type is a domain record type we already handle
            $domainTypes = [
                'customer_payment', 'supplier_payment', 'supplier_purchase', 
                'contributor_payment', 'employee_borrow', 'expense', 
                'order', 'payroll', 'fuel_log', 'equipment_maintenance',
                'rental_shift', 'rental_maintenance', 'land_rent_payment',
                'neighboring_station_transaction'
            ];
            
            if (in_array($transaction->reference_type, $domainTypes)) {
                return; // Already handled by domain-specific posting
            }
        }

        $amount = (float) $transaction->amount;
        if ($amount <= 0) {
            return;
        }

        $date = $transaction->transaction_date?->toDateString() ?? now()->toDateString();
        $description = $transaction->description ?: ($transaction->category_label ?? 'معاملة خزينة');

        if ($transaction->type === 'in') {
            // Cash IN: DR Cash | CR Other Income
            $lines = [
                JournalLineDTO::debit($this->account('1110'),  $amount, $description),
                JournalLineDTO::credit($this->account('4300'), $amount, $transaction->category_label ?? 'إيرادات أخرى'),
            ];
            $desc = 'إيراد نقدي - ' . $description;
        } else {
            // Cash OUT: DR Operating Expense | CR Cash
            $expenseAcctCode = $this->mapTreasuryCategory($transaction->category);
            $lines = [
                JournalLineDTO::debit($this->account($expenseAcctCode), $amount, $description),
                JournalLineDTO::credit($this->account('1110'),          $amount, 'صرف نقدي'),
            ];
            $desc = 'مصروف نقدي - ' . $description;
        }

        $this->journal->create(
            description:   $desc,
            date:          $date,
            lines:         $lines,
            referenceType: $refType,
            referenceId:   $transaction->id,
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // VOID / REVERSAL
    // ═══════════════════════════════════════════════════════════════════════════

    public function voidJournalEntry(string $referenceType, int $referenceId): void
    {
        $this->journal->voidForReference($referenceType, $referenceId);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Resolve account ID from account_number, with in-memory caching.
     */
    private function account(string $accountNumber): int
    {
        $cacheKey = "acct_id_{$accountNumber}";

        return Cache::remember($cacheKey, 3600, function () use ($accountNumber) {
            $id = Account::where('account_number', $accountNumber)->value('id');

            if (!$id) {
                throw new \RuntimeException("Account {$accountNumber} not found in Chart of Accounts. Run the ChartOfAccountsSeeder first.");
            }

            return $id;
        });
    }

    /**
     * Map business expense categories to accounting expense account codes.
     */
    private function mapExpenseCategory(string $category): string
    {
        return match ($category) {
            'rental'                          => '5400',
            'rental_maintenance'              => '5900',
            'vehicle_equipment'               => '5800',
            'plant_maintenance', 'maintenance'=> '5700',
            'salary', 'salaries', 'overtime'  => '5300',
            'employee_deductions'             => '5300',
            'land_rent'                       => '5500',
            default                           => '5600', // General operating expenses
        };
    }

    /**
     * Map treasury transaction categories to accounting expense account codes.
     */
    private function mapTreasuryCategory(string $category): string
    {
        return match ($category) {
            'rental', 'rental_maintenance'    => '5400',
            'vehicle_equipment'               => '5800',
            'plant_maintenance', 'maintenance'=> '5700',
            'salary', 'salaries', 'overtime'  => '5300',
            'employee_deductions'             => '5300',
            'land_rent'                       => '5500',
            'material_cost', 'inventory_purchase' => '5600',
            default                           => '5600', // General operating expenses
        };
    }
}
