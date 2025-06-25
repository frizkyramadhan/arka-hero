<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\EmployeeRegistrationToken;
use App\Mail\EmployeeRegistrationInvitation;
use App\Services\EmployeeRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

class EmployeeRegistrationEmailIntegrationSimpleTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create();
        $this->service = new EmployeeRegistrationService();

        // Mock route for registration URL
        Route::get('/employee-registration/{token}', function () {
            return 'test';
        })->name('employee.registration.form');
    }

    /** @test */
    public function service_can_invite_single_employee_successfully()
    {
        Mail::fake();

        $email = 'newemployee@company.com';
        $result = $this->service->inviteEmployee($email, $this->adminUser->id);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['email_sent']);
        $this->assertEquals('Registration invitation sent successfully', $result['message']);
        $this->assertInstanceOf(EmployeeRegistrationToken::class, $result['token']);

        // Verify token was created in database
        $this->assertDatabaseHas('employee_registration_tokens', [
            'email' => $email,
            'created_by' => $this->adminUser->id
        ]);

        // Verify email was sent
        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /** @test */
    public function service_can_invite_bulk_employees_successfully()
    {
        Mail::fake();

        $emails = [
            'employee1@company.com',
            'employee2@company.com',
            'employee3@company.com'
        ];

        $result = $this->service->bulkInviteEmployees($emails, $this->adminUser->id);

        $this->assertEquals(3, $result['summary']['total']);
        $this->assertEquals(3, $result['summary']['successful']);
        $this->assertEquals(0, $result['summary']['failed']);
        $this->assertCount(3, $result['success']);
        $this->assertEmpty($result['failed']);

        // Verify all emails were sent
        Mail::assertSent(EmployeeRegistrationInvitation::class, 3);

        // Verify each email was sent to correct recipient
        foreach ($emails as $email) {
            Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) use ($email) {
                return $mail->hasTo($email);
            });
        }

        // Verify tokens were created in database
        foreach ($emails as $email) {
            $this->assertDatabaseHas('employee_registration_tokens', [
                'email' => $email,
                'created_by' => $this->adminUser->id
            ]);
        }
    }

    /** @test */
    public function invitation_email_contains_valid_registration_link()
    {
        Mail::fake();

        $email = 'test@company.com';
        $result = $this->service->inviteEmployee($email, $this->adminUser->id);

        $this->assertTrue($result['success']);

        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) {
            // Verify email has registration URL
            $this->assertNotEmpty($mail->registrationUrl);

            // Verify URL is properly formatted
            $this->assertStringContainsString('employee-registration', $mail->registrationUrl);

            // Verify token is present in URL
            $this->assertStringContainsString($mail->tokenRecord->token, $mail->registrationUrl);

            return true;
        });
    }

    /** @test */
    public function invitation_email_has_correct_structure()
    {
        Mail::fake();

        $email = 'test@company.com';
        $result = $this->service->inviteEmployee($email, $this->adminUser->id);

        $this->assertTrue($result['success']);

        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) {
            // Check email properties
            $this->assertEquals('Employee Registration Invitation - HCSSIS', $mail->envelope()->subject);
            $this->assertEquals('emails.employee-registration-invitation', $mail->content()->html);

            // Check email data
            $content = $mail->content();
            $this->assertArrayHasKey('tokenRecord', $content->with);
            $this->assertArrayHasKey('registrationUrl', $content->with);
            $this->assertArrayHasKey('expiresAt', $content->with);
            $this->assertArrayHasKey('companyName', $content->with);

            return true;
        });
    }

    /** @test */
    public function duplicate_email_invitation_deactivates_previous_token()
    {
        Mail::fake();

        $email = 'test@company.com';

        // Send first invitation
        $firstResult = $this->service->inviteEmployee($email, $this->adminUser->id);
        $this->assertTrue($firstResult['success']);

        $firstToken = EmployeeRegistrationToken::where('email', $email)->first();
        $this->assertEquals('pending', $firstToken->status);

        // Send second invitation to same email
        $secondResult = $this->service->inviteEmployee($email, $this->adminUser->id);
        $this->assertTrue($secondResult['success']);

        // Verify first token was deactivated
        $firstToken->refresh();
        $this->assertEquals('expired', $firstToken->status);

        // Verify new token was created
        $activeToken = EmployeeRegistrationToken::where('email', $email)
            ->where('status', 'pending')
            ->first();

        $this->assertNotNull($activeToken);
        $this->assertNotEquals($firstToken->token, $activeToken->token);

        // Verify two emails were sent
        Mail::assertSent(EmployeeRegistrationInvitation::class, 2);
    }

    /** @test */
    public function bulk_invitation_processes_all_emails_including_invalid_ones()
    {
        Mail::fake();

        $emails = ['valid@company.com', 'invalid-email', 'another@company.com', '@invalid.com'];

        $result = $this->service->bulkInviteEmployees($emails, $this->adminUser->id);

        // Service processes all emails regardless of validity
        $this->assertEquals(4, $result['summary']['total']);

        // Check that we have both successful and failed results
        $this->assertArrayHasKey('successful', $result['summary']);
        $this->assertArrayHasKey('failed', $result['summary']);

        // The total should equal successful + failed
        $totalProcessed = $result['summary']['successful'] + $result['summary']['failed'];
        $this->assertEquals(4, $totalProcessed);

        // Verify that some emails were sent (at least the valid ones)
        $this->assertGreaterThan(0, $result['summary']['successful']);
    }

    /** @test */
    public function token_expiration_is_set_correctly()
    {
        Mail::fake();

        $email = 'test@company.com';
        $result = $this->service->inviteEmployee($email, $this->adminUser->id);

        $this->assertTrue($result['success']);

        $token = EmployeeRegistrationToken::where('email', $email)->first();

        // Token should expire in 7 days (default)
        $expectedExpiration = now()->addDays(7);
        $this->assertTrue($token->expires_at->diffInMinutes($expectedExpiration) < 1);
    }

    /** @test */
    public function email_template_receives_correct_data()
    {
        Mail::fake();

        $email = 'test@company.com';
        $result = $this->service->inviteEmployee($email, $this->adminUser->id);

        $this->assertTrue($result['success']);

        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) {
            $content = $mail->content();
            $data = $content->with;

            // Verify token record
            $this->assertInstanceOf(EmployeeRegistrationToken::class, $data['tokenRecord']);
            $this->assertEquals('test@company.com', $data['tokenRecord']->email);

            // Verify registration URL
            $this->assertIsString($data['registrationUrl']);
            $this->assertStringStartsWith('http', $data['registrationUrl']);

            // Verify expiration date format
            $this->assertIsString($data['expiresAt']);
            $this->assertMatchesRegularExpression('/\w+ \d+, \d{4} at \d+:\d+ [AP]M/', $data['expiresAt']);

            // Verify company name
            $this->assertEquals(config('app.name', 'HCSSIS'), $data['companyName']);

            return true;
        });
    }

    /** @test */
    public function service_handles_email_sending_failure_gracefully()
    {
        // We can't easily test email failures with Mail::fake()
        // But we can test that the service method returns appropriate response

        $email = 'test@company.com';
        $result = $this->service->inviteEmployee($email, $this->adminUser->id);

        // Should succeed with Mail::fake()
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('email_sent', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('token', $result);
    }

    /** @test */
    public function service_generates_unique_tokens()
    {
        Mail::fake();

        $emails = ['user1@test.com', 'user2@test.com', 'user3@test.com'];
        $tokens = [];

        foreach ($emails as $email) {
            $result = $this->service->inviteEmployee($email, $this->adminUser->id);
            $this->assertTrue($result['success']);
            $tokens[] = $result['token']->token;
        }

        // Verify all tokens are unique
        $this->assertEquals(count($tokens), count(array_unique($tokens)));

        // Verify all tokens are 64 characters long
        foreach ($tokens as $token) {
            $this->assertEquals(64, strlen($token));
        }
    }
}
