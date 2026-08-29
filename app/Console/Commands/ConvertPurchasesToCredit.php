<?php

namespace App\Console\Commands;

use App\Models\Credit;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPurchase;
use App\Models\TreasuryTransaction;
use App\Services\TreasuryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertPurchasesToCredit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchases:convert-to-credit {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert all supplier purchases to credit-only (remove cash payments and update all related data)';

    /**
     * Execute the console command.
     */
    public function handle(TreasuryService $treasuryService)
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->warn('⚠️  WARNING: This will modify purchases, payments, credits, suppliers, and treasury data!');
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }
        
        $this->newLine();
        $this->info('Starting conversion of purchases to credit-only...');
        $this->newLine();
        
        $stats = [
            'purchases_converted' => 0,
            'supplier_payments_deleted' => 0,
            'treasury_transactions_deleted' => 0,
            'credits_created' => 0,
            'credits_updated' => 0,
            'suppliers_updated' => 0,
        ];
        
        if (!$isDryRun) {
            DB::transaction(function () use (&$stats, $treasuryService) {
                $this->processConversion($stats, $treasuryService);
            });
        } else {
            $this->processConversion($stats, $treasuryService, true);
        }
        
        // Display summary
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info($isDryRun ? '📋 DRY RUN SUMMARY (no changes made):' : '✓ CONVERSION COMPLETED SUCCESSFULLY!');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Purchases converted to credit', $stats['purchases_converted']],
                ['Supplier payments deleted', $stats['supplier_payments_deleted']],
                ['Treasury transactions deleted', $stats['treasury_transactions_deleted']],
                ['Credits created', $stats['credits_created']],
                ['Credits updated', $stats['credits_updated']],
                ['Suppliers updated', $stats['suppliers_updated']],
            ]
        );
        
        if (!$isDryRun) {
            $currentBalance = $treasuryService->getCurrentBalance();
            $this->newLine();
            $this->info("New treasury balance: " . number_format($currentBalance, 2));
        }
        
        return Command::SUCCESS;
    }
    
    private function processConversion(array &$stats, TreasuryService $treasuryService, bool $isDryRun = false)
    {
        // Step 1: Find all purchases that have cash payments
        $purchasesWithCash = SupplierPurchase::where(function ($q) {
            $q->where('payment_type', 'cash')
              ->orWhere('payment_type', 'mixed');
        })->where('cash_amount', '>', 0)->get();
        
        $this->info("Found {$purchasesWithCash->count()} purchases with cash payments to convert...");
        $this->newLine();
        
        $progressBar = $this->output->createProgressBar($purchasesWithCash->count());
        $progressBar->start();
        
        foreach ($purchasesWithCash as $purchase) {
            $this->convertPurchaseToCredit($purchase, $stats, $isDryRun);
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Step 2: Delete supplier payments with reference_type = 'purchase'
        $this->info('Deleting supplier payments linked to purchases...');
        $supplierPayments = SupplierPayment::where('supplier_purchase_id', '>', 0)
            ->where('payment_type', 'payment')
            ->get();
        
        foreach ($supplierPayments as $payment) {
            if (!$isDryRun) {
                // Adjust supplier balance (add back the payment since we're removing it)
                if ($payment->supplier) {
                    $payment->supplier->increment('balance', $payment->amount);
                    $stats['suppliers_updated']++;
                }
                $payment->delete();
            }
            $stats['supplier_payments_deleted']++;
        }
        
        $this->info("Deleted {$stats['supplier_payments_deleted']} supplier payments");
        $this->newLine();
        
        // Step 3: Delete treasury transactions with reference_type = 'purchase' and category = 'supplier_payment'
        $this->info('Deleting treasury transactions for purchase payments...');
        $treasuryTransactions = TreasuryTransaction::where('reference_type', 'purchase')
            ->where('category', 'supplier_payment')
            ->get();
        
        foreach ($treasuryTransactions as $tx) {
            if (!$isDryRun) {
                $tx->delete();
            }
            $stats['treasury_transactions_deleted']++;
        }
        
        $this->info("Deleted {$stats['treasury_transactions_deleted']} treasury transactions");
        $this->newLine();
        
        // Step 4: Recalculate treasury balances
        if (!$isDryRun) {
            $this->info('Recalculating treasury balances...');
            $treasuryService->recalculateBalances();
            $this->info('✓ Treasury balances recalculated');
        }
    }
    
    private function convertPurchaseToCredit(SupplierPurchase $purchase, array &$stats, bool $isDryRun = false)
    {
        $cashAmount = (float) $purchase->cash_amount;
        $creditAmount = (float) $purchase->credit_amount;
        
        if ($cashAmount <= 0) {
            return; // Nothing to convert
        }
        
        // Calculate new credit amount (add cash to credit)
        $newCreditAmount = $creditAmount + $cashAmount;
        
        if (!$isDryRun) {
            // Update purchase
            $purchase->update([
                'payment_type' => 'credit',
                'cash_amount' => 0,
                'credit_amount' => $newCreditAmount,
            ]);
            
            // Update or create credit record
            $credit = Credit::where('reference_type', 'purchase')
                ->where('reference_id', $purchase->id)
                ->first();
            
            if ($credit) {
                // Update existing credit (reset to pending if it was paid)
                $credit->update([
                    'amount' => $newCreditAmount,
                    'status' => 'pending',
                    'paid_date' => null,
                ]);
                $stats['credits_updated']++;
            } else {
                // Create new credit record
                Credit::create([
                    'creditable_type' => 'supplier',
                    'creditable_id' => $purchase->supplier_id,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'amount' => $newCreditAmount,
                    'due_date' => $purchase->due_date ?? now()->addDays(30),
                    'status' => 'pending',
                    'notes' => "Converted from cash payment (was: cash={$cashAmount}, credit={$creditAmount})",
                    'created_by' => $purchase->created_by,
                ]);
                $stats['credits_created']++;
            }
            
            // Update supplier balance (add the cash amount that was removed - we now owe them more)
            if ($purchase->supplier) {
                $purchase->supplier->increment('balance', $cashAmount);
                $stats['suppliers_updated']++;
            }
        }
        
        $stats['purchases_converted']++;
    }
}
