<?php

namespace App\Imports;

use App\Models\Emrgcall;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;

class EmergencycallImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Emrgcall([
            'employee_id' => $row['employee_id'],
            'emrg_call_relation' => $row['relationship'],
            'emrg_call_name' => $row['name'],
            'emrg_call_address' => $row['address'],
            'emrg_call_phone' => $row['phone'],
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'relationship' => 'required',
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'relationship.required' => 'Relationship is required',
            'name.required' => 'Name is required',
            'address.required' => 'Address is required',
            'phone.required' => 'Phone is required',
        ];
    }
}
