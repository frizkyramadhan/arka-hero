<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

class TelegramFuelBotWebhookCommand extends Command
{
    protected $signature = 'telegram:fuel-bot-webhook
        {action=set : set|delete|info}
        {url? : Public HTTPS webhook URL (for set)}';

    protected $description = 'Manage Telegram Fuel Bot webhook';

    public function handle(TelegramClient $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('TELEGRAM_FUEL_BOT_TOKEN is not set.');

            return self::FAILURE;
        }

        $action = $this->argument('action');

        if ($action === 'info') {
            $this->line(json_encode($telegram->getWebhookInfo(), JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if ($action === 'delete') {
            $this->line(json_encode($telegram->deleteWebhook(), JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $url = $this->argument('url') ?: url('/api/v1/telegram/fuel-bot/webhook');
        $secret = config('telegram.webhook_secret') ?: null;
        if ($secret) {
            $separator = str_contains($url, '?') ? '&' : '?';
            // Prefer header secret; also allow query for simple tunnels
            // setWebhook secret_token is sent as header by Telegram
        }

        $result = $telegram->setWebhook($url, $secret ?: null);
        $this->line(json_encode($result, JSON_PRETTY_PRINT));
        $this->info('Webhook URL: '.$url);

        if ($result['ok'] ?? false) {
            $cmds = $telegram->setMyCommands(TelegramFuelBotCommandsCommand::menuCommands());
            if ($cmds['ok'] ?? false) {
                $this->info('Bot slash commands also registered.');
            }
        }

        return ($result['ok'] ?? false) ? self::SUCCESS : self::FAILURE;
    }
}
