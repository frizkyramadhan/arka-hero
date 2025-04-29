<?php

namespace App\Imports;

use App\Models\Additionaldata;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;

class AdditionaldataImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Additionaldata([
            'employee_id' => $row['employee_id'],
            'cloth_size' => $row['cloth_size'],
            'pants_size' => $row['pants_size'],
            'shoes_size' => $row['shoes_size'],
            'height' => $row['height'],
            'weight' => $row['weight'],
            'glasses' => $row['glasses'],
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'cloth_size' => 'required',
            'pants_size' => 'required',
            'shoes_size' => 'required',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'glasses' => 'required|boolean',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'cloth_size.required' => 'Cloth Size is required',
            'pants_size.required' => 'Pants Size is required',
            'shoes_size.required' => 'Shoes Size is required',
            'height.required' => 'Height is required',
            'height.numeric' => 'Height must be a number',
            'weight.required' => 'Weight is required',
            'weight.numeric' => 'Weight must be a number',
            'glasses.required' => 'Glasses status is required',
            'glasses.boolean' => 'Glasses status must be true or false',
        ];
    }
}
