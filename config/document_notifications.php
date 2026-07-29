<?php

use App\Models\FlightRequest;
use App\Models\FlightRequestIssuance;
use App\Models\LeaveRequest;
use App\Models\Officialtravel;
use App\Models\OvertimeRequest;
use App\Models\RecruitmentRequest;
use App\Models\RoomConsumptionRequest;

return [

    'enabled' => env('DOCUMENT_NOTIFICATIONS_ENABLED', true),

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
