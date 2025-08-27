<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Department;
use App\Models\Project;

class ApprovalStage extends Model
{
    use HasFactory;

    // protected $guarded = [];

    protected $fillable = [
        'approver_id',
        'document_type',
        'approval_order'
    ];

    protected $casts = [
        'approval_order' => 'integer'
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function details()
    {
        return $this->hasMany(ApprovalStageDetail::class);
    }

    // Scope untuk dokumen tertentu
    public function scopeForDocument($query, $documentType)
    {
        return $query->where('document_type', $documentType);
    }

    // Scope untuk project dan department tertentu (via details)
    public function scopeForProjectAndDepartment($query, $projectId, $departmentId)
    {
        return $query->whereHas('details', function($query) use ($projectId, $departmentId) {
            $query->where('project_id', $projectId)
                  ->where('department_id', $departmentId);
        });
    }
}
