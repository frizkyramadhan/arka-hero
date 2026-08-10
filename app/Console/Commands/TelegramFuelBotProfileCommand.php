<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

class TelegramFuelBotProfileCommand extends Command
{
    protected $signature = 'telegram:fuel-bot-profile
        {action=set : set|show}';

    protected $description = 'Set or show About / Description for @ARKAHeroFuel_bot';

    /** Profile “About” (max 120 chars). */
    public static function shortDescription(): string
    {
        return 'Bot resmi ARKA HERO untuk kirim nota BBM SPBU. Foto → AI → konfirmasi → verifikasi kantor.';
    }

    /** Empty-chat description (max 512 chars). */
    public static function description(): string
    {
        return <<<'TXT'
ARKA Fuel Bot — pencatatan BBM untuk driver ARKA (HERO).

Cara pakai:
1. /start atau /id — salin Telegram User ID Anda
2. Minta GA whitelist di HERO (SYSTEMS → Fuel Bot Whitelist)
3. Kirim foto nota SPBU (caption opsional: VA083 41570)
4. Konfirmasi YA / TIDAK
5. Data masuk HERO untuk verifikasi kantor

Perintah: /help · /id · /status · /batal
TXT;
    }

    public function handle(TelegramClient $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('TELEGRAM_FUEL_BOT_TOKEN is not set.');

            return self::FAILURE;
        }

        if ($this->argument('action') === 'show') {
            $this->line('Short (About):');
            $this->line(json_encode($telegram->getMyShortDescription(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->newLine();
            $this->line('Description:');
            $this->line(json_encode($telegram->getMyDescription(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $short = self::shortDescription();
        $full = self::description();

        if (mb_strlen($short) > 120) {
            $this->error('Short description exceeds 120 characters ('.mb_strlen($short).').');

            return self::FAILURE;
        }
        if (mb_strlen($full) > 512) {
            $this->error('Description exceeds 512 characters ('.mb_strlen($full).').');

            return self::FAILURE;
        }

        $shortResult = $telegram->setMyShortDescription($short);
        $descResult = $telegram->setMyDescription($full);

        $this->line('setMyShortDescription: '.json_encode($shortResult, JSON_UNESCAPED_UNICODE));
        $this->line('setMyDescription: '.json_encode($descResult, JSON_UNESCAPED_UNICODE));

        $ok = ($shortResult['ok'] ?? false) && ($descResult['ok'] ?? false);
        if ($ok) {
            $this->info('Updated About + Description for @ARKAHeroFuel_bot');
            $this->line('About ('.mb_strlen($short).' chars): '.$short);
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
