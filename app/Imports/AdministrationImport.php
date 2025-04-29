<?php

namespace App\Imports;

use App\Models\Administration;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;

class AdministrationImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Administration([
            'employee_id' => $row['employee_id'],
            'project_id' => $row['project_id'],
            'position_id' => $row['position_id'],
            'nik' => $row['nik'],
            'class' => $row['class'],
            'doh' => $row['date_of_hire'],
            'poh' => $row['place_of_hire'],
            'foc' => $row['foc'],
            'agreement' => $row['agreement'],
            'company_program' => $row['company_program'],
            'no_fptk' => $row['no_fptk'],
            'no_sk_active' => $row['no_sk_active'],
            'basic_salary' => $row['basic_salary'],
            'site_allowance' => $row['site_allowance'],
            'other_allowance' => $row['other_allowance'],
            'is_active' => $row['is_active'],
            'user_id' => auth()->user()->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'position_id' => 'required|exists:positions,id',
            'nik' => 'required|unique:administrations,nik',
            'class' => 'required',
            'date_of_hire' => 'required|date',
            'place_of_hire' => 'required',
            'is_active' => 'required|boolean',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'project_id.required' => 'Project is required',
            'project_id.exists' => 'Project does not exist',
            'position_id.required' => 'Position is required',
            'position_id.exists' => 'Position does not exist',
            'nik.required' => 'NIK is required',
            'nik.unique' => 'NIK already exists',
            'class.required' => 'Class is required',
            'date_of_hire.required' => 'Date of Hire is required',
            'date_of_hire.date' => 'Date of Hire must be a valid date',
            'place_of_hire.required' => 'Place of Hire is required',
            'is_active.required' => 'Active Status is required',
            'is_active.boolean' => 'Active Status must be true or false',
        ];
    }
}
