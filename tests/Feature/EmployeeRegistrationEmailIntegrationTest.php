<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\EmployeeRegistrationToken;
use App\Mail\EmployeeRegistrationInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

class EmployeeRegistrationEmailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create();

        // Give admin user necessary permissions
        // Note: This assumes you have a permission system, adjust as needed

        // Mock route for registration URL
        Route::get('/employee-registration/{token}', function () {
            return 'test';
        })->name('employee.registration.form');
    }

    /** @test */
    public function admin_can_send_single_employee_invitation()
    {
        Mail::fake();

        $response = $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/invite', [
                'email' => 'newemployee@company.com'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Registration invitation sent successfully'
            ]);

        // Verify email was sent
        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) {
            return $mail->hasTo('newemployee@company.com');
        });

        // Verify token was created in database
        $this->assertDatabaseHas('employee_registration_tokens', [
            'email' => 'newemployee@company.com',
            'created_by' => $this->adminUser->id
        ]);
    }

    /** @test */
    public function admin_can_send_bulk_employee_invitations()
    {
        Mail::fake();

        $emails = "employee1@company.com\nemployee2@company.com\nemployee3@company.com";

        $response = $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/bulk-invite', [
                'emails' => $emails
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'results' => [
                    'success',
                    'failed',
                    'summary' => [
                        'total',
                        'successful',
                        'failed'
                    ]
                ]
            ]);

        // Verify all emails were sent
        Mail::assertSent(EmployeeRegistrationInvitation::class, 3);

        // Verify each email was sent to correct recipient
        $emailAddresses = ['employee1@company.com', 'employee2@company.com', 'employee3@company.com'];
        foreach ($emailAddresses as $email) {
            Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) use ($email) {
                return $mail->hasTo($email);
            });
        }

        // Verify tokens were created in database
        foreach ($emailAddresses as $email) {
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

        $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/invite', [
                'email' => 'test@company.com'
            ]);

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

        $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/invite', [
                'email' => 'test@company.com'
            ]);

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
        $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/invite', [
                'email' => $email
            ]);

        $firstToken = EmployeeRegistrationToken::where('email', $email)->first();
        $this->assertEquals('pending', $firstToken->status);

        // Send second invitation to same email
        $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/invite', [
                'email' => $email
            ]);

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
    public function invitation_validation_prevents_invalid_emails()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/invite', [
                'email' => 'invalid-email'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        Mail::assertNothingSent();
    }

    /** @test */
    public function bulk_invitation_filters_invalid_emails()
    {
        Mail::fake();

        $emails = "valid@company.com\ninvalid-email\nanother@company.com\n@invalid.com";

        $response = $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/bulk-invite', [
                'emails' => $emails
            ]);

        $response->assertStatus(200);

        // Only valid emails should have been processed
        Mail::assertSent(EmployeeRegistrationInvitation::class, 2);

        Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) {
            return $mail->hasTo('valid@company.com') || $mail->hasTo('another@company.com');
        });
    }

    /** @test */
    public function token_expiration_is_set_correctly()
    {
        Mail::fake();

        $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/invite', [
                'email' => 'test@company.com'
            ]);

        $token = EmployeeRegistrationToken::where('email', 'test@company.com')->first();

        // Token should expire in 7 days (default)
        $expectedExpiration = now()->addDays(7);
        $this->assertTrue($token->expires_at->diffInMinutes($expectedExpiration) < 1);
    }

    /** @test */
    public function email_template_receives_correct_data()
    {
        Mail::fake();

        $this->actingAs($this->adminUser)
            ->postJson('/employee-registrations/invite', [
                'email' => 'test@company.com'
            ]);

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
    public function unauthenticated_user_cannot_send_invitations()
    {
        $response = $this->postJson('/employee-registrations/invite', [
            'email' => 'test@company.com'
        ]);

        $response->assertStatus(401);
        Mail::assertNothingSent();
    }
}
