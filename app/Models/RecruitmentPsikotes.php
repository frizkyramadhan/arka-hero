<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentPsikotes extends Model
{
    protected $fillable = [
        'session_id',
        'online_score',
        'offline_score',
        'result',
        'notes',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'online_score' => 'decimal:2',
        'offline_score' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    protected $dates = [
        'reviewed_at',
        'created_at',
        'updated_at'
    ];

    // Result options
    public const RESULTS = [
        'pass',
        'fail'
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
    public function scopePassed($query)
    {
        return $query->where('result', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('result', 'fail');
    }

    /**
     * Accessors & Mutators
     */
    public function getIsPassedAttribute()
    {
        return $this->result === 'pass';
    }

    public function getResultLabelAttribute()
    {
        return $this->result === 'pass' ? 'Pass' : 'Fail';
    }

    public function getOverallScoreAttribute()
    {
        $scores = [];
        if ($this->online_score !== null) $scores[] = $this->online_score;
        if ($this->offline_score !== null) $scores[] = $this->offline_score;

        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }
}
