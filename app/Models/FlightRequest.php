<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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

    /**
     * Generate unique form number for new flight request.
     */
    public static function generateFormNumber(): string
    {
        $year = date('y');
        $lastRequest = self::where('form_number', 'like', "{$year}FRF-%")
            ->orderBy('form_number', 'desc')
            ->first();

        if ($lastRequest && preg_match('/\d+$/', $lastRequest->form_number, $matches)) {
            $nextNumber = (int) $matches[0] + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%sFRF-%05d', $year, $nextNumber);
    }

    /**
     * Create a flight request from fr_data when submitted with leave request or official travel.
     * Returns the created FlightRequest or null if fr_data not present/invalid.
     */
    public static function createFromFrData(Request $request, Model $parent): ?self
    {
        $frData = $request->input('fr_data');
        if (!$frData || empty($frData['need_flight_ticket']) || empty($frData['details']) || !is_array($frData['details'])) {
            return null;
        }

        $details = array_values(array_filter($frData['details'], function ($d) {
            return !empty($d['flight_date']) && !empty($d['departure_city']) && !empty($d['arrival_city']);
        }));
        if (empty($details)) {
            return null;
        }

        $userId = $request->user()->id ?? null;
        if ($parent instanceof LeaveRequest) {
            $parent->load(['employee', 'administration.position.department', 'administration.project', 'leaveType']);
            $administration = $parent->administration;
            $employee = $parent->employee;
            $purpose = 'Leave: ' . ($parent->leaveType->name ?? '') . ' ' . $parent->start_date?->format('d/m/Y') . ' - ' . $parent->end_date?->format('d/m/Y');
            $flightRequest = self::create([
                'form_number' => self::generateFormNumber(),
                'request_type' => self::TYPE_LEAVE_BASED,
                'employee_id' => $parent->employee_id,
                'administration_id' => $parent->administration_id,
                'employee_name' => $employee->fullname ?? null,
                'nik' => $administration->nik ?? null,
                'position' => $administration->position->position_name ?? null,
                'department' => $administration->position->department->department_name ?? null,
                'project' => $administration->project->project_name ?? null,
                'phone_number' => null,
                'purpose_of_travel' => $purpose,
                'total_travel_days' => (string) ($parent->total_days ?? ''),
                'leave_request_id' => $parent->id,
                'official_travel_id' => null,
                'status' => self::STATUS_DRAFT,
                'manual_approvers' => null,
                'requested_by' => $userId,
                'requested_at' => !empty($frData['requested_at']) ? \Carbon\Carbon::parse($frData['requested_at']) : null,
                'notes' => 'Created from Leave Request submission.',
            ]);
        } elseif ($parent instanceof Officialtravel) {
            $parent->load(['traveler.employee', 'traveler.position.department', 'traveler.project']);
            $administration = $parent->traveler;
            $employee = $administration->employee ?? null;
            $purpose = ($parent->purpose ?? '') . ' | Destination: ' . ($parent->destination ?? '') . ', Duration: ' . ($parent->duration ?? '');
            $flightRequest = self::create([
                'form_number' => self::generateFormNumber(),
                'request_type' => self::TYPE_TRAVEL_BASED,
                'employee_id' => $employee->id ?? null,
                'administration_id' => $administration->id ?? null,
                'employee_name' => $employee->fullname ?? null,
                'nik' => $administration->nik ?? null,
                'position' => $administration->position->position_name ?? null,
                'department' => $administration->position->department->department_name ?? null,
                'project' => $administration->project->project_name ?? null,
                'phone_number' => null,
                'purpose_of_travel' => $purpose,
                'total_travel_days' => $parent->duration ?? null,
                'leave_request_id' => null,
                'official_travel_id' => $parent->id,
                'status' => self::STATUS_DRAFT,
                'manual_approvers' => null,
                'requested_by' => $userId,
                'requested_at' => !empty($frData['requested_at']) ? \Carbon\Carbon::parse($frData['requested_at']) : null,
                'notes' => 'Created from Official Travel (LOT) submission.',
            ]);
        } else {
            return null;
        }

        foreach ($details as $index => $d) {
            FlightRequestDetail::create([
                'flight_request_id' => $flightRequest->id,
                'segment_order' => $index + 1,
                'segment_type' => $d['segment_type'] ?? ($index === 0 ? 'departure' : 'return'),
                'flight_date' => $d['flight_date'],
                'departure_city' => $d['departure_city'],
                'arrival_city' => $d['arrival_city'],
                'airline' => $d['airline'] ?? null,
                'flight_time' => !empty($d['flight_time']) ? $d['flight_time'] : null,
            ]);
        }

        return $flightRequest;
    }

    /**
     * Create a flight request from fr_data array (for bulk/periodic leave).
     * Returns the created FlightRequest or null if fr_data not present/invalid.
     */
    public static function createFromFrDataArray(array $frData, LeaveRequest $leaveRequest, ?int $userId = null): ?self
    {
        if (empty($frData['need_flight_ticket']) || empty($frData['details']) || !is_array($frData['details'])) {
            return null;
        }

        $details = array_values(array_filter($frData['details'], function ($d) {
            return !empty($d['flight_date']) && !empty($d['departure_city']) && !empty($d['arrival_city']);
        }));
        if (empty($details)) {
            return null;
        }

        $userId = $userId ?? auth()->id();
        $leaveRequest->load(['employee', 'administration.position.department', 'administration.project', 'leaveType']);
        $administration = $leaveRequest->administration;
        $employee = $leaveRequest->employee;
        $purpose = 'Leave: ' . ($leaveRequest->leaveType->name ?? '') . ' ' . $leaveRequest->start_date?->format('d/m/Y') . ' - ' . $leaveRequest->end_date?->format('d/m/Y');

        $flightRequest = self::create([
            'form_number' => self::generateFormNumber(),
            'request_type' => self::TYPE_LEAVE_BASED,
            'employee_id' => $leaveRequest->employee_id,
            'administration_id' => $leaveRequest->administration_id,
            'employee_name' => $employee->fullname ?? null,
            'nik' => $administration->nik ?? null,
            'position' => $administration->position->position_name ?? null,
            'department' => $administration->position->department->department_name ?? null,
            'project' => $administration->project->project_name ?? null,
            'phone_number' => null,
            'purpose_of_travel' => $purpose,
            'total_travel_days' => (string) ($leaveRequest->total_days ?? ''),
            'leave_request_id' => $leaveRequest->id,
            'official_travel_id' => null,
            'status' => self::STATUS_DRAFT,
            'manual_approvers' => null,
            'requested_by' => $userId,
            'requested_at' => !empty($frData['requested_at']) ? \Carbon\Carbon::parse($frData['requested_at']) : null,
            'notes' => 'Created from Periodic Leave Request submission.',
        ]);

        foreach ($details as $index => $d) {
            FlightRequestDetail::create([
                'flight_request_id' => $flightRequest->id,
                'segment_order' => $index + 1,
                'segment_type' => $d['segment_type'] ?? ($index === 0 ? 'departure' : 'return'),
                'flight_date' => $d['flight_date'],
                'departure_city' => $d['departure_city'],
                'arrival_city' => $d['arrival_city'],
                'airline' => $d['airline'] ?? null,
                'flight_time' => !empty($d['flight_time']) ? $d['flight_time'] : null,
            ]);
        }

        return $flightRequest;
    }
}
