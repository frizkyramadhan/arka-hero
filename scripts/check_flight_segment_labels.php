<?php

/**
 * ponytail: self-check for Flight Segment label/reservation helpers.
 * Run: php scripts/check_flight_segment_labels.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FlightRequestDetail;
use Carbon\Carbon;

$d = new FlightRequestDetail;
$d->forceFill([
    'segment_type' => FlightRequestDetail::TYPE_RETURN,
    'flight_date' => '2026-02-10',
    'departure_city' => 'SUB',
    'arrival_city' => 'CGK',
    'flight_time' => '14:30:00',
]);

assert($d->typeLabel() === 'Return', 'typeLabel');
assert(str_contains($d->optionLabel('FRF-1'), 'Return'), 'optionLabel type');
assert(str_contains($d->optionLabel('FRF-1'), 'FRF-1'), 'optionLabel form');
assert(str_contains($d->reservationText(), '10 FEB 2026'), 'reservation date');
assert(str_contains($d->reservationText(), 'SUB CGK'), 'reservation route');

$detail = new App\Models\FlightRequestIssuanceDetail;
$detail->ticket_order = 2;
$detail->setRelation('flightRequestDetail', $d);
assert($detail->resolveFlightSegment() === $d, 'explicit segment link');

echo "OK flight segment helpers\n";
