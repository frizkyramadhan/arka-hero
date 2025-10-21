<?php

namespace App\Imports;

use App\Models\Insurance;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class HealthInsuranceImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Insurance([
            'employee_id' => $row['employee_id'],
            'health_insurance_type' => $row['insurance_type'],
            'health_insurance_no' => $row['insurance_number'],
            'health_facility' => $row['facility'],
            'health_insurance_remarks' => $row['remarks'],
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'insurance_type' => 'required',
            'insurance_number' => 'required',
            'facility' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'insurance_type.required' => 'Insurance Type is required',
            'insurance_number.required' => 'Insurance Number is required',
            'facility.required' => 'Health Facility is required',
        ];
    }

    public function chunkSize(): int
    {
        return 50;
    }

    public function batchSize(): int
    {
        return 50;
    }
}
