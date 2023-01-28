<?php

namespace App\Imports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProjectImport implements
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
        $project = new Project();
        $project->project_code = $row['project_code'] ?? NULL;
        $project->project_name = $row['project_name'] ?? NULL;
        $project->project_location = $row['project_location'] ?? NULL;
        $project->bowheer = $row['bowheer'] ?? NULL;
        $project->project_status = $row['project_status'] ?? NULL;
        $project->save();
    }

    public function rules(): array
    {
        return [
            '*.project_code' => ['required', 'unique:projects,project_code'],
            '*.project_name' => ['required'],
            '*.project_location' => ['required'],
            '*.bowheer' => ['required'],
            '*.project_status' => ['required'],
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
