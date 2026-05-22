<?php

namespace App\Services;

use App\Models\Officialtravel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfficialTravelLotNumberNormalizer
{
    private const OLD_FORMAT_PATTERN = '/^ARKA\/([^\/]+)\/HR\/([^\/]+)\/(\d{4})$/';

    private const NEW_FORMAT_PATTERN = '/^ARKA\/([^\/]+)\/HR-([^\/]+)\/([^\/]+)\/(\d{4})$/';

    /**
     * Normalize legacy LOT numbers from ARKA/{Letter}/HR/{month}/{year}
     * to ARKA/{Letter}/HR-{Project Code}/{month}/{year}.
     *
     * @return array{updated: int, skipped: int, failed: int}
     */
    public function normalize(): array
    {
        $stats = [
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        Officialtravel::query()
            ->with(['project', 'letterNumber.project'])
            ->orderBy('created_at')
            ->chunk(100, function ($travels) use (&$stats) {
                DB::transaction(function () use ($travels, &$stats) {
                    foreach ($travels as $travel) {
                        $this->normalizeOne($travel, $stats);
                    }
                });
            });

        return $stats;
    }

    /**
     * @param  array{updated: int, skipped: int, failed: int}  $stats
     */
    private function normalizeOne(Officialtravel $travel, array &$stats): void
    {
        $current = trim((string) $travel->official_travel_number);

        if ($current === '' || str_starts_with($current, 'REQ')) {
            $stats['skipped']++;

            return;
        }

        if (preg_match(self::NEW_FORMAT_PATTERN, $current)) {
            $stats['skipped']++;

            return;
        }

        if (! preg_match(self::OLD_FORMAT_PATTERN, $current, $matches)) {
            Log::warning('LOT number normalization skipped: unrecognized format', [
                'id' => $travel->id,
                'official_travel_number' => $current,
            ]);
            $stats['skipped']++;

            return;
        }

        [, $letterNumber, $romanMonth, $year] = $matches;

        $projectCode = $this->resolveProjectCode($travel);
        if ($projectCode === null) {
            Log::warning('LOT number normalization failed: missing project code', [
                'id' => $travel->id,
                'official_travel_number' => $current,
                'official_travel_origin' => $travel->official_travel_origin,
                'letter_number_id' => $travel->letter_number_id,
            ]);
            $stats['failed']++;

            return;
        }

        $newNumber = sprintf(
            'ARKA/%s/HR-%s/%s/%s',
            $letterNumber,
            $projectCode,
            $romanMonth,
            $year
        );

        if ($newNumber === $current) {
            $stats['skipped']++;

            return;
        }

        $duplicate = Officialtravel::query()
            ->where('official_travel_number', $newNumber)
            ->where('id', '!=', $travel->id)
            ->exists();

        if ($duplicate) {
            Log::warning('LOT number normalization failed: target number already exists', [
                'id' => $travel->id,
                'current' => $current,
                'target' => $newNumber,
            ]);
            $stats['failed']++;

            return;
        }

        $travel->update(['official_travel_number' => $newNumber]);
        $stats['updated']++;

        Log::info('LOT number normalized', [
            'id' => $travel->id,
            'from' => $current,
            'to' => $newNumber,
        ]);
    }

    private function resolveProjectCode(Officialtravel $travel): ?string
    {
        $fromOrigin = $travel->project?->project_code;
        if (filled($fromOrigin)) {
            return trim((string) $fromOrigin);
        }

        $fromLetter = $travel->letterNumber?->project?->project_code;
        if (filled($fromLetter)) {
            return trim((string) $fromLetter);
        }

        return null;
    }
}
