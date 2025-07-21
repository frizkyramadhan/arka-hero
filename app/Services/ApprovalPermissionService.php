<?php

namespace App\Services;

use App\Models\User;
use App\Models\DocumentApproval;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Service class for handling approval permissions and authorization.
 */
class ApprovalPermissionService
{
    /**
     * Check if user can view approval.
     *
     * @param User $user The user
     * @param DocumentApproval $approval The document approval
     * @return bool True if user can view
     */
    public function canViewApproval(User $user, DocumentApproval $approval): bool
    {
        try {
            // User can view if they are the submitter
            if ($approval->submitted_by === $user->id) {
                return true;
            }

            // User can view if they are an approver
            if ($this->isUserApprover($user, $approval)) {
                return true;
            }

            // User can view if they have admin permissions
            if ($this->hasAdminPermissions($user)) {
                return true;
            }

            // User can view if they have view permission for this document type
            if ($this->hasDocumentTypePermission($user, $approval->document_type, 'view')) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check view approval permission', [
                'user_id' => $user->id,
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user can approve.
     *
     * @param User $user The user
     * @param DocumentApproval $approval The document approval
     * @return bool True if user can approve
     */
    public function canApprove(User $user, DocumentApproval $approval): bool
    {
        try {
            // User cannot approve their own submission
            if ($approval->submitted_by === $user->id) {
                return false;
            }

            // User can approve if they are an approver
            if ($this->isUserApprover($user, $approval)) {
                return true;
            }

            // User can approve if they have admin permissions
            if ($this->hasAdminPermissions($user)) {
                return true;
            }

            // User can approve if they have approve permission for this document type
            if ($this->hasDocumentTypePermission($user, $approval->document_type, 'approve')) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check approve permission', [
                'user_id' => $user->id,
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user can reject.
     *
     * @param User $user The user
     * @param DocumentApproval $approval The document approval
     * @return bool True if user can reject
     */
    public function canReject(User $user, DocumentApproval $approval): bool
    {
        try {
            // User cannot reject their own submission
            if ($approval->submitted_by === $user->id) {
                return false;
            }

            // User can reject if they are an approver
            if ($this->isUserApprover($user, $approval)) {
                return true;
            }

            // User can reject if they have admin permissions
            if ($this->hasAdminPermissions($user)) {
                return true;
            }

            // User can reject if they have reject permission for this document type
            if ($this->hasDocumentTypePermission($user, $approval->document_type, 'reject')) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check reject permission', [
                'user_id' => $user->id,
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user can cancel approval.
     *
     * @param User $user The user
     * @param DocumentApproval $approval The document approval
     * @return bool True if user can cancel
     */
    public function canCancelApproval(User $user, DocumentApproval $approval): bool
    {
        try {
            // User can cancel if they are the submitter
            if ($approval->submitted_by === $user->id) {
                return true;
            }

            // User can cancel if they have admin permissions
            if ($this->hasAdminPermissions($user)) {
                return true;
            }

            // User can cancel if they have cancel permission for this document type
            if ($this->hasDocumentTypePermission($user, $approval->document_type, 'cancel')) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check cancel approval permission', [
                'user_id' => $user->id,
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user can manage approval flows.
     *
     * @param User $user The user
     * @return bool True if user can manage flows
     */
    public function canManageApprovalFlows(User $user): bool
    {
        try {
            // User can manage if they have admin permissions
            if ($this->hasAdminPermissions($user)) {
                return true;
            }

            // User can manage if they have manage flows permission
            if ($user->hasPermissionTo('manage_approval_flows')) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check manage approval flows permission', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user can view approval statistics.
     *
     * @param User $user The user
     * @param string|null $documentType Optional document type filter
     * @return bool True if user can view statistics
     */
    public function canViewApprovalStatistics(User $user, ?string $documentType = null): bool
    {
        try {
            // User can view if they have admin permissions
            if ($this->hasAdminPermissions($user)) {
                return true;
            }

            // User can view if they have view statistics permission
            if ($user->hasPermissionTo('view_approval_statistics')) {
                return true;
            }

            // User can view if they have view permission for specific document type
            if ($documentType && $this->hasDocumentTypePermission($user, $documentType, 'view_statistics')) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check view approval statistics permission', [
                'user_id' => $user->id,
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user is an approver for a specific approval.
     *
     * @param User $user The user
     * @param DocumentApproval $approval The document approval
     * @return bool True if user is an approver
     */
    public function isUserApprover(User $user, DocumentApproval $approval): bool
    {
        try {
            if (!$approval->currentStage) {
                return false;
            }

            foreach ($approval->currentStage->approvers as $approver) {
                if ($approver->canUserApprove($user)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check if user is approver', [
                'user_id' => $user->id,
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user has admin permissions.
     *
     * @param User $user The user
     * @return bool True if user has admin permissions
     */
    public function hasAdminPermissions(User $user): bool
    {
        try {
            // Check if user has admin role
            if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
                return true;
            }

            // Check if user has admin permissions
            if ($user->hasPermissionTo('approval_admin')) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check admin permissions', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user has permission for specific document type.
     *
     * @param User $user The user
     * @param string $documentType The document type
     * @param string $permission The permission type
     * @return bool True if user has permission
     */
    public function hasDocumentTypePermission(User $user, string $documentType, string $permission): bool
    {
        try {
            $permissionName = "{$documentType}_{$permission}";
            return $user->hasPermissionTo($permissionName);
        } catch (\Exception $e) {
            Log::error('Failed to check document type permission', [
                'user_id' => $user->id,
                'document_type' => $documentType,
                'permission' => $permission,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get user's approval permissions.
     *
     * @param User $user The user
     * @return array The user's approval permissions
     */
    public function getUserApprovalPermissions(User $user): array
    {
        try {
            $permissions = [
                'can_manage_flows' => $this->canManageApprovalFlows($user),
                'can_view_statistics' => $this->canViewApprovalStatistics($user),
                'is_admin' => $this->hasAdminPermissions($user),
                'document_type_permissions' => [],
            ];

            // Get document type permissions
            $documentTypes = ['officialtravel', 'recruitment_request', 'employee_registration'];
            foreach ($documentTypes as $documentType) {
                $permissions['document_type_permissions'][$documentType] = [
                    'view' => $this->hasDocumentTypePermission($user, $documentType, 'view'),
                    'approve' => $this->hasDocumentTypePermission($user, $documentType, 'approve'),
                    'reject' => $this->hasDocumentTypePermission($user, $documentType, 'reject'),
                    'cancel' => $this->hasDocumentTypePermission($user, $documentType, 'cancel'),
                    'view_statistics' => $this->hasDocumentTypePermission($user, $documentType, 'view_statistics'),
                ];
            }

            return $permissions;
        } catch (\Exception $e) {
            Log::error('Failed to get user approval permissions', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Create approval permissions for a document type.
     *
     * @param string $documentType The document type
     * @return bool True if permissions were created
     */
    public function createDocumentTypePermissions(string $documentType): bool
    {
        try {
            $permissions = [
                "{$documentType}_view",
                "{$documentType}_approve",
                "{$documentType}_reject",
                "{$documentType}_cancel",
                "{$documentType}_view_statistics",
            ];

            foreach ($permissions as $permissionName) {
                Permission::findOrCreate($permissionName);
            }

            Log::info('Document type permissions created', [
                'document_type' => $documentType,
                'permissions' => $permissions,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create document type permissions', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Assign approval permissions to role.
     *
     * @param string $roleName The role name
     * @param array $permissions The permissions to assign
     * @return bool True if permissions were assigned
     */
    public function assignApprovalPermissionsToRole(string $roleName, array $permissions): bool
    {
        try {
            $role = Role::findByName($roleName);
            if (!$role) {
                Log::error('Role not found', ['role_name' => $roleName]);
                return false;
            }

            foreach ($permissions as $permission) {
                $permissionModel = Permission::findByName($permission);
                if ($permissionModel) {
                    $role->givePermissionTo($permissionModel);
                }
            }

            Log::info('Approval permissions assigned to role', [
                'role_name' => $roleName,
                'permissions' => $permissions,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to assign approval permissions to role', [
                'role_name' => $roleName,
                'permissions' => $permissions,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get users who can approve a specific document type.
     *
     * @param string $documentType The document type
     * @return \Illuminate\Database\Eloquent\Collection The users
     */
    public function getUsersWhoCanApprove(string $documentType)
    {
        try {
            $permissionName = "{$documentType}_approve";

            return User::permission($permissionName)->get();
        } catch (\Exception $e) {
            Log::error('Failed to get users who can approve', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    /**
     * Get users who can view a specific document type.
     *
     * @param string $documentType The document type
     * @return \Illuminate\Database\Eloquent\Collection The users
     */
    public function getUsersWhoCanView(string $documentType)
    {
        try {
            $permissionName = "{$documentType}_view";

            return User::permission($permissionName)->get();
        } catch (\Exception $e) {
            Log::error('Failed to get users who can view', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    /**
     * Check if user can delegate approval.
     *
     * @param User $user The user
     * @param DocumentApproval $approval The document approval
     * @param User $delegateTo The user to delegate to
     * @return bool True if user can delegate
     */
    public function canDelegateApproval(User $user, DocumentApproval $approval, User $delegateTo): bool
    {
        try {
            // User must be an approver
            if (!$this->isUserApprover($user, $approval)) {
                return false;
            }

            // Delegate must have approve permission for this document type
            if (!$this->hasDocumentTypePermission($delegateTo, $approval->document_type, 'approve')) {
                return false;
            }

            // User cannot delegate to themselves
            if ($user->id === $delegateTo->id) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to check delegate approval permission', [
                'user_id' => $user->id,
                'delegate_to_id' => $delegateTo->id,
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user can forward approval.
     *
     * @param User $user The user
     * @param DocumentApproval $approval The document approval
     * @param User $forwardTo The user to forward to
     * @return bool True if user can forward
     */
    public function canForwardApproval(User $user, DocumentApproval $approval, User $forwardTo): bool
    {
        try {
            // User must be an approver
            if (!$this->isUserApprover($user, $approval)) {
                return false;
            }

            // Forward to user must have approve permission for this document type
            if (!$this->hasDocumentTypePermission($forwardTo, $approval->document_type, 'approve')) {
                return false;
            }

            // User cannot forward to themselves
            if ($user->id === $forwardTo->id) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to check forward approval permission', [
                'user_id' => $user->id,
                'forward_to_id' => $forwardTo->id,
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user can approve by approval ID.
     *
     * @param int $approvalId The approval ID
     * @param User $user The user
     * @return bool True if user can approve
     */
    public function canUserApprove(int $approvalId, User $user): bool
    {
        try {
            $approval = DocumentApproval::find($approvalId);
            if (!$approval) {
                return false;
            }

            return $this->canApprove($user, $approval);
        } catch (\Exception $e) {
            Log::error('Failed to check user approve permission', [
                'approval_id' => $approvalId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user can view approval by approval ID.
     *
     * @param int $approvalId The approval ID
     * @param User $user The user
     * @return bool True if user can view
     */
    public function canUserViewApproval(int $approvalId, User $user): bool
    {
        try {
            $approval = DocumentApproval::find($approvalId);
            if (!$approval) {
                return false;
            }

            return $this->canViewApproval($user, $approval);
        } catch (\Exception $e) {
            Log::error('Failed to check user view approval permission', [
                'approval_id' => $approvalId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user can manage approval flows.
     *
     * @param User $user The user
     * @return bool True if user can manage flows
     */
    public function canUserManageApprovalFlows(User $user): bool
    {
        try {
            return $this->canManageApprovalFlows($user);
        } catch (\Exception $e) {
            Log::error('Failed to check user manage flows permission', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get approvers for document type.
     *
     * @param string $documentType The document type
     * @return array The approvers
     */
    public function getApproversForDocumentType(string $documentType): array
    {
        try {
            $approvers = [];

            // Get users who can approve this document type
            $users = $this->getUsersWhoCanApprove($documentType);

            foreach ($users as $user) {
                $approvers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ];
            }

            return $approvers;
        } catch (\Exception $e) {
            Log::error('Failed to get approvers for document type', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get user pending approval IDs.
     *
     * @param User $user The user
     * @return array The approval IDs
     */
    public function getUserPendingApprovalIds(User $user): array
    {
        try {
            $approvalIds = [];

            // Get approvals where user is an approver
            $approvals = DocumentApproval::where('overall_status', 'pending')
                ->whereHas('flow.stages.approvers', function ($query) use ($user) {
                    $query->where('approver_id', $user->id);
                })
                ->pluck('id')
                ->toArray();

            return $approvalIds;
        } catch (\Exception $e) {
            Log::error('Failed to get user pending approval IDs', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
