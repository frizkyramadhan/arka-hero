<?php

namespace Tests\Unit;

use App\Services\OpenRouterReceiptParser;
use ReflectionMethod;
use Tests\TestCase;

class OpenRouterReceiptNumberParseTest extends TestCase
{
    private function float(mixed $value, string $style = 'auto'): ?float
    {
        $m = new ReflectionMethod(OpenRouterReceiptParser::class, 'toFloat');
        $m->setAccessible(true);

        return $m->invoke(app(OpenRouterReceiptParser::class), $value, $style);
    }

    private function normalize(array $raw): array
    {
        $m = new ReflectionMethod(OpenRouterReceiptParser::class, 'normalize');
        $m->setAccessible(true);

        return $m->invoke(app(OpenRouterReceiptParser::class), $raw);
    }

    public function test_va083_style_money_and_volume_separators(): void
    {
        // Harga/Liter Rp. 16,650 ; Volume (L) 3.00 ; Total Harga Rp. 50,000
        $this->assertSame(16650.0, $this->float('Rp. 16,650', 'money'));
        $this->assertSame(3.0, $this->float('3.00', 'quantity'));
        $this->assertSame(50000.0, $this->float('Rp. 50,000', 'money'));
    }

    public function test_va077_style_comma_decimal_volume(): void
    {
        // Volume 36,04 ; Unit Price 16650 ; Amount 600000
        $this->assertSame(36.04, $this->float('36,04', 'quantity'));
        $this->assertSame(16650.0, $this->float('16650', 'money'));
        $this->assertSame(600000.0, $this->float('600000', 'money'));
    }

    public function test_mixed_eu_and_us_grouped_numbers(): void
    {
        $this->assertSame(1234.56, $this->float('1.234,56', 'money'));
        $this->assertSame(1234.56, $this->float('1,234.56', 'money'));
        $this->assertSame(16650.0, $this->float('16.650', 'money'));
    }

    public function test_normalize_keeps_printed_total_not_qty_times_price(): void
    {
        $n = $this->normalize([
            'quantity' => '3.00',
            'price_per_liter' => '16,650',
            'total_cost' => '50,000', // printed; 3*16650=49950
            'vehicle_code' => 'VA083',
            'fuel_type' => 'PERTAMAX',
        ]);

        $this->assertSame(3.0, $n['quantity']);
        $this->assertSame(16650.0, $n['price_per_liter']);
        $this->assertSame(50000.0, $n['total_cost']);
    }

    public function test_normalize_does_not_invent_total_from_formula(): void
    {
        $n = $this->normalize([
            'quantity' => '36,04',
            'price_per_liter' => 16650,
            // total missing on purpose
            'fuel_type' => 'PERTAMAX',
        ]);

        $this->assertSame(36.04, $n['quantity']);
        $this->assertNull($n['total_cost']);
    }
}
