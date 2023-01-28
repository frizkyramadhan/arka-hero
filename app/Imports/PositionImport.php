<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Position;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PositionImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use Importable, SkipsErrors, SkipsFailures;

    private $departments;

    public function __construct()
    {
        $this->departments = Department::select('id', 'department_name')->get();
    }

    public function headingRow(): int
    {
        return 2;
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

    public function rules(): array
    {
        return [
            '*.position_name' => ['required'],
            '*.department_name' => ['required', 'exists:departments,department_name'],
            '*.position_status' => ['required']
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
