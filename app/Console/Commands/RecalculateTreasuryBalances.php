<?php

namespace App\Console\Commands;

use App\Services\TreasuryService;
use Illuminate\Console\Command;

class RecalculateTreasuryBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'treasury:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate all treasury transaction balances based on transaction date and ID order';

    /**
     * Execute the console command.
     */
    public function handle(TreasuryService $treasuryService)
    {
        $this->info('Starting treasury balance recalculation...');
        
        try {
            $treasuryService->recalculateBalances();
            $this->info('✓ Treasury balances recalculated successfully!');
            
            $currentBalance = $treasuryService->getCurrentBalance();
            $this->info("Current treasury balance: " . number_format($currentBalance, 2));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to recalculate treasury balances: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

