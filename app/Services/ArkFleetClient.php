<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArkFleetClient
{
    public function isConfigured(): bool
    {
        return ! empty(config('ark_fleet.base_url'));
    }

    /**
     * Fetch vehicle equipments from ArkFleet (Light Vehicles + Bus + extras; cached briefly).
     *
     * @return array{success: bool, message?: string, data: array<int, array<string, mixed>>}
     */
    public function getLightVehicleEquipments(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'ArkFleet base URL is not configured.',
                'data' => [],
            ];
        }

        $plantGroupIds = $this->allowedPlantGroupIds();
        $extraUnitNos = $this->normalizedExtraUnitNos();
        $cacheKey = 'ark_fleet.vehicles.'.md5(implode(',', $plantGroupIds).'|'.implode(',', $extraUnitNos));

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['success'] ?? false) === true) {
            return $cached;
        }

        $result = $this->fetchLightVehicleEquipments($plantGroupIds, $extraUnitNos);

        if (($result['success'] ?? false) === true) {
            Cache::put($cacheKey, $result, now()->addMinutes(5));
        }

        return $result;
    }

    /**
     * @param  array<int, int>  $plantGroupIds
     * @param  array<int, string>  $extraUnitNos  Normalized unit codes (no spaces, upper)
     * @return array{success: bool, message?: string, data: array<int, array<string, mixed>>}
     */
    protected function fetchLightVehicleEquipments(array $plantGroupIds, array $extraUnitNos = []): array
    {
        $url = rtrim((string) config('ark_fleet.base_url'), '/').'/api/equipments';

        try {
            $response = Http::timeout((int) config('ark_fleet.timeout', 30))
                ->acceptJson()
                ->withHeaders($this->headers())
                ->get($url);

            if (! $response->successful()) {
                Log::warning('ArkFleet equipments request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'ArkFleet API returned HTTP '.$response->status(),
                    'data' => [],
                ];
            }

            $payload = $response->json();
            $items = $this->normalizeList($payload);

            $filtered = collect($items)
                ->filter(function ($row) use ($plantGroupIds, $extraUnitNos) {
                    $id = (int) ($row['plant_group_id'] ?? 0);
                    $name = strtolower(trim((string) ($row['plant_group'] ?? '')));
                    $unitNo = $this->normalizeUnitNo($row['unit_no'] ?? null);

                    return in_array($id, $plantGroupIds, true)
                        || str_contains($name, 'light vehicle')
                        || $name === 'bus'
                        || ($unitNo !== '' && in_array($unitNo, $extraUnitNos, true));
                })
                ->map(fn ($row) => $this->mapEquipment($row))
                ->values()
                ->all();

            return [
                'success' => true,
                'data' => $filtered,
            ];
        } catch (\Throwable $e) {
            Log::error('ArkFleet equipments request exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * @return array<int, int>
     */
    protected function allowedPlantGroupIds(): array
    {
        return collect([
            (int) config('ark_fleet.light_vehicle_plant_group_id', 3),
            (int) config('ark_fleet.bus_plant_group_id', 5),
        ])
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function normalizedExtraUnitNos(): array
    {
        return collect(config('ark_fleet.extra_unit_nos', []))
            ->map(fn ($code) => $this->normalizeUnitNo($code))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeUnitNo(mixed $unitNo): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $unitNo) ?? '');
    }

    /**
     * @return array<string, string>
     */
    protected function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'X-Source' => 'arka-hero',
        ];

        $apiKey = (string) config('ark_fleet.api_key', '');
        if ($apiKey !== '') {
            $headers['X-API-Key'] = $apiKey;
            $headers['Authorization'] = 'Bearer '.$apiKey;
        }

        return $headers;
    }

    /**
     * @param  mixed  $payload
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeList($payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (array_is_list($payload)) {
            return $payload;
        }

        foreach (['data', 'equipments', 'items', 'results'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return array_is_list($payload[$key]) ? $payload[$key] : [];
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function mapEquipment(array $row): array
    {
        return [
            'id' => $row['id'] ?? null,
            'unit_no' => $row['unit_no'] ?? null,
            'nomor_polisi' => $row['nomor_polisi'] ?? null,
            'description' => $row['description'] ?? null,
            'manufacture' => $row['manufacture'] ?? null,
            'model' => $row['model'] ?? null,
            'bahan_bakar' => $row['bahan_bakar'] ?? null,
            'warna' => $row['warna'] ?? null,
            'capacity' => $row['capacity'] ?? null,
            'unitstatus' => $row['unitstatus'] ?? null,
            'project_code' => $row['project_code'] ?? null,
            'plant_group' => $row['plant_group'] ?? null,
            'plant_group_id' => $row['plant_group_id'] ?? null,
            'serial_no' => $row['serial_no'] ?? null,
            'machine_no' => $row['machine_no'] ?? null,
            'remarks' => $row['remarks'] ?? null,
        ];
    }
}
