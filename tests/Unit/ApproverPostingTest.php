<?php

namespace Tests\Unit;

use App\Support\ApproverPosting;
use PHPUnit\Framework\TestCase;

class ApproverPostingTest extends TestCase
{
    public function test_primary_project_uses_administration_and_other_project_uses_acting(): void
    {
        $assignments = [
            $this->row(9, 'HCS Manager', 'APS - Kariangau', 'APS'),
            $this->row(1, 'HCS Manager', 'HO - Balikpapan', '000H'),
        ];
        $home = [
            'project_id' => 9,
            'position_name' => 'Project Manager',
            'project_name' => 'APS - Kariangau',
            'project_code' => 'APS',
        ];

        $aps = ApproverPosting::pick($assignments, 9, $home);
        $ho = ApproverPosting::pick($assignments, 1, $home);

        $this->assertSame('Project Manager - APS', ApproverPosting::display($aps['position'], $aps['project_label']));
        $this->assertSame('HCS Manager - HO', ApproverPosting::display($ho['position'], $ho['project_label']));
    }

    public function test_unknown_project_uses_the_home_posting(): void
    {
        $home = [
            'position_name' => 'HCS Manager',
            'project_name' => 'APS - Kariangau',
            'project_code' => 'APS',
        ];

        $picked = ApproverPosting::pick([], 99, $home);

        $this->assertSame('HCS Manager - APS', ApproverPosting::display($picked['position'], $picked['project_label']));
    }

    public function test_inactive_assignment_is_skipped(): void
    {
        $assignments = [
            $this->row(9, 'Project Manager', 'APS - Kariangau', 'APS', 0),
        ];
        $home = [
            'position_name' => 'HCS Manager',
            'project_name' => 'HO - Balikpapan',
            'project_code' => '000H',
        ];

        $picked = ApproverPosting::pick($assignments, 9, $home);

        $this->assertSame('HCS Manager - HO', ApproverPosting::display($picked['position'], $picked['project_label']));
    }

    public function test_empty_posting_renders_a_dash(): void
    {
        $this->assertSame('—', ApproverPosting::display(null, null));
        $this->assertSame('Project Manager', ApproverPosting::display('Project Manager', null));
    }

    private function row(int $projectId, string $position, string $projectName, string $projectCode, int $active = 1): array
    {
        return [
            'project_id' => $projectId,
            'is_active' => $active,
            'position_name' => $position,
            'project_name' => $projectName,
            'project_code' => $projectCode,
        ];
    }
}
