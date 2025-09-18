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
        $schedule->command('recruitment:close-expired-fptk')->dailyAt('00:01');

        // Auto update employee bonds status to completed when end date equals today, run daily at 00:01
        $schedule->command('employee-bonds:update-expired')->dailyAt('00:01');
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
