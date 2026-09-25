<?php

namespace Tests\Unit;

use App\Models\LeaveEntitlement;
use Tests\TestCase;

class LeaveEntitlementOverlapPickTest extends TestCase
{
    public function test_overlapping_windows_pick_the_later_period_start(): void
    {
        $older = $this->entitlement(25891, '2026-02-17', '2027-02-16');
        $newer = $this->entitlement(33178, '2026-03-23', '2027-03-22');

        $picked = LeaveEntitlement::pickCovering([$older, $newer], '2026-09-25', '2026-09-25');

        $this->assertSame(33178, $picked?->id);
    }

    public function test_date_outside_the_later_window_keeps_the_window_that_contains_it(): void
    {
        $older = $this->entitlement(25891, '2026-02-17', '2027-02-16');
        $newer = $this->entitlement(33178, '2026-03-23', '2027-03-22');

        $picked = LeaveEntitlement::pickCovering([$newer, $older], '2026-03-01', '2026-03-01');

        $this->assertSame(25891, $picked?->id);
        $this->assertFalse($newer->containsRange('2026-03-01', '2026-03-01'));
    }

    private function entitlement(int $id, string $start, string $end): LeaveEntitlement
    {
        $entitlement = new LeaveEntitlement([
            'period_start' => $start,
            'period_end' => $end,
            'entitled_days' => 10,
            'taken_days' => 0,
        ]);
        $entitlement->id = $id;

        return $entitlement;
    }
}
