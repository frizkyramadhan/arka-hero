<?php

namespace App\Imports;

use App\Models\Operableunit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;

class OperableunitImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Operableunit([
            'employee_id' => $row['employee_id'],
            'unit_name' => $row['unit_name'],
            'unit_type' => $row['unit_type'],
            'unit_remarks' => $row['remarks'],
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'unit_name' => 'required',
            'unit_type' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'unit_name.required' => 'Unit Name is required',
            'unit_type.required' => 'Unit Type is required',
        ];
    }
}
