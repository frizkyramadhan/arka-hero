<?php

namespace App\Services\Telegram;

use App\Models\FuelBotSubmission;
use App\Models\FuelBotSubscriber;
use App\Services\FuelBotIngestService;
use App\Services\OpenRouterReceiptParser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TelegramFuelBotHandler
{
    public function __construct(
        protected TelegramClient $telegram,
        protected OpenRouterReceiptParser $parser,
        protected FuelBotIngestService $ingest,
    ) {}

    /**
     * @param  array<string, mixed>  $update
     */
    public function handle(array $update): void
    {
        if (! $this->telegram->isConfigured()) {
            Log::warning('Telegram fuel bot update ignored: bot not configured');

            return;
        }

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);

            return;
        }

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }
    }

    /**
     * @param  array<string, mixed>  $message
     */
    protected function handleMessage(array $message): void
    {
        $from = $message['from'] ?? [];
        $chat = $message['chat'] ?? [];
        $telegramUserId = (int) ($from['id'] ?? 0);
        $chatId = (int) ($chat['id'] ?? 0);
        if (! $telegramUserId || ! $chatId) {
            return;
        }

        $text = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
        $command = $this->extractCommand($text);

        if ($command === 'start') {
            $this->cmdStart($chatId, $telegramUserId);

            return;
        }

        if ($command === 'help') {
            $this->cmdHelp($chatId);

            return;
        }

        if ($command === 'id') {
            $this->cmdId($chatId, $telegramUserId);

            return;
        }

        if ($command === 'status') {
            $this->cmdStatus($chatId, $telegramUserId);

            return;
        }

        if ($command === 'batal' || $command === 'cancel') {
            $this->cmdBatal($chatId, $telegramUserId);

            return;
        }

        // Whitelist required for photo / YA-TIDAK flow
        $subscriber = FuelBotSubscriber::findActiveByTelegramId($telegramUserId);
        if (! $subscriber) {
            $this->telegram->sendMessage(
                $chatId,
                "Akun Telegram Anda belum terdaftar / tidak aktif.\n" .
                    "User ID: <code>{$telegramUserId}</code>\n" .
                    "Kirim /id lalu hubungi GA untuk whitelist di ARKA HERO.\n" .
                    'Bantuan: /help'
            );

            return;
        }

        $upper = Str::upper($text);
        if (in_array($upper, ['YA', 'YES', 'Y', 'BENAR', 'OK'], true)) {
            $this->confirmLatest($telegramUserId, $chatId, true);

            return;
        }
        if (in_array($upper, ['TIDAK', 'NO', 'N', 'SALAH'], true)) {
            $this->confirmLatest($telegramUserId, $chatId, false);

            return;
        }

        $photos = $message['photo'] ?? null;
        $document = $message['document'] ?? null;
        $fileId = null;
        $mime = 'image/jpeg';

        if (is_array($photos) && count($photos) > 0) {
            $best = collect($photos)->sortByDesc('file_size')->first();
            $fileId = $best['file_id'] ?? null;
        } elseif (is_array($document)) {
            $mimeType = (string) ($document['mime_type'] ?? '');
            if (Str::startsWith($mimeType, 'image/')) {
                $fileId = $document['file_id'] ?? null;
                $mime = $mimeType ?: 'image/jpeg';
            }
        }

        if (! $fileId) {
            $this->telegram->sendMessage(
                $chatId,
                "Kirim <b>foto nota</b> SPBU, atau gunakan menu perintah:\n" .
                    "/help — cara pakai\n" .
                    "/status — cek status\n" .
                    '/batal — batalkan konfirmasi menunggu'
            );

            return;
        }

        // Caption may include "/something" only if user mistyped — strip leading slash commands from caption hints
        $caption = $command ? '' : $text;
        $this->processPhoto($subscriber, $chatId, $fileId, $mime, $caption);
    }

    /**
     * Normalize "/start@ARKAHeroFuel_bot args" → "start".
     */
    protected function extractCommand(string $text): ?string
    {
        if ($text === '' || ! Str::startsWith($text, '/')) {
            return null;
        }

        $token = strtok($text, " \n\t") ?: '';
        $token = ltrim($token, '/');
        if (str_contains($token, '@')) {
            $token = Str::before($token, '@');
        }

        $token = Str::lower($token);

        return $token !== '' ? $token : null;
    }

    protected function cmdStart(int $chatId, int $telegramUserId): void
    {
        $subscriber = FuelBotSubscriber::findActiveByTelegramId($telegramUserId);
        $whitelistLine = $subscriber
            ? '✅ Status whitelist: <b>aktif</b> — silakan kirim foto nota.'
            : '⚠️ Status whitelist: <b>belum terdaftar</b> — kirim /id ke GA untuk didaftarkan di ARKA HERO.';

        $this->telegram->sendMessage(
            $chatId,
            "<b>ARKA Fuel Bot</b> (@ARKAHeroFuel_bot)\n\n" .
                "Telegram User ID Anda: <code>{$telegramUserId}</code>\n" .
                $whitelistLine . "\n\n" .
                "Cara singkat:\n" .
                "1. Kirim <b>foto nota</b> SPBU\n" .
                "2. Caption opsional: <code>VA083 41570</code>\n" .
                "3. Konfirmasi YA / TIDAK\n" .
                "4. Data masuk ARKA HERO untuk verifikasi\n\n" .
                'Perintah: /help · /id · /status · /batal'
        );
    }

    protected function cmdHelp(int $chatId): void
    {
        $this->telegram->sendMessage(
            $chatId,
            "<b>Cara pakai ARKA Fuel Bot</b>\n\n" .
                "1. Pastikan akun sudah di-whitelist (lihat /status)\n" .
                "2. Kirim <b>foto jelas</b> nota SPBU\n" .
                "3. Caption opsional untuk melengkapi data:\n" .
                "   • <code>VA083 41570</code> → unit + KM\n" .
                "   • atau <code>VA083|41570|Pertamax|36.04|16650</code>\n" .
                "4. Bot baca nota (AI) lalu minta konfirmasi\n" .
                "5. Ketuk <b>YA</b> / balas YA → kirim ke ARKA HERO\n" .
                "6. Ketuk <b>TIDAK</b> / /batal → batalkan, kirim ulang foto\n\n" .
                "<b>Perintah</b>\n" .
                "/start — sambutan & User ID\n" .
                "/id — User ID untuk whitelist GA\n" .
                "/status — whitelist + pengiriman terakhir\n" .
                "/batal — batalkan yang menunggu konfirmasi\n" .
                '/help — bantuan ini'
        );
    }

    protected function cmdId(int $chatId, int $telegramUserId): void
    {
        $this->telegram->sendMessage(
            $chatId,
            "Telegram User ID Anda:\n<code>{$telegramUserId}</code>\n\n" .
                'Salin angka di atas dan serahkan ke GA untuk whitelist di ARKA HERO (SYSTEMS → Fuel Bot Whitelist).'
        );
    }

    protected function cmdStatus(int $chatId, int $telegramUserId): void
    {
        $subscriber = FuelBotSubscriber::findActiveByTelegramId($telegramUserId);
        if (! $subscriber) {
            $inactive = FuelBotSubscriber::query()->where('telegram_user_id', $telegramUserId)->first();
            $line = $inactive
                ? 'Whitelist: <b>nonaktif</b> — hubungi GA untuk diaktifkan kembali.'
                : 'Whitelist: <b>belum terdaftar</b> — kirim /id ke GA.';

            $this->telegram->sendMessage(
                $chatId,
                "<b>Status akun</b>\n" .
                    "User ID: <code>{$telegramUserId}</code>\n" .
                    $line
            );

            return;
        }

        $subscriber->load('user:id,name,email');
        $latest = FuelBotSubmission::query()
            ->where('telegram_user_id', $telegramUserId)
            ->latest()
            ->first();

        $lines = [
            '<b>Status akun</b>',
            'User ID: <code>' . $telegramUserId . '</code>',
            'Whitelist: <b>aktif</b>',
            'User ARKA HERO: ' . e($subscriber->user?->name ?? '—'),
            '',
            '<b>Pengiriman terakhir</b>',
        ];

        if (! $latest) {
            $lines[] = 'Belum ada. Kirim foto nota untuk mulai.';
        } else {
            $lines[] = 'Status: <code>' . $latest->status . '</code>';
            $lines[] = 'Ref: <code>' . $latest->client_uuid . '</code>';
            if ($latest->fuel_record_id) {
                $lines[] = 'Fuel record: <code>' . $latest->fuel_record_id . '</code>';
            }
            if ($latest->error_message) {
                $lines[] = 'Catatan: ' . e($latest->error_message);
            }
            $lines[] = 'Waktu: ' . optional($latest->updated_at)->timezone(config('app.timezone'))->format('Y-m-d H:i');
        }

        if ($latest?->isAwaitingConfirm()) {
            $lines[] = '';
            $lines[] = 'Ada nota menunggu konfirmasi — balas YA/TIDAK atau /batal.';
        }

        $this->telegram->sendMessage($chatId, implode("\n", $lines));
    }

    protected function cmdBatal(int $chatId, int $telegramUserId): void
    {
        $submission = FuelBotSubmission::query()
            ->where('telegram_user_id', $telegramUserId)
            ->where('status', FuelBotSubmission::STATUS_AWAITING_CONFIRM)
            ->latest()
            ->first();

        if (! $submission) {
            $this->telegram->sendMessage($chatId, 'Tidak ada nota yang menunggu konfirmasi.');

            return;
        }

        $submission->update([
            'status' => FuelBotSubmission::STATUS_REJECTED_BY_DRIVER,
            'error_message' => 'Cancelled via /batal',
        ]);

        $this->telegram->sendMessage(
            $chatId,
            "Dibatalkan.\nKirim ulang foto nota yang lebih jelas, atau tambahkan caption: <code>VA083 41570</code>"
        );
    }

    /**
     * @param  array<string, mixed>  $callback
     */
    protected function handleCallback(array $callback): void
    {
        $id = (string) ($callback['id'] ?? '');
        $data = (string) ($callback['data'] ?? '');
        $fromId = (int) ($callback['from']['id'] ?? 0);
        $chatId = (int) ($callback['message']['chat']['id'] ?? 0);

        $this->telegram->answerCallbackQuery($id);

        if (! preg_match('/^fuel:(ya|tidak):([0-9a-f\-]{36})$/i', $data, $m)) {
            return;
        }

        $yes = Str::lower($m[1]) === 'ya';
        $submissionId = $m[2];
        $submission = FuelBotSubmission::find($submissionId);
        if (! $submission || (int) $submission->telegram_user_id !== $fromId) {
            $this->telegram->sendMessage($chatId, 'Konfirmasi tidak valid atau sudah kedaluwarsa.');

            return;
        }

        $this->finalizeConfirm($submission, $chatId, $yes);
    }

    protected function confirmLatest(int $telegramUserId, int $chatId, bool $yes): void
    {
        $submission = FuelBotSubmission::query()
            ->where('telegram_user_id', $telegramUserId)
            ->where('status', FuelBotSubmission::STATUS_AWAITING_CONFIRM)
            ->latest()
            ->first();

        if (! $submission) {
            $this->telegram->sendMessage($chatId, 'Tidak ada nota yang menunggu konfirmasi. Kirim foto nota terlebih dahulu.');

            return;
        }

        $this->finalizeConfirm($submission, $chatId, $yes);
    }

    protected function finalizeConfirm(FuelBotSubmission $submission, int $chatId, bool $yes): void
    {
        if (! $submission->isAwaitingConfirm()) {
            $this->telegram->sendMessage($chatId, 'Nota ini sudah diproses sebelumnya.');

            return;
        }

        if (! $yes) {
            $submission->update([
                'status' => FuelBotSubmission::STATUS_REJECTED_BY_DRIVER,
                'error_message' => 'Rejected by driver',
            ]);
            $this->telegram->sendMessage(
                $chatId,
                "Dibatalkan.\nKirim ulang foto nota yang lebih jelas, atau tambahkan caption: <code>VA083 41570</code>"
            );

            return;
        }

        $submission->update([
            'status' => FuelBotSubmission::STATUS_PUSHING,
            'confirmed_at' => now(),
        ]);

        try {
            $record = $this->ingest->syncSubmission($submission->fresh());
            $this->telegram->sendMessage(
                $chatId,
                "✅ Berhasil dikirim ke ARKA HERO untuk verifikasi kantor.\n" .
                    'Ref: <code>' . $submission->client_uuid . "</code>\n" .
                    'Fuel record: <code>' . $record->id . '</code>'
            );
        } catch (\Throwable $e) {
            $submission->update([
                'status' => FuelBotSubmission::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
            $this->telegram->sendMessage(
                $chatId,
                "❌ Gagal mengirim ke ARKA HERO:\n" . $e->getMessage() . "\n\n" .
                    'Perbaiki data (foto ulang / caption unit+KM) lalu kirim lagi.'
            );
        }
    }

    protected function processPhoto(FuelBotSubscriber $subscriber, int $chatId, string $fileId, string $mime, string $caption): void
    {
        $this->telegram->sendMessage($chatId, 'Foto diterima. Membaca nota dengan AI…');

        $fileInfo = $this->telegram->getFile($fileId);
        $tgPath = data_get($fileInfo, 'result.file_path');
        if (! $tgPath) {
            $this->telegram->sendMessage($chatId, 'Gagal mengunduh foto dari Telegram. Coba lagi.');

            return;
        }

        $ext = pathinfo((string) $tgPath, PATHINFO_EXTENSION) ?: 'jpg';
        $relative = 'fuel_bot_inbox/' . now()->format('Ymd') . '/' . Str::uuid() . '.' . $ext;
        $absolute = Storage::disk('private')->path($relative);

        if (! $this->telegram->downloadFileTo((string) $tgPath, $absolute)) {
            $this->telegram->sendMessage($chatId, 'Gagal menyimpan foto. Coba lagi.');

            return;
        }

        $submission = FuelBotSubmission::create([
            'telegram_user_id' => $subscriber->telegram_user_id,
            'chat_id' => $chatId,
            'user_id' => $subscriber->user_id,
            'status' => FuelBotSubmission::STATUS_PARSING,
            'receipt_path' => $relative,
            'telegram_file_id' => $fileId,
            'caption' => $caption !== '' ? $caption : null,
        ]);

        if (! $this->parser->isConfigured()) {
            $submission->update([
                'status' => FuelBotSubmission::STATUS_FAILED,
                'error_message' => 'OpenRouter not configured',
            ]);
            $this->telegram->sendMessage($chatId, 'AI belum dikonfigurasi di server. Hubungi admin IT.');

            return;
        }

        $result = $this->parser->parseFromPath($absolute, $mime);
        if (! ($result['success'] ?? false)) {
            $submission->update([
                'status' => FuelBotSubmission::STATUS_FAILED,
                'error_message' => $result['message'] ?? 'AI parse failed',
            ]);
            $this->telegram->sendMessage(
                $chatId,
                'AI gagal membaca nota: ' . ($result['message'] ?? 'unknown') . "\n" .
                    'Kirim foto lebih jelas, atau caption: <code>VA083 41570</code>'
            );

            return;
        }

        $data = $result['data'];
        $data = $this->applyCaptionHints($data, $caption);

        $submission->update([
            'status' => FuelBotSubmission::STATUS_AWAITING_CONFIRM,
            'parsed_json' => $data,
            'ai_model' => $data['ai_model'] ?? null,
        ]);

        $summary = $this->formatSummary($data);
        $this->telegram->sendMessage($chatId, $summary, [
            'inline_keyboard' => [[
                ['text' => '✅ YA', 'callback_data' => 'fuel:ya:' . $submission->id],
                ['text' => '❌ TIDAK', 'callback_data' => 'fuel:tidak:' . $submission->id],
            ]],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function applyCaptionHints(array $data, string $caption): array
    {
        if ($caption === '') {
            return $data;
        }

        // Patterns: "VA083 41570" or "VA083|41570|PERTAMAX|36.04|16650"
        if (preg_match('/\b([A-Za-z]{1,3}\s?\d{2,4})\b/', $caption, $m) && empty($data['vehicle_code'])) {
            $data['vehicle_code'] = preg_replace('/\s+/', '', $m[1]);
            // re-resolve via parser normalize path — simple local resolve
            $normalized = Str::upper((string) $data['vehicle_code']);
            $vehicle = \App\Models\Vehicle::query()->where('status', 'active')->get(['id', 'kode'])
                ->first(fn($v) => Str::upper(preg_replace('/\s+/', '', (string) $v->kode) ?? '') === $normalized);
            if ($vehicle) {
                $data['vehicle_id'] = $vehicle->id;
            }
        }
        if (preg_match('/\b(\d{4,7})\b/', $caption, $m) && empty($data['odometer'])) {
            $data['odometer'] = (int) $m[1];
        }

        $parts = preg_split('/[|]/', $caption) ?: [];
        if (count($parts) >= 5) {
            $data['vehicle_code'] = $data['vehicle_code'] ?: trim($parts[0]);
            $data['odometer'] = $data['odometer'] ?: (int) trim($parts[1]);
            $data['fuel_type'] = $data['fuel_type'] ?: trim($parts[2]);
            $data['quantity'] = $data['quantity'] ?: (float) trim($parts[3]);
            $data['price_per_liter'] = $data['price_per_liter'] ?: (float) trim($parts[4]);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function formatSummary(array $data): string
    {
        $lines = [
            '<b>Konfirmasi data nota</b>',
            'Unit: ' . ($data['vehicle_code'] ?? '—') . ($data['vehicle_id'] ? ' ✅' : ' ⚠️ tidak match'),
            'Tanggal: ' . ($data['fuel_date'] ?? '—'),
            'KM: ' . ($data['odometer'] ?? '—'),
            'Jenis: ' . ($data['fuel_type'] ?? '—'),
            'Qty: ' . ($data['quantity'] ?? '—') . ' L',
            'Harga/L: ' . ($data['price_per_liter'] ?? '—'),
            'Total: ' . ($data['total_cost'] ?? '—'),
            'SPBU: ' . ($data['fuel_station'] ?? '—'),
            'No. Trans / Receipt: ' . ($data['receipt_number'] ?? '—'),
            '',
            'Benar? Ketuk YA / TIDAK, atau balas teks YA / TIDAK.',
        ];

        return implode("\n", $lines);
    }
}
