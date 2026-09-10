<?php

/**
 * ponytail: smoke check for Stock In/Out export headings + import grouping keys.
 * Run: php scripts/check-supply-stock-io-excel.php
 */

require __DIR__.'/../vendor/autoload.php';

$in = new App\Exports\SupplyStockInExport(collect());
$out = new App\Exports\SupplyStockOutExport(collect());

$expectedIn = ['document_number', 'project_code', 'stock_date', 'notes', 'item_code', 'stock_unit', 'quantity', 'remarks'];
$expectedOut = ['document_number', 'project_code', 'stock_date', 'notes', 'item_code', 'stock_unit', 'quantity', 'location', 'person_in_charge'];

assert($in->headings() === $expectedIn, 'Stock In export headings mismatch');
assert($out->headings() === $expectedOut, 'Stock Out export headings mismatch');
assert($in->collection()->isEmpty(), 'Stock In template collection should be empty');
assert($out->collection()->isEmpty(), 'Stock Out template collection should be empty');

echo "OK: supply stock in/out excel headings\n";
