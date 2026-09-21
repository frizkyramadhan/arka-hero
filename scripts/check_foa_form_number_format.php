<?php

/**
 * ponytail: smoke-check FOA No formatting (no DB).
 * Run: php scripts/check_foa_form_number_format.php
 */
require __DIR__.'/../vendor/autoload.php';

use App\Models\VehicleAssignment;

$cases = [
    ['FOA4965', 'APS', 'FOA-APS-4965'],
    ['FOA4965', '000H', 'FOA-000H-4965'],
    ['FOA4965', null, 'FOA4965'],
    ['4965', 'aps', 'FOA-APS-4965'],
    ['FOA-APS-4965', 'APS', 'FOA-APS-4965'],
];

foreach ($cases as [$letter, $code, $expect]) {
    $got = VehicleAssignment::formatFormNumber($letter, $code);
    if ($got !== $expect) {
        fwrite(STDERR, "FAIL: formatFormNumber({$letter}, {$code}) => [{$got}], expected [{$expect}]\n");
        exit(1);
    }
}

echo "OK formatFormNumber\n";
