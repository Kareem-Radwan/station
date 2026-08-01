<?php

namespace App\Console\Commands;

use App\Accounting\Services\AccountingPostingService;
use App\Models\ContributorPayment;
use App\Models\CustomerPayment;
use App\Models\EmployeeBorrow;
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
use App\Models\TreasuryTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Idempotent historical accounting rebuild command.
 *
 * Scans every existing business record in chronological order and creates
 * journal entries for each one. Safe to run multiple times — skips records
 * that already have a journal entry (unless --force is passed).
 *
 * Usage:
 *   php artisan accounting:rebuild
 *   php artisan accounting:rebuild --from=2025-01-01
 *   php artisan accounting:rebuild --dry-run
 *   php artisan accounting:rebuild --force          (re-posts even if entry exists)
 */
class RebuildAccountingCommand extends Command
{
    protected $signature = 'accounting:rebuild
                            {--from=        : Rebuild only records on or after this date (Y-m-d)}
                            {--dry-run      : Show what would be posted without writing anything}
                            {--force        : Re-void and repost even if journal entries already exist}
                            {--chunk=500    : Records per chunk (default 500)}';

    protected $description = 'Rebuild historical journal entries from all existing business records (idempotent)';

    private int $posted  = 0;
    private int $skipped = 0;
    private int $errors  = 0;

    public function __construct(
        private readonly AccountingPostingService $posting,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $isForce  = $this->option('force');
        $fromDate = $this->option('from');
        $chunk    = (int) ($this->option('chunk') ?: 500);

        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════╗');
        $this->line('║   محرك المحاسبة بالقيد المزدوج — إعادة البناء       ║');
        $this->line('╚══════════════════════════════════════════════════════╝');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠  وضع المعاينة (dry-run) — لن يتم حفظ أي بيانات');
            $this->newLine();
        }

        if ($isForce) {
            $this->warn('⚠  وضع الإجبار (force) — سيتم إعادة ترحيل كل القيود');
            $this->newLine();
        }

        $steps = [
            'مدفوعات المساهمين'      => fn() => $this->rebuildContributorPayments($fromDate, $chunk, $isDryRun, $isForce),
            'طلبات مسلّمة (فواتير)' => fn() => $this->rebuildOrders($fromDate, $chunk, $isDryRun, $isForce),
            'مدفوعات العملاء'        => fn() => $this->rebuildCustomerPayments($fromDate, $chunk, $isDryRun, $isForce),
            'مشتريات الموردين'       => fn() => $this->rebuildSupplierPurchases($fromDate, $chunk, $isDryRun, $isForce),
            'مدفوعات الموردين'       => fn() => $this->rebuildSupplierPayments($fromDate, $chunk, $isDryRun, $isForce),
            'المصروفات'              => fn() => $this->rebuildExpenses($fromDate, $chunk, $isDryRun, $isForce),
            'الرواتب'                => fn() => $this->rebuildPayroll($fromDate, $chunk, $isDryRun, $isForce),
            'سلف الموظفين'           => fn() => $this->rebuildEmployeeBorrows($fromDate, $chunk, $isDryRun, $isForce),
            'سجلات الوقود'           => fn() => $this->rebuildFuelLogs($fromDate, $chunk, $isDryRun, $isForce),
            'صيانة المعدات'          => fn() => $this->rebuildEquipmentMaintenance($fromDate, $chunk, $isDryRun, $isForce),
            'ورديات الإيجار'         => fn() => $this->rebuildRentalShifts($fromDate, $chunk, $isDryRun, $isForce),
            'صيانة المعدات المستأجرة'=> fn() => $this->rebuildRentalMaintenance($fromDate, $chunk, $isDryRun, $isForce),
            'دفعات إيجار الأرض'     => fn() => $this->rebuildLandRentPayments($fromDate, $chunk, $isDryRun, $isForce),
            'محطات مجاورة'           => fn() => $this->rebuildNeighboringTransactions($fromDate, $chunk, $isDryRun, $isForce),
            'معاملات خزينة مستقلة'   => fn() => $this->rebuildStandaloneTreasuryTransactions($fromDate, $chunk, $isDryRun, $isForce),
        ];

        foreach ($steps as $label => $fn) {
            $this->info("▶  {$label} ...");
            $fn();
        }

        $this->newLine();
        $this->line('──────────────────────────────────────────────────────');
        $this->info("✅  مرحّل:   {$this->posted}");
        $this->comment("⏭   متجاوز:  {$this->skipped}");

        if ($this->errors > 0) {
            $this->error("❌  أخطاء:   {$this->errors}");
        }

        $this->line('──────────────────────────────────────────────────────');
        $this->newLine();

        return $this->errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    // ─── Step Handlers ───────────────────────────────────────────────────────────

    private function rebuildContributorPayments(?string $from, int $chunk, bool $dry, bool $force): void
    {
        ContributorPayment::with(['contributor', 'treasuryTransaction'])
            ->when($from, fn($q) => $q->where('payment_date', '>=', $from))
            ->orderBy('payment_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('contributor_payment', $record->id, $dry, $force,
                        fn() => $this->posting->postContributorPayment($record));
                }
            });
    }

    private function rebuildOrders(?string $from, int $chunk, bool $dry, bool $force): void
    {
        Order::with(['customer'])
            ->where('status', 'delivered')
            ->when($from, fn($q) => $q->where('delivery_date', '>=', $from))
            ->orderBy('delivery_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('order', $record->id, $dry, $force,
                        fn() => $this->posting->postCustomerInvoice($record));
                }
            });
    }

    private function rebuildCustomerPayments(?string $from, int $chunk, bool $dry, bool $force): void
    {
        CustomerPayment::with(['customer'])
            ->when($from, fn($q) => $q->where('payment_date', '>=', $from))
            ->orderBy('payment_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('customer_payment', $record->id, $dry, $force,
                        fn() => $this->posting->postCustomerPayment($record));
                }
            });
    }

    private function rebuildSupplierPurchases(?string $from, int $chunk, bool $dry, bool $force): void
    {
        SupplierPurchase::with(['supplier'])
            ->when($from, fn($q) => $q->where('purchase_date', '>=', $from))
            ->orderBy('purchase_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('supplier_purchase', $record->id, $dry, $force,
                        fn() => $this->posting->postSupplierPurchase($record));
                }
            });
    }

    private function rebuildSupplierPayments(?string $from, int $chunk, bool $dry, bool $force): void
    {
        SupplierPayment::with(['supplier'])
            ->when($from, fn($q) => $q->where('payment_date', '>=', $from))
            ->orderBy('payment_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('supplier_payment', $record->id, $dry, $force,
                        fn() => $this->posting->postSupplierPayment($record));
                }
            });
    }

    private function rebuildExpenses(?string $from, int $chunk, bool $dry, bool $force): void
    {
        Expense::when($from, fn($q) => $q->where('expense_date', '>=', $from))
            ->orderBy('expense_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('expense', $record->id, $dry, $force,
                        fn() => $this->posting->postExpense($record));
                }
            });
    }

    private function rebuildPayroll(?string $from, int $chunk, bool $dry, bool $force): void
    {
        Payroll::with(['employee'])
            ->where('status', 'paid')
            ->when($from, fn($q) => $q->where('payment_date', '>=', $from))
            ->orderBy('payment_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('payroll', $record->id, $dry, $force,
                        fn() => $this->posting->postPayroll($record));
                }
            });
    }

    private function rebuildEmployeeBorrows(?string $from, int $chunk, bool $dry, bool $force): void
    {
        EmployeeBorrow::with(['employee'])
            ->when($from, fn($q) => $q->where('borrow_date', '>=', $from))
            ->orderBy('borrow_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('employee_borrow', $record->id, $dry, $force,
                        fn() => $this->posting->postEmployeeBorrow($record));
                }
            });
    }

    private function rebuildFuelLogs(?string $from, int $chunk, bool $dry, bool $force): void
    {
        EquipmentFuelLog::with(['equipment'])
            ->when($from, fn($q) => $q->where('log_date', '>=', $from))
            ->orderBy('log_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('fuel_log', $record->id, $dry, $force,
                        fn() => $this->posting->postFuelExpense($record));
                }
            });
    }

    private function rebuildEquipmentMaintenance(?string $from, int $chunk, bool $dry, bool $force): void
    {
        EquipmentMaintenance::with(['equipment'])
            ->when($from, fn($q) => $q->where('maintenance_date', '>=', $from))
            ->orderBy('maintenance_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('equipment_maintenance', $record->id, $dry, $force,
                        fn() => $this->posting->postEquipmentMaintenance($record));
                }
            });
    }

    private function rebuildRentalShifts(?string $from, int $chunk, bool $dry, bool $force): void
    {
        RentalShift::with(['contract'])
            ->when($from, fn($q) => $q->where('shift_date', '>=', $from))
            ->orderBy('shift_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('rental_shift', $record->id, $dry, $force,
                        fn() => $this->posting->postRentalShift($record));
                }
            });
    }

    private function rebuildRentalMaintenance(?string $from, int $chunk, bool $dry, bool $force): void
    {
        RentalMaintenance::with(['contract'])
            ->when($from, fn($q) => $q->where('maintenance_date', '>=', $from))
            ->orderBy('maintenance_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('rental_maintenance', $record->id, $dry, $force,
                        fn() => $this->posting->postRentalMaintenance($record));
                }
            });
    }

    private function rebuildLandRentPayments(?string $from, int $chunk, bool $dry, bool $force): void
    {
        LandRentPayment::when($from, fn($q) => $q->where('payment_date', '>=', $from))
            ->orderBy('payment_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('land_rent_payment', $record->id, $dry, $force,
                        fn() => $this->posting->postLandRentPayment($record));
                }
            });
    }

    private function rebuildNeighboringTransactions(?string $from, int $chunk, bool $dry, bool $force): void
    {
        NeighboringStationTransaction::with(['station'])
            ->when($from, fn($q) => $q->where('transaction_date', '>=', $from))
            ->orderBy('transaction_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('neighboring_station_transaction', $record->id, $dry, $force,
                        fn() => $this->posting->postNeighboringTransaction($record));
                }
            });
    }

    private function rebuildStandaloneTreasuryTransactions(?string $from, int $chunk, bool $dry, bool $force): void
    {
        // Only post treasury transactions that don't have a domain record reference
        // or have a reference type that isn't handled by domain-specific posting
        $domainTypes = [
            'customer_payment', 'supplier_payment', 'supplier_purchase', 
            'contributor_payment', 'employee_borrow', 'expense', 
            'order', 'payroll', 'fuel_log', 'equipment_maintenance',
            'rental_shift', 'rental_maintenance', 'land_rent_payment',
            'neighboring_station_transaction'
        ];

        TreasuryTransaction::when($from, fn($q) => $q->where('transaction_date', '>=', $from))
            ->where(function($q) use ($domainTypes) {
                // Either no reference at all
                $q->whereNull('reference_type')
                  ->orWhereNull('reference_id')
                  // Or reference type is not a domain type
                  ->orWhereNotIn('reference_type', $domainTypes);
            })
            ->orderBy('transaction_date')->orderBy('id')
            ->chunk($chunk, function ($records) use ($dry, $force) {
                foreach ($records as $record) {
                    $this->post('treasury_transaction', $record->id, $dry, $force,
                        fn() => $this->posting->postTreasuryTransaction($record));
                }
            });
    }

    // ─── Core post helper ────────────────────────────────────────────────────────

    /**
     * Post a single record, handling dry-run, force, skip, and error cases.
     */
    private function post(
        string   $refType,
        int      $refId,
        bool     $dry,
        bool     $force,
        callable $postFn
    ): void {
        try {
            // In force mode: void existing entry first
            if ($force) {
                $this->posting->voidJournalEntry($refType, $refId);
            }

            if ($dry) {
                $this->line("  [DRY] {$refType} #{$refId}");
                $this->posted++;
                return;
            }

            $postFn();
            $this->posted++;

        } catch (Throwable $e) {
            // Idempotency: if entry already exists and we're not forcing, skip silently
            if (!$force && str_contains($e->getMessage(), 'Duplicate entry')) {
                $this->skipped++;
                return;
            }
            $this->errors++;
            $this->error("  ❌ خطأ في {$refType} #{$refId}: " . $e->getMessage());
        }
    }
}
