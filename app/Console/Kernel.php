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
        // Tutup otomatis FPTK yang sudah lebih dari 6 bulan sejak approval, dijalankan setiap hari pukul 00:00
        $schedule->command('recruitment:close-expired-fptk')->dailyAt('00:00');

        // Update otomatis status employee bonds menjadi selesai jika end date sama dengan hari ini, dijalankan setiap hari pukul 00:05
        $schedule->command('employee-bonds:update-expired')->dailyAt('00:05');

        // Konversi otomatis cuti tahunan berbayar menjadi unpaid jika dokumen pendukung tidak diupload setelah 12 hari, dijalankan setiap hari pukul 00:10
        $schedule->command('leave:auto-convert')->dailyAt('00:10');

        // Approve otomatis cuti yang pending lebih dari 3 hari, dijalankan setiap hari pukul 00:15
        $schedule->command('leave:auto-approve')->dailyAt('00:15');

        // Tandai otomatis letter number yang reserved menjadi used setelah 3 hari, dijalankan setiap hari pukul 00:20
        $schedule->command('letter-numbers:auto-mark-used')->dailyAt('00:20');
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
