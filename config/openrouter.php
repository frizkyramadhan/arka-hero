<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenRouter (vision receipt parsing)
    |--------------------------------------------------------------------------
    |
    | Used by driver fuel log: photo of SPBU nota → structured JSON fields.
    | Empty api_key disables AI path (manual entry still works).
    |
    */
    'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
    'api_key' => env('OPENROUTER_API_KEY', ''),
    'model' => env('OPENROUTER_MODEL', 'google/gemini-2.0-flash-001'),
    'timeout' => (int) env('OPENROUTER_TIMEOUT', 60),
    'site_url' => env('OPENROUTER_SITE_URL', env('APP_URL', 'http://localhost')),
    'site_name' => env('OPENROUTER_SITE_NAME', 'ARKA HERO'),
];
