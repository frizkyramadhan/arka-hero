<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalPlan extends Model
{
    use HasFactory;

    // protected $guarded = [];

    protected $fillable = [
        'document_id',
        'document_type',
        'approver_id',
        'status',
        'remarks',
        'is_open',
        'is_read',
        'approval_order'
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'is_read' => 'boolean',
        'approval_order' => 'integer'
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id')->withDefault([
            'name' => 'Not Available',
        ]);
    }

    // Relationship ke ApprovalStage untuk mendapatkan order dan sequential info
    public function approvalStage()
    {
        return $this->belongsTo(\App\Models\ApprovalStage::class, 'approver_id', 'approver_id')
            ->where('document_type', $this->document_type);
    }

    public function officialtravel()
    {
        return $this->belongsTo(Officialtravel::class, 'document_id', 'id');
    }

    public function recruitment_request()
    {
        return $this->belongsTo(RecruitmentRequest::class, 'document_id', 'id');
    }

    // Check if this approval can be processed (sequential validation)
    public function canBeProcessed()
    {
        // Jika tidak ada approval stage atau bukan sequential, langsung bisa diproses
        if (!$this->approvalStage || !$this->approvalStage->is_sequential) {
            return true;
        }

        // Check if previous approvals are completed
        $previousApprovals = static::where('document_id', $this->document_id)
            ->where('document_type', $this->document_type)
            ->where('approval_order', '<', $this->approval_order)
            ->where('status', 1) // Approved
            ->count();

        $expectedPrevious = $this->approval_order - 1;

        return $previousApprovals >= $expectedPrevious;
    }

    // Get next approval in sequence
    public function getNextApproval()
    {
        return static::where('document_id', $this->document_id)
            ->where('document_type', $this->document_type)
            ->where('approval_order', $this->approval_order + 1)
            ->first();
    }

    // Check if this is the last approval in sequence
    public function isLastApproval()
    {
        $maxOrder = static::where('document_id', $this->document_id)
            ->where('document_type', $this->document_type)
            ->max('approval_order');

        return $this->approval_order >= $maxOrder;
    }
}
