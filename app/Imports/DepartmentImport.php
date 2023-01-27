<?php

namespace App\Imports;

use App\Models\Department;
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

class DepartmentImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use Importable, SkipsErrors, SkipsFailures;

    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        return new Department([
            'department_name' => $row['department_name'],
            'slug' => $row['slug'],
            'department_status' => $row['department_status'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.department_name' => ['required'],
            '*.slug' => ['required', 'unique:departments,slug'],
            '*.department_status' => ['required']
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
