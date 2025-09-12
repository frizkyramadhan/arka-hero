<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalStageDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_stage_id',
        'project_id',
        'department_id',
        'request_reason'
    ];

    // Request reasons to match recruitment_requests table
    public const REQUEST_REASONS = [
        'replacement_resign',
        'replacement_promotion',
        'additional_workplan',
        'other'
    ];

    // Relationships
    public function approvalStage()
    {
        return $this->belongsTo(ApprovalStage::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Scopes
    public function scopeForProjectAndDepartment($query, $projectId, $departmentId)
    {
        return $query->where('project_id', $projectId)
            ->where('department_id', $departmentId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
