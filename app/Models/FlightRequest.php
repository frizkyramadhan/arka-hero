<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightRequest extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'manual_approvers' => 'array',
    ];

    // Status Constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    // Request Type Constants
    public const TYPE_STANDALONE = 'standalone';
    public const TYPE_LEAVE_BASED = 'leave_based';
    public const TYPE_TRAVEL_BASED = 'travel_based';

    public static function getRequestTypeOptions()
    {
        return [
            self::TYPE_STANDALONE => 'Standalone',
            self::TYPE_LEAVE_BASED => 'Based on Leave Request',
            self::TYPE_TRAVEL_BASED => 'Based on Official Travel',
        ];
    }

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function administration()
    {
        return $this->belongsTo(Administration::class);
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function officialTravel()
    {
        return $this->belongsTo(Officialtravel::class, 'official_travel_id');
    }

    public function details()
    {
        return $this->hasMany(FlightRequestDetail::class);
    }

    public function issuances()
    {
        return $this->belongsToMany(
            FlightRequestIssuance::class,
            'flight_request_issuance',
            'flight_request_id',
            'flight_request_issuance_id'
        )->withTimestamps();
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function approvalPlans()
    {
        return $this->hasMany(ApprovalPlan::class, 'document_id')
            ->where('document_type', 'flight_request');
    }

    // Helper Methods
    public function hasIssuance(): bool
    {
        return $this->issuances()->exists();
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED && $this->hasIssuance();
    }

    public function canBeIssued(): bool
    {
        // 1 FR dapat punya beberapa LG: boleh tambah LG saat approved atau issued
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_ISSUED]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_APPROVED
        ]);
    }
}
