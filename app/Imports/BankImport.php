<?php

namespace App\Imports;

use App\Models\Employeebank;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;

class BankImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Employeebank([
            'employee_id' => $row['employee_id'],
            'bank_id' => $row['bank_id'],
            'bank_account_no' => $row['account_number'],
            'bank_account_name' => $row['account_name'],
            'bank_account_branch' => $row['branch'],
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required',
            'account_name' => 'required',
            'branch' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'bank_id.required' => 'Bank is required',
            'bank_id.exists' => 'Bank does not exist',
            'account_number.required' => 'Account Number is required',
            'account_name.required' => 'Account Name is required',
            'branch.required' => 'Branch is required',
        ];
    }
}
