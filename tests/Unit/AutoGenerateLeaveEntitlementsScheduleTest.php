<?php

namespace Tests\Unit;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use ReflectionMethod;
use Tests\TestCase;

class AutoGenerateLeaveEntitlementsScheduleTest extends TestCase
{
    public function test_missing_leave_entitlements_are_generated_daily_at_0030(): void
    {
        $schedule = $this->app->make(Schedule::class);
        $method = new ReflectionMethod($this->app->make(Kernel::class), 'schedule');
        $method->setAccessible(true);
        $method->invoke($this->app->make(Kernel::class), $schedule);

        $event = collect($schedule->events())->first(function ($event) {
            return str_contains((string) $event->command, 'leave:auto-generate-entitlements');
        });

        $this->assertNotNull($event);
        $this->assertSame('30 0 * * *', $event->expression);
    }
}
