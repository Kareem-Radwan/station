<?php

namespace App\Providers;

use App\Accounting\Commands\RebuildAccountingCommand;
use App\Accounting\Reports\BalanceSheetReport;
use App\Accounting\Reports\GeneralLedgerReport;
use App\Accounting\Reports\IncomeStatementReport;
use App\Accounting\Reports\TrialBalanceReport;
use App\Accounting\Services\AccountingPostingService;
use App\Accounting\Services\JournalEntryService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ─── Accounting Layer — Singleton bindings ──────────────────────────────
        // JournalEntryService: stateless, safe as singleton
        $this->app->singleton(JournalEntryService::class);

        // AccountingPostingService: depends on JournalEntryService
        $this->app->singleton(AccountingPostingService::class, function ($app) {
            return new AccountingPostingService(
                $app->make(JournalEntryService::class),
            );
        });

        // Report classes: lightweight, can be singletons
        $this->app->singleton(TrialBalanceReport::class);
        $this->app->singleton(GeneralLedgerReport::class);

        $this->app->singleton(IncomeStatementReport::class, function ($app) {
            return new IncomeStatementReport(
                $app->make(TrialBalanceReport::class),
            );
        });

        $this->app->singleton(BalanceSheetReport::class, function ($app) {
            return new BalanceSheetReport(
                $app->make(TrialBalanceReport::class),
            );
        });
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'customer' => \App\Models\Customer::class,
            'supplier' => \App\Models\Supplier::class,
        ]);

        // ─── Register Artisan Commands ──────────────────────────────────────────
        if ($this->app->runningInConsole()) {
            $this->commands([
                RebuildAccountingCommand::class,
            ]);
        }

        // ─── Scheduled Jobs ─────────────────────────────────────────────────────
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('credits:mark-overdue')->dailyAt('00:01');
            $schedule->command('db:backup')->dailyAt('23:00');
        });
    }
}
