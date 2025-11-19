<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class RecruitmentCandidate extends Model
{
    use Uuids;

    protected $fillable = [
        'candidate_number',
        'fullname',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'education_level',
        'experience_years',
        'position_applied',
        'remarks',
        'cv_file_path',
        'skills',
        'previous_companies',
        'certifications',
        'current_salary',
        'expected_salary',
        'global_status',
        'created_by',
        'updated_by',
        'blacklist_reason',
        'blacklisted_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'experience_years' => 'integer',
        'current_salary' => 'decimal:2',
        'expected_salary' => 'decimal:2',
    ];

    protected $dates = [
        'date_of_birth',
        'created_at',
        'updated_at',
    ];

    // Enums for validation
    public const GLOBAL_STATUSES = ['available', 'in_process', 'hired', 'blacklisted'];

    /**
     * Relationships
     */

    // Core relationship: Candidate has many sessions
    public function sessions()
    {
        return $this->hasMany(RecruitmentSession::class, 'candidate_id');
    }

    // Through sessions, get all FPTKs candidate applied to
    public function fptks()
    {
        return $this->belongsToMany(RecruitmentRequest::class, 'recruitment_sessions', 'candidate_id', 'fptk_id')
            ->withPivot('id', 'session_number', 'current_stage', 'status', 'applied_date')
            ->withTimestamps();
    }

    // Get active (in_process) sessions
    public function activeSessions()
    {
        return $this->hasMany(RecruitmentSession::class, 'candidate_id')
            ->where('status', 'in_process');
    }

    // Get successful applications (hired)
    public function successfulApplications()
    {
        return $this->hasMany(RecruitmentSession::class, 'candidate_id')
            ->where('status', 'hired');
    }

    // Get all documents across all sessions
    public function documents()
    {
        return $this->hasManyThrough(
            RecruitmentDocument::class,
            RecruitmentSession::class,
            'candidate_id', // Foreign key on RecruitmentSession
            'session_id',   // Foreign key on RecruitmentDocument
            'id',           // Local key on RecruitmentCandidate
            'id'            // Local key on RecruitmentSession
        );
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('global_status', 'available');
    }

    public function scopeInProcess($query)
    {
        return $query->where('global_status', 'in_process');
    }

    public function scopeHired($query)
    {
        return $query->where('global_status', 'hired');
    }

    public function scopeBlacklisted($query)
    {
        return $query->where('global_status', 'blacklisted');
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeByEducationLevel($query, $level)
    {
        return $query->where('education_level', 'like', '%' . $level . '%');
    }

    public function scopeByExperienceYears($query, $minYears, $maxYears = null)
    {
        $query->where('experience_years', '>=', $minYears);
        if ($maxYears) {
            $query->where('experience_years', '<=', $maxYears);
        }
        return $query;
    }

    public function scopeByExpectedSalary($query, $minSalary, $maxSalary = null)
    {
        $query->where('expected_salary', '>=', $minSalary);
        if ($maxSalary) {
            $query->where('expected_salary', '<=', $maxSalary);
        }
        return $query;
    }

    /**
     * Accessors & Mutators
     */
    public function getFullnameAttribute($value)
    {
        return ucwords(strtolower($value));
    }

    public function getIsAvailableAttribute()
    {
        return $this->global_status === 'available';
    }

    public function getApplicationsCountAttribute()
    {
        return $this->sessions()->count();
    }

    public function getSuccessRateAttribute()
    {
        $totalApplications = $this->sessions()->count();
        if ($totalApplications === 0) return 0;

        $successfulApplications = $this->sessions()->where('status', 'hired')->count();
        return round(($successfulApplications / $totalApplications) * 100, 2);
    }

    public function getLatestApplicationAttribute()
    {
        return $this->sessions()->orderBy('created_at', 'desc')->first();
    }

    public function getActiveApplicationsCountAttribute()
    {
        return $this->activeSessions()->count();
    }

    public function getHasActiveCvAttribute()
    {
        return !empty($this->cv_file_path) && file_exists(storage_path('app/' . $this->cv_file_path));
    }

    /**
     * Business Logic Methods
     */
    public function canApplyToFptk($fptkId)
    {
        // Check if candidate is available
        if ($this->global_status !== 'available') {
            return false;
        }

        // Check if already applied to this FPTK
        $existingSession = $this->sessions()
            ->where('fptk_id', $fptkId)
            ->exists();

        return !$existingSession;
    }

    public function applyToFptk($fptkId, $source = 'website')
    {
        if (!$this->canApplyToFptk($fptkId)) {
            return false;
        }

        // Create session
        $session = RecruitmentSession::create([
            'session_number' => RecruitmentSession::generateSessionNumber(),
            'fptk_id' => $fptkId,
            'candidate_id' => $this->id,
            'applied_date' => now()->toDateString(),
            'source' => $source,
            'current_stage' => 'cv_review',
            'stage_status' => 'pending',
            'stage_started_at' => now(),
            'overall_progress' => 10, // CV Review = 10%
            'status' => 'in_process',
        ]);

        // Update candidate status to in_process
        $this->update(['global_status' => 'in_process']);

        return $session;
    }

    public function updateGlobalStatus()
    {
        $activeSessions = $this->activeSessions()->count();
        $hiredSessions = $this->successfulApplications()->count();

        if ($hiredSessions > 0) {
            $this->update(['global_status' => 'hired']);
        } elseif ($activeSessions > 0) {
            $this->update(['global_status' => 'in_process']);
        } else {
            $this->update(['global_status' => 'available']);
        }
    }

    public function blacklist($reason = null)
    {
        $this->update(['global_status' => 'blacklisted']);

        // Cancel all active sessions
        $this->activeSessions()->update([
            'status' => 'cancelled',
            'final_decision_date' => now(),
            'final_decision_notes' => 'Candidate blacklisted. Reason: ' . ($reason ?? 'No reason provided'),
        ]);
    }

    public function removeFromBlacklist()
    {
        $this->update(['global_status' => 'available']);
    }

    /**
     * Generate unique candidate number
     */
    public static function generateCandidateNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastNumber = static::whereRaw('YEAR(created_at) = ? AND MONTH(created_at) = ?', [$year, $month])
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($lastNumber && preg_match('/CAND\/\d+\/\d+\/(\d+)$/', $lastNumber->candidate_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return sprintf('CAND/%d/%02d/%04d', $year, $month, $sequence);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->candidate_number)) {
                $model->candidate_number = static::generateCandidateNumber();
            }
        });
    }
}
