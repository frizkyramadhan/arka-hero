<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramClient
{
    public function isConfigured(): bool
    {
        return ! empty(config('telegram.bot_token'));
    }

    protected function apiUrl(string $method): string
    {
        $base = rtrim((string) config('telegram.api_base'), '/');
        $token = (string) config('telegram.bot_token');

        return "{$base}/bot{$token}/{$method}";
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function call(string $method, array $payload = []): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'description' => 'Telegram bot token is not configured.'];
        }

        try {
            $response = Http::timeout((int) config('telegram.timeout', 60))
                ->asJson()
                ->post($this->apiUrl($method), $payload);

            $json = $response->json() ?? [];
            if (! $response->successful() || ! ($json['ok'] ?? false)) {
                Log::warning('Telegram API call failed', [
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return is_array($json) ? $json : ['ok' => false];
        } catch (\Throwable $e) {
            Log::error('Telegram API exception', ['method' => $method, 'message' => $e->getMessage()]);

            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    /**
     * @param  array<string, mixed>|null  $replyMarkup
     */
    public function sendMessage(int|string $chatId, string $text, ?array $replyMarkup = null): array
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        if ($replyMarkup) {
            $payload['reply_markup'] = $replyMarkup;
        }

        return $this->call('sendMessage', $payload);
    }

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null): array
    {
        return $this->call('answerCallbackQuery', array_filter([
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ], fn ($v) => $v !== null));
    }

    public function getFile(string $fileId): array
    {
        return $this->call('getFile', ['file_id' => $fileId]);
    }

    public function downloadFileTo(string $filePath, string $absoluteDest): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $token = (string) config('telegram.bot_token');
        $base = rtrim((string) config('telegram.api_base'), '/');
        $url = "{$base}/file/bot{$token}/{$filePath}";

        try {
            $response = Http::timeout((int) config('telegram.timeout', 60))->get($url);
            if (! $response->successful()) {
                return false;
            }
            $dir = dirname($absoluteDest);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            return file_put_contents($absoluteDest, $response->body()) !== false;
        } catch (\Throwable $e) {
            Log::error('Telegram file download failed', ['message' => $e->getMessage()]);

            return false;
        }
    }

    public function setWebhook(string $url, ?string $secretToken = null): array
    {
        $payload = [
            'url' => $url,
            'allowed_updates' => ['message', 'callback_query'],
            'drop_pending_updates' => false,
        ];
        if ($secretToken) {
            $payload['secret_token'] = $secretToken;
        }

        return $this->call('setWebhook', $payload);
    }

    public function deleteWebhook(): array
    {
        return $this->call('deleteWebhook', ['drop_pending_updates' => false]);
    }

    public function getWebhookInfo(): array
    {
        return $this->call('getWebhookInfo');
    }

    /**
     * Register bot menu commands (shown when user types /).
     *
     * @param  list<array{command: string, description: string}>  $commands
     * @return array<string, mixed>
     */
    public function setMyCommands(array $commands, ?string $languageCode = null): array
    {
        $payload = ['commands' => array_values($commands)];
        if ($languageCode) {
            $payload['language_code'] = $languageCode;
        }

        return $this->call('setMyCommands', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMyCommands(?string $languageCode = null): array
    {
        $payload = [];
        if ($languageCode) {
            $payload['language_code'] = $languageCode;
        }

        return $this->call('getMyCommands', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteMyCommands(?string $languageCode = null): array
    {
        $payload = [];
        if ($languageCode) {
            $payload['language_code'] = $languageCode;
        }

        return $this->call('deleteMyCommands', $payload);
    }

    /**
     * Full description shown in an empty chat with the bot (max 512 chars).
     *
     * @return array<string, mixed>
     */
    public function setMyDescription(?string $description, ?string $languageCode = null): array
    {
        $payload = ['description' => $description ?? ''];
        if ($languageCode) {
            $payload['language_code'] = $languageCode;
        }

        return $this->call('setMyDescription', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMyDescription(?string $languageCode = null): array
    {
        $payload = [];
        if ($languageCode) {
            $payload['language_code'] = $languageCode;
        }

        return $this->call('getMyDescription', $payload);
    }

    /**
     * Short “About” text on the bot profile / share link (max 120 chars).
     *
     * @return array<string, mixed>
     */
    public function setMyShortDescription(?string $shortDescription, ?string $languageCode = null): array
    {
        $payload = ['short_description' => $shortDescription ?? ''];
        if ($languageCode) {
            $payload['language_code'] = $languageCode;
        }

        return $this->call('setMyShortDescription', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMyShortDescription(?string $languageCode = null): array
    {
        $payload = [];
        if ($languageCode) {
            $payload['language_code'] = $languageCode;
        }

        return $this->call('getMyShortDescription', $payload);
    }
}
