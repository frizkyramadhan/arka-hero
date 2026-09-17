<?php

namespace Tests\Unit;

use App\Services\OpenRouterReceiptParser;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReceiptAiDriverConfigTest extends TestCase
{
    public function test_openrouter_driver_uses_openrouter_config(): void
    {
        config([
            'receipt_ai.driver' => 'openrouter',
            'openrouter.base_url' => 'https://openrouter.ai/api/v1',
            'openrouter.api_key' => 'or-key',
            'openrouter.model' => 'google/gemini-2.5-flash-lite',
            'openrouter.timeout' => 60,
            'openrouter.site_url' => 'http://hero.test',
            'openrouter.site_name' => 'ARKA HERO',
            'receipt_ai.local.base_url' => 'http://192.168.1.10:8000/v1',
            'receipt_ai.local.api_key' => 'local-key',
            'receipt_ai.local.model' => 'vision-local',
            'receipt_ai.9router.base_url' => 'http://9router.test/v1',
            'receipt_ai.9router.api_key' => 'nr-key',
            'receipt_ai.9router.model' => 'gemini/gemini-3.5-flash-lite',
        ]);

        $parser = app(OpenRouterReceiptParser::class);
        $conn = $parser->connection();

        $this->assertSame('openrouter', $parser->driver());
        $this->assertTrue($parser->isConfigured());
        $this->assertSame('https://openrouter.ai/api/v1', $conn['base_url']);
        $this->assertSame('or-key', $conn['api_key']);
        $this->assertSame('google/gemini-2.5-flash-lite', $conn['model']);
        $this->assertArrayHasKey('HTTP-Referer', $conn['headers']);
    }

    public function test_local_driver_uses_local_llm_config(): void
    {
        config([
            'receipt_ai.driver' => 'local',
            'openrouter.api_key' => 'or-key',
            'openrouter.model' => 'google/gemini-2.5-flash-lite',
            'openrouter.base_url' => 'https://openrouter.ai/api/v1',
            'receipt_ai.local.base_url' => 'http://192.168.1.10:8000/v1',
            'receipt_ai.local.api_key' => 'local-key',
            'receipt_ai.local.model' => 'qwen2.5-vl',
            'receipt_ai.local.timeout' => 120,
            'receipt_ai.9router.base_url' => 'http://9router.test/v1',
            'receipt_ai.9router.api_key' => 'nr-key',
            'receipt_ai.9router.model' => 'gemini/gemini-3.5-flash-lite',
        ]);

        $parser = app(OpenRouterReceiptParser::class);
        $conn = $parser->connection();

        $this->assertSame('local', $parser->driver());
        $this->assertTrue($parser->isConfigured());
        $this->assertSame('http://192.168.1.10:8000/v1', $conn['base_url']);
        $this->assertSame('local-key', $conn['api_key']);
        $this->assertSame('qwen2.5-vl', $conn['model']);
        $this->assertArrayNotHasKey('HTTP-Referer', $conn['headers']);
    }

    public function test_ninerouter_driver_uses_ninerouter_config(): void
    {
        config([
            'receipt_ai.driver' => '9router',
            'openrouter.api_key' => 'or-key',
            'openrouter.model' => 'google/gemini-2.5-flash-lite',
            'openrouter.base_url' => 'https://openrouter.ai/api/v1',
            'receipt_ai.local.base_url' => 'http://192.168.1.10:8000/v1',
            'receipt_ai.local.api_key' => 'local-key',
            'receipt_ai.local.model' => 'qwen2.5-vl',
            'receipt_ai.9router.base_url' => 'http://10.10.110.97:20128/v1',
            'receipt_ai.9router.api_key' => 'nr-key',
            'receipt_ai.9router.model' => 'gemini/gemini-3.5-flash-lite',
            'receipt_ai.9router.timeout' => 120,
        ]);

        $parser = app(OpenRouterReceiptParser::class);
        $conn = $parser->connection();

        $this->assertSame('9router', $parser->driver());
        $this->assertTrue($parser->isConfigured());
        $this->assertSame('http://10.10.110.97:20128/v1', $conn['base_url']);
        $this->assertSame('nr-key', $conn['api_key']);
        $this->assertSame('gemini/gemini-3.5-flash-lite', $conn['model']);
        $this->assertArrayNotHasKey('HTTP-Referer', $conn['headers']);
    }

    public function test_parse_sends_stream_false(): void
    {
        config([
            'receipt_ai.driver' => '9router',
            'receipt_ai.9router.base_url' => 'http://9router.test/v1',
            'receipt_ai.9router.api_key' => 'nr-key',
            'receipt_ai.9router.model' => 'gemini/gemini-3.5-flash-lite',
            'receipt_ai.9router.timeout' => 30,
        ]);

        Http::fake([
            '9router.test/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'role' => 'assistant',
                        'content' => '{"vehicle_code":null,"odometer":null,"fuel_date":null,"fuel_time":null,"fuel_type":null,"quantity":null,"price_per_liter":null,"total_cost":null,"fuel_station":null,"receipt_number":null,"confidence":0,"notes":null}',
                    ],
                    'finish_reason' => 'stop',
                ]],
            ], 200),
        ]);

        $tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $dataUrl = 'data:image/png;base64,'.base64_encode($tinyPng);

        $result = app(OpenRouterReceiptParser::class)->parseDataUrl($dataUrl);

        $this->assertTrue($result['success']);
        Http::assertSent(function ($request) {
            $data = $request->data();

            return ($data['stream'] ?? null) === false;
        });
    }
}
