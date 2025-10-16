<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\UpdateCryptoPrices::class,
        \App\Console\Commands\UpdateMetalPrices::class,
        \App\Console\Commands\UpdateOpenOrdersPnL::class,

    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('update:crypto-price')->everyMinute()->withoutOverlapping();
        $schedule->command('update:metal-price')->everyMinute()->withoutOverlapping();
        $schedule->command('update:open-orders-pnl')->everyMinute()->withoutOverlapping();

    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
