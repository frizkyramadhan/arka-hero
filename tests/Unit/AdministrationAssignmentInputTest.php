<?php

namespace Tests\Unit;

use App\Support\AdministrationAssignmentInput;
use PHPUnit\Framework\TestCase;

class AdministrationAssignmentInputTest extends TestCase
{
    public function test_blank_rows_are_dropped_and_one_project_is_kept(): void
    {
        $rows = AdministrationAssignmentInput::rows([
            ['project_id' => '', 'position_id' => ''],
            ['project_id' => 9, 'position_id' => 205, 'is_active' => 1],
            ['project_id' => 1, 'position_id' => 71, 'is_active' => 0],
        ]);

        $this->assertSame([
            ['project_id' => 9, 'position_id' => 205, 'is_active' => 1],
            ['project_id' => 1, 'position_id' => 71, 'is_active' => 0],
        ], $rows);
    }

    public function test_acting_project_cannot_repeat_the_primary_project(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AdministrationAssignmentInput::rows([
            ['project_id' => 9, 'position_id' => 71],
        ], 9);
    }

    public function test_duplicate_project_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AdministrationAssignmentInput::rows([
            ['project_id' => 9, 'position_id' => 205],
            ['project_id' => 9, 'position_id' => 71],
        ]);
    }
}
