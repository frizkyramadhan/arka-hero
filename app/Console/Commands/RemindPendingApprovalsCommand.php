<?php

namespace App\Console\Commands;

use App\Services\DocumentNotificationService;
use Illuminate\Console\Command;

class RemindPendingApprovalsCommand extends Command
{
    protected $signature = 'documents:remind-pending-approvals';

    protected $description = 'Email approvers for pending approval plans older than the configured number of days';

    public function handle(DocumentNotificationService $notifications): int
    {
        if (! config('document_notifications.enabled', true)) {
            $this->info('Document notifications disabled; skipping reminders.');

            return self::SUCCESS;
        }

        if (! config('document_notifications.reminder_enabled', true)) {
            $this->info('Approval reminders disabled; skipping.');

            return self::SUCCESS;
        }

        $result = $notifications->remindPendingApprovals();
        $this->info("Reminders queued: {$result['reminded']}; skipped: {$result['skipped']}.");

        return self::SUCCESS;
    }
}
