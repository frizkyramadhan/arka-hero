<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OpenRouterReceiptParser
{
    public function isConfigured(): bool
    {
        $conn = $this->connection();

        return $conn['base_url'] !== '' && $conn['api_key'] !== '' && $conn['model'] !== '';
    }

    public function driver(): string
    {
        $driver = strtolower(trim((string) config('receipt_ai.driver', 'openrouter')));

        return in_array($driver, ['openrouter', 'local', '9router'], true) ? $driver : 'openrouter';
    }

    /**
     * Active OpenAI-compatible connection (OpenRouter, local LLM, or 9router).
     *
     * @return array{driver: string, base_url: string, api_key: string, model: string, timeout: int, headers: array<string, string>}
     */
    public function connection(): array
    {
        $driver = $this->driver();

        if ($driver === 'local' || $driver === '9router') {
            $cfg = config('receipt_ai.'.$driver, []);

            return [
                'driver' => $driver,
                'base_url' => rtrim((string) ($cfg['base_url'] ?? ''), '/'),
                'api_key' => (string) ($cfg['api_key'] ?? ''),
                'model' => (string) ($cfg['model'] ?? ''),
                'timeout' => (int) ($cfg['timeout'] ?? 120),
                'headers' => [
                    'Authorization' => 'Bearer '.($cfg['api_key'] ?? ''),
                    'Content-Type' => 'application/json',
                ],
            ];
        }

        return [
            'driver' => 'openrouter',
            'base_url' => rtrim((string) config('openrouter.base_url'), '/'),
            'api_key' => (string) config('openrouter.api_key', ''),
            'model' => (string) config('openrouter.model', ''),
            'timeout' => (int) config('openrouter.timeout', 60),
            'headers' => [
                'Authorization' => 'Bearer '.config('openrouter.api_key'),
                'HTTP-Referer' => (string) config('openrouter.site_url'),
                'X-Title' => (string) config('openrouter.site_name'),
                'Content-Type' => 'application/json',
            ],
        ];
    }

    /**
     * Parse a fuel receipt image (absolute path or storage path content as binary).
     *
     * @return array{success: bool, message?: string, data?: array<string, mixed>}
     */
    public function parseFromPath(string $absolutePath, ?string $mime = null): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Receipt AI is not configured for driver '.$this->driver().'.'];
        }

        if (! is_readable($absolutePath)) {
            return ['success' => false, 'message' => 'Receipt image is not readable.'];
        }

        $bytes = file_get_contents($absolutePath);
        if ($bytes === false) {
            return ['success' => false, 'message' => 'Failed to read receipt image.'];
        }

        $mime = $mime ?: (mime_content_type($absolutePath) ?: 'image/jpeg');
        $dataUrl = 'data:'.$mime.';base64,'.base64_encode($bytes);

        return $this->parseDataUrl($dataUrl);
    }

    /**
     * @return array{success: bool, message?: string, data?: array<string, mixed>}
     */
    public function parseDataUrl(string $dataUrl): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Receipt AI is not configured for driver '.$this->driver().'.'];
        }

        $conn = $this->connection();
        $url = $conn['base_url'].'/chat/completions';
        $model = $conn['model'];

        $prompt = <<<'PROMPT'
Extract ALL fields from this Indonesian SPBU fuel receipt photo (printed + handwritten).
Return ONLY one JSON object (no markdown, no extra text) with exactly these keys:
vehicle_code, odometer, fuel_date, fuel_time, fuel_type, quantity, price_per_liter, total_cost, fuel_station, receipt_number, confidence, notes

Field rules:
- vehicle_code: handwritten unit code near top (examples: VA062, VA 088, TS 001). Prefer VA/LV/TS + digits. null only if absent.
- odometer: handwritten KM / odometer near top (examples: "KM. 76316" → 76316). Integer only.
- fuel_date: printed date as YYYY-MM-DD. Receipts use DD/MM/YYYY (example 24/07/2026 → 2026-07-24). Day must be 01-31.
- fuel_time: printed time as HH:MM:SS (example 09:32 → 09:32:00).
- fuel_type: Grade (PERTAMAX, DEXLITE, PERTAMINA DEX, Solar, etc).
- quantity: Volume in liters as number. Indonesian receipts mix separators: "36,04" and "3.00" both mean liters (36.04 / 3.00). Use comma OR dot as decimal when 1–2 digits follow; do NOT treat "36,04" as 3604.
- price_per_liter: Unit Price / Harga/Liter as number. "Rp. 16,650" or "16.650" → 16650 (thousands separators). "16650" stays 16650.
- total_cost: printed Amount / Total Harga / Total ONLY (example "Rp. 50,000" → 50000, "600000" → 600000). NEVER compute quantity × price — SPBU totals are often rounded (3.00×16650 printed as 50000).
- fuel_station: SPBU number + address if present (example "6476112 JL. SOEKARNO HATTA KM 4,5 BPP"). Do NOT put SPBU number into receipt_number.
- receipt_number: ONLY "Receipt No." / No. Trans / No. Struk / No. Nota value (example 3537). Never use SPBU NO.
- confidence: 0 to 1 number.
- notes: other handwritten names/signatures if useful, else null.

Read handwriting carefully at the top for VA code and KM.
Your final answer must be the JSON object only. Numbers in JSON must use dot as decimal separator (JSON standard), after you correctly interpret the receipt's local separators.
PROMPT;

        try {
            $payload = [
                'model' => $model,
                'stream' => false, // 9router defaults to SSE; Laravel Http needs one JSON body
                'temperature' => 0,
                'max_tokens' => 4096,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
                        ],
                    ],
                ],
            ];

            // Reasoning free models often spend the whole budget thinking and return empty/non-JSON.
            if ($conn['driver'] === 'openrouter' && str_contains(strtolower($model), 'reasoning')) {
                $payload['reasoning'] = ['effort' => 'low'];
            }

            $response = Http::timeout($conn['timeout'])
                ->withHeaders($conn['headers'])
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('Receipt AI parse failed', [
                    'driver' => $conn['driver'],
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'AI parse failed (HTTP '.$response->status().').',
                ];
            }

            $json = $response->json();
            if (! is_array($json)) {
                $raw = trim($response->body());
                Log::warning('Receipt AI non-JSON body', [
                    'driver' => $conn['driver'],
                    'content_type' => $response->header('Content-Type'),
                    'preview' => Str::limit($raw, 300),
                ]);
                if ($raw === '' || str_starts_with(ltrim($raw), '<')) {
                    $hint = match ($conn['driver']) {
                        '9router' => 'Check NINEROUTER_BASE_URL (must end with /v1).',
                        'local' => 'Check LOCAL_LLM_BASE_URL (AnythingLLM needs …/api/v1/openai).',
                        default => 'Check OPENROUTER_BASE_URL.',
                    };

                    return [
                        'success' => false,
                        'message' => 'AI endpoint returned HTML/empty. '.$hint,
                    ];
                }
                if (str_starts_with($raw, 'data:')) {
                    return [
                        'success' => false,
                        'message' => 'AI returned a stream (SSE). Ensure stream=false is sent to the endpoint.',
                    ];
                }

                return ['success' => false, 'message' => 'AI returned a non-JSON HTTP body.'];
            }

            // OpenRouter often returns HTTP 200 with a top-level error (NVIDIA free CapacityExhausted).
            if (! empty($json['error'])) {
                $providerMsg = (string) (data_get($json, 'error.message') ?: 'Unknown provider error');
                Log::warning('Receipt AI provider error', [
                    'driver' => $conn['driver'],
                    'error' => $json['error'],
                ]);

                return [
                    'success' => false,
                    'message' => $this->providerErrorMessage($providerMsg),
                ];
            }

            $content = $this->extractAssistantText($json);
            if ($content === '') {
                Log::warning('Receipt AI empty content', [
                    'driver' => $conn['driver'],
                    'finish' => data_get($json, 'choices.0.finish_reason'),
                    'usage' => $json['usage'] ?? null,
                ]);

                return ['success' => false, 'message' => 'AI returned empty content. Retry, or switch to local driver.'];
            }

            $parsed = $this->decodeJsonContent($content);
            if ($parsed === null) {
                Log::warning('Receipt AI unreadable JSON', [
                    'driver' => $conn['driver'],
                    'preview' => Str::limit($content, 500),
                ]);

                return ['success' => false, 'message' => 'AI returned unreadable JSON.'];
            }

            $normalized = $this->normalize($parsed);
            $normalized['vehicle_id'] = $this->resolveVehicleId($normalized['vehicle_code'] ?? null);
            $normalized['ai_model'] = $model;
            $normalized['ai_driver'] = $conn['driver'];
            $normalized['ai_raw_json'] = $parsed;

            return ['success' => true, 'data' => $normalized];
        } catch (\Throwable $e) {
            Log::error('Receipt AI parse exception', [
                'driver' => $conn['driver'],
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Prefer message.content; fall back to reasoning when that holds usable text/JSON.
     *
     * @param  array<string, mixed>  $json
     */
    protected function extractAssistantText(array $json): string
    {
        $message = data_get($json, 'choices.0.message', []);
        if (! is_array($message)) {
            return '';
        }

        $parts = [];
        foreach (['content', 'reasoning', 'reasoning_content'] as $key) {
            if (! array_key_exists($key, $message)) {
                continue;
            }
            $value = $message[$key];
            if (is_array($value)) {
                $value = collect($value)
                    ->map(function ($part) {
                        if (is_string($part)) {
                            return $part;
                        }
                        if (is_array($part) && isset($part['text'])) {
                            return (string) $part['text'];
                        }

                        return '';
                    })
                    ->implode("\n");
            }
            $value = trim((string) $value);
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        foreach ($parts as $part) {
            if (str_contains($part, '{') && str_contains($part, '}')) {
                return $part;
            }
        }

        return $parts[0] ?? '';
    }

    protected function providerErrorMessage(string $providerMsg): string
    {
        $lower = strtolower($providerMsg);
        if (str_contains($lower, 'resourceexhausted')
            || str_contains($lower, 'request limit')
            || str_contains($lower, 'provider_unavailable')
            || str_contains($lower, 'capacity')) {
            return 'AI free endpoint is busy (NVIDIA rate limit). Wait and retry, or set RECEIPT_AI_DRIVER=local|9router.';
        }

        return 'AI provider error: '.$providerMsg;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    protected function normalize(array $raw): array
    {
        // Quantity vs money use different separator heuristics (see toFloat).
        $qty = $this->toFloat($raw['quantity'] ?? null, 'quantity');
        $price = $this->toFloat($raw['price_per_liter'] ?? null, 'money');
        // Always prefer printed Amount/Total Harga — never invent from qty × price.
        $total = $this->toFloat($raw['total_cost'] ?? null, 'money');

        $code = isset($raw['vehicle_code']) ? trim((string) $raw['vehicle_code']) : null;
        if ($code === '' || Str::lower((string) $code) === 'null') {
            $code = null;
        }
        if ($code === null) {
            $code = $this->findVehicleCodeInBlob($raw);
        }

        $odometer = $this->toInt($raw['odometer'] ?? null);
        if ($odometer === null) {
            $odometer = $this->findOdometerInBlob($raw);
        }

        $fuelTime = $this->toTime($raw['fuel_time'] ?? null);

        return [
            'vehicle_code' => $code,
            'odometer' => $odometer,
            'fuel_date' => $this->toDate($raw['fuel_date'] ?? null),
            'fuel_time' => $fuelTime,
            'fuel_type' => isset($raw['fuel_type']) ? trim((string) $raw['fuel_type']) : null,
            'quantity' => $qty,
            'price_per_liter' => $price,
            'total_cost' => $total,
            'fuel_station' => isset($raw['fuel_station']) ? trim((string) $raw['fuel_station']) : null,
            'receipt_number' => $this->extractReceiptNumber($raw),
            'confidence' => $this->toFloat($raw['confidence'] ?? null),
            'notes' => isset($raw['notes']) ? trim((string) $raw['notes']) : null,
        ];
    }

    /**
     * Accept common alternate keys / labels the model may emit for No. Trans.
     *
     * @param  array<string, mixed>  $raw
     */
    protected function extractReceiptNumber(array $raw): ?string
    {
        $keys = [
            'receipt_number',
            'receipt_no',
            'receiptNo',
            'no_trans',
            'no_transaksi',
            'nomor_transaksi',
            'nomor_trans',
            'transaction_number',
            'transaction_no',
            'trans_number',
            'trans_no',
            'no_struk',
            'nomor_struk',
            'no_nota',
            'nomor_nota',
            'invoice_number',
            'invoice_no',
            'ref_no',
            'reference_number',
        ];

        foreach ($keys as $key) {
            if (! array_key_exists($key, $raw) || $raw[$key] === null || $raw[$key] === '') {
                continue;
            }
            $value = $this->cleanReceiptNumberValue($raw[$key]);
            if ($value !== null) {
                return $value;
            }
        }

        // Flat scan: keys containing trans/struk/nota/receipt/invoice
        foreach ($raw as $key => $value) {
            if (! is_string($key) || is_array($value) || is_object($value)) {
                continue;
            }
            $k = Str::lower($key);
            if (! preg_match('/(trans|struk|nota|receipt|invoice|ref)/', $k)) {
                continue;
            }
            if (preg_match('/(date|tanggal|time|jam|total|harga|liter|qty|quantity)/', $k)) {
                continue;
            }
            $cleaned = $this->cleanReceiptNumberValue($value);
            if ($cleaned !== null) {
                return $cleaned;
            }
        }

        return null;
    }

    protected function cleanReceiptNumberValue(mixed $value): ?string
    {
        $s = trim((string) $value);
        if ($s === '' || Str::lower($s) === 'null') {
            return null;
        }

        // Drop leading label if model included it in the value
        $s = preg_replace(
            '/^(no\.?\s*(trans(aksi)?|struk|nota|receipt|invoice|ref(erence)?)\.?\s*[:#\-]?\s*)/iu',
            '',
            $s
        ) ?? $s;
        $s = preg_replace(
            '/^(nomor\s*(transaksi|struk|nota)\s*[:#\-]?\s*)/iu',
            '',
            $s
        ) ?? $s;

        $s = trim($s, " \t\n\r\0\x0B:#-");

        return $s !== '' ? $s : null;
    }

    protected function resolveVehicleId(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        $normalized = Str::upper(preg_replace('/\s+/', '', $code) ?? '');

        $vehicle = Vehicle::query()
            ->where('status', 'active')
            ->get(['id', 'kode'])
            ->first(function (Vehicle $v) use ($normalized) {
                return Str::upper(preg_replace('/\s+/', '', (string) $v->kode) ?? '') === $normalized;
            });

        return $vehicle?->id;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decodeJsonContent(string $content): ?array
    {
        $content = trim($content);
        if ($content === '') {
            return null;
        }

        // Strip common markdown / thinking wrappers from small local models.
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/', '', $content) ?? $content;
        $content = preg_replace('/<think>.*?<\/think>/is', '', $content) ?? $content;
        $content = trim($content);

        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $content, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Parse receipt numbers with mixed ID/EU/US separators.
     *
     * @param  'auto'|'quantity'|'money'  $style
     */
    protected function toFloat(mixed $value, string $style = 'auto'): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Already a real number from JSON (model emitted JSON-standard floats).
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $s = trim((string) $value);
        if ($s === '' || Str::lower($s) === 'null') {
            return null;
        }

        // Drop currency labels before separator logic ("Rp. 16,650" must not keep the dot from "Rp.").
        $s = preg_replace('/^(rp\.?|idr\.?)\s*/iu', '', $s) ?? $s;
        $s = trim($s);

        $negative = str_starts_with($s, '-');
        $s = ltrim($s, '-+');
        $s = preg_replace('/[^\d,.]/', '', $s) ?? '';
        $s = trim($s, '.,');

        if ($s === '') {
            return null;
        }

        $hasComma = str_contains($s, ',');
        $hasDot = str_contains($s, '.');

        // Money: dotted thousands like 16.650 / 1.234.567 (common on ID receipts).
        if (($style === 'money' || $style === 'auto') && ! $hasComma && preg_match('/^\d{1,3}(\.\d{3})+$/', $s)) {
            $s = str_replace('.', '', $s);
        } elseif ($hasComma && $hasDot) {
            // Last separator is the decimal mark.
            if (strrpos($s, ',') > strrpos($s, '.')) {
                // 1.234,56
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } else {
                // 1,234.56
                $s = str_replace(',', '', $s);
            }
        } elseif ($hasComma) {
            // 36,04 (decimal) vs 16,650 / 50,000 (thousands)
            if (preg_match('/,\d{3}$/', $s) && ($style === 'money' || $style === 'auto')) {
                $s = str_replace(',', '', $s);
            } elseif (preg_match('/,\d{1,2}$/', $s)) {
                $s = str_replace(',', '.', $s);
            } elseif (preg_match('/,\d{3}$/', $s)) {
                $s = str_replace(',', '', $s);
            } else {
                $s = str_replace(',', '.', $s);
            }
        }
        // else: only dot left as decimal (3.00, 36.04) — keep

        if (! is_numeric($s)) {
            return null;
        }

        $n = (float) $s;

        return $negative ? -$n : $n;
    }

    protected function toInt(mixed $value): ?int
    {
        $f = $this->toFloat($value, 'money');

        return $f === null ? null : (int) round($f);
    }

    protected function toDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $s = trim((string) $value);
        if ($s === '' || Str::lower($s) === 'null') {
            return null;
        }

        // Indonesian receipts: DD/MM/YYYY or DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})$/', $s, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if ($year < 100) {
                $year += 2000;
            }
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }

            return null;
        }

        // Already ISO-ish: validate calendar day (reject 2026-07-34)
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $s, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }

            return null;
        }

        try {
            $parsed = \Carbon\Carbon::parse($s);

            return checkdate((int) $parsed->month, (int) $parsed->day, (int) $parsed->year)
                ? $parsed->toDateString()
                : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function toTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $s = trim((string) $value);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $s, $m)) {
            $h = (int) $m[1];
            $i = (int) $m[2];
            $sec = isset($m[3]) ? (int) $m[3] : 0;
            if ($h > 23 || $i > 59 || $sec > 59) {
                return null;
            }

            return sprintf('%02d:%02d:%02d', $h, $i, $sec);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    protected function findVehicleCodeInBlob(array $raw): ?string
    {
        $blob = $this->rawTextBlob($raw);
        if (preg_match('/\b((?:VA|LV|TS)\s*\d{2,4})\b/i', $blob, $m)) {
            return strtoupper(preg_replace('/\s+/', '', $m[1]) ?? $m[1]);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    protected function findOdometerInBlob(array $raw): ?int
    {
        $blob = $this->rawTextBlob($raw);
        if (preg_match('/\bKM\.?\s*[:#\-]?\s*(\d{3,7})\b/i', $blob, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    protected function rawTextBlob(array $raw): string
    {
        $parts = [];
        foreach ($raw as $value) {
            if (is_string($value) || is_numeric($value)) {
                $parts[] = (string) $value;
            }
        }

        return implode(' ', $parts);
    }
}
