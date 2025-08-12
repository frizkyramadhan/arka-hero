<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentMcu extends Model
{
    protected $table = 'recruitment_mcu';

    protected $fillable = [
        'session_id',
        'result',
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

    // Result options
    public const RESULTS = [
        'fit',
        'unfit',
        'follow_up'
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
    public function scopeFit($query)
    {
        return $query->where('result', 'fit');
    }

    public function scopeUnfit($query)
    {
        return $query->where('result', 'unfit');
    }

    public function scopeFollowUp($query)
    {
        return $query->where('result', 'follow_up');
    }

    /**
     * Accessors & Mutators
     */
    public function getIsFitAttribute()
    {
        return $this->result === 'fit';
    }

    public function getIsUnfitAttribute()
    {
        return $this->result === 'unfit';
    }

    public function getIsFollowUpAttribute()
    {
        return $this->result === 'follow_up';
    }

    public function getResultLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->result));
    }
}
