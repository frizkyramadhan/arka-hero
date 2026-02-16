<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Traits\Uuids;
use App\Traits\HasLetterNumber;



class RecruitmentRequest extends Model
{
    use Uuids, HasLetterNumber;

    protected $fillable = [
        'letter_number_id',
        'letter_number',
        'request_number',
        'department_id',
        'project_id',
        'position_id',
        'level_id',
        'required_qty',
        'required_date',
        'employment_type',
        'request_reason',
        'other_reason',
        'job_description',
        'required_gender',
        'required_age_min',
        'required_age_max',
        'required_marital_status',
        'required_education',
        'required_skills',
        'required_experience',
        'required_physical',
        'required_mental',
        'other_requirements',
        'requires_theory_test',
        'created_by',
        'status',
        'submitted_by_user',
        'positions_filled',
        'submit_at',
        'approved_at',
        // HR Acknowledgment fields
        'known_by',
        'known_status',
        'known_at',
        'known_remark',
        'known_timestamps',
        // Project Manager Approval fields
        'approved_by_pm',
        'pm_approval_status',
        'pm_approved_at',
        'pm_approval_remark',
        'pm_approval_timestamps',
        // Director Approval fields
        'approved_by_director',
        'director_approval_status',
        'director_approved_at',
        'director_approval_remark',
        'director_approval_timestamps',
        'manual_approvers',
    ];

    protected $casts = [
        'required_date' => 'date',
        'positions_filled' => 'integer',
        'required_qty' => 'integer',
        'required_age_min' => 'integer',
        'required_age_max' => 'integer',
        'submitted_by_user' => 'boolean',
        'submit_at' => 'datetime',
        'approved_at' => 'datetime',
        'manual_approvers' => 'array',
        // HR Acknowledgment casts
        'known_at' => 'datetime',
        'known_timestamps' => 'datetime',
        // Project Manager Approval casts
        'pm_approved_at' => 'datetime',
        'pm_approval_timestamps' => 'datetime',
        // Director Approval casts
        'director_approved_at' => 'datetime',
        'director_approval_timestamps' => 'datetime',
    ];

    protected $dates = [
        'required_date',
        'created_at',
        'updated_at',
        'deleted_at',
        'approved_at',
        // HR Acknowledgment dates
        'known_at',
        'known_timestamps',
        // Project Manager Approval dates
        'pm_approved_at',
        'pm_approval_timestamps',
        // Director Approval dates
        'director_approved_at',
        'director_approval_timestamps',
    ];

    // Enums for validation
    public const EMPLOYMENT_TYPES = ['pkwtt', 'pkwt', 'harian', 'magang'];
    public const REQUEST_REASONS = [
        'replacement_resign',
        'replacement_promotion',
        'additional_workplan',
        'other'
    ];
    public const GENDERS = ['male', 'female', 'any'];
    public const MARITAL_STATUSES = ['single', 'married', 'any'];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CLOSED = 'closed';
    public const STATUSES = ['draft', 'submitted', 'approved', 'rejected', 'cancelled', 'closed'];

    /**
     * Get status options for forms
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    /**
     * Relationships
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acknowledger()
    {
        return $this->belongsTo(User::class, 'known_by');
    }

    public function projectManagerApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_pm');
    }

    public function directorApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_director');
    }




    // Core relationship: FPTK has many sessions
    public function sessions()
    {
        return $this->hasMany(RecruitmentSession::class, 'fptk_id');
    }

    // Through sessions, get all candidates who applied
    public function candidates()
    {
        return $this->belongsToMany(RecruitmentCandidate::class, 'recruitment_sessions', 'fptk_id', 'candidate_id')
            ->withPivot('id', 'session_number', 'current_stage', 'status', 'applied_date')
            ->withTimestamps();
    }

    // Get active (in_process) sessions
    public function activeSessions()
    {
        return $this->hasMany(RecruitmentSession::class, 'fptk_id')
            ->where('status', 'in_process');
    }

    // Get hired candidates
    public function hiredCandidates()
    {
        return $this->belongsToMany(RecruitmentCandidate::class, 'recruitment_sessions', 'fptk_id', 'candidate_id')
            ->wherePivot('status', 'hired')
            ->withPivot('id', 'session_number', 'final_decision_date')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['submitted', 'approved']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'approved')
            ->whereRaw('positions_filled < required_qty');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    /**
     * Accessors & Mutators
     */
    public function getIsOpenAttribute()
    {
        return $this->status === 'approved' && $this->positions_filled < $this->required_qty;
    }

    public function getRemainingPositionsAttribute()
    {
        return max(0, $this->required_qty - $this->positions_filled);
    }

    public function getSuccessRateAttribute()
    {
        $totalSessions = $this->sessions()->count();
        if ($totalSessions === 0) return 0;

        $hiredSessions = $this->sessions()->where('status', 'hired')->count();
        return round(($hiredSessions / $totalSessions) * 100, 2);
    }

    public function getApplicationsCountAttribute()
    {
        return $this->sessions()->count();
    }

    /**
     * Business Logic Methods
     */
    public function canReceiveApplications()
    {
        // Allow applications as long as FPTK is approved, regardless of required_qty and positions_filled
        return $this->status === 'approved';
    }

    public function incrementPositionsFilled()
    {
        $this->increment('positions_filled');

        // Auto-close if all positions filled
        if ($this->positions_filled >= $this->required_qty) {
            $this->update(['status' => 'closed']);
        }
    }

    public function decrementPositionsFilled()
    {
        if ($this->positions_filled > 0) {
            $this->decrement('positions_filled');

            // Reopen if was closed and now has available positions
            if ($this->status === 'closed' && $this->positions_filled < $this->required_qty) {
                $this->update(['status' => 'approved']);
            }
        }
    }

    public function approve($approverId, $notes = null)
    {
        // Auto-assign letter number jika belum ada
        if (!$this->hasLetterNumber()) {
            try {
                $this->assignFPTKLetterNumber();
            } catch (\Exception $e) {
                Log::error('Failed to assign letter number to FPTK: ' . $e->getMessage());
            }
        }

        $this->update([
            'status' => 'approved',
        ]);
    }

    public function reject($approverId, $reason)
    {
        $this->update([
            'status' => 'rejected',
        ]);
    }

    /**
     * Implementation of HasLetterNumber trait
     */
    protected function getDocumentType(): string
    {
        return 'recruitment_request';
    }

    /**
     * Auto-assign letter number from FPTK category
     *
     * @return bool
     */
    public function assignFPTKLetterNumber()
    {
        // Cari category FPTK
        $fptkCategory = LetterCategory::where('category_code', 'FPTK')
            ->where('is_active', 1)
            ->first();

        if (!$fptkCategory) {
            throw new \Exception('FPTK Letter Category not found or inactive');
        }

        // Buat letter number baru
        $letterNumber = new LetterNumber([
            'letter_category_id' => $fptkCategory->id,
            'letter_date' => now(),
            'subject_id' => null, // FPTK tidak memerlukan subject
            'administration_id' => null, // FPTK tidak terkait dengan employee
            'project_id' => $this->project_id,
            'user_id' => $this->created_by,
            'is_active' => 1,
        ]);

        $letterNumber->save();

        // Assign ke FPTK
        return $this->assignLetterNumber($letterNumber->id);
    }

    /**
     * Get formatted FPTK letter number
     *
     * @return string
     */
    public function getFPTKLetterNumber()
    {
        if ($this->hasLetterNumber()) {
            return $this->letter_number;
        }

        return $this->request_number ?: 'No Number';
    }

    /**
     * Check if FPTK has proper letter number assignment
     *
     * @return bool
     */
    public function hasValidLetterNumber()
    {
        return $this->hasLetterNumber() &&
            $this->letterNumber &&
            $this->letterNumber->category &&
            $this->letterNumber->category->category_code === 'FPTK';
    }

    /**
     * Get letter number info for display
     *
     * @return array
     */
    public function getLetterNumberInfo()
    {
        if (!$this->hasLetterNumber()) {
            return [
                'number' => $this->request_number ?: 'No Number',
                'status' => 'no_letter_number',
                'category' => 'Manual Request Number',
                'date' => $this->created_at->format('Y-m-d'),
            ];
        }

        $letterNumber = $this->letterNumber;
        return [
            'number' => $this->letter_number,
            'status' => $letterNumber->status,
            'category' => $letterNumber->category->category_name,
            'date' => $letterNumber->letter_date->format('Y-m-d'),
            'sequence' => $letterNumber->sequence_number,
        ];
    }



    /**
     * Generate request number (fallback jika tidak ada letter number)
     */
    public static function generateRequestNumber()
    {
        $year = date('Y');
        $month = date('m');

        // Get last sequence for this year
        $lastRequest = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($lastRequest && $lastRequest->request_number) {
            // Extract sequence from request number
            preg_match('/(\d+)/', $lastRequest->request_number, $matches);
            if ($matches) {
                $sequence = (int)$matches[1] + 1;
            }
        }

        return sprintf('REQ/%04d/FPTK/%02d/%s', $sequence, $month, $year);
    }

    /**
     * Check if this FPTK requires theory test
     *
     * @return bool
     */
    public function requiresTheoryTest(): bool
    {
        return $this->requires_theory_test;
    }

    /**
     * Boot method
     */
    public function approval_plans()
    {
        return $this->hasMany(ApprovalPlan::class, 'document_id', 'id');
    }

    // Get manual approvers as User collection
    public function getManualApprovers()
    {
        if (empty($this->manual_approvers)) {
            return collect();
        }

        return User::whereIn('id', $this->manual_approvers)->get();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate request number sebagai fallback
            if (empty($model->request_number)) {
                $model->request_number = static::generateRequestNumber();
            }
        });

        static::created(function ($model) {
            // Auto-assign letter number setelah FPTK dibuat
            if ($model->status === 'submitted' || $model->status === 'approved') {
                try {
                    $model->assignFPTKLetterNumber();
                } catch (\Exception $e) {
                    // Log error tapi tidak menghentikan proses
                    Log::error('Failed to auto-assign letter number to FPTK: ' . $e->getMessage());
                }
            }
        });
    }
}
