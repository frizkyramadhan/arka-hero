<?php

namespace App\Models;

use App\Contracts\NotifiableDocument;
use App\Traits\Uuids;
use App\Traits\HasLetterNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightRequestIssuance extends Model implements NotifiableDocument
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

    public function notificationDocumentType(): string
    {
        return 'flight_request_issuance';
    }

    public function notificationDocumentLabel(): string
    {
        return config('document_notifications.labels.flight_request_issuance', 'Flight Ticket Issuance');
    }

    public function notificationReference(): string
    {
        return $this->letter_number
            ?: ($this->issued_number ?: ('ISS-'.$this->getKey()));
    }

    public function notificationTitle(): string
    {
        return 'Flight Ticket Issuance '.$this->notificationReference();
    }

    public function notificationSummary(): array
    {
        return [
            'Issued Number' => $this->issued_number,
            'Issued Date' => optional($this->issued_date)->format('d M Y'),
            'Tickets' => (string) $this->total_tickets,
            'Status' => $this->status,
        ];
    }

    public function notificationRequester(): ?User
    {
        return $this->issuedBy ?? User::find($this->issued_by);
    }

    public function notificationActionUrl(): string
    {
        return route('flight-issuances.show', $this->getKey());
    }
}
