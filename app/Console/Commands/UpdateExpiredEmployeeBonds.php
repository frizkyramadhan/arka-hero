<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\EmployeeBond;
use Carbon\Carbon;

class UpdateExpiredEmployeeBonds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee-bonds:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto update employee bonds status to completed when end date equals today.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting employee bonds expiration check...');

        try {
            // Get today's date
            $today = Carbon::today();

            // Find active employee bonds where end_date equals today
            $expiredBonds = EmployeeBond::where('status', 'active')
                ->whereDate('end_date', $today)
                ->get();

            if ($expiredBonds->isEmpty()) {
                $this->info('No employee bonds found that expire today.');
                return Command::SUCCESS;
            }

            $this->info("Found {$expiredBonds->count()} employee bond(s) that expire today.");

            $updatedCount = 0;

            foreach ($expiredBonds as $bond) {
                // Update status to completed
                $bond->update([
                    'status' => 'completed'
                ]);

                $this->line("✓ Updated bond: {$bond->employee_bond_number} - {$bond->employee->fullname}");

                // Log the update
                Log::info("Employee bond auto-completed", [
                    'bond_id' => $bond->id,
                    'bond_number' => $bond->employee_bond_number,
                    'employee_name' => $bond->employee->fullname,
                    'end_date' => $bond->end_date->format('Y-m-d'),
                    'updated_at' => now()->format('Y-m-d H:i:s')
                ]);

                $updatedCount++;
            }

            $this->info("Successfully updated {$updatedCount} employee bond(s) to completed status.");

            // Log summary
            Log::info("Employee bonds auto-update completed", [
                'total_updated' => $updatedCount,
                'date' => $today->format('Y-m-d'),
                'executed_at' => now()->format('Y-m-d H:i:s')
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error updating employee bonds: " . $e->getMessage());

            Log::error("Employee bonds auto-update failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'executed_at' => now()->format('Y-m-d H:i:s')
            ]);

            return Command::FAILURE;
        }
    }
}
