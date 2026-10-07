<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class RecruitmentRequestClosure extends Model
{
    use Uuids;

    protected $fillable = [
        'recruitment_request_id',
        'close_reason',
        'close_notes',
        'closed_by',
        'closed_at',
        'reopened_by',
        'reopened_at',
        'reopen_reason',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function recruitmentRequest()
    {
        return $this->belongsTo(RecruitmentRequest::class, 'recruitment_request_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy()
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function getReasonLabelAttribute(): string
    {
        return RecruitmentRequest::CLOSE_REASONS[$this->close_reason] ?? ucfirst($this->close_reason);
    }
}
