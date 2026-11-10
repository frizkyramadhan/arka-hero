<?php

namespace Tests\Unit;

use App\Support\LotOriginPosting;
use PHPUnit\Framework\TestCase;

class LotOriginPostingTest extends TestCase
{
    public function test_origin_uses_primary_or_the_matching_acting_posting(): void
    {
        $primary = $this->posting(9, 'Project Manager', 'APS - Kariangau', 'APS', 'Operation', 4);
        $acting = [
            $this->posting(1, 'HCS Manager', 'HO - Balikpapan', '000H', 'Human Capital & Support', 8),
        ];

        $aps = LotOriginPosting::pick($primary, $acting, 9);
        $ho = LotOriginPosting::pick($primary, $acting, 1);

        $this->assertSame('Project Manager', $aps['position_name']);
        $this->assertSame('APS', $aps['project_code']);
        $this->assertSame('Operation', $aps['department_name']);
        $this->assertSame('HCS Manager', $ho['position_name']);
        $this->assertSame('HO - Balikpapan', $ho['project_name']);
        $this->assertSame('Human Capital & Support', $ho['department_name']);
    }

    public function test_employee_without_acting_keeps_the_primary_posting(): void
    {
        $primary = $this->posting(9, 'Project Manager', 'APS - Kariangau', 'APS', 'Operation', 4);

        $picked = LotOriginPosting::pick($primary, [], 1);

        $this->assertSame($primary, $picked);
    }

    private function posting(int $projectId, string $position, string $projectName, string $projectCode, string $department, int $departmentId): array
    {
        return [
            'project_id' => $projectId,
            'is_active' => 1,
            'position_name' => $position,
            'project_name' => $projectName,
            'project_code' => $projectCode,
            'department_name' => $department,
            'department_id' => $departmentId,
        ];
    }
}
