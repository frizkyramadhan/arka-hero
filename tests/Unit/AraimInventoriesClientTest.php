<?php

namespace Tests\Unit;

use App\Services\AraimInventoriesClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AraimInventoriesClientTest extends TestCase
{
    public function test_unconfigured_returns_empty_without_http(): void
    {
        config(['araim.base_url' => '', 'araim.api_key' => '']);

        Http::fake();

        $result = app(AraimInventoriesClient::class)->inventoriesForEmployee('abc-uuid');

        $this->assertSame('unconfigured', $result['status']);
        $this->assertSame([], $result['items']);
        Http::assertNothingSent();
    }

    public function test_missing_api_key_is_unconfigured(): void
    {
        config(['araim.base_url' => 'http://araim.test/api', 'araim.api_key' => '']);

        Http::fake();

        $result = app(AraimInventoriesClient::class)->inventoriesForEmployee('abc-uuid');

        $this->assertSame('unconfigured', $result['status']);
        Http::assertNothingSent();
    }

    public function test_maps_inventories_and_history_from_data_wrapper(): void
    {
        config([
            'araim.base_url' => 'http://araim.test/api',
            'araim.api_key' => 'secret-key',
            'araim.timeout' => 5,
        ]);

        Http::fake([
            'araim.test/api/hero/employees/hero-1/inventories' => Http::response([
                'data' => [
                    [
                        'inventory_no' => 'INV-01',
                        'asset_name' => 'Laptop',
                        'serial_no' => 'SN-9',
                        'brand_name' => 'Dell',
                        'model_asset' => 'XPS 13',
                        'part_no' => 'P-1',
                        'quantity' => 1,
                        'inventory_status' => 'Good',
                        'transfer_status' => 'Available',
                        'project_code' => '000H',
                        'project_name' => 'Head Office',
                        'department_name' => 'IT',
                        'location_name' => 'Warehouse A',
                        'remarks' => 'Dock included',
                        'date' => '2026-01-02',
                        'history' => [
                            [
                                'holder_name' => 'Alice',
                                'nik' => '1001',
                                'hero_employee_id' => 'hero-1',
                                'inventory_status' => 'Good',
                                'transfer_status' => 'Available',
                                'quantity' => 1,
                                'date' => '2026-01-02',
                            ],
                            [
                                'holder_name' => 'Bob',
                                'nik' => '1002',
                                'hero_employee_id' => 'hero-2',
                                'inventory_status' => 'Good',
                                'transfer_status' => 'Mutated',
                                'quantity' => 1,
                                'date' => '2025-12-01',
                            ],
                        ],
                    ],
                    [
                        'inventory_no' => 'INV-09',
                        'asset_name' => 'Monitor',
                        'quantity' => 1,
                        'inventory_status' => 'Good',
                        'transfer_status' => 'Available',
                        'date' => '2026-03-01',
                        'history' => [],
                    ],
                ],
            ], 200),
        ]);

        $result = app(AraimInventoriesClient::class)->inventoriesForEmployee('hero-1');

        $this->assertSame('ok', $result['status']);
        $this->assertCount(2, $result['items']);
        $this->assertSame(['INV-09', 'INV-01'], array_column($result['items'], 'inventory_no'));
        $this->assertSame('Laptop', $result['items'][1]['asset_name']);
        $this->assertSame('SN-9', $result['items'][1]['serial_no']);
        $this->assertSame('Dell', $result['items'][1]['brand_name']);
        $this->assertSame('XPS 13', $result['items'][1]['model_asset']);
        $this->assertSame('Good', $result['items'][1]['inventory_status']);
        $this->assertSame('000H', $result['items'][1]['project_code']);
        $this->assertSame('Warehouse A', $result['items'][1]['location_name']);
        $this->assertSame('Dock included', $result['items'][1]['remarks']);
        $this->assertCount(2, $result['items'][1]['history']);
        $this->assertSame('Alice', $result['items'][1]['history'][0]['holder_name']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-Key', 'secret-key')
                && $request->url() === 'http://araim.test/api/hero/employees/hero-1/inventories';
        });
    }

    public function test_http_error_returns_error_status(): void
    {
        config([
            'araim.base_url' => 'http://araim.test/api',
            'araim.api_key' => 'secret-key',
            'araim.timeout' => 5,
        ]);

        Http::fake([
            'araim.test/*' => Http::response(['message' => 'nope'], 500),
        ]);

        $result = app(AraimInventoriesClient::class)->inventoriesForEmployee('hero-1');

        $this->assertSame('error', $result['status']);
        $this->assertSame([], $result['items']);
    }
}
