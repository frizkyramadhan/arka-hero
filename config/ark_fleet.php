<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ArkFleet (equipment master)
    |--------------------------------------------------------------------------
    |
    | Vehicle codes for HERO vehicle master come from:
    |   GET {base_url}/api/equipments
    | filtered by plant_group_id (default 3 = Light Vehicles, 5 = Bus)
    | plus optional extra_unit_nos allowlist.
    |
    | Empty base_url disables remote calls (graceful empty list).
    |
    */
    'base_url' => env('ARK_FLEET_BASE_URL', 'http://192.168.32.15/ark-fleet'),
    'api_key' => env('ARK_FLEET_API_KEY', ''),
    'light_vehicle_plant_group_id' => (int) env('ARK_FLEET_LIGHT_VEHICLE_PLANT_GROUP_ID', 3),
    'bus_plant_group_id' => (int) env('ARK_FLEET_BUS_PLANT_GROUP_ID', 5),
    /*
    | Extra unit_no values always included even outside allowed plant groups.
    | Matching ignores spaces/case (TS001 == "TS 001"). Comma-separated in env.
    */
    'extra_unit_nos' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ARK_FLEET_EXTRA_UNIT_NOS', 'TS001'))
    ))),
    'timeout' => (int) env('ARK_FLEET_TIMEOUT', 30),
];
