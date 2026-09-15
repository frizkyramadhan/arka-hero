<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightRequestIssuanceDetail extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    protected $casts = [
        'ticket_price' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'service_vat' => 'decimal:2',
        'company_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
    ];

    /** DB column is advance_amount; alias for views (151 Advance) */
    public function getEmployeeAmountAttribute()
    {
        return $this->getAttribute('advance_amount');
    }

    /** Resolved passenger name: from linked employee or fallback to passenger_name. */
    public function getResolvedPassengerNameAttribute(): ?string
    {
        if ($this->employee_id && $this->employee) {
            return $this->employee->fullname;
        }
        return $this->passenger_name;
    }

    // Relationships
    public function issuance()
    {
        return $this->belongsTo(FlightRequestIssuance::class, 'flight_request_issuance_id');
    }

    /** Linked FRF flight segment (Departure / Return). */
    public function flightRequestDetail()
    {
        return $this->belongsTo(FlightRequestDetail::class, 'flight_request_detail_id');
    }

    /**
     * Employee (passenger) - linked for taking name from employees joined with administration active.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Resolve flight segment for this ticket: explicit link first, then order fallback on FR segments.
     *
     * @param  \Illuminate\Support\Collection<int, FlightRequestDetail>|null  $fallbackSegments
     */
    public function resolveFlightSegment($fallbackSegments = null): ?FlightRequestDetail
    {
        if ($this->flightRequestDetail) {
            return $this->flightRequestDetail;
        }

        if ($fallbackSegments === null || $fallbackSegments->isEmpty()) {
            return null;
        }

        $ordered = $fallbackSegments->sortBy('segment_order')->values();

        return $ordered->get(max(0, (int) $this->ticket_order - 1)) ?? $ordered->first();
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('ticket_order', 'asc');
    }
}
