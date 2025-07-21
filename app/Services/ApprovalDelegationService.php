<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Models\DocumentApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApprovalDelegationService
{
    /**
     * Delegate approval to another user
     */
    public function delegateApproval(DocumentApproval $approval, int $delegatedTo, int $delegatedBy, string $reason = null, int $durationHours = 24): bool
    {
        try {
            DB::beginTransaction();

            // Create delegation action
            $delegationAction = ApprovalAction::create([
                'document_approval_id' => $approval->id,
                'approval_stage_id' => $approval->current_stage_id,
                'approver_id' => $delegatedBy,
                'action' => 'delegated',
                'comments' => $reason,
                'action_date' => now(),
                'delegated_to' => $delegatedTo,
                'is_automatic' => false,
                'metadata' => [
                    'delegation_duration_hours' => $durationHours,
                    'delegation_expires_at' => now()->addHours($durationHours)->toISOString(),
                    'original_approver_id' => $delegatedBy
                ]
            ]);

            // Update approval metadata to track delegation
            $approval->update([
                'metadata' => array_merge($approval->metadata ?? [], [
                    'delegated_to' => $delegatedTo,
                    'delegated_by' => $delegatedBy,
                    'delegation_reason' => $reason,
                    'delegation_expires_at' => now()->addHours($durationHours)->toISOString(),
                    'delegation_created_at' => now()->toISOString()
                ])
            ]);

            // Send notification to delegated user
            $this->notifyDelegation($approval, $delegatedTo, $delegatedBy, $reason);

            DB::commit();

            Log::info('Approval delegated successfully', [
                'approval_id' => $approval->id,
                'delegated_to' => $delegatedTo,
                'delegated_by' => $delegatedBy,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delegate approval', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if delegation is expired
     */
    public function isDelegationExpired(DocumentApproval $approval): bool
    {
        $metadata = $approval->metadata ?? [];
        $expiresAt = $metadata['delegation_expires_at'] ?? null;

        if (!$expiresAt) {
            return false;
        }

        return Carbon::parse($expiresAt)->isPast();
    }

    /**
     * Get active delegations for a user
     */
    public function getActiveDelegations(int $userId): array
    {
        $delegations = ApprovalAction::where('delegated_to', $userId)
            ->where('action', 'delegated')
            ->whereHas('documentApproval', function ($query) {
                $query->where('overall_status', 'pending');
            })
            ->with(['documentApproval', 'approvalStage', 'approver'])
            ->get();

        $activeDelegations = [];

        foreach ($delegations as $delegation) {
            if (!$this->isDelegationExpired($delegation->documentApproval)) {
                $activeDelegations[] = [
                    'id' => $delegation->id,
                    'approval_id' => $delegation->document_approval_id,
                    'document_type' => $delegation->documentApproval->document_type,
                    'document_id' => $delegation->documentApproval->document_id,
                    'stage_name' => $delegation->approvalStage->stage_name,
                    'delegated_by' => $delegation->approver->name,
                    'delegated_at' => $delegation->action_date,
                    'expires_at' => $delegation->metadata['delegation_expires_at'] ?? null,
                    'reason' => $delegation->comments
                ];
            }
        }

        return $activeDelegations;
    }

    /**
     * Revoke delegation
     */
    public function revokeDelegation(DocumentApproval $approval, int $revokedBy, string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            // Create revocation action
            $revocationAction = ApprovalAction::create([
                'document_approval_id' => $approval->id,
                'approval_stage_id' => $approval->current_stage_id,
                'approver_id' => $revokedBy,
                'action' => 'delegation_revoked',
                'comments' => $reason,
                'action_date' => now(),
                'is_automatic' => false,
                'metadata' => [
                    'revocation_reason' => $reason,
                    'revoked_by' => $revokedBy
                ]
            ]);

            // Remove delegation metadata
            $metadata = $approval->metadata ?? [];
            unset($metadata['delegated_to']);
            unset($metadata['delegated_by']);
            unset($metadata['delegation_reason']);
            unset($metadata['delegation_expires_at']);
            unset($metadata['delegation_created_at']);

            $approval->update(['metadata' => $metadata]);

            // Notify original approver
            $this->notifyDelegationRevoked($approval, $revokedBy, $reason);

            DB::commit();

            Log::info('Delegation revoked successfully', [
                'approval_id' => $approval->id,
                'revoked_by' => $revokedBy,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to revoke delegation', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get delegation history for an approval
     */
    public function getDelegationHistory(DocumentApproval $approval): array
    {
        return ApprovalAction::where('document_approval_id', $approval->id)
            ->whereIn('action', ['delegated', 'delegation_revoked'])
            ->with(['approver', 'delegatedTo'])
            ->orderBy('action_date', 'desc')
            ->get()
            ->map(function ($action) {
                return [
                    'id' => $action->id,
                    'action' => $action->action,
                    'approver' => $action->approver->name,
                    'delegated_to' => $action->delegatedTo->name ?? null,
                    'action_date' => $action->action_date,
                    'comments' => $action->comments,
                    'metadata' => $action->metadata
                ];
            })
            ->toArray();
    }

    /**
     * Check if user can delegate approval
     */
    public function canUserDelegate(DocumentApproval $approval, int $userId): bool
    {
        // Check if user is the current approver
        if (!$approval->currentStage) {
            return false;
        }

        return $approval->currentStage->approvers()
            ->where(function ($query) use ($userId) {
                $query->where('approver_type', 'user')->where('approver_id', $userId)
                    ->orWhere('approver_type', 'role')->whereIn('approver_id', function ($q) use ($userId) {
                        $q->select('role_id')->from('model_has_roles')->where('model_id', $userId);
                    })
                    ->orWhere('approver_type', 'department')->where('approver_id', function ($q) use ($userId) {
                        $q->select('department_id')->from('users')->where('id', $userId);
                    });
            })
            ->exists();
    }

    /**
     * Get available users for delegation
     */
    public function getAvailableDelegates(int $excludeUserId = null): array
    {
        $query = User::where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['administrator', 'HR', 'HR Manager', 'Manager', 'Director']);
            });

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray()
                ];
            })
            ->toArray();
    }

    /**
     * Notify user about delegation
     */
    private function notifyDelegation(DocumentApproval $approval, int $delegatedTo, int $delegatedBy, string $reason = null): void
    {
        $delegatedUser = User::find($delegatedTo);
        $delegatedByUser = User::find($delegatedBy);

        if (!$delegatedUser || !$delegatedByUser) {
            return;
        }

        // Create notification
        \App\Models\ApprovalNotification::create([
            'document_approval_id' => $approval->id,
            'recipient_id' => $delegatedTo,
            'notification_type' => 'delegation',
            'sent_at' => now()
        ]);

        // Log notification
        Log::info('Delegation notification sent', [
            'delegated_to' => $delegatedUser->name,
            'delegated_by' => $delegatedByUser->name,
            'approval_id' => $approval->id
        ]);
    }

    /**
     * Notify about delegation revocation
     */
    private function notifyDelegationRevoked(DocumentApproval $approval, int $revokedBy, string $reason = null): void
    {
        $metadata = $approval->metadata ?? [];
        $originalApproverId = $metadata['delegated_by'] ?? null;

        if (!$originalApproverId) {
            return;
        }

        // Create notification for original approver
        \App\Models\ApprovalNotification::create([
            'document_approval_id' => $approval->id,
            'recipient_id' => $originalApproverId,
            'notification_type' => 'delegation_revoked',
            'sent_at' => now()
        ]);

        Log::info('Delegation revocation notification sent', [
            'approval_id' => $approval->id,
            'revoked_by' => $revokedBy
        ]);
    }

    /**
     * Get delegation statistics
     */
    public function getDelegationStats(): array
    {
        $totalDelegations = ApprovalAction::where('action', 'delegated')->count();
        $activeDelegations = ApprovalAction::where('action', 'delegated')
            ->whereHas('documentApproval', function ($query) {
                $query->where('overall_status', 'pending');
            })
            ->count();

        $expiredDelegations = ApprovalAction::where('action', 'delegated')
            ->whereHas('documentApproval', function ($query) {
                $query->where('overall_status', 'pending');
            })
            ->get()
            ->filter(function ($action) {
                $metadata = $action->metadata ?? [];
                $expiresAt = $metadata['delegation_expires_at'] ?? null;
                return $expiresAt && Carbon::parse($expiresAt)->isPast();
            })
            ->count();

        return [
            'total_delegations' => $totalDelegations,
            'active_delegations' => $activeDelegations,
            'expired_delegations' => $expiredDelegations,
            'delegation_success_rate' => $totalDelegations > 0 ?
                round((($totalDelegations - $expiredDelegations) / $totalDelegations) * 100, 2) : 0
        ];
    }
}
