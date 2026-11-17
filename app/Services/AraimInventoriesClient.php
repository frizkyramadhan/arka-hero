<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AraimInventoriesClient
{
    public function isConfigured(): bool
    {
        return ! empty(config('araim.base_url'))
            && ! empty(config('araim.api_key'));
    }

    /**
     * Current inventories for a Hero employee UUID (live from ARAIM).
     *
     * ARAIM_API_URL already includes the /api prefix (same pattern as ARAIM's HERO_API_URL).
     * Auth is X-API-Key = ARAIM_API_KEY (matches ARAIM API_KEY). Bearer/Sanctum is not accepted.
     *
     * @return array{
     *     status: 'ok'|'empty'|'unconfigured'|'error',
     *     message?: string,
     *     items: array<int, array<string, mixed>>
     * }
     */
    public function inventoriesForEmployee(string $heroEmployeeId): array
    {
        if (! $this->isConfigured()) {
            return [
                'status' => 'unconfigured',
                'message' => 'ARAIM integration is not configured.',
                'items' => [],
            ];
        }

        $url = rtrim((string) config('araim.base_url'), '/')
            .'/hero/employees/'.rawurlencode($heroEmployeeId).'/inventories';

        try {
            $response = Http::timeout((int) config('araim.timeout', 5))
                ->acceptJson()
                ->withHeaders(['X-API-Key' => (string) config('araim.api_key')])
                ->get($url);

            if (! $response->successful()) {
                Log::warning('ARAIM inventories request failed', [
                    'hero_employee_id' => $heroEmployeeId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'status' => 'error',
                    'message' => 'ARAIM returned HTTP '.$response->status(),
                    'items' => [],
                ];
            }

            $items = $this->normalizeInventories($response->json());

            return [
                'status' => $items === [] ? 'empty' : 'ok',
                'items' => $items,
            ];
        } catch (\Throwable $e) {
            Log::warning('ARAIM inventories request exception', [
                'hero_employee_id' => $heroEmployeeId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Could not reach ARAIM: '.$e->getMessage(),
                'items' => [],
            ];
        }
    }

    /**
     * @param  mixed  $payload
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeInventories($payload): array
    {
        $rows = [];
        if (is_array($payload)) {
            if (isset($payload['data']) && is_array($payload['data'])) {
                $rows = $payload['data'];
            } elseif (array_is_list($payload)) {
                $rows = $payload;
            } elseif (isset($payload['inventories']) && is_array($payload['inventories'])) {
                $rows = $payload['inventories'];
            } elseif (isset($payload['holdings']) && is_array($payload['holdings'])) {
                // ponytail: temporary alias if ARAIM still wraps as holdings
                $rows = $payload['holdings'];
            }
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => $this->mapInventory($row))
            ->sortByDesc(fn (array $row) => (string) ($row['inventory_no'] ?? ''))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function mapInventory(array $row): array
    {
        $asset = is_array($row['asset'] ?? null) ? $row['asset'] : [];
        $brand = is_array($row['brand'] ?? null) ? $row['brand'] : [];
        $project = is_array($row['project'] ?? null) ? $row['project'] : [];
        $department = is_array($row['department'] ?? null) ? $row['department'] : [];
        $location = is_array($row['location'] ?? null) ? $row['location'] : [];

        return [
            'inventory_no' => (string) ($row['inventory_no'] ?? $row['inventoryNo'] ?? ''),
            'asset_name' => (string) (
                $row['asset_name']
                ?? $row['assetName']
                ?? $asset['name']
                ?? $asset['asset_name']
                ?? $row['name']
                ?? ''
            ),
            'serial_no' => $this->nullableString($row['serial_no'] ?? $row['serialNo'] ?? null),
            'brand_name' => $this->nullableString(
                $row['brand_name'] ?? $brand['brand_name'] ?? $brand['name'] ?? null
            ),
            'model_asset' => $this->nullableString($row['model_asset'] ?? $row['modelAsset'] ?? null),
            'part_no' => $this->nullableString($row['part_no'] ?? $row['partNo'] ?? null),
            'quantity' => $row['quantity'] ?? $row['qty'] ?? null,
            'inventory_status' => $this->nullableString(
                $row['inventory_status'] ?? $row['inventoryStatus'] ?? null
            ),
            'transfer_status' => $this->nullableString(
                $row['transfer_status'] ?? $row['transferStatus'] ?? null
            ),
            'project_code' => $this->nullableString(
                $row['project_code'] ?? $project['project_code'] ?? null
            ),
            'project_name' => $this->nullableString(
                $row['project_name'] ?? $project['project_name'] ?? null
            ),
            'department_name' => $this->nullableString(
                $row['department_name']
                ?? $department['dept_name']
                ?? $department['department_name']
                ?? null
            ),
            'location_name' => $this->nullableString(
                $row['location_name'] ?? $location['location_name'] ?? $location['name'] ?? null
            ),
            'date' => $row['date'] ?? $row['input_date'] ?? $row['transferred_at'] ?? $row['created_at'] ?? null,
            'remarks' => $this->nullableString($row['remarks'] ?? null),
            'history' => $this->mapHistory($row['history'] ?? $row['holders'] ?? []),
        ];
    }

    /**
     * @param  mixed  $value
     */
    protected function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    /**
     * @param  mixed  $history
     * @return array<int, array<string, mixed>>
     */
    protected function mapHistory($history): array
    {
        if (! is_array($history)) {
            return [];
        }

        return collect($history)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) {
                return [
                    'holder_name' => (string) (
                        $row['holder_name']
                        ?? $row['employee_name']
                        ?? $row['name']
                        ?? ''
                    ),
                    'nik' => isset($row['nik']) ? (string) $row['nik'] : (isset($row['employee_nik']) ? (string) $row['employee_nik'] : null),
                    'hero_employee_id' => $row['hero_employee_id'] ?? $row['heroEmployeeId'] ?? null,
                    'quantity' => $row['quantity'] ?? $row['qty'] ?? null,
                    'inventory_status' => $this->nullableString(
                        $row['inventory_status'] ?? $row['inventoryStatus'] ?? null
                    ),
                    'transfer_status' => $this->nullableString(
                        $row['transfer_status'] ?? $row['transferStatus'] ?? null
                    ),
                    'date' => $row['date'] ?? $row['input_date'] ?? $row['transferred_at'] ?? $row['created_at'] ?? null,
                ];
            })
            ->values()
            ->all();
    }
}
