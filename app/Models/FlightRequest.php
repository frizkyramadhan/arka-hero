<?php

namespace App\Models;

use App\Contracts\NotifiableDocument;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FlightRequest extends Model implements NotifiableDocument
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

    public function followers()
    {
        return $this->hasMany(FlightRequestFollower::class)->orderBy('sort_order');
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

    public function approval_plans()
    {
        return $this->approvalPlans();
    }

    /**
     * True when no approval plan has moved past pending (status 0).
     */
    public function hasNoApprovalActionsYet(): bool
    {
        if ($this->relationLoaded('approval_plans')) {
            return $this->approval_plans->every(fn (ApprovalPlan $plan) => (int) $plan->status === 0);
        }

        return ! $this->approvalPlans()->where('status', '!=', 0)->exists();
    }

    /**
     * Approver IDs that already have a final decision (approved/rejected/etc.) and cannot be changed.
     *
     * @return array<int, int>
     */
    public function getLockedApproverIds(): array
    {
        return $this->approvalPlans()
            ->where('status', '!=', 0)
            ->pluck('approver_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function hasPendingApprovers(): bool
    {
        if ($this->status !== self::STATUS_SUBMITTED) {
            return false;
        }

        return $this->approvalPlans()->where('status', 0)->exists();
    }

    /**
     * Pending approvers may be changed while FR is submitted and at least one step is still pending.
     */
    public function canChangeApprovers(): bool
    {
        return $this->hasPendingApprovers();
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

    public function notificationDocumentType(): string
    {
        return 'flight_request';
    }

    public function notificationDocumentLabel(): string
    {
        return config('document_notifications.labels.flight_request', 'Flight Request');
    }

    public function notificationReference(): string
    {
        return $this->form_number ?: ('FRF-'.$this->getKey());
    }

    public function notificationTitle(): string
    {
        return (string) ($this->purpose_of_travel ?: $this->notificationReference());
    }

    /**
     * Eager-load relations used by approval-request show and email content.
     */
    public function loadNotificationRelations(): self
    {
        return $this->loadMissing([
            'employee',
            'administration.position.department',
            'administration.project',
            'details',
            'requestedBy',
            'leaveRequest.employee',
            'leaveRequest.administration',
            'officialTravel.traveler.employee',
            'officialTravel.stops',
            'officialTravel.details.follower.employee',
            'officialTravel.details.follower.position.department',
            'officialTravel.details.follower.project',
            'followers.employee',
            'followers.administration.position.department',
            'followers.administration.project',
        ]);
    }

    /**
     * Resolved employee/admin display values aligned with approval-requests/show.
     *
     * @return array{
     *     name: string,
     *     nik: string,
     *     position: string,
     *     department: string,
     *     project_number: string,
     *     phone_number: string|null,
     *     poh: string,
     *     doh: string,
     *     request_type_label: string
     * }
     */
    public function notificationEmployeeContext(): array
    {
        $this->loadNotificationRelations();

        $employee = $this->employee;
        $administration = $this->administration
            ?? ($employee ? $employee->activeAdministration : null);

        $name = $this->employee_name
            ?? ($employee?->fullname ?: 'N/A');
        $nik = $this->nik
            ?? ($administration?->nik ?: 'N/A');
        $position = $this->position
            ?? ($administration?->position?->position_name ?: 'N/A');
        $department = $this->department
            ?? ($administration?->position?->department?->department_name ?: 'N/A');
        $project = $this->project
            ?? ($administration?->project?->project_name ?: 'N/A');
        $projectCode = $administration?->project?->project_code;
        $projectNumber = $projectCode ? $projectCode.' - '.$project : $project;
        $phoneNumber = $this->phone_number
            ?? ($administration?->phone_number ?: null);
        $poh = $administration?->poh ?: 'N/A';
        $doh = $administration?->doh
            ? \Carbon\Carbon::parse($administration->doh)->format('d F Y')
            : 'N/A';

        return [
            'name' => $name,
            'nik' => $nik,
            'position' => $position,
            'department' => $department,
            'project_number' => $projectNumber,
            'phone_number' => $phoneNumber,
            'poh' => $poh,
            'doh' => $doh,
            'request_type_label' => $this->notificationRequestTypeLabel(),
        ];
    }

    public function notificationRequestTypeLabel(): string
    {
        $this->loadNotificationRelations();

        if ($this->request_type === self::TYPE_LEAVE_BASED && $this->leaveRequest) {
            $leave = $this->leaveRequest;
            $leaveEmployee = $leave->employee;
            $leaveAdmin = $leave->administration
                ?? ($leaveEmployee ? $leaveEmployee->activeAdministration : null);

            return sprintf(
                'Leave Request (Cuti) - %s - %s (%s to %s)',
                $leaveEmployee?->fullname ?? 'N/A',
                $leaveAdmin?->nik ?? 'N/A',
                optional($leave->start_date)->format('d M Y') ?? 'N/A',
                optional($leave->end_date)->format('d M Y') ?? 'N/A'
            );
        }

        if ($this->request_type === self::TYPE_TRAVEL_BASED && $this->officialTravel) {
            $travel = $this->officialTravel;
            $traveler = $travel->traveler;
            $travelEmployee = $traveler?->employee;

            return sprintf(
                'Official Travel (LOT) - %s - %s (%s)',
                $travel->official_travel_number ?? 'N/A',
                $travelEmployee?->fullname ?? 'N/A',
                $travel->destination ?: ($travel->itinerarySummaryForDisplay() ?: 'N/A')
            );
        }

        return 'Standalone';
    }

    /**
     * Summary aligned with approval-requests/show Flight Request cards.
     *
     * @return array<string, string|null>
     */
    public function notificationSummary(): array
    {
        $ctx = $this->notificationEmployeeContext();
        $segments = $this->details
            ->sortBy(['segment_order', 'flight_date'])
            ->values()
            ->map(function ($detail, $index) {
                $date = optional($detail->flight_date)->format('d M Y') ?: '—';
                $time = $detail->flight_time
                    ? \Carbon\Carbon::parse($detail->flight_time)->format('H:i')
                    : null;
                $route = trim(($detail->departure_city ?? '—').' → '.($detail->arrival_city ?? '—'));
                $airline = $detail->airline ? ' / '.$detail->airline : '';

                return sprintf(
                    'Flight %d: %s%s · %s%s',
                    $index + 1,
                    $route,
                    $airline,
                    $date,
                    $time ? ' '.$time : ''
                );
            })
            ->implode('; ');

        $summary = [
            'Form Number' => $this->notificationReference(),
            'Requested At' => $this->requested_at
                ? format_date_with_weekday($this->requested_at)
                : format_date_with_weekday($this->created_at),
            'Name' => $ctx['name'],
            'Request Type' => $ctx['request_type_label'],
            'ID Number / NIK' => $ctx['nik'],
            'Position' => $ctx['position'],
            'Dept/Division' => $ctx['department'],
            'POH' => $ctx['poh'],
            'DOH' => $ctx['doh'],
            'Project Number' => $ctx['project_number'],
        ];

        if (! empty($ctx['phone_number'])) {
            $summary['Phone Number'] = $ctx['phone_number'];
        }

        $summary['Purpose of Travel'] = $this->purpose_of_travel ?: '—';
        $summary['Total Travel Days'] = (string) ($this->total_travel_days ?? '—');
        $summary['Flight Details'] = $segments !== '' ? $segments : 'No flight details available';

        if ($this->request_type === self::TYPE_TRAVEL_BASED
            && $this->officialTravel
            && $this->officialTravel->details->isNotEmpty()) {
            $summary['Followers'] = $this->officialTravel->details
                ->map(function ($detail) {
                    $name = $detail->follower?->employee?->fullname ?? 'Unknown Employee';
                    $nik = $detail->follower?->nik ?? '';

                    return trim($nik !== '' ? "{$name} ({$nik})" : $name);
                })
                ->implode('; ');
        } elseif ($this->request_type === self::TYPE_STANDALONE && $this->followers->isNotEmpty()) {
            $summary['Followers'] = $this->followers
                ->map(fn (FlightRequestFollower $follower) => $follower->displayName())
                ->implode('; ');
        }

        if (filled($this->notes)) {
            $summary['Notes'] = $this->notes;
        }

        return $summary;
    }

    public function notificationRequester(): ?User
    {
        return $this->requestedBy ?? User::find($this->requested_by);
    }

    public function notificationActionUrl(): string
    {
        return route('flight-requests.show', $this->getKey());
    }
}
