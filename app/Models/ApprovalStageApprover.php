<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class ApprovalStageApprover extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approval_stage_id',
        'approver_type',
        'approver_id',
        'is_backup',
        'approval_condition',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_backup' => 'boolean',
        'approval_condition' => 'array',
    ];

    /**
     * Get the approval stage that owns this approver.
     */
    public function approvalStage(): BelongsTo
    {
        return $this->belongsTo(ApprovalStage::class);
    }

    /**
     * Get the user approver (if approver_type is 'user').
     */
    public function userApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get the role approver (if approver_type is 'role').
     */
    public function roleApprover(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approver_id');
    }

    /**
     * Get the department approver (if approver_type is 'department').
     */
    public function departmentApprover(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'approver_id');
    }

    /**
     * Alias for userApprover for compatibility with views.
     */
    public function approverUser(): BelongsTo
    {
        return $this->userApprover();
    }

    /**
     * Alias for roleApprover for compatibility with views.
     */
    public function approverRole(): BelongsTo
    {
        return $this->roleApprover();
    }

    /**
     * Alias for departmentApprover for compatibility with views.
     */
    public function approverDepartment(): BelongsTo
    {
        return $this->departmentApprover();
    }

    /**
     * Alias for userApprover for compatibility with views.
     */
    public function approver(): BelongsTo
    {
        return $this->userApprover();
    }

    /**
     * Get the actual approver based on the approver type.
     */
    public function getApprover()
    {
        switch ($this->approver_type) {
            case 'user':
                return $this->userApprover;
            case 'role':
                return $this->roleApprover;
            case 'department':
                return $this->departmentApprover;
            default:
                return null;
        }
    }

    /**
     * Get users who can approve based on this approver configuration.
     */
    public function getApproverUsers()
    {
        switch ($this->approver_type) {
            case 'user':
                return collect([$this->userApprover])->filter();
            case 'role':
                return $this->roleApprover ? $this->roleApprover->users : collect();
            case 'department':
                return $this->departmentApprover ? $this->departmentApprover->users : collect();
            default:
                return collect();
        }
    }

    /**
     * Check if a specific user can approve based on this approver configuration.
     */
    public function canUserApprove(User $user): bool
    {
        switch ($this->approver_type) {
            case 'user':
                return $this->approver_id === $user->id;
            case 'role':
                return $this->roleApprover && $user->hasRole($this->roleApprover);
            case 'department':
                return $this->departmentApprover && $user->department_id === $this->approver_id;
            default:
                return false;
        }
    }

    /**
     * Check if approval conditions are met for this approver.
     */
    public function conditionsMet($documentData = []): bool
    {
        if (!$this->approval_condition) {
            return true;
        }

        // Implementation for conditional approval logic
        // This would be implemented based on specific business rules
        return true;
    }

    /**
     * Scope a query to only include primary approvers.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_backup', false);
    }

    /**
     * Scope a query to only include backup approvers.
     */
    public function scopeBackup($query)
    {
        return $query->where('is_backup', true);
    }

    /**
     * Scope a query to only include user approvers.
     */
    public function scopeUserApprovers($query)
    {
        return $query->where('approver_type', 'user');
    }

    /**
     * Scope a query to only include role approvers.
     */
    public function scopeRoleApprovers($query)
    {
        return $query->where('approver_type', 'role');
    }

    /**
     * Scope a query to only include department approvers.
     */
    public function scopeDepartmentApprovers($query)
    {
        return $query->where('approver_type', 'department');
    }
}
