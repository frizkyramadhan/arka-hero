<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Receipt AI driver
    |--------------------------------------------------------------------------
    |
    | Fuel log (UI + Telegram) vision parse. Pick one:
    |   openrouter — cloud OpenRouter (config/openrouter.php + OPENROUTER_*)
    |   local      — LAN OpenAI-compatible LLM (LOCAL_LLM_* below)
    |   9router    — 9router OpenAI-compatible endpoint (NINEROUTER_* below)
    |
    */
    'driver' => env('RECEIPT_AI_DRIVER', 'openrouter'),

    /*
    |--------------------------------------------------------------------------
    | Local LLM (OpenAI-compatible)
    |--------------------------------------------------------------------------
    |
    | Used when RECEIPT_AI_DRIVER=local. AnythingLLM typically needs
    | …/api/v1/openai as base_url.
    |
    */
    'local' => [
        'base_url' => trim((string) env('LOCAL_LLM_BASE_URL', ''), " \t\n\r\0\x0B\"'"),
        'api_key' => trim((string) env('LOCAL_LLM_API_KEY', ''), " \t\n\r\0\x0B\"'"),
        'model' => trim((string) env('LOCAL_LLM_MODEL', ''), " \t\n\r\0\x0B\"'"),
        'timeout' => (int) env('LOCAL_LLM_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | 9router (OpenAI-compatible)
    |--------------------------------------------------------------------------
    |
    | Used when RECEIPT_AI_DRIVER=9router. Base URL must include /v1
    | (chat completions: {base_url}/chat/completions). Defaults to SSE;
    | parser sends stream=false.
    |
    */
    '9router' => [
        'base_url' => trim((string) env('NINEROUTER_BASE_URL', ''), " \t\n\r\0\x0B\"'"),
        'api_key' => trim((string) env('NINEROUTER_API_KEY', ''), " \t\n\r\0\x0B\"'"),
        'model' => trim((string) env('NINEROUTER_MODEL', ''), " \t\n\r\0\x0B\"'"),
        'timeout' => (int) env('NINEROUTER_TIMEOUT', 120),
    ],
];
