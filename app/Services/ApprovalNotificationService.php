<?php

namespace App\Services;

use App\Models\DocumentApproval;
use App\Models\ApprovalNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Service class for handling approval notifications.
 */
class ApprovalNotificationService
{
    /**
     * Notify approvers about pending approval.
     *
     * @param DocumentApproval $approval The document approval
     * @return void
     */
    public function notifyPendingApproval(DocumentApproval $approval): void
    {
        try {
            $approvers = $approval->getCurrentApprovers();

            foreach ($approvers as $approver) {
                $approverUsers = $approver->getApproverUsers();

                foreach ($approverUsers as $user) {
                    $this->createNotification($approval, $user->id, 'pending');
                }
            }

            Log::info('Pending approval notifications sent', [
                'approval_id' => $approval->id,
                'approver_count' => $approvers->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send pending approval notifications', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify about completed approval.
     *
     * @param DocumentApproval $approval The document approval
     * @return void
     */
    public function notifyApprovalComplete(DocumentApproval $approval): void
    {
        try {
            // Notify the document submitter
            $this->createNotification($approval, $approval->submitted_by, 'approved');

            // Notify all approvers who participated
            $approvalActions = $approval->approvalActions()->with('approver')->get();
            foreach ($approvalActions as $action) {
                if ($action->approver_id !== $approval->submitted_by) {
                    $this->createNotification($approval, $action->approver_id, 'approved');
                }
            }

            Log::info('Approval complete notifications sent', [
                'approval_id' => $approval->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send approval complete notifications', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify about rejected approval.
     *
     * @param DocumentApproval $approval The document approval
     * @return void
     */
    public function notifyApprovalRejected(DocumentApproval $approval): void
    {
        try {
            // Notify the document submitter
            $this->createNotification($approval, $approval->submitted_by, 'rejected');

            // Notify all approvers who participated
            $approvalActions = $approval->approvalActions()->with('approver')->get();
            foreach ($approvalActions as $action) {
                if ($action->approver_id !== $approval->submitted_by) {
                    $this->createNotification($approval, $action->approver_id, 'rejected');
                }
            }

            Log::info('Approval rejected notifications sent', [
                'approval_id' => $approval->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send approval rejected notifications', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send escalation notification.
     *
     * @param DocumentApproval $approval The document approval
     * @param mixed $approver The approver to notify
     * @return void
     */
    public function sendEscalationNotification(DocumentApproval $approval, $approver): void
    {
        try {
            if ($approver instanceof User) {
                $this->createNotification($approval, $approver->id, 'escalation');
            } else {
                $approverUsers = $approver->getApproverUsers();
                foreach ($approverUsers as $user) {
                    $this->createNotification($approval, $user->id, 'escalation');
                }
            }

            Log::info('Escalation notification sent', [
                'approval_id' => $approval->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send escalation notification', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify about cancelled approval.
     *
     * @param DocumentApproval $approval The document approval
     * @return void
     */
    public function notifyApprovalCancelled(DocumentApproval $approval): void
    {
        try {
            // Notify the document submitter
            $this->createNotification($approval, $approval->submitted_by, 'cancelled');

            // Notify all approvers who participated
            $approvalActions = $approval->approvalActions()->with('approver')->get();
            foreach ($approvalActions as $action) {
                if ($action->approver_id !== $approval->submitted_by) {
                    $this->createNotification($approval, $action->approver_id, 'cancelled');
                }
            }

            Log::info('Approval cancelled notifications sent', [
                'approval_id' => $approval->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send approval cancelled notifications', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify about forwarded approval.
     *
     * @param DocumentApproval $approval The document approval
     * @param int $forwardedToUserId The user ID to forward to
     * @return void
     */
    public function notifyForwardedApproval(DocumentApproval $approval, int $forwardedToUserId): void
    {
        try {
            $this->createNotification($approval, $forwardedToUserId, 'pending');

            Log::info('Forwarded approval notification sent', [
                'approval_id' => $approval->id,
                'forwarded_to' => $forwardedToUserId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send forwarded approval notification', [
                'approval_id' => $approval->id,
                'forwarded_to' => $forwardedToUserId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify about delegated approval.
     *
     * @param DocumentApproval $approval The document approval
     * @param int $delegatedToUserId The user ID to delegate to
     * @return void
     */
    public function notifyDelegatedApproval(DocumentApproval $approval, int $delegatedToUserId): void
    {
        try {
            $this->createNotification($approval, $delegatedToUserId, 'pending');

            Log::info('Delegated approval notification sent', [
                'approval_id' => $approval->id,
                'delegated_to' => $delegatedToUserId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send delegated approval notification', [
                'approval_id' => $approval->id,
                'delegated_to' => $delegatedToUserId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a notification record.
     *
     * @param DocumentApproval $approval The document approval
     * @param int $recipientId The recipient user ID
     * @param string $notificationType The notification type
     * @return ApprovalNotification The created notification
     */
    private function createNotification(DocumentApproval $approval, int $recipientId, string $notificationType): ApprovalNotification
    {
        return ApprovalNotification::create([
            'document_approval_id' => $approval->id,
            'recipient_id' => $recipientId,
            'notification_type' => $notificationType,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark notification as read.
     *
     * @param int $notificationId The notification ID
     * @param int $userId The user ID
     * @return bool True if marked as read successfully
     */
    public function markNotificationAsRead(int $notificationId, int $userId): bool
    {
        try {
            $notification = ApprovalNotification::where('id', $notificationId)
                ->where('recipient_id', $userId)
                ->first();

            if ($notification) {
                $notification->markAsRead();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get unread notifications for a user.
     *
     * @param int $userId The user ID
     * @param array $filters Optional filters
     * @return \Illuminate\Database\Eloquent\Collection The unread notifications
     */
    public function getUnreadNotificationsForUser(int $userId, array $filters = [])
    {
        $query = ApprovalNotification::with(['documentApproval.approvalFlow', 'documentApproval.currentStage'])
            ->where('recipient_id', $userId)
            ->unread();

        // Apply filters
        if (isset($filters['notification_type'])) {
            $query->where('notification_type', $filters['notification_type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('sent_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('sent_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('sent_at', 'desc')->get();
    }

    /**
     * Get notification statistics for a user.
     *
     * @param int $userId The user ID
     * @param array $filters Optional filters
     * @return array The statistics
     */
    public function getNotificationStatisticsForUser(int $userId, array $filters = []): array
    {
        $query = ApprovalNotification::where('recipient_id', $userId);

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('sent_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('sent_at', '<=', $filters['date_to']);
        }

        $totalNotifications = $query->count();
        $unreadNotifications = $query->unread()->count();
        $pendingNotifications = $query->pending()->count();
        $approvedNotifications = $query->approved()->count();
        $rejectedNotifications = $query->rejected()->count();
        $escalationNotifications = $query->escalation()->count();

        return [
            'total_notifications' => $totalNotifications,
            'unread_notifications' => $unreadNotifications,
            'pending_notifications' => $pendingNotifications,
            'approved_notifications' => $approvedNotifications,
            'rejected_notifications' => $rejectedNotifications,
            'escalation_notifications' => $escalationNotifications,
        ];
    }

    /**
     * Send email notifications (placeholder for future implementation).
     *
     * @param ApprovalNotification $notification The notification
     * @return void
     */
    public function sendEmailNotification(ApprovalNotification $notification): void
    {
        // TODO: Implement email notification logic
        // This would integrate with Laravel's mail system
        Log::info('Email notification would be sent', [
            'notification_id' => $notification->id,
            'recipient_id' => $notification->recipient_id,
            'notification_type' => $notification->notification_type,
        ]);
    }

    /**
     * Send real-time notifications (placeholder for future implementation).
     *
     * @param ApprovalNotification $notification The notification
     * @return void
     */
    public function sendRealTimeNotification(ApprovalNotification $notification): void
    {
        // TODO: Implement real-time notification logic
        // This would integrate with WebSockets or similar technology
        Log::info('Real-time notification would be sent', [
            'notification_id' => $notification->id,
            'recipient_id' => $notification->recipient_id,
            'notification_type' => $notification->notification_type,
        ]);
    }

    /**
     * Send bulk notifications to multiple users.
     *
     * @param DocumentApproval $approval The document approval
     * @param array $userIds Array of user IDs to notify
     * @param string $notificationType The notification type
     * @return int Number of notifications sent
     */
    public function sendBulkNotifications(DocumentApproval $approval, array $userIds, string $notificationType): int
    {
        try {
            $notifications = [];
            foreach ($userIds as $userId) {
                $notifications[] = [
                    'document_approval_id' => $approval->id,
                    'recipient_id' => $userId,
                    'notification_type' => $notificationType,
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            ApprovalNotification::insert($notifications);

            Log::info('Bulk notifications sent', [
                'approval_id' => $approval->id,
                'notification_type' => $notificationType,
                'recipient_count' => count($userIds),
            ]);

            return count($userIds);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk notifications', [
                'approval_id' => $approval->id,
                'notification_type' => $notificationType,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Send reminder notifications for overdue approvals.
     *
     * @param DocumentApproval $approval The document approval
     * @return int Number of reminder notifications sent
     */
    public function sendReminderNotifications(DocumentApproval $approval): int
    {
        try {
            $approvers = $approval->getCurrentApprovers();
            $reminderCount = 0;

            foreach ($approvers as $approver) {
                $approverUsers = $approver->getApproverUsers();

                foreach ($approverUsers as $user) {
                    $this->createNotification($approval, $user->id, 'reminder');
                    $reminderCount++;
                }
            }

            Log::info('Reminder notifications sent', [
                'approval_id' => $approval->id,
                'reminder_count' => $reminderCount,
            ]);

            return $reminderCount;
        } catch (\Exception $e) {
            Log::error('Failed to send reminder notifications', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Send summary notifications for approval statistics.
     *
     * @param int $userId The user ID
     * @param array $statistics The statistics data
     * @return bool True if summary notification was sent
     */
    public function sendSummaryNotification(int $userId, array $statistics): bool
    {
        try {
            // Create a summary notification
            $notification = ApprovalNotification::create([
                'document_approval_id' => null, // Summary notifications don't have specific approval
                'recipient_id' => $userId,
                'notification_type' => 'summary',
                'sent_at' => now(),
                'metadata' => $statistics,
            ]);

            Log::info('Summary notification sent', [
                'user_id' => $userId,
                'notification_id' => $notification->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send summary notification', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send custom notification with custom message.
     *
     * @param DocumentApproval $approval The document approval
     * @param int $recipientId The recipient user ID
     * @param string $notificationType The notification type
     * @param string $customMessage The custom message
     * @param array $metadata Additional metadata
     * @return ApprovalNotification The created notification
     */
    public function sendCustomNotification(
        DocumentApproval $approval,
        int $recipientId,
        string $notificationType,
        string $customMessage,
        array $metadata = []
    ): ApprovalNotification {
        try {
            $notification = ApprovalNotification::create([
                'document_approval_id' => $approval->id,
                'recipient_id' => $recipientId,
                'notification_type' => $notificationType,
                'sent_at' => now(),
                'metadata' => array_merge($metadata, [
                    'custom_message' => $customMessage,
                ]),
            ]);

            Log::info('Custom notification sent', [
                'approval_id' => $approval->id,
                'recipient_id' => $recipientId,
                'notification_type' => $notificationType,
                'custom_message' => $customMessage,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to send custom notification', [
                'approval_id' => $approval->id,
                'recipient_id' => $recipientId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark all notifications as read for a user.
     *
     * @param int $userId The user ID
     * @param array $filters Optional filters
     * @return int Number of notifications marked as read
     */
    public function markAllNotificationsAsRead(int $userId, array $filters = []): int
    {
        try {
            $query = ApprovalNotification::where('recipient_id', $userId)->unread();

            // Apply filters
            if (isset($filters['notification_type'])) {
                $query->where('notification_type', $filters['notification_type']);
            }

            if (isset($filters['date_from'])) {
                $query->where('sent_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('sent_at', '<=', $filters['date_to']);
            }

            $count = $query->update(['read_at' => now()]);

            Log::info('All notifications marked as read', [
                'user_id' => $userId,
                'count' => $count,
            ]);

            return $count;
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Delete old notifications for a user.
     *
     * @param int $userId The user ID
     * @param int $daysOld Number of days old to delete
     * @return int Number of notifications deleted
     */
    public function deleteOldNotifications(int $userId, int $daysOld = 90): int
    {
        try {
            $cutoffDate = now()->subDays($daysOld);

            $count = ApprovalNotification::where('recipient_id', $userId)
                ->where('sent_at', '<', $cutoffDate)
                ->delete();

            Log::info('Old notifications deleted', [
                'user_id' => $userId,
                'days_old' => $daysOld,
                'count' => $count,
            ]);

            return $count;
        } catch (\Exception $e) {
            Log::error('Failed to delete old notifications', [
                'user_id' => $userId,
                'days_old' => $daysOld,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Get notification preferences for a user.
     *
     * @param int $userId The user ID
     * @return array The notification preferences
     */
    public function getUserNotificationPreferences(int $userId): array
    {
        // TODO: Implement user notification preferences
        // This would integrate with a user preferences system
        return [
            'email_notifications' => true,
            'real_time_notifications' => true,
            'summary_notifications' => true,
            'reminder_notifications' => true,
            'escalation_notifications' => true,
        ];
    }

    /**
     * Update user notification preferences.
     *
     * @param int $userId The user ID
     * @param array $preferences The notification preferences
     * @return bool True if preferences were updated
     */
    public function updateUserNotificationPreferences(int $userId, array $preferences): bool
    {
        // TODO: Implement user notification preferences update
        // This would integrate with a user preferences system
        Log::info('User notification preferences updated', [
            'user_id' => $userId,
            'preferences' => $preferences,
        ]);

        return true;
    }

    /**
     * Send notification to specific user groups.
     *
     * @param DocumentApproval $approval The document approval
     * @param array $userGroups Array of user group IDs (roles, departments)
     * @param string $notificationType The notification type
     * @return int Number of notifications sent
     */
    public function sendNotificationsToUserGroups(
        DocumentApproval $approval,
        array $userGroups,
        string $notificationType
    ): int {
        try {
            $userIds = [];

            foreach ($userGroups as $groupId) {
                // Get users from role or department
                $users = User::whereHas('roles', function ($query) use ($groupId) {
                    $query->where('id', $groupId);
                })->orWhereHas('department', function ($query) use ($groupId) {
                    $query->where('id', $groupId);
                })->pluck('id')->toArray();

                $userIds = array_merge($userIds, $users);
            }

            // Remove duplicates
            $userIds = array_unique($userIds);

            return $this->sendBulkNotifications($approval, $userIds, $notificationType);
        } catch (\Exception $e) {
            Log::error('Failed to send notifications to user groups', [
                'approval_id' => $approval->id,
                'user_groups' => $userGroups,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Get notification delivery status.
     *
     * @param int $notificationId The notification ID
     * @return array The delivery status
     */
    public function getNotificationDeliveryStatus(int $notificationId): array
    {
        try {
            $notification = ApprovalNotification::find($notificationId);

            if (!$notification) {
                return ['status' => 'not_found'];
            }

            return [
                'status' => $notification->read_at ? 'read' : 'unread',
                'sent_at' => $notification->sent_at,
                'read_at' => $notification->read_at,
                'delivery_time' => $notification->read_at ?
                    $notification->read_at->diffInMinutes($notification->sent_at) : null,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get notification delivery status', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);
            return ['status' => 'error'];
        }
    }
}
