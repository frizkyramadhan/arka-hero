<?php

/**
 * ponytail: smoke-check FOA persist error mapping (no DB).
 * Run: php scripts/check_foa_persist_error_message.php
 */
require __DIR__.'/../vendor/autoload.php';

use App\Http\Controllers\VehicleAssignmentController;
use Illuminate\Database\QueryException;

$ref = new ReflectionClass(VehicleAssignmentController::class);
$method = $ref->getMethod('foaPersistErrorMessage');
$method->setAccessible(true);
$controller = $ref->newInstanceWithoutConstructor();

$mk = static function (string $sqlMessage): QueryException {
    return new QueryException('mysql', $sqlMessage, [], new PDOException($sqlMessage, 23000));
};

$cases = [
    [$mk("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '15501' for key 'vehicle_assignments_letter_number_id_unique'"), 'Selected letter number is already linked to another FOA.'],
    [$mk("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'FOA4964' for key 'vehicle_assignments_form_number_unique'"), 'FOA No already exists on another assignment'],
    [new RuntimeException('boom'), 'boom'],
];

foreach ($cases as [$err, $expect]) {
    $got = $method->invoke($controller, $err);
    if ($got !== $expect && ! str_contains($got, $expect)) {
        fwrite(STDERR, "FAIL: expected containing [{$expect}], got [{$got}]\n");
        exit(1);
    }
}

echo "OK foaPersistErrorMessage\n";
