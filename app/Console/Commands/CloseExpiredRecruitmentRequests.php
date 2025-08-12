<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\RecruitmentRequest;
use App\Models\ApprovalPlan;
use Carbon\Carbon;

class CloseExpiredRecruitmentRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recruitment:close-expired-fptk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto close FPTKs that have passed 6 months since approval date.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        // Fetch approved FPTKs only
        $fptks = RecruitmentRequest::where('status', RecruitmentRequest::STATUS_APPROVED)->get();

        $closed = 0;
        foreach ($fptks as $fptk) {
            // Find the final approval timestamp based on approval plans
            $approvedAt = ApprovalPlan::where('document_type', 'recruitment_request')
                ->where('document_id', $fptk->id)
                ->where('status', 1) // approved
                ->max('updated_at');

            if (!$approvedAt) {
                continue; // skip if not determinable
            }

            $expiry = Carbon::parse($approvedAt)->addMonthsNoOverflow(6);
            if ($now->greaterThan($expiry)) {
                $fptk->update(['status' => RecruitmentRequest::STATUS_CLOSED]);
                $closed++;
                Log::info('Auto-closed FPTK after 6 months', [
                    'fptk_id' => $fptk->id,
                    'approved_at' => $approvedAt,
                    'closed_at' => $now->toDateTimeString(),
                ]);
            }
        }

        $this->info("Closed {$closed} FPTK(s) that exceeded 6 months since approval.");
        return Command::SUCCESS;
    }
}
