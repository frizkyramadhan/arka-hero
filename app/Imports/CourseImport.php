<?php

namespace App\Imports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;

class CourseImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        return new Course([
            'employee_id' => $row['employee_id'],
            'course_name' => $row['course_name'],
            'course_address' => $row['course_address'],
            'course_year' => $row['course_year'],
            'course_remarks' => $row['course_remarks'],
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'course_name' => 'required',
            'course_address' => 'required',
            'course_year' => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'course_name.required' => 'Course Name is required',
            'course_address.required' => 'Course Address is required',
            'course_year.required' => 'Course Year is required',
            'course_year.numeric' => 'Course Year must be a number',
        ];
    }
}
