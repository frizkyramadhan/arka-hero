<?php

namespace App\Console\Commands;

use App\Http\Controllers\LeaveEntitlementController;
use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoGenerateLeaveEntitlements extends Command
{
    protected $signature = 'leave:auto-generate-entitlements';

    protected $description = 'Create missing leave entitlements for the current period of every active employee';

    public function handle(LeaveEntitlementController $entitlements): int
    {
        $year = now()->year;
        $generated = 0;
        $skipped = 0;
        $failed = 0;
        $employees = 0;

        Employee::query()
            ->whereHas('administrations', fn ($query) => $query->where('is_active', true))
            ->with([
                'administrations' => fn ($query) => $query->orderBy('doh'),
                'administrations.project',
                'administrations.level',
                'mutations.project',
            ])
            ->orderBy('id')
            ->chunk(200, function ($chunk) use ($entitlements, $year, &$generated, &$skipped, &$failed, &$employees) {
                foreach ($chunk as $employee) {
                    $employees++;

                    try {
                        $result = $entitlements->generateEntitlementsForEmployee($employee, $year);
                        $generated += $result['generated'];
                        $skipped += $result['skipped'];
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::error('Failed to auto-generate leave entitlements', [
                            'employee_id' => $employee->id,
                            'error' => $e->getMessage(),
                        ]);
                        $this->error("{$employee->fullname}: {$e->getMessage()}");
                    }
                }
            });

        $summary = "Leave entitlements {$year}: employees {$employees}, generated {$generated}, skipped {$skipped}, failed {$failed}";
        $this->info($summary);
        Log::info($summary);

        return Command::SUCCESS;
    }
}
