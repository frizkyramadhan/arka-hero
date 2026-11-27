<?php

namespace Tests\Unit;

use App\Models\OvertimeRequest;
use Tests\TestCase;

class OvertimeRequestCancelTest extends TestCase
{
    public function test_draft_is_deletable_and_pending_or_approved_is_cancellable(): void
    {
        $ot = new OvertimeRequest;

        $ot->status = OvertimeRequest::STATUS_DRAFT;
        $this->assertTrue($ot->isDeletable());
        $this->assertFalse($ot->canCancel());

        foreach ([OvertimeRequest::STATUS_PENDING, OvertimeRequest::STATUS_APPROVED] as $status) {
            $ot->status = $status;
            $this->assertFalse($ot->isDeletable());
            $this->assertTrue($ot->canCancel(), $status);
        }

        foreach ([
            OvertimeRequest::STATUS_REJECTED,
            OvertimeRequest::STATUS_FINISHED,
            OvertimeRequest::STATUS_CANCELLED,
        ] as $status) {
            $ot->status = $status;
            $this->assertFalse($ot->isDeletable());
            $this->assertFalse($ot->canCancel(), $status);
        }

        $this->assertSame('dark', OvertimeRequest::statusBadgeClassFor(OvertimeRequest::STATUS_CANCELLED));

        $ot->status = OvertimeRequest::STATUS_DRAFT;
        $this->assertTrue($ot->isHrEditable());
        $ot->status = OvertimeRequest::STATUS_APPROVED;
        $this->assertTrue($ot->isHrEditable());
        $this->assertFalse($ot->isEditable());
        foreach ([OvertimeRequest::STATUS_PENDING, OvertimeRequest::STATUS_REJECTED, OvertimeRequest::STATUS_FINISHED, OvertimeRequest::STATUS_CANCELLED] as $status) {
            $ot->status = $status;
            $this->assertFalse($ot->isHrEditable(), $status);
        }
    }
}
