<?php

namespace App\Imports;

use App\Models\Taxidentification;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;

class TaxImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Taxidentification([
            'employee_id' => $row['employee_id'],
            'tax_no' => $row['tax_number'],
            'tax_valid_date' => $row['valid_date'],
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'tax_number' => 'required|unique:taxidentifications,tax_no',
            'valid_date' => 'required|date',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'tax_number.required' => 'Tax Number is required',
            'tax_number.unique' => 'Tax Number already exists',
            'valid_date.required' => 'Valid Date is required',
            'valid_date.date' => 'Valid Date must be a valid date',
        ];
    }
}
