<?php

namespace App\Models;

use App\Traits\Uuids;
use App\Traits\HasLetterNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightRequestIssuance extends Model
{
    use HasFactory, Uuids, HasLetterNumber;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $guarded = [];

    protected $casts = [
        'issued_date' => 'date',
        'issued_at' => 'datetime',
        'approved_at' => 'datetime',
        'manual_approvers' => 'array',
    ];

    // Relationships
    public function flightRequests()
    {
        return $this->belongsToMany(
            FlightRequest::class,
            'flight_request_issuance',
            'flight_request_issuance_id',
            'flight_request_id'
        )->withTimestamps();
    }

    public function businessPartner()
    {
        return $this->belongsTo(BusinessPartner::class, 'business_partner_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function approvalPlans()
    {
        return $this->hasMany(ApprovalPlan::class, 'document_id')
            ->where('document_type', 'flight_request_issuance');
    }

    // letterNumber() relationship is provided by HasLetterNumber trait

    public function issuanceDetails()
    {
        return $this->hasMany(FlightRequestIssuanceDetail::class, 'flight_request_issuance_id')
            ->orderBy('ticket_order', 'asc');
    }

    // Helper Methods
    public function getTotalTicketPriceAttribute()
    {
        return $this->issuanceDetails()->sum('ticket_price');
    }

    public function getTotalTicketsAttribute()
    {
        return $this->issuanceDetails()->count();
    }

    // Get document type untuk letter number tracking (required by HasLetterNumber trait)
    protected function getDocumentType(): string
    {
        return 'flight_request_issuance';
    }
}
