<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmployeeDisciplinary extends Model
{
    use HasFactory;

    public const TYPE_COACHING = 'coaching';

    public const TYPE_COUNSELING = 'counseling';

    public const TYPE_SP1 = 'sp1';

    public const TYPE_SP2 = 'sp2';

    public const TYPE_SP3 = 'sp3';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_SUPERSEDED = 'superseded';

    public const STATUS_TERMINATED = 'terminated';

    public const TYPE_LABELS = [
        self::TYPE_COACHING => 'Coaching',
        self::TYPE_COUNSELING => 'Counseling',
        self::TYPE_SP1 => 'Warning Letter I (SP1)',
        self::TYPE_SP2 => 'Warning Letter II (SP2)',
        self::TYPE_SP3 => 'First & Final Warning (SP3)',
    ];

    public const SP_TYPES = [
        self::TYPE_SP1,
        self::TYPE_SP2,
        self::TYPE_SP3,
    ];

    public const PEMBINAAN_TYPES = [
        self::TYPE_COACHING,
        self::TYPE_COUNSELING,
    ];

    protected $fillable = [
        'employee_id',
        'type',
        'effective_date',
        'end_date',
        'reason',
        'pp_notes',
        'document_path',
        'status',
        'created_by',
        'imported_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date' => 'date',
        'imported_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(
            DisciplinaryCriterion::class,
            'employee_disciplinary_criterion',
            'employee_disciplinary_id',
            'disciplinary_criterion_id'
        )->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForEmployee($query, string $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeOfTypes($query, array $types)
    {
        return $query->whereIn('type', $types);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? ucfirst($this->type);
    }

    public function getRemainingDaysAttribute(): int
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return 0;
        }

        return max(0, (int) Carbon::today()->diffInDays($this->end_date, false));
    }

    public function isSp(): bool
    {
        return in_array($this->type, self::SP_TYPES, true);
    }

    public function isPembinaan(): bool
    {
        return in_array($this->type, self::PEMBINAAN_TYPES, true);
    }

    public function isImported(): bool
    {
        return $this->imported_at !== null;
    }

    /**
     * Imported rows may attach a supporting document later when none exists yet.
     */
    public function allowsDeferredDocument(): bool
    {
        return $this->isImported() && blank($this->document_path);
    }
}
