<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

class TelegramFuelBotCommandsCommand extends Command
{
    protected $signature = 'telegram:fuel-bot-commands
        {action=set : set|list|delete}';

    protected $description = 'Manage Telegram Fuel Bot slash commands menu';

    /**
     * Commands shown in Telegram client "/" menu for @ARKAHeroFuel_bot.
     *
     * @return list<array{command: string, description: string}>
     */
    public static function menuCommands(): array
    {
        return [
            ['command' => 'start', 'description' => 'Mulai bot & lihat Telegram User ID'],
            ['command' => 'help', 'description' => 'Cara kirim foto nota SPBU'],
            ['command' => 'id', 'description' => 'Tampilkan Telegram User ID untuk whitelist'],
            ['command' => 'status', 'description' => 'Cek whitelist & status pengiriman terakhir'],
            ['command' => 'batal', 'description' => 'Batalkan nota yang menunggu konfirmasi YA/TIDAK'],
        ];
    }

    public function handle(TelegramClient $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('TELEGRAM_FUEL_BOT_TOKEN is not set.');

            return self::FAILURE;
        }

        $action = $this->argument('action');

        if ($action === 'list') {
            $this->line(json_encode($telegram->getMyCommands(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        if ($action === 'delete') {
            $this->line(json_encode($telegram->deleteMyCommands(), JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $commands = self::menuCommands();
        $result = $telegram->setMyCommands($commands);
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($result['ok'] ?? false) {
            $this->info('Registered '.count($commands).' commands for ARKA Fuel Bot.');
            foreach ($commands as $cmd) {
                $this->line('  /'.$cmd['command'].' — '.$cmd['description']);
            }
        }

        return ($result['ok'] ?? false) ? self::SUCCESS : self::FAILURE;
    }
}
