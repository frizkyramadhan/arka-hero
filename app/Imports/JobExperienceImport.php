<?php

namespace App\Imports;

use App\Models\Jobexperience;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;

class JobExperienceImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Jobexperience([
            'employee_id' => $row['employee_id'],
            'company_name' => $row['company_name'],
            'company_address' => $row['company_address'],
            'job_position' => $row['position'],
            'job_duration' => $row['duration'],
            'quit_reason' => $row['quit_reason'],
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'company_name' => 'required',
            'company_address' => 'required',
            'position' => 'required',
            'duration' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'company_name.required' => 'Company Name is required',
            'company_address.required' => 'Company Address is required',
            'position.required' => 'Position is required',
            'duration.required' => 'Duration is required',
        ];
    }
}
