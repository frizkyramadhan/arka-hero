<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Position;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PositionImport implements ToModel, WithHeadingRow
{
    private $departments;

    public function __construct()
    {
        $this->departments = Department::select('id', 'department_name')->get();
    }

    public function model(array $row)
    {
        $department = $this->departments->where('department_name', $row['department_name'])->first();

        return new Position([
            'position_name' => $row['position_name'],
            'department_id' => $department->id ?? NULL,
            'position_status' => $row['position_status'],
        ]);
    }
}
