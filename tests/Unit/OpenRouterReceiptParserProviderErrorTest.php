<?php

namespace Tests\Unit;

use App\Services\OpenRouterReceiptParser;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenRouterReceiptParserProviderErrorTest extends TestCase
{
    public function test_openrouter_http_200_with_provider_error_is_not_empty_content(): void
    {
        config([
            'receipt_ai.driver' => 'openrouter',
            'openrouter.base_url' => 'https://openrouter.ai/api/v1',
            'openrouter.api_key' => 'test-key',
            'openrouter.model' => 'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning:free',
            'openrouter.timeout' => 30,
            'openrouter.site_url' => 'http://hero.test',
            'openrouter.site_name' => 'ARKA HERO',
        ]);

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'id' => 'gen-test',
                'error' => [
                    'message' => 'Upstream error from Nvidia: ResourceExhausted: Worker local total request limit reached (16/16)',
                    'code' => 502,
                    'metadata' => ['error_type' => 'provider_unavailable'],
                ],
            ], 200),
        ]);

        $tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $dataUrl = 'data:image/png;base64,'.base64_encode($tinyPng);

        $result = app(OpenRouterReceiptParser::class)->parseDataUrl($dataUrl);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('NVIDIA rate limit', $result['message'] ?? '');
        $this->assertStringNotContainsString('empty content', strtolower($result['message'] ?? ''));
    }
}
