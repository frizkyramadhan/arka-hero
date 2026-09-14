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
    |
    */
    'driver' => env('RECEIPT_AI_DRIVER', 'openrouter'),

    'local' => [
        'base_url' => trim((string) env('LOCAL_LLM_BASE_URL', ''), " \t\n\r\0\x0B\"'"),
        'api_key' => trim((string) env('LOCAL_LLM_API_KEY', ''), " \t\n\r\0\x0B\"'"),
        'model' => trim((string) env('LOCAL_LLM_MODEL', ''), " \t\n\r\0\x0B\"'"),
        'timeout' => (int) env('LOCAL_LLM_TIMEOUT', 120),
    ],
];
