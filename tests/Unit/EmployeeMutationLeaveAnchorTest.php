<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\EmployeeMutation;
use App\Models\Project;
use App\Services\EmployeeMutationLeaveAnchor;
use PHPUnit\Framework\TestCase;

class EmployeeMutationLeaveAnchorTest extends TestCase
{
    private EmployeeMutationLeaveAnchor $anchor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->anchor = new EmployeeMutationLeaveAnchor;
    }

    public function test_latest_mutation_wins_for_annual_leave_anchor(): void
    {
        $employee = $this->employeeWithMutations([
            ['id' => 1, 'mutated_at' => '2024-03-01'],
            ['id' => 2, 'mutated_at' => '2025-06-15'],
        ]);

        $date = $this->anchor->annualLeaveAnchorDate($employee, '2018-01-10', '2018-01-10');

        $this->assertSame('2025-06-15', $date->toDateString());
    }

    public function test_without_mutation_falls_back_to_service_start_doh(): void
    {
        $employee = new Employee;
        $employee->setRelation('mutations', collect());

        $date = $this->anchor->annualLeaveAnchorDate($employee, '2018-01-10', '2020-02-02');

        $this->assertSame('2018-01-10', $date->toDateString());
    }

    public function test_same_date_uses_higher_id_as_latest(): void
    {
        $employee = $this->employeeWithMutations([
            ['id' => 4, 'mutated_at' => '2025-01-01'],
            ['id' => 9, 'mutated_at' => '2025-01-01'],
        ]);

        $latest = $this->anchor->latestMutation($employee);

        $this->assertSame(9, $latest->id);
    }

    public function test_leave_project_is_latest_mutation_project(): void
    {
        $olderProject = new Project(['project_code' => '000H']);
        $newerProject = new Project(['project_code' => '017C']);
        $fallback = new Project(['project_code' => 'APS']);

        $older = new EmployeeMutation(['mutated_at' => '2024-03-01']);
        $older->id = 1;
        $older->setRelation('project', $olderProject);

        $newer = new EmployeeMutation(['mutated_at' => '2025-06-15']);
        $newer->id = 2;
        $newer->setRelation('project', $newerProject);

        $employee = new Employee;
        $employee->setRelation('mutations', collect([$older, $newer]));

        $project = $this->anchor->leaveProject($employee, $fallback);

        $this->assertSame('017C', $project->project_code);
    }

    public function test_inactive_mutation_is_ignored_for_annual_leave_anchor(): void
    {
        $employee = $this->employeeWithMutations([
            ['id' => 1, 'mutated_at' => '2024-03-01', 'status' => 'active'],
            ['id' => 2, 'mutated_at' => '2025-06-15', 'status' => 'inactive'],
        ]);

        $date = $this->anchor->annualLeaveAnchorDate($employee, '2018-01-10', '2018-01-10');

        $this->assertSame('2024-03-01', $date->toDateString());
    }

    /**
     * @param  array<int, array{id: int, mutated_at: string, status?: string}>  $rows
     */
    private function employeeWithMutations(array $rows): Employee
    {
        $mutations = collect($rows)->map(function (array $row) {
            $mutation = new EmployeeMutation([
                'mutated_at' => $row['mutated_at'],
                'status' => $row['status'] ?? 'active',
            ]);
            $mutation->id = $row['id'];

            return $mutation;
        });

        $employee = new Employee;
        $employee->setRelation('mutations', $mutations);

        return $employee;
    }
}
