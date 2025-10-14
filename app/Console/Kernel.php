<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Auto close FPTKs older than 6 months since approval, run daily at 01:00
        $schedule->command('recruitment:close-expired-fptk')->dailyAt('00:00');

        // Auto update employee bonds status to completed when end date equals today, run daily at 00:01
        $schedule->command('employee-bonds:update-expired')->dailyAt('01:00');

        // Auto convert paid leave requests to unpaid if no supporting document after 12 days, run daily at 02:00
        $schedule->command('leave:auto-convert')->dailyAt('02:00');

        // Auto approve leave requests pending more than 3 days, run daily at 03:00
        $schedule->command('leave:auto-approve')->dailyAt('03:00');
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
