<?php

use App\Models\FlightRequest;
use App\Models\FlightRequestIssuance;
use App\Models\LeaveRequest;
use App\Models\Officialtravel;
use App\Models\OvertimeRequest;
use App\Models\RecruitmentRequest;
use App\Models\RoomConsumptionRequest;

return [

    /*
    |--------------------------------------------------------------------------
    | Document approval email notifications
    |--------------------------------------------------------------------------
    |
    | Toggle production approval emails (submit / next approver / approved /
    | rejected / reminders). Set DOCUMENT_NOTIFICATIONS_ENABLED=false in .env
    | to disable. Audit logging still runs. Debug Email Notify preview/send
    | is separate and is not gated by this flag.
    |
    */
    'enabled' => filter_var(env('DOCUMENT_NOTIFICATIONS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    | Absolute root used by CTA links and email logo URLs. Keep this separate
    | from APP_URL so local previews can still point to the production server.
    */
    'base_url' => rtrim(
        env('DOCUMENT_NOTIFICATIONS_BASE_URL', env('APP_URL', 'http://localhost')),
        '/'
    ),

    'logo_path' => env('DOCUMENT_NOTIFICATIONS_LOGO_PATH', '/images/logo_2.jpg'),

    /*
    | Overdue approval reminders (schedule: documents:remind-pending-approvals).
    */
    'reminder_enabled' => filter_var(env('DOCUMENT_NOTIFICATIONS_REMINDER_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'reminder_days' => (int) env('DOCUMENT_NOTIFICATIONS_REMINDER_DAYS', 3),

    /*
    | Optional CC addresses per document type (applied on approved/rejected only).
    | Example: 'leave_request' => ['hr@arka.co.id'],
    */
    'cc' => [
        // 'leave_request' => [],
    ],

    'document_types' => [
        'officialtravel' => Officialtravel::class,
        'recruitment_request' => RecruitmentRequest::class,
        'leave_request' => LeaveRequest::class,
        'flight_request' => FlightRequest::class,
        'flight_request_issuance' => FlightRequestIssuance::class,
        'overtime_request' => OvertimeRequest::class,
        'room_consumption_request' => RoomConsumptionRequest::class,
    ],

    'labels' => [
        'officialtravel' => 'Official Travel',
        'recruitment_request' => 'Recruitment Request (FPTK)',
        'leave_request' => 'Leave Request',
        'flight_request' => 'Flight Request',
        'flight_request_issuance' => 'Flight Ticket Issuance',
        'overtime_request' => 'Overtime Request',
        'room_consumption_request' => 'Room & Consumption Request',
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@arka.co.id'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'ARKA HERO')),
    ],

];
