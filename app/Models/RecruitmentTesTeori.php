<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentTesTeori extends Model
{
    protected $table = 'recruitment_tes_teori';

    protected $fillable = [
        'session_id',
        'score',
        'result',
        'notes',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'score' => 'decimal:2',
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
}
