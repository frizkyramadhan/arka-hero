<?php

namespace Tests\Unit;

use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Services\LeaveEntitlementCarryOverService;
use PHPUnit\Framework\TestCase;

class LeaveEntitlementCarryOverServiceTest extends TestCase
{
    private LeaveEntitlementCarryOverService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeaveEntitlementCarryOverService;
    }

    public function test_lsl_carry_over_when_leave_type_allows_it(): void
    {
        $leaveType = new LeaveType([
            'category' => 'lsl',
            'default_days' => 50,
            'carry_over' => true,
        ]);

        $previous = new LeaveEntitlement([
            'entitled_days' => 50,
            'taken_days' => 20,
        ]);

        $result = $this->service->calculateFromPrevious($leaveType, $previous, true);

        $this->assertSame(30, $result['carried_over']);
        $this->assertSame(80, $result['entitled_days']);
    }

    public function test_annual_carry_over_only_for_manager_and_director(): void
    {
        $leaveType = new LeaveType([
            'category' => 'annual',
            'default_days' => 12,
            'carry_over' => false,
        ]);

        $previous = new LeaveEntitlement([
            'entitled_days' => 12,
            'taken_days' => 5,
        ]);

        $this->assertTrue($this->service->supportsCarryOver($leaveType, 'Manager'));
        $this->assertTrue($this->service->supportsCarryOver($leaveType, 'Director'));
        $this->assertFalse($this->service->supportsCarryOver($leaveType, 'Supervisor'));
        $this->assertFalse($this->service->supportsCarryOver($leaveType, 'Project Manager'));

        $managerResult = $this->service->calculateFromPrevious($leaveType, $previous, true);
        $this->assertSame(7, $managerResult['carried_over']);
        $this->assertSame(19, $managerResult['entitled_days']);

        $staffResult = $this->service->calculateFromPrevious($leaveType, $previous, false);
        $this->assertSame(0, $staffResult['carried_over']);
        $this->assertSame(12, $staffResult['entitled_days']);
    }

    public function test_first_period_has_no_carry_over(): void
    {
        $leaveType = new LeaveType([
            'category' => 'annual',
            'default_days' => 12,
            'carry_over' => false,
        ]);

        $result = $this->service->calculateFromPrevious($leaveType, null, true);

        $this->assertSame(0, $result['carried_over']);
        $this->assertSame(12, $result['entitled_days']);
    }
}
