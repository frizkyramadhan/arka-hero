<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoApproveLeaveRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:auto-approve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto approve leave requests pending more than 3 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threeDaysAgo = now()->subDays(3);

        $pendingRequests = LeaveRequest::where('status', 'pending')
            ->where('created_at', '<=', $threeDaysAgo)
            ->with('approvalPlans')
            ->get();

        $this->info("Found {$pendingRequests->count()} leave requests pending more than 3 days");

        $approvedCount = 0;

        foreach ($pendingRequests as $request) {
            try {
                // Auto approve semua approval plans yang masih pending dan open
                $pendingApprovals = $request->approvalPlans()
                    ->where('status', 0) // Pending
                    ->where('is_open', true) // Still open
                    ->get();

                // Check if there are any pending approvals
                if ($pendingApprovals->isEmpty()) {
                    $this->info("No pending approvals found for leave request ID: {$request->id}");
                    continue;
                }

                // Auto approve all pending approvals sequentially
                foreach ($pendingApprovals as $approval) {
                    // Check if this approval can be processed (sequential validation)
                    if (!$approval->canBeProcessed()) {
                        $this->warn("Skipping approval ID: {$approval->id} - previous approvals must be completed first");
                        continue;
                    }

                    $approval->update([
                        'status' => 1, // Approved
                        'remarks' => 'Auto approved after 3 days'
                    ]);

                    $this->info("Auto approved approval plan ID: {$approval->id} for leave request ID: {$request->id}");
                }

                // Check if all sequential approvals are now completed
                $allApprovals = $request->approvalPlans()
                    ->where('is_open', true)
                    ->get();

                $allCompleted = $allApprovals->every(function ($approval) {
                    return $approval->status == 1; // All approved
                });

                // Update leave request status to approved if all approvals are completed
                if ($allCompleted && $allApprovals->isNotEmpty()) {
                    $request->update([
                        'status' => 'approved', // Use 'approved' instead of 'auto_approved' for consistency
                        'approved_at' => now()
                    ]);

                    $approvedCount++;
                    $this->info("Successfully auto approved leave request ID: {$request->id}");
                } else {
                    $this->info("Leave request ID: {$request->id} partially approved - waiting for remaining approvals");
                }

                Log::info("Auto approved leave request ID: {$request->id}");
            } catch (\Exception $e) {
                $this->error("Failed to auto approve leave request ID: {$request->id}. Error: " . $e->getMessage());
                Log::error("Failed to auto approve leave request ID: {$request->id}. Error: " . $e->getMessage());
            }
        }

        $this->info("Successfully auto approved {$approvedCount} leave requests");

        return Command::SUCCESS;
    }
}
