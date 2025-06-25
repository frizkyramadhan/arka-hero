<?php

namespace App\Mail;

use App\Models\EmployeeRegistrationToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeRegistrationInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $tokenRecord;
    public $registrationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(EmployeeRegistrationToken $tokenRecord, string $registrationUrl)
    {
        $this->tokenRecord = $tokenRecord;
        $this->registrationUrl = $registrationUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Employee Registration Invitation - HCSSIS',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.employee-registration-invitation',
            with: [
                'tokenRecord' => $this->tokenRecord,
                'registrationUrl' => $this->registrationUrl,
                'expiresAt' => $this->tokenRecord->expires_at->format('F j, Y \a\t g:i A'),
                'companyName' => config('app.name', 'HCSSIS')
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
