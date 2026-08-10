<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramFuelBotHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramFuelBotHandler $handler): Response
    {
        $secret = (string) config('telegram.webhook_secret');
        if ($secret !== '') {
            $header = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');
            $query = (string) $request->query('secret', '');
            if (! hash_equals($secret, $header) && ! hash_equals($secret, $query)) {
                return response('Unauthorized', 401);
            }
        }

        try {
            $handler->handle($request->all());
        } catch (\Throwable $e) {
            Log::error('Telegram fuel bot webhook error', ['message' => $e->getMessage()]);
        }

        return response('OK', 200);
    }
}
