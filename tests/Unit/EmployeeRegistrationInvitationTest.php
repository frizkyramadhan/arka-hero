<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\EmployeeRegistrationInvitation;
use App\Models\EmployeeRegistrationToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EmployeeRegistrationInvitationTest extends TestCase
{
    use RefreshDatabase;

    private $tokenRecord;
    private $registrationUrl;
    private $mailable;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $user = User::factory()->create();

        // Create a test token record
        $this->tokenRecord = EmployeeRegistrationToken::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'email' => 'test@example.com',
            'token' => \Illuminate\Support\Str::random(64),
            'created_by' => $user->id,
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->registrationUrl = 'https://example.com/employee-registration/test-token';
        $this->mailable = new EmployeeRegistrationInvitation($this->tokenRecord, $this->registrationUrl);
    }

    /** @test */
    public function it_can_be_instantiated_with_required_parameters()
    {
        $mailable = new EmployeeRegistrationInvitation($this->tokenRecord, $this->registrationUrl);

        $this->assertInstanceOf(EmployeeRegistrationInvitation::class, $mailable);
        $this->assertEquals($this->tokenRecord, $mailable->tokenRecord);
        $this->assertEquals($this->registrationUrl, $mailable->registrationUrl);
    }

    /** @test */
    public function it_has_correct_envelope_configuration()
    {
        $envelope = $this->mailable->envelope();

        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertEquals('Employee Registration Invitation - HCSSIS', $envelope->subject);
    }

    /** @test */
    public function it_has_correct_content_configuration()
    {
        $content = $this->mailable->content();

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('emails.employee-registration-invitation', $content->html);

        // Check that required variables are passed to the view
        $with = $content->with;
        $this->assertArrayHasKey('tokenRecord', $with);
        $this->assertArrayHasKey('registrationUrl', $with);
        $this->assertArrayHasKey('expiresAt', $with);
        $this->assertArrayHasKey('companyName', $with);

        $this->assertEquals($this->tokenRecord, $with['tokenRecord']);
        $this->assertEquals($this->registrationUrl, $with['registrationUrl']);
        $this->assertEquals(config('app.name', 'HCSSIS'), $with['companyName']);
    }

    /** @test */
    public function it_formats_expiration_date_correctly()
    {
        $content = $this->mailable->content();
        $with = $content->with;

        $expectedFormat = $this->tokenRecord->expires_at->format('F j, Y \a\t g:i A');
        $this->assertEquals($expectedFormat, $with['expiresAt']);
    }

    /** @test */
    public function it_uses_correct_company_name_from_config()
    {
        // Test with default config
        $content = $this->mailable->content();
        $this->assertEquals(config('app.name', 'HCSSIS'), $content->with['companyName']);

        // Test with custom config
        config(['app.name' => 'Custom Company Name']);
        $content = $this->mailable->content();
        $this->assertEquals('Custom Company Name', $content->with['companyName']);
    }

    /** @test */
    public function it_has_no_attachments_by_default()
    {
        $attachments = $this->mailable->attachments();

        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    /** @test */
    public function it_is_queueable()
    {
        $this->assertTrue(method_exists($this->mailable, 'onQueue'));
        $this->assertTrue(method_exists($this->mailable, 'onConnection'));
    }

    /** @test */
    public function it_can_be_serialized()
    {
        $serialized = serialize($this->mailable);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(EmployeeRegistrationInvitation::class, $unserialized);
        $this->assertEquals($this->tokenRecord->id, $unserialized->tokenRecord->id);
        $this->assertEquals($this->registrationUrl, $unserialized->registrationUrl);
    }

    /** @test */
    public function it_passes_correct_data_to_email_view()
    {
        $content = $this->mailable->content();
        $data = $content->with;

        // Verify all required data is present
        $this->assertArrayHasKey('tokenRecord', $data);
        $this->assertArrayHasKey('registrationUrl', $data);
        $this->assertArrayHasKey('expiresAt', $data);
        $this->assertArrayHasKey('companyName', $data);

        // Verify data types and values
        $this->assertInstanceOf(EmployeeRegistrationToken::class, $data['tokenRecord']);
        $this->assertIsString($data['registrationUrl']);
        $this->assertIsString($data['expiresAt']);
        $this->assertIsString($data['companyName']);

        // Verify URL is properly formatted
        $this->assertStringContainsString('http', $data['registrationUrl']);

        // Verify expiration date is readable
        $this->assertMatchesRegularExpression('/\w+ \d+, \d{4} at \d+:\d+ [AP]M/', $data['expiresAt']);
    }

    /** @test */
    public function it_handles_different_token_expiration_dates()
    {
        // Test with different expiration dates
        $testDates = [
            now()->addDays(1),
            now()->addDays(7),
            now()->addDays(30),
            now()->addHours(1),
        ];

        foreach ($testDates as $date) {
            $this->tokenRecord->expires_at = $date;
            $this->tokenRecord->save();

            $mailable = new EmployeeRegistrationInvitation($this->tokenRecord, $this->registrationUrl);
            $content = $mailable->content();

            $expectedFormat = $date->format('F j, Y \a\t g:i A');
            $this->assertEquals($expectedFormat, $content->with['expiresAt']);
        }
    }

    /** @test */
    public function it_works_with_different_registration_urls()
    {
        $testUrls = [
            'https://example.com/register/token123',
            'http://localhost:8000/employee-registration/abc123',
            'https://mycompany.com/registration/xyz789',
        ];

        foreach ($testUrls as $url) {
            $mailable = new EmployeeRegistrationInvitation($this->tokenRecord, $url);
            $content = $mailable->content();

            $this->assertEquals($url, $content->with['registrationUrl']);
        }
    }
}
