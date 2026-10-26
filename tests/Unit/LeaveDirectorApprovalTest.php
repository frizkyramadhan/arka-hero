<?php

namespace Tests\Unit;

use App\Support\LeaveDirectorApproval;
use PHPUnit\Framework\TestCase;

class LeaveDirectorApprovalTest extends TestCase
{
    public function test_non_director_is_not_limited_to_one_peer(): void
    {
        $this->assertNull(LeaveDirectorApproval::error('Manager', 10, []));
    }

    public function test_director_accepts_one_other_director(): void
    {
        $this->assertNull(LeaveDirectorApproval::error('Director', 10, [
            ['employee_id' => 20, 'is_director' => true],
        ]));
    }

    public function test_director_accepts_more_than_one_director(): void
    {
        $this->assertNull(LeaveDirectorApproval::error('Director', 10, [
            ['employee_id' => 20, 'is_director' => true],
            ['employee_id' => 21, 'is_director' => true],
        ]));
    }

    public function test_director_rejects_a_non_director_or_themself(): void
    {
        $nonDirector = [['employee_id' => 20, 'is_director' => false]];
        $mixed = [
            ['employee_id' => 20, 'is_director' => true],
            ['employee_id' => 21, 'is_director' => false],
        ];
        $self = [['employee_id' => 10, 'is_director' => true]];

        $this->assertSame(LeaveDirectorApproval::ERROR, LeaveDirectorApproval::error('Director', 10, $nonDirector));
        $this->assertSame(LeaveDirectorApproval::ERROR, LeaveDirectorApproval::error('Director', 10, $mixed));
        $this->assertSame(LeaveDirectorApproval::ERROR, LeaveDirectorApproval::error('Director', 10, $self));
        $this->assertSame(LeaveDirectorApproval::ERROR, LeaveDirectorApproval::error('Director', 10, []));
    }

    public function test_first_peer_skips_the_applicant(): void
    {
        $this->assertSame(8, LeaveDirectorApproval::firstPeerId([4, 8], 4));
        $this->assertNull(LeaveDirectorApproval::firstPeerId([4], 4));
    }
}
