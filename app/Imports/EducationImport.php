<?php

namespace App\Imports;

use App\Models\Education;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;

class EducationImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Education([
            'employee_id' => $row['employee_id'],
            'education_name' => $row['institution_name'],
            'education_address' => $row['address'],
            'education_year' => $row['year'],
            'education_remarks' => $row['remarks'],
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'institution_name' => 'required',
            'address' => 'required',
            'year' => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'institution_name.required' => 'Institution Name is required',
            'address.required' => 'Address is required',
            'year.required' => 'Year is required',
            'year.numeric' => 'Year must be a number',
        ];
    }
}
