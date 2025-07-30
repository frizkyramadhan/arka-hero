<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalPlan extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id')->withDefault([
            'name' => 'Not Available',
        ]);
    }

    public function officialtravel()
    {
        return $this->belongsTo(Officialtravel::class, 'document_id', 'id');
    }

    public function recruitment_request()
    {
        return $this->belongsTo(RecruitmentRequest::class, 'document_id', 'id');
    }
}
