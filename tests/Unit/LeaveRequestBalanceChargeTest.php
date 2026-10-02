<?php

namespace Tests\Unit;

use App\Models\LeaveRequest;
use Tests\TestCase;

class LeaveRequestBalanceChargeTest extends TestCase
{
    public function test_same_charge_skips_a_zero_remaining_balance(): void
    {
        $request = $this->savedRequest();

        $this->assertTrue($request->keepsExistingBalanceCharge(
            'd90a4b4e-aca4-4f20-b910-f482a7379df4',
            1,
            10,
            '04 Apr 2026 - 03 Apr 2027',
        ));
    }

    public function test_a_different_total_days_is_a_balance_change(): void
    {
        $request = $this->savedRequest();

        $this->assertFalse($request->keepsExistingBalanceCharge(
            'd90a4b4e-aca4-4f20-b910-f482a7379df4',
            1,
            11,
            '04 Apr 2026 - 03 Apr 2027',
        ));
    }

    public function test_an_approved_edit_returns_its_days_to_the_entitlement(): void
    {
        $request = new LeaveRequest;
        $request->status = 'approved';
        $request->total_days = 10;

        $this->assertSame(10, $request->alreadyChargedDays());
        $this->assertSame(10, LeaveRequest::takenDaysAfterChargeEdit(12, 10, 8));
        $this->assertSame(13, LeaveRequest::takenDaysAfterChargeEdit(12, 10, 11));
    }

    public function test_a_pending_edit_is_not_already_charged(): void
    {
        $request = new LeaveRequest;
        $request->status = 'pending';
        $request->total_days = 10;

        $this->assertSame(0, $request->alreadyChargedDays());
    }

    public function test_approved_statuses_lock_identity_fields(): void
    {
        $request = new LeaveRequest;
        $request->status = 'approved';
        $this->assertTrue($request->locksApprovedEditFields());

        $request->status = 'auto_approved';
        $this->assertTrue($request->locksApprovedEditFields());

        $request->status = 'pending';
        $this->assertFalse($request->locksApprovedEditFields());
    }

    public function test_a_different_leave_period_is_a_balance_change(): void
    {
        $request = $this->savedRequest();

        $this->assertFalse($request->keepsExistingBalanceCharge(
            'd90a4b4e-aca4-4f20-b910-f482a7379df4',
            1,
            10,
            '17 Feb 2026 - 16 Feb 2027',
        ));
    }

    private function savedRequest(): LeaveRequest
    {
        $request = new LeaveRequest;
        $request->employee_id = 'd90a4b4e-aca4-4f20-b910-f482a7379df4';
        $request->leave_type_id = 1;
        $request->total_days = 10;
        $request->leave_period = '04 Apr 2026 - 03 Apr 2027';
        $request->lsl_taken_days = 0;
        $request->lsl_cashout_days = 0;

        return $request;
    }
}
