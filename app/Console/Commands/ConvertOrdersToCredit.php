<?php

namespace App\Console\Commands;

use App\Models\Credit;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Order;
use App\Models\TreasuryTransaction;
use App\Services\TreasuryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertOrdersToCredit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:convert-to-credit {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert all orders to credit-only (remove cash payments and update all related data)';

    /**
     * Execute the console command.
     */
    public function handle(TreasuryService $treasuryService)
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->warn('⚠️  WARNING: This will modify orders, payments, credits, customers, and treasury data!');
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }
        
        $this->newLine();
        $this->info('Starting conversion of orders to credit-only...');
        $this->newLine();
        
        $stats = [
            'orders_converted' => 0,
            'customer_payments_deleted' => 0,
            'treasury_transactions_deleted' => 0,
            'credits_created' => 0,
            'credits_updated' => 0,
            'customers_updated' => 0,
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
                ['Orders converted to credit', $stats['orders_converted']],
                ['Customer payments deleted', $stats['customer_payments_deleted']],
                ['Treasury transactions deleted', $stats['treasury_transactions_deleted']],
                ['Credits created', $stats['credits_created']],
                ['Credits updated', $stats['credits_updated']],
                ['Customers updated', $stats['customers_updated']],
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
        // Step 1: Find all orders that have cash payments
        $ordersWithCash = Order::where(function ($q) {
            $q->where('payment_type', 'cash')
              ->orWhere('payment_type', 'mixed');
        })->where('cash_amount', '>', 0)->get();
        
        $this->info("Found {$ordersWithCash->count()} orders with cash payments to convert...");
        $this->newLine();
        
        $progressBar = $this->output->createProgressBar($ordersWithCash->count());
        $progressBar->start();
        
        foreach ($ordersWithCash as $order) {
            $this->convertOrderToCredit($order, $stats, $isDryRun);
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Step 2: Delete customer payments with reference_type = 'order'
        $this->info('Deleting customer payments linked to orders...');
        $customerPayments = CustomerPayment::where('order_id', '>', 0)->get();
        
        foreach ($customerPayments as $payment) {
            if (!$isDryRun) {
                $payment->delete();
            }
            $stats['customer_payments_deleted']++;
        }
        
        $this->info("Deleted {$stats['customer_payments_deleted']} customer payments");
        $this->newLine();
        
        // Step 3: Delete treasury transactions with reference_type = 'order' and category = 'customer_payment'
        $this->info('Deleting treasury transactions for order payments...');
        $treasuryTransactions = TreasuryTransaction::where('reference_type', 'order')
            ->where('category', 'customer_payment')
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
    
    private function convertOrderToCredit(Order $order, array &$stats, bool $isDryRun = false)
    {
        $cashAmount = (float) $order->cash_amount;
        $creditAmount = (float) $order->credit_amount;
        $totalAmount = (float) $order->total_amount;
        
        if ($cashAmount <= 0) {
            return; // Nothing to convert
        }
        
        // Calculate new credit amount (add cash to credit)
        $newCreditAmount = $creditAmount + $cashAmount;
        
        if (!$isDryRun) {
            // Update order
            $order->update([
                'payment_type' => 'credit',
                'cash_amount' => 0,
                'credit_amount' => $newCreditAmount,
            ]);
            
            // Update or create credit record
            $credit = Credit::where('reference_type', 'order')
                ->where('reference_id', $order->id)
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
                    'creditable_type' => 'customer',
                    'creditable_id' => $order->customer_id,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'amount' => $newCreditAmount,
                    'due_date' => $order->credit_due_date ?? now()->addDays(30),
                    'status' => 'pending',
                    'notes' => "Converted from cash payment (was: cash={$cashAmount}, credit={$creditAmount})",
                    'created_by' => $order->created_by,
                ]);
                $stats['credits_created']++;
            }
        }
        
        $stats['orders_converted']++;
    }
}

