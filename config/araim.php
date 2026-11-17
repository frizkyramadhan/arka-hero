<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ARAIM (inventories)
    |--------------------------------------------------------------------------
    |
    | Hero reads current inventories live — no local inventory copy:
    |   GET {ARAIM_API_URL}/hero/employees/{heroEmployeeId}/inventories
    |
    | ARAIM_API_URL should include the /api prefix, e.g.
    |   http://localhost/araim.v2/api
    | (mirrors ARAIM HERO_API_URL = http://localhost/arka-hero/api).
    |
    | Empty base_url or api_key disables remote calls (pane unconfigured).
    | ARAIM_API_KEY is sent as X-API-Key and must equal ARAIM's API_KEY.
    | Bearer / Sanctum is not accepted by ARAIM hero routes.
    |
    */
    'base_url' => env('ARAIM_API_URL', ''),
    'api_key' => env('ARAIM_API_KEY', ''),
    'timeout' => (int) env('ARAIM_TIMEOUT', 5),
];
