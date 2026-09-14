<?php

namespace Tests\Unit;

use App\Services\OpenRouterReceiptParser;
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
}
