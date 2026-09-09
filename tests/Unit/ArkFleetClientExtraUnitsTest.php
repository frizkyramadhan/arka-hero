<?php

namespace Tests\Unit;

use App\Services\ArkFleetClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArkFleetClientExtraUnitsTest extends TestCase
{
    public function test_extra_unit_outside_light_vehicle_is_included(): void
    {
        Cache::flush();
        config([
            'ark_fleet.base_url' => 'http://ark-fleet.test',
            'ark_fleet.light_vehicle_plant_group_id' => 3,
            'ark_fleet.extra_unit_nos' => ['TS001'],
            'ark_fleet.timeout' => 5,
        ]);

        Http::fake([
            'ark-fleet.test/api/equipments' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'unit_no' => 'LV 001',
                        'plant_group_id' => 3,
                        'plant_group' => 'Light Vehicles',
                    ],
                    [
                        'id' => 835,
                        'unit_no' => 'TS 001',
                        'plant_group_id' => 19,
                        'plant_group' => 'Highway  Truck',
                        'description' => 'Highway Truck Support Hino FG 260 JS',
                    ],
                    [
                        'id' => 99,
                        'unit_no' => 'EX 001',
                        'plant_group_id' => 1,
                        'plant_group' => 'Excavator',
                    ],
                ],
            ], 200),
        ]);

        $result = app(ArkFleetClient::class)->getLightVehicleEquipments();

        $this->assertTrue($result['success']);
        $unitNos = collect($result['data'])->pluck('unit_no')->all();
        $this->assertContains('LV 001', $unitNos);
        $this->assertContains('TS 001', $unitNos);
        $this->assertNotContains('EX 001', $unitNos);
    }
}
