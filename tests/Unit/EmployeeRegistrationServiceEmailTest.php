<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EmployeeRegistrationService;
use App\Models\EmployeeRegistrationToken;
use App\Models\User;
use App\Mail\EmployeeRegistrationInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

class EmployeeRegistrationServiceEmailTest extends TestCase
{
    use RefreshDatabase;

    private $service;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new EmployeeRegistrationService();
        $this->user = User::factory()->create();

        // Mock route for registration URL
        Route::get('/employee-registration/{token}', function () {
            return 'test';
        })->name('employee.registration.form');
    }

    /** @test */
    public function it_can_send_registration_invitation_successfully()
    {
        Mail::fake();

        $tokenRecord = EmployeeRegistrationToken::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'email' => 'test@example.com',
            'token' => \Illuminate\Support\Str::random(64),
            'created_by' => $this->user->id,
            'expires_at' => now()->addDays(7),
        ]);

        $result = $this->service->sendRegistrationInvitation($tokenRecord);

        $this->assertTrue($result);

        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) use ($tokenRecord) {
            return $mail->hasTo($tokenRecord->email) &&
                $mail->tokenRecord->id === $tokenRecord->id &&
                str_contains($mail->registrationUrl, $tokenRecord->token);
        });
    }

    /** @test */
    public function it_generates_correct_registration_url()
    {
        Mail::fake();

        $token = 'test-token-123';
        $tokenRecord = EmployeeRegistrationToken::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'email' => 'test@example.com',
            'token' => $token,
            'created_by' => $this->user->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->service->sendRegistrationInvitation($tokenRecord);

        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) use ($token) {
            $expectedUrl = route('employee.registration.form', ['token' => $token]);
            return $mail->registrationUrl === $expectedUrl;
        });
    }

    /** @test */
    public function invite_employee_creates_token_and_sends_email_successfully()
    {
        Mail::fake();

        $email = 'newemployee@company.com';
        $result = $this->service->inviteEmployee($email, $this->user->id);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['email_sent']);
        $this->assertEquals('Registration invitation sent successfully', $result['message']);
        $this->assertInstanceOf(EmployeeRegistrationToken::class, $result['token']);

        // Verify token was created
        $this->assertDatabaseHas('employee_registration_tokens', [
            'email' => $email,
            'created_by' => $this->user->id
        ]);

        // Verify email was sent
        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /** @test */
    public function bulk_invite_employees_processes_multiple_emails()
    {
        Mail::fake();

        $emails = [
            'employee1@company.com',
            'employee2@company.com',
            'employee3@company.com'
        ];

        $result = $this->service->bulkInviteEmployees($emails, $this->user->id);

        $this->assertEquals(3, $result['summary']['total']);
        $this->assertEquals(3, $result['summary']['successful']);
        $this->assertEquals(0, $result['summary']['failed']);
        $this->assertCount(3, $result['success']);
        $this->assertEmpty($result['failed']);

        // Verify all emails were sent
        Mail::assertSent(EmployeeRegistrationInvitation::class, 3);

        foreach ($emails as $email) {
            Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) use ($email) {
                return $mail->hasTo($email);
            });
        }
    }

    /** @test */
    public function bulk_invite_employees_returns_correct_data_structure()
    {
        Mail::fake();

        $emails = ['test@company.com'];
        $result = $this->service->bulkInviteEmployees($emails, $this->user->id);

        // Check main structure
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('failed', $result);
        $this->assertArrayHasKey('summary', $result);

        // Check summary structure
        $this->assertArrayHasKey('total', $result['summary']);
        $this->assertArrayHasKey('successful', $result['summary']);
        $this->assertArrayHasKey('failed', $result['summary']);

        // Check success item structure
        $this->assertArrayHasKey('email', $result['success'][0]);
        $this->assertArrayHasKey('token', $result['success'][0]);
        $this->assertArrayHasKey('email_sent', $result['success'][0]);
    }

    /** @test */
    public function it_deactivates_existing_tokens_before_creating_new_one()
    {
        $email = 'test@company.com';

        // Create existing token
        $existingToken = EmployeeRegistrationToken::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'email' => $email,
            'token' => 'old-token',
            'created_by' => $this->user->id,
            'expires_at' => now()->addDays(7),
            'status' => 'pending'
        ]);

        Mail::fake();

        // Create new invitation
        $result = $this->service->inviteEmployee($email, $this->user->id);

        $this->assertTrue($result['success']);

        // Check that old token was deactivated
        $existingToken->refresh();
        $this->assertEquals('expired', $existingToken->status);

        // Check that new token was created
        $newToken = EmployeeRegistrationToken::where('email', $email)
            ->where('status', 'pending')
            ->first();

        $this->assertNotNull($newToken);
        $this->assertNotEquals($existingToken->token, $newToken->token);
    }

    /** @test */
    public function email_contains_required_data()
    {
        Mail::fake();

        $tokenRecord = EmployeeRegistrationToken::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'email' => 'test@example.com',
            'token' => 'test-token-123',
            'created_by' => $this->user->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->service->sendRegistrationInvitation($tokenRecord);

        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) use ($tokenRecord) {
            // Check email has correct recipient
            $hasCorrectRecipient = $mail->hasTo($tokenRecord->email);

            // Check email contains token record
            $hasTokenRecord = $mail->tokenRecord->id === $tokenRecord->id;

            // Check email contains registration URL
            $hasRegistrationUrl = !empty($mail->registrationUrl);

            // Check URL contains the token
            $urlContainsToken = str_contains($mail->registrationUrl, $tokenRecord->token);

            return $hasCorrectRecipient && $hasTokenRecord && $hasRegistrationUrl && $urlContainsToken;
        });
    }

    /** @test */
    public function email_subject_is_correct()
    {
        $tokenRecord = EmployeeRegistrationToken::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'email' => 'test@example.com',
            'token' => 'test-token-123',
            'created_by' => $this->user->id,
            'expires_at' => now()->addDays(7),
        ]);

        $registrationUrl = route('employee.registration.form', ['token' => $tokenRecord->token]);
        $mailable = new EmployeeRegistrationInvitation($tokenRecord, $registrationUrl);

        $envelope = $mailable->envelope();
        $this->assertEquals('Employee Registration Invitation - HCSSIS', $envelope->subject);
    }

    /** @test */
    public function email_uses_correct_template()
    {
        $tokenRecord = EmployeeRegistrationToken::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'email' => 'test@example.com',
            'token' => 'test-token-123',
            'created_by' => $this->user->id,
            'expires_at' => now()->addDays(7),
        ]);

        $registrationUrl = route('employee.registration.form', ['token' => $tokenRecord->token]);
        $mailable = new EmployeeRegistrationInvitation($tokenRecord, $registrationUrl);

        $content = $mailable->content();
        $this->assertEquals('emails.employee-registration-invitation', $content->html);
    }
}
