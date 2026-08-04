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
        return ! empty(config('openrouter.api_key'));
    }

    /**
     * Parse a fuel receipt image (absolute path or storage path content as binary).
     *
     * @return array{success: bool, message?: string, data?: array<string, mixed>}
     */
    public function parseFromPath(string $absolutePath, ?string $mime = null): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'OpenRouter API key is not configured.'];
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
            return ['success' => false, 'message' => 'OpenRouter API key is not configured.'];
        }

        $url = rtrim((string) config('openrouter.base_url'), '/').'/chat/completions';
        $model = (string) config('openrouter.model');

        $prompt = <<<'PROMPT'
You extract data from an Indonesian SPBU / Pertamina fuel receipt photo.
Also read handwritten notes (vehicle code like VA083, KM/odometer).
Return ONLY valid JSON (no markdown) with keys:
vehicle_code (string|null),
odometer (integer|null),
fuel_date (YYYY-MM-DD|null),
fuel_time (HH:MM:SS|null),
fuel_type (string|null),
quantity (number|null, liters),
price_per_liter (number|null),
total_cost (number|null),
fuel_station (string|null, SPBU id and/or address),
receipt_number (string|null, No. Trans),
confidence (number 0-1),
notes (string|null).
Strip "Rp", dots used as thousand separators. Use plain numbers.
PROMPT;

        try {
            $response = Http::timeout((int) config('openrouter.timeout', 60))
                ->withHeaders([
                    'Authorization' => 'Bearer '.config('openrouter.api_key'),
                    'HTTP-Referer' => (string) config('openrouter.site_url'),
                    'X-Title' => (string) config('openrouter.site_name'),
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'model' => $model,
                    'temperature' => 0.1,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
                            ],
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('OpenRouter receipt parse failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'AI parse failed (HTTP '.$response->status().').',
                ];
            }

            $content = data_get($response->json(), 'choices.0.message.content', '');
            $parsed = $this->decodeJsonContent((string) $content);
            if ($parsed === null) {
                return ['success' => false, 'message' => 'AI returned unreadable JSON.'];
            }

            $normalized = $this->normalize($parsed);
            $normalized['vehicle_id'] = $this->resolveVehicleId($normalized['vehicle_code'] ?? null);
            $normalized['ai_model'] = $model;
            $normalized['ai_raw_json'] = $parsed;

            return ['success' => true, 'data' => $normalized];
        } catch (\Throwable $e) {
            Log::error('OpenRouter receipt parse exception', ['message' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    protected function normalize(array $raw): array
    {
        $qty = $this->toFloat($raw['quantity'] ?? null);
        $price = $this->toFloat($raw['price_per_liter'] ?? null);
        $total = $this->toFloat($raw['total_cost'] ?? null);

        if ($total === null && $qty !== null && $price !== null) {
            $total = round($qty * $price, 2);
        }

        $code = isset($raw['vehicle_code']) ? trim((string) $raw['vehicle_code']) : null;
        if ($code === '') {
            $code = null;
        }

        return [
            'vehicle_code' => $code,
            'odometer' => $this->toInt($raw['odometer'] ?? null),
            'fuel_date' => $this->toDate($raw['fuel_date'] ?? null),
            'fuel_time' => isset($raw['fuel_time']) ? (string) $raw['fuel_time'] : null,
            'fuel_type' => isset($raw['fuel_type']) ? trim((string) $raw['fuel_type']) : null,
            'quantity' => $qty,
            'price_per_liter' => $price,
            'total_cost' => $total,
            'fuel_station' => isset($raw['fuel_station']) ? trim((string) $raw['fuel_station']) : null,
            'receipt_number' => isset($raw['receipt_number']) ? trim((string) $raw['receipt_number']) : null,
            'confidence' => $this->toFloat($raw['confidence'] ?? null),
            'notes' => isset($raw['notes']) ? trim((string) $raw['notes']) : null,
        ];
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
        if (Str::startsWith($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
            $content = preg_replace('/\s*```$/', '', $content) ?? $content;
        }

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

    protected function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $s = preg_replace('/[^\d,.\-]/', '', (string) $value) ?? '';
        if (str_contains($s, ',') && str_contains($s, '.')) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (str_contains($s, ',')) {
            $s = str_replace(',', '.', $s);
        }

        return is_numeric($s) ? (float) $s : null;
    }

    protected function toInt(mixed $value): ?int
    {
        $f = $this->toFloat($value);

        return $f === null ? null : (int) round($f);
    }

    protected function toDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
