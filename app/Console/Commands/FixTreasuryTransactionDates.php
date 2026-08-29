<?php

namespace App\Console\Commands;

use App\Models\TreasuryTransaction;
use App\Services\TreasuryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTreasuryTransactionDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'treasury:fix-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix treasury transaction dates to match their reference records (orders, purchases, payments, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(TreasuryService $treasuryService)
    {
        $this->info('Starting treasury transaction date fix...');
        $this->newLine();
        
        $fixedCount = 0;
        $skippedCount = 0;
        
        DB::transaction(function () use (&$fixedCount, &$skippedCount, $treasuryService) {
            $transactions = TreasuryTransaction::whereNotNull('reference_type')
                ->whereNotNull('reference_id')
                ->orderBy('id')
                ->get();
            
            $this->info("Found {$transactions->count()} transactions with references to check...");
            $this->newLine();
            
            foreach ($transactions as $tx) {
                $correctDate = $this->getCorrectDateForTransaction($tx);
                
                if ($correctDate && $correctDate !== $tx->transaction_date->toDateString()) {
                    $oldDate = $tx->transaction_date->toDateString();
                    $tx->update(['transaction_date' => $correctDate]);
                    $fixedCount++;
                    
                    $this->line("✓ Updated TX #{$tx->id} ({$tx->category}): {$oldDate} → {$correctDate}");
                } else {
                    $skippedCount++;
                }
            }
            
            $this->newLine();
            $this->info('Recalculating all balances...');
            $treasuryService->recalculateBalances();
        });
        
        $this->newLine();
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✓ Treasury transaction dates fixed successfully!");
        $this->info("  Fixed: {$fixedCount}");
        $this->info("  Skipped (already correct): {$skippedCount}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        $currentBalance = $treasuryService->getCurrentBalance();
        $this->info("Current treasury balance: " . number_format($currentBalance, 2));
        
        return Command::SUCCESS;
    }
    
    /**
     * Get the correct date for a treasury transaction based on its reference
     */
    private function getCorrectDateForTransaction(TreasuryTransaction $tx): ?string
    {
        try {
            switch ($tx->reference_type) {
                // Orders - use delivery_date
                case 'order':
                    $order = \App\Models\Order::find($tx->reference_id);
                    return $order?->delivery_date?->toDateString();
                
                // Purchases - use purchase_date
                case 'purchase':
                case 'supplier_purchase':
                    $purchase = \App\Models\SupplierPurchase::find($tx->reference_id);
                    return $purchase?->purchase_date?->toDateString();
                
                // Customer Payments - use payment_date
                case 'customer_payment':
                    $payment = \App\Models\CustomerPayment::find($tx->reference_id);
                    return $payment?->payment_date?->toDateString();
                
                // Supplier Payments - use payment_date
                case 'supplier_payment':
                    $payment = \App\Models\SupplierPayment::find($tx->reference_id);
                    return $payment?->payment_date?->toDateString();
                
                // Contributor Payments - use payment_date
                case 'contributor_payment':
                case \App\Models\ContributorPayment::class:
                    $payment = \App\Models\ContributorPayment::find($tx->reference_id);
                    return $payment?->payment_date?->toDateString();
                
                // Expenses - use expense_date
                case 'expense':
                    $expense = \App\Models\Expense::find($tx->reference_id);
                    return $expense?->expense_date?->toDateString();
                
                // Order Expenses - get from order's delivery_date
                case 'order_expense':
                    $orderExpense = \App\Models\OrderExpense::find($tx->reference_id);
                    $order = $orderExpense?->order;
                    return $order?->delivery_date?->toDateString();
                
                // Receipts - use receipt_date
                case 'receipt':
                    $receipt = \App\Models\Receipt::find($tx->reference_id);
                    return $receipt?->receipt_date?->toDateString();
                
                // Employee Borrow - use borrow_date
                case \App\Models\EmployeeBorrow::class:
                case 'employee_borrow':
                    $borrow = \App\Models\EmployeeBorrow::find($tx->reference_id);
                    return $borrow?->borrow_date?->toDateString();
                
                // Land Rent Payments - use payment_date
                case 'land_rent_payment':
                    $payment = \App\Models\LandRentPayment::find($tx->reference_id);
                    return $payment?->payment_date?->toDateString();
                
                // Rental Maintenance - use maintenance_date
                case 'App\Models\RentalMaintenance':
                    $maintenance = \App\Models\RentalMaintenance::find($tx->reference_id);
                    return $maintenance?->maintenance_date?->toDateString();
                
                // Equipment - use purchase_date
                case \App\Models\Equipment::class:
                case 'equipment':
                    $equipment = \App\Models\Equipment::find($tx->reference_id);
                    return $equipment?->purchase_date?->toDateString();
                
                // Equipment Maintenance - use maintenance_date
                case \App\Models\EquipmentMaintenance::class:
                case 'equipment_maintenance':
                    $maintenance = \App\Models\EquipmentMaintenance::find($tx->reference_id);
                    return $maintenance?->maintenance_date?->toDateString();
                
                // Equipment Tool - use created_at as fallback
                case \App\Models\EquipmentTool::class:
                    $tool = \App\Models\EquipmentTool::find($tx->reference_id);
                    return $tool?->created_at?->toDateString();
                
                // Rental Shifts - use shift_date
                case \App\Models\RentalShift::class:
                case 'rental_shift':
                    $shift = \App\Models\RentalShift::find($tx->reference_id);
                    return $shift?->shift_date?->toDateString();
                
                // Payroll - use payment_date
                case \App\Models\Payroll::class:
                case 'payroll':
                    $payroll = \App\Models\Payroll::find($tx->reference_id);
                    return $payroll?->payment_date?->toDateString();
                
                // Neighboring Station Transaction - use transaction_date
                case \App\Models\NeighboringStationTransaction::class:
                case 'neighboring_station_transaction':
                    $nst = \App\Models\NeighboringStationTransaction::find($tx->reference_id);
                    return $nst?->transaction_date?->toDateString();
                
                default:
                    // Unknown reference type, keep existing date
                    return null;
            }
        } catch (\Exception $e) {
            // If any error occurs, skip this transaction
            $this->warn("⚠ Error processing TX #{$tx->id}: " . $e->getMessage());
            return null;
        }
    }
}
