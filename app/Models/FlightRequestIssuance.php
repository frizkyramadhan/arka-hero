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
        return $this->issued_number
            ?: ($this->letter_number ?: ('ISS-'.$this->getKey()));
    }

    public function notificationTitle(): string
    {
        $this->loadNotificationRelations();

        return $this->businessPartner?->bp_name
            ?: ('Flight Ticket Issuance '.$this->notificationReference());
    }

    /**
     * Eager-load relations used by approval-request show and email content.
     */
    public function loadNotificationRelations(): self
    {
        return $this->loadMissing([
            'businessPartner',
            'issuedBy',
            'issuanceDetails.employee',
        ]);
    }

    /**
     * Summary aligned with approval-requests/show LG Information + Ticket Details.
     *
     * @return array<string, string|null>
     */
    public function notificationSummary(): array
    {
        $this->loadNotificationRelations();

        $details = $this->issuanceDetails;
        $totalPrice = $details->sum(fn ($detail) => (float) ($detail->ticket_price ?? 0));

        $tickets = $details
            ->map(function ($detail) {
                $passenger = $detail->resolved_passenger_name ?: '-';
                $booking = $detail->booking_code ?: '-';
                $price = $detail->ticket_price
                    ? 'Rp '.number_format((float) $detail->ticket_price, 0, ',', '.')
                    : '-';

                return sprintf(
                    '#%s %s / %s / %s',
                    $detail->ticket_order ?? '-',
                    $passenger,
                    $booking,
                    $price
                );
            })
            ->implode('; ');

        $summary = [
            'Issued Number' => $this->issued_number ?: '—',
            'Issued Date' => optional($this->issued_date)->format('d F Y') ?: '—',
            'Letter Number' => $this->letter_number ?: '—',
            'Business Partner' => $this->businessPartner?->bp_name ?: '—',
            'Issued By' => $this->issuedBy?->name ?: '—',
            'Total Tickets' => (string) $details->count(),
            'Total Price' => 'Rp '.number_format($totalPrice, 0, ',', '.'),
            'Ticket Details' => $tickets !== '' ? $tickets : 'No ticket details',
        ];

        if (filled($this->notes)) {
            $summary['Notes'] = $this->notes;
        }

        return $summary;
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
