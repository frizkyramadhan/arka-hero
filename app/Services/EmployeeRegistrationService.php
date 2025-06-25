<?php

namespace App\Services;

use App\Models\EmployeeRegistrationToken;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\EmployeeRegistrationInvitation;

class EmployeeRegistrationService
{
    /**
     * Generate a new registration token for an employee
     */
    public function generateRegistrationToken(string $email, int $createdBy, int $validDays = 7): EmployeeRegistrationToken
    {
        // Deactivate any existing tokens for this email
        EmployeeRegistrationToken::where('email', $email)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // Generate new token
        $token = Str::random(64);

        $tokenRecord = EmployeeRegistrationToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addDays($validDays),
            'created_by' => $createdBy,
            'status' => 'pending'
        ]);

        return $tokenRecord;
    }

    /**
     * Send registration invitation email
     */
    public function sendRegistrationInvitation(EmployeeRegistrationToken $tokenRecord): bool
    {
        try {
            $registrationUrl = route('employee.registration.form', ['token' => $tokenRecord->token]);

            Mail::to($tokenRecord->email)->send(
                new EmployeeRegistrationInvitation($tokenRecord, $registrationUrl)
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send registration invitation', [
                'email' => $tokenRecord->email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate and send registration token in one step
     */
    public function inviteEmployee(string $email, int $createdBy): array
    {
        try {
            $tokenRecord = $this->generateRegistrationToken($email, $createdBy);
            $emailSent = $this->sendRegistrationInvitation($tokenRecord);

            return [
                'success' => true,
                'token' => $tokenRecord,
                'email_sent' => $emailSent,
                'message' => $emailSent ?
                    'Registration invitation sent successfully' :
                    'Token generated but email failed to send'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate registration token: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate a registration token
     */
    public function validateToken(string $token): ?EmployeeRegistrationToken
    {
        return EmployeeRegistrationToken::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Mark token as used
     */
    public function markTokenAsUsed(string $token): bool
    {
        $tokenRecord = EmployeeRegistrationToken::where('token', $token)->first();

        if ($tokenRecord) {
            $tokenRecord->update(['status' => 'used']);
            return true;
        }

        return false;
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return EmployeeRegistrationToken::where('expires_at', '<', now())
            ->where('status', 'pending')
            ->update(['status' => 'expired']);
    }

    /**
     * Get registration statistics
     */
    public function getRegistrationStats(): array
    {
        return [
            'pending_invitations' => EmployeeRegistrationToken::where('status', 'pending')->count(),
            'expired_tokens' => EmployeeRegistrationToken::where('status', 'expired')->count(),
            'used_tokens' => EmployeeRegistrationToken::where('status', 'used')->count(),
            'pending_registrations' => \App\Models\EmployeeRegistration::where('status', 'submitted')->count(),
        ];
    }

    /**
     * Generate bulk invitations
     */
    public function bulkInviteEmployees(array $emails, int $createdBy): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'summary' => [
                'total' => count($emails),
                'successful' => 0,
                'failed' => 0
            ]
        ];

        foreach ($emails as $email) {
            $result = $this->inviteEmployee($email, $createdBy);

            if ($result['success']) {
                $results['success'][] = [
                    'email' => $email,
                    'token' => $result['token']->token,
                    'email_sent' => $result['email_sent']
                ];
                $results['summary']['successful']++;
            } else {
                $results['failed'][] = [
                    'email' => $email,
                    'error' => $result['message']
                ];
                $results['summary']['failed']++;
            }
        }

        return $results;
    }

    /**
     * Resend invitation email for existing token
     */
    public function resendInvitation(EmployeeRegistrationToken $token): array
    {
        try {
            // Validate token status
            if ($token->status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Cannot resend invitation for token with status: ' . $token->status
                ];
            }

            if ($token->expires_at < now()) {
                return [
                    'success' => false,
                    'message' => 'Cannot resend invitation. Token has expired.'
                ];
            }

            // Send the invitation email
            $emailSent = $this->sendRegistrationInvitation($token);

            return [
                'success' => true,
                'email_sent' => $emailSent,
                'message' => $emailSent ?
                    'Invitation resent successfully' :
                    'Token is valid but email failed to send'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to resend invitation: ' . $e->getMessage()
            ];
        }
    }
}
