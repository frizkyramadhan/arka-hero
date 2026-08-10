<?php

namespace App\Services;

use App\Models\FuelBotSubmission;
use App\Models\FuelRecord;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FuelBotIngestService
{
    /**
     * Create a submitted fuel record from a confirmed bot submission.
     *
     * @throws \RuntimeException
     */
    public function syncSubmission(FuelBotSubmission $submission): FuelRecord
    {
        if ($submission->fuel_record_id) {
            $existing = FuelRecord::find($submission->fuel_record_id);
            if ($existing) {
                return $existing;
            }
        }

        $parsed = $submission->parsed_json ?? [];
        $vehicleId = $parsed['vehicle_id'] ?? null;
        if (! $vehicleId && ! empty($parsed['vehicle_code'])) {
            $vehicleId = $this->resolveVehicleId((string) $parsed['vehicle_code']);
        }

        $required = [
            'fuel_date' => 'tanggal BBM',
            'odometer' => 'odometer',
            'fuel_type' => 'jenis BBM',
            'quantity' => 'jumlah liter',
            'price_per_liter' => 'harga per liter',
        ];
        foreach ($required as $key => $label) {
            if (! isset($parsed[$key]) || $parsed[$key] === '' || $parsed[$key] === null) {
                throw new \RuntimeException("Field wajib tidak ditemukan: {$label}.");
            }
        }
        if (! $vehicleId) {
            throw new \RuntimeException('Kendaraan tidak dapat dikenali. Cantumkan kode unit (mis. VA083) pada nota atau caption.');
        }

        if (! Vehicle::where('id', $vehicleId)->exists()) {
            throw new \RuntimeException('Kendaraan tidak ditemukan.');
        }

        $qty = (float) $parsed['quantity'];
        $price = (float) $parsed['price_per_liter'];
        $total = isset($parsed['total_cost']) && $parsed['total_cost'] !== null
            ? (float) $parsed['total_cost']
            : round($qty * $price, 2);

        app(FuelReceiptDuplicateChecker::class)->assertNotDuplicate(
            (string) $vehicleId,
            $parsed['fuel_date'],
            isset($parsed['receipt_number']) ? (string) $parsed['receipt_number'] : null,
            $total,
        );

        return DB::transaction(function () use ($submission, $parsed, $vehicleId, $qty, $price, $total) {
            $submission->loadMissing('user');

            $receiptPath = null;
            if ($submission->receipt_path && Storage::disk('private')->exists($submission->receipt_path)) {
                $ext = pathinfo($submission->receipt_path, PATHINFO_EXTENSION) ?: 'jpg';
                $receiptPath = 'fuel_receipts/'.now()->format('YmdHis').'_bot_'.$submission->id.'.'.$ext;
                Storage::disk('private')->copy($submission->receipt_path, $receiptPath);
            }

            $notes = trim(implode(' | ', array_filter([
                $parsed['notes'] ?? null,
                'Telegram bot ref '.$submission->client_uuid,
            ])));

            $record = FuelRecord::create([
                'vehicle_id' => $vehicleId,
                'fuel_date' => $parsed['fuel_date'],
                'odometer' => (int) $parsed['odometer'],
                'fuel_type' => (string) $parsed['fuel_type'],
                'quantity' => $qty,
                'price_per_liter' => $price,
                'total_cost' => $total,
                'fuel_station' => $parsed['fuel_station'] ?? null,
                'receipt_number' => $parsed['receipt_number'] ?? null,
                'receipt_image' => $receiptPath,
                'notes' => $notes !== '' ? $notes : null,
                'driver_id' => $submission->user?->employee_id,
                'created_by' => $submission->user_id,
                'status' => FuelRecord::STATUS_SUBMITTED,
                'ai_parsed_at' => now(),
                'ai_model' => $submission->ai_model,
                'ai_raw_json' => $parsed['ai_raw_json'] ?? $parsed,
            ]);

            $vehicle = Vehicle::find($vehicleId);
            if ($vehicle && (int) $parsed['odometer'] > (int) $vehicle->odometer) {
                $vehicle->update(['odometer' => (int) $parsed['odometer']]);
            }

            $submission->update([
                'status' => FuelBotSubmission::STATUS_SYNCED,
                'fuel_record_id' => $record->id,
                'synced_at' => now(),
                'error_message' => null,
            ]);

            return $record;
        });
    }

    protected function resolveVehicleId(string $code): ?string
    {
        $normalized = Str::upper(preg_replace('/\s+/', '', $code) ?? '');

        return Vehicle::query()
            ->where('status', 'active')
            ->get(['id', 'kode'])
            ->first(function (Vehicle $v) use ($normalized) {
                return Str::upper(preg_replace('/\s+/', '', (string) $v->kode) ?? '') === $normalized;
            })
            ?->id;
    }
}
