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

    /**
     * Employee (passenger) - linked for taking name from employees joined with administration active.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('ticket_order', 'asc');
    }
}
