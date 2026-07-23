<?php

namespace App\Console\Commands;

use App\Models\Credit;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Console\Command;

class MarkOverdueCredits extends Command
{
    protected $signature   = 'credits:mark-overdue';
    protected $description = 'Mark past-due credits as overdue';

    public function handle(): void
    {
        $count = Credit::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $this->info("Marked {$count} credits as overdue.");
    }
}
