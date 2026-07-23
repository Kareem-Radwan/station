<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'customer' => \App\Models\Customer::class,
            'supplier' => \App\Models\Supplier::class,
        ]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('credits:mark-overdue')->dailyAt('00:01');
            $schedule->command('db:backup')->dailyAt('23:00');
        });
    }
}
