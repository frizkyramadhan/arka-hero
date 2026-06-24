<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentHiring extends Model
{
    protected $table = 'recruitment_hiring';

    protected $fillable = [
        'session_id',
        'employee_id',
        'agreement_type',
        'letter_number',
        'notes',
        'reviewed_by',
        'reviewed_at',
        'employee_registered_at',
        'employee_registered_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'employee_registered_at' => 'datetime',
    ];

    protected $dates = [
        'reviewed_at',
        'created_at',
        'updated_at'
    ];

    // Agreement type options
    public const AGREEMENT_TYPES = [
        'pkwt',
        'pkwtt',
        'magang',
        'harian'
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

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function employeeRegisteredBy()
    {
        return $this->belongsTo(User::class, 'employee_registered_by');
    }

    public function isEmployeeRegistered(): bool
    {
        return $this->employee_id !== null;
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

    public function scopeMagang($query)
    {
        return $query->where('agreement_type', 'magang');
    }

    public function scopeHarian($query)
    {
        return $query->where('agreement_type', 'harian');
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

    public function getIsMagangAttribute()
    {
        return $this->agreement_type === 'magang';
    }

    public function getIsHarianAttribute()
    {
        return $this->agreement_type === 'harian';
    }

    /**
     * Get agreement type based on FPTK employment type
     * Hardcode mapping: employment_type -> agreement_type
     */
    public static function getAgreementTypeFromEmploymentType(string $employmentType): string
    {
        return match ($employmentType) {
            'pkwt' => 'pkwt',
            'pkwtt' => 'pkwtt',
            'magang' => 'magang',
            'harian' => 'harian',
            default => 'pkwt' // Default fallback
        };
    }
}
