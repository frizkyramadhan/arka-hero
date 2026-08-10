<?php

namespace App\Console\Commands;

use App\Services\DisciplinaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireEmployeeDisciplinariesCommand extends Command
{
    protected $signature = 'disciplinary:expire';

    protected $description = 'Expire active pembinaan/SP records whose end_date has passed';

    public function handle(DisciplinaryService $service): int
    {
        $this->info('Starting disciplinary expiration check...');

        try {
            $updated = $service->expireDue();

            if ($updated === 0) {
                $this->info('No disciplinary records to expire.');
            } else {
                $this->info("Expired {$updated} disciplinary record(s).");
            }

            Log::info('Disciplinary expire completed', [
                'total_updated' => $updated,
                'executed_at' => now()->toDateTimeString(),
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error expiring disciplinaries: '.$e->getMessage());
            Log::error('Disciplinary expire failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
