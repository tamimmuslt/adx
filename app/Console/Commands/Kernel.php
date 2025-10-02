<?php

namespace App\Console\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * أوامر أرتيسان الخاصة بالتطبيق
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\UpdateCryptoPrices::class,
    ];

    /**
     * جدولة الأوامر.
     */
  protected function schedule(Schedule $schedule): void
{
    // تشغيل الأمر update:crypto-prices كل دقيقة
    $schedule->command('update:crypto-prices')->everyMinute();
}


    /**
     * تسجيل جميع الأوامر.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }   
}
