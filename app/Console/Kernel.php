<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\sendVendorNotification::class,
        \App\Console\Commands\cancelOrder::class,
        \App\Console\Commands\PaymentCheck::class,
        \App\Console\Commands\SandgridSync::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        // $schedule->command('notification:send') ->everyFiveMinutes();
        $schedule->command('notification:send')->everyFiveMinutes();
        $schedule->command('notification:bookingNotification')->everyFiveMinutes();

        $schedule->command('cancel:order')->everyFiveMinutes();
        $schedule->command('payment:check')->everyFiveMinutes();
        $schedule->command('sandgrid:sync')->dailyAt('13:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');


        require base_path('routes/console.php');
    }
}
