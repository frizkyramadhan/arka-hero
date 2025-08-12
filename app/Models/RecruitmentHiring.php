<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentHiring extends Model
{
    protected $table = 'recruitment_hiring';

    protected $fillable = [
        'session_id',
        'agreement_type',
        'letter_number',
        'notes',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected $dates = [
        'reviewed_at',
        'created_at',
        'updated_at'
    ];

    // Agreement type options
    public const AGREEMENT_TYPES = [
        'pkwt',
        'pkwtt'
    ];

    /**
     * Relationships
     */
    public function session()
    {
        return $this->belongsTo(RecruitmentSession::class, 'session_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scopes
     */
    public function scopePkwt($query)
    {
        return $query->where('agreement_type', 'pkwt');
    }

    public function scopePkwtt($query)
    {
        return $query->where('agreement_type', 'pkwtt');
    }

    /**
     * Accessors & Mutators
     */
    public function getAgreementTypeLabelAttribute()
    {
        return strtoupper($this->agreement_type);
    }

    public function getIsPkwtAttribute()
    {
        return $this->agreement_type === 'pkwt';
    }

    public function getIsPkwttAttribute()
    {
        return $this->agreement_type === 'pkwtt';
    }
}
