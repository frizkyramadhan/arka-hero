<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoConvertLeaveRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:auto-convert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically convert paid leave requests to unpaid if no supporting document after 12 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto conversion process...');

        // Find leave requests that need auto conversion
        $candidates = LeaveRequest::where('auto_conversion_at', '<=', now())
            ->whereNull('supporting_document')
            ->whereHas('leaveType', function ($query) {
                $query->where('category', 'paid');
            })
            ->get();

        $this->info("Found {$candidates->count()} leave requests to convert");

        $convertedCount = 0;

        foreach ($candidates as $leaveRequest) {
            try {
                $this->convertToUnpaid($leaveRequest);
                $convertedCount++;

                $this->info("Converted leave request ID: {$leaveRequest->id} for employee: {$leaveRequest->employee->fullname}");

                // Log the conversion
                Log::info('Leave request auto converted', [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                    'employee_name' => $leaveRequest->employee->fullname,
                    'original_leave_type' => $leaveRequest->leaveType->name,
                    'conversion_date' => now()
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to convert leave request ID: {$leaveRequest->id} - {$e->getMessage()}");
                Log::error('Auto conversion failed', [
                    'leave_request_id' => $leaveRequest->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Auto conversion completed. {$convertedCount} leave requests converted.");

        return Command::SUCCESS;
    }

    /**
     * Convert leave request to unpaid
     */
    private function convertToUnpaid(LeaveRequest $leaveRequest)
    {
        // Find corresponding unpaid leave type
        $unpaidLeaveType = $this->findUnpaidLeaveType($leaveRequest->leaveType);

        if (!$unpaidLeaveType) {
            throw new \Exception("No corresponding unpaid leave type found for: {$leaveRequest->leaveType->name}");
        }

        // Update leave request
        $leaveRequest->update([
            'leave_type_id' => $unpaidLeaveType->id,
            'auto_conversion_at' => null, // Clear the conversion date
        ]);
    }

    /**
     * Find corresponding unpaid leave type
     */
    private function findUnpaidLeaveType(LeaveType $paidLeaveType)
    {
        // Try to find unpaid version by name pattern
        $unpaidName = str_replace(['Paid', 'paid'], ['Unpaid', 'unpaid'], $paidLeaveType->name);

        $unpaidLeaveType = LeaveType::where('name', $unpaidName)
            ->where('category', 'unpaid')
            ->first();

        // If not found, try to find by code pattern
        if (!$unpaidLeaveType) {
            $unpaidCode = str_replace(['P', 'p'], ['U', 'u'], $paidLeaveType->code);

            $unpaidLeaveType = LeaveType::where('code', $unpaidCode)
                ->where('category', 'unpaid')
                ->first();
        }

        // If still not found, use default unpaid leave type
        if (!$unpaidLeaveType) {
            $unpaidLeaveType = LeaveType::where('category', 'unpaid')
                ->where('is_active', true)
                ->first();
        }

        return $unpaidLeaveType;
    }
}
