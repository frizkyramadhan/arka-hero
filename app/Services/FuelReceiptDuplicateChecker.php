<?php

namespace App\Services;

use App\Models\FuelRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FuelReceiptDuplicateChecker
{
    /**
     * Find an existing non-rejected fuel record that looks like the same SPBU nota.
     *
     * Match key: vehicle_id + fuel_date + normalized receipt_number.
     * `$totalCost` is accepted for callers but matching does not require it
     * (avoids false negatives from OCR total variance).
     *
     * Returns null when receipt_number is empty (cannot safely match).
     */
    public function findDuplicate(
        string $vehicleId,
        mixed $fuelDate,
        ?string $receiptNumber,
        mixed $totalCost = null,
        ?string $ignoreRecordId = null,
    ): ?FuelRecord {
        $normalized = $this->normalizeReceiptNumber($receiptNumber);
        if ($normalized === null) {
            return null;
        }

        $date = $this->normalizeDate($fuelDate);
        if ($date === null) {
            return null;
        }

        $query = FuelRecord::query()
            ->where('vehicle_id', $vehicleId)
            ->whereDate('fuel_date', $date)
            ->where('status', '!=', FuelRecord::STATUS_REJECTED)
            ->whereNotNull('receipt_number')
            ->where('receipt_number', '!=', '');

        if ($ignoreRecordId) {
            $query->where('id', '!=', $ignoreRecordId);
        }

        $candidates = $query->orderByDesc('created_at')->get();

        return $candidates->first(function (FuelRecord $record) use ($normalized) {
            return $this->normalizeReceiptNumber($record->receipt_number) === $normalized;
        });
    }

    public function assertNotDuplicate(
        string $vehicleId,
        mixed $fuelDate,
        ?string $receiptNumber,
        mixed $totalCost = null,
        ?string $ignoreRecordId = null,
    ): void {
        $dup = $this->findDuplicate($vehicleId, $fuelDate, $receiptNumber, $totalCost, $ignoreRecordId);
        if (! $dup) {
            return;
        }

        throw new \RuntimeException($this->messageFor($dup));
    }

    public function messageFor(FuelRecord $duplicate): string
    {
        $date = optional($duplicate->fuel_date)->format('Y-m-d') ?? '—';
        $no = $duplicate->receipt_number ?: '—';

        return "Nota yang sama sudah tercatat (No. Trans {$no}, tanggal {$date}, status {$duplicate->status}).";
    }

    public function normalizeReceiptNumber(?string $receiptNumber): ?string
    {
        if ($receiptNumber === null) {
            return null;
        }

        $normalized = Str::upper(preg_replace('/\s+/', '', trim($receiptNumber)) ?? '');
        $normalized = ltrim($normalized, '#');

        return $normalized !== '' ? $normalized : null;
    }

    protected function normalizeDate(mixed $fuelDate): ?string
    {
        try {
            return Carbon::parse($fuelDate)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
