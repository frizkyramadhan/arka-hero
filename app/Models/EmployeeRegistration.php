<?php

namespace App\Models;

use App\Traits\Uuids;
use App\Traits\HasApproval;
use App\Models\ApprovalFlow;
use App\Models\DocumentApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class EmployeeRegistration extends Model
{
    use HasFactory, Uuids, HasApproval;

    protected $guarded = [];

    protected $casts = [
        'personal_data' => 'array',
        'document_files' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function token()
    {
        return $this->belongsTo(EmployeeRegistrationToken::class, 'token_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents()
    {
        return $this->hasMany(RegistrationDocument::class, 'registration_id');
    }

    // Approval System Integration
    public function approvalFlow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    /**
     * Get approval document type for approval system
     *
     * @return string
     */
    public function getApprovalDocumentType(): string
    {
        return 'employee_registration';
    }

    /**
     * Get approval document ID
     *
     * @return string
     */
    public function getApprovalDocumentId(): string
    {
        return $this->id;
    }

    /**
     * Get approval metadata
     *
     * @return array
     */
    public function getApprovalMetadata(): array
    {
        $personalData = $this->personal_data ?? [];

        return [
            'registration_id' => $this->id,
            'applicant_name' => $personalData['fullname'] ?? 'Unknown',
            'email' => $personalData['email'] ?? 'Unknown',
            'phone' => $personalData['phone'] ?? 'Unknown',
            'position_applied' => $personalData['position'] ?? 'Unknown',
            'department_applied' => $personalData['department'] ?? 'Unknown',
            'submitted_at' => $this->created_at->toISOString(),
            'status' => $this->status,
            'created_by' => $this->created_by
        ];
    }

    /**
     * Check if document can be approved
     *
     * @return bool
     */
    public function canBeApproved(): bool
    {
        // Check if document is in submitted status
        return in_array($this->status, ['submitted', 'draft']);
    }

    /**
     * Handle approval completion
     */
    public function onApprovalCompleted(): void
    {
        // Update existing approval status to approved
        $this->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => 'Approved through approval system'
        ]);

        // Log the approval completion
        Log::info('Employee Registration approval completed', [
            'registration_id' => $this->id,
            'applicant_name' => $this->personal_data['fullname'] ?? 'Unknown',
            'approved_at' => now()
        ]);
    }

    /**
     * Handle approval rejection
     */
    public function onApprovalRejected(): void
    {
        // Update existing approval status to rejected
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => 'Rejected through approval system'
        ]);

        // Log the approval rejection
        Log::info('Employee Registration approval rejected', [
            'registration_id' => $this->id,
            'applicant_name' => $this->personal_data['fullname'] ?? 'Unknown',
            'rejected_at' => now()
        ]);
    }

    /**
     * Get current approval status
     *
     * @return string
     */
    public function getCurrentApprovalStatus(): string
    {
        if ($this->status === 'rejected') {
            return 'rejected';
        }

        if ($this->status === 'approved') {
            return 'approved';
        }

        if ($this->status === 'submitted') {
            return 'pending_review';
        }

        return 'draft';
    }

    /**
     * Get approval progress percentage
     *
     * @return int
     */
    public function getApprovalProgress(): int
    {
        if ($this->status === 'approved') {
            return 100;
        }

        if ($this->status === 'rejected') {
            return 100;
        }

        if ($this->status === 'submitted') {
            return 50;
        }

        return 0;
    }

    /**
     * Check if approval is overdue
     *
     * @return bool
     */
    public function isApprovalOverdue(): bool
    {
        $overdueHours = 48; // 2 days

        if ($this->status === 'submitted' && $this->created_at->diffInHours(now()) > $overdueHours) {
            return true;
        }

        return false;
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }
}
