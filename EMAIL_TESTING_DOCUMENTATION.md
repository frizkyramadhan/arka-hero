# Employee Registration System - Email Testing Documentation

## Overview

This document describes the comprehensive unit and integration tests for the email functionality in the Employee Registration System. The tests ensure that the email invitation system works correctly across all scenarios.

## Test Files Created

### 1. `tests/Unit/EmployeeRegistrationInvitationTest.php`

**Purpose**: Unit tests for the `EmployeeRegistrationInvitation` Mailable class

**Test Coverage**:

-   ✅ Mailable instantiation with required parameters
-   ✅ Email envelope configuration (subject line)
-   ✅ Email content configuration (template and data)
-   ✅ Expiration date formatting
-   ✅ Company name configuration
-   ✅ Attachment handling
-   ✅ Queueable functionality
-   ✅ Serialization support
-   ✅ Data passing to email template
-   ✅ Different expiration date scenarios
-   ✅ Various registration URL formats

### 2. `tests/Unit/EmployeeRegistrationServiceEmailTest.php`

**Purpose**: Unit tests for email-related methods in `EmployeeRegistrationService`

**Test Coverage**:

-   ✅ Successful email sending
-   ✅ Registration URL generation
-   ✅ Single employee invitation flow
-   ✅ Bulk employee invitation processing
-   ✅ Data structure validation
-   ✅ Token deactivation logic
-   ✅ Email content validation
-   ✅ Email subject verification
-   ✅ Template usage verification

### 3. `tests/Feature/EmployeeRegistrationEmailIntegrationTest.php`

**Purpose**: Integration tests for the complete email workflow from controller to email delivery

**Test Coverage**:

-   ✅ Admin single invitation endpoint
-   ✅ Admin bulk invitation endpoint
-   ✅ Email content and structure validation
-   ✅ Registration link validation
-   ✅ Token management and deactivation
-   ✅ Email validation and error handling
-   ✅ Invalid email filtering
-   ✅ Token expiration settings
-   ✅ Authentication requirements
-   ✅ Data integrity checks

## Key Testing Features

### Email Mocking

All tests use Laravel's `Mail::fake()` to prevent actual email sending during testing:

```php
Mail::fake();

// Test code here...

Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) {
    return $mail->hasTo('test@example.com');
});
```

### Database Testing

Tests use `RefreshDatabase` trait to ensure clean database state:

```php
use RefreshDatabase;

// Database is automatically reset between tests
```

### Route Mocking

Tests mock the registration route to avoid dependency issues:

```php
Route::get('/employee-registration/{token}', function () {
    return 'test';
})->name('employee.registration.form');
```

## Test Scenarios Covered

### 1. Email Structure Tests

-   **Subject Line**: Verifies correct email subject
-   **Template**: Ensures correct Blade template is used
-   **Data Passing**: Validates all required data is passed to template
-   **Recipient**: Confirms email goes to correct recipient

### 2. Business Logic Tests

-   **Token Generation**: Tests unique token creation
-   **Token Deactivation**: Verifies old tokens are expired
-   **URL Generation**: Tests registration URL creation
-   **Expiration Handling**: Validates token expiration logic

### 3. Integration Tests

-   **Controller Integration**: Tests complete flow from HTTP request to email
-   **Service Integration**: Tests service layer email methods
-   **Database Integration**: Verifies database operations during email flow
-   **Validation Integration**: Tests email validation and error handling

### 4. Error Handling Tests

-   **Invalid Emails**: Tests handling of malformed email addresses
-   **Authentication**: Verifies unauthorized access prevention
-   **Validation Errors**: Tests proper error responses

### 5. Bulk Operations Tests

-   **Multiple Emails**: Tests bulk invitation processing
-   **Mixed Results**: Tests handling of partial failures
-   **Data Structure**: Validates response format for bulk operations

## Running the Tests

### Run All Email Tests

```bash
# Run all tests in the email test files
php artisan test tests/Unit/EmployeeRegistrationInvitationTest.php
php artisan test tests/Unit/EmployeeRegistrationServiceEmailTest.php
php artisan test tests/Feature/EmployeeRegistrationEmailIntegrationTest.php
```

### Run Specific Test Categories

```bash
# Run only unit tests
php artisan test tests/Unit/

# Run only feature tests
php artisan test tests/Feature/

# Run with coverage (if configured)
php artisan test --coverage
```

### Run Individual Tests

```bash
# Run specific test method
php artisan test --filter=it_can_send_registration_invitation_successfully

# Run specific test class
php artisan test tests/Unit/EmployeeRegistrationInvitationTest.php
```

## Test Data Requirements

### User Factory

Tests require a User factory for creating test users:

```php
// Ensure this exists in database/factories/UserFactory.php
$user = User::factory()->create();
```

### Database Tables

Tests require these tables to exist:

-   `users`
-   `employee_registration_tokens`
-   `employee_registrations` (for some integration tests)

## Test Assertions Used

### Email Assertions

```php
// Email was sent
Mail::assertSent(EmployeeRegistrationInvitation::class);

// Email sent to specific recipient
Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) {
    return $mail->hasTo('test@example.com');
});

// Specific number of emails sent
Mail::assertSent(EmployeeRegistrationInvitation::class, 3);

// No emails sent
Mail::assertNothingSent();
```

### Database Assertions

```php
// Record exists in database
$this->assertDatabaseHas('employee_registration_tokens', [
    'email' => 'test@example.com',
    'status' => 'pending'
]);
```

### HTTP Assertions

```php
// Response status and JSON structure
$response->assertStatus(200)
    ->assertJson(['success' => true]);

// Validation errors
$response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);
```

## Mock Objects and Fakes

### Mail Facade

```php
// Fake mail sending
Mail::fake();

// Assert emails were sent
Mail::assertSent(EmployeeRegistrationInvitation::class);
```

### Route Registration

```php
// Mock route for testing
Route::get('/employee-registration/{token}', function () {
    return 'test';
})->name('employee.registration.form');
```

## Test Environment Setup

### Required Configuration

Ensure these are set in your testing environment:

```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# Mail
MAIL_MAILER=log

# App
APP_NAME=HCSSIS
APP_ENV=testing
```

### Required Dependencies

```json
{
    "require-dev": {
        "phpunit/phpunit": "^9.5",
        "mockery/mockery": "^1.4",
        "fakerphp/faker": "^1.9.1"
    }
}
```

## Test Coverage Goals

### Unit Tests Coverage

-   ✅ All public methods in `EmployeeRegistrationInvitation`
-   ✅ All email-related methods in `EmployeeRegistrationService`
-   ✅ Edge cases and error conditions
-   ✅ Data validation and formatting

### Integration Tests Coverage

-   ✅ Complete email workflow from HTTP request to email delivery
-   ✅ Authentication and authorization
-   ✅ Database operations during email flow
-   ✅ Error handling and validation

### Functional Tests Coverage

-   ✅ Single and bulk invitation workflows
-   ✅ Token management and lifecycle
-   ✅ Email content and structure validation
-   ✅ Business logic validation

## Continuous Integration

### GitHub Actions Example

```yaml
name: Email Tests
on: [push, pull_request]
jobs:
    test:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v2
            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: "8.1"
            - name: Install Dependencies
              run: composer install
            - name: Run Email Tests
              run: |
                  php artisan test tests/Unit/EmployeeRegistrationInvitationTest.php
                  php artisan test tests/Unit/EmployeeRegistrationServiceEmailTest.php
                  php artisan test tests/Feature/EmployeeRegistrationEmailIntegrationTest.php
```

## Best Practices Applied

### 1. Test Isolation

-   Each test is independent and can run in any order
-   Database is reset between tests
-   Mail fake is reset between tests

### 2. Descriptive Test Names

-   Test names clearly describe what is being tested
-   Uses `it_` prefix for behavior-driven naming

### 3. Comprehensive Coverage

-   Tests cover happy path, edge cases, and error conditions
-   Both positive and negative test cases included

### 4. Realistic Test Data

-   Uses realistic email addresses and data
-   Tests with various input scenarios

### 5. Clear Assertions

-   Each test has clear, specific assertions
-   Assertions test one concept per test method

## Troubleshooting

### Common Issues

1. **Route Not Found Errors**

    - Ensure route mocking is set up in `setUp()` method
    - Check route name matches what's used in service

2. **Database Errors**

    - Ensure `RefreshDatabase` trait is used
    - Check that required tables exist in migrations

3. **Mail Assertion Failures**

    - Ensure `Mail::fake()` is called before testing
    - Check that email addresses in tests match exactly

4. **Factory Errors**
    - Ensure User factory exists and is properly configured
    - Check that factory creates all required fields

### Debugging Tips

```php
// Debug email content
Mail::assertSent(EmployeeRegistrationInvitation::class, function ($mail) {
    dump($mail->content()->with); // Debug email data
    return true;
});

// Debug database state
$this->assertDatabaseHas('employee_registration_tokens', [
    'email' => 'test@example.com'
]);
```

## Conclusion

This comprehensive test suite ensures that the email functionality in the Employee Registration System is robust, reliable, and handles all edge cases appropriately. The tests provide confidence that the email invitation system will work correctly in production environments.

---

**Total Test Methods**: 25+  
**Coverage Areas**: Mailable class, Service layer, Controller integration, Error handling  
**Test Types**: Unit, Integration, Feature  
**Frameworks Used**: PHPUnit, Laravel Testing, Mail Fake
