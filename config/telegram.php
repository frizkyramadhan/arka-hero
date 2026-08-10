<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Fuel Bot
    |--------------------------------------------------------------------------
    */
    'bot_token' => env('TELEGRAM_FUEL_BOT_TOKEN', ''),
    'webhook_secret' => env('TELEGRAM_FUEL_BOT_WEBHOOK_SECRET', ''),
    'api_base' => env('TELEGRAM_API_BASE', 'https://api.telegram.org'),
    'timeout' => (int) env('TELEGRAM_TIMEOUT', 60),
];
