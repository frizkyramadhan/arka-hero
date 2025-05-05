<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class CourseImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithEvents, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsErrors, SkipsFailures;

    private $employees;
    private $rowNumber = 0;
    private $sheetName;

    public function __construct()
    {
        $this->employees = Employee::select('id', 'fullname', 'identity_card')->get();
    }

    public function sheets(): array
    {
        return [
            'course' => $this,
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->sheetName = $event->getSheet()->getTitle();
            }
        ];
    }

    public function getSheetName()
    {
        return $this->sheetName;
    }

    public function getRowNumber()
    {
        return $this->rowNumber;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'exists:employees,fullname'],
            'identity_card_no' => ['required', 'string', 'exists:employees,identity_card'],
            'course_name' => ['required', 'string'],
            'course_address' => ['nullable', 'string'],
            'course_year' => ['nullable', 'numeric'],
            'course_remarks' => ['nullable', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'full_name.required' => 'Full Name is required',
            'full_name.exists' => 'Employee with this name does not exist',
            'identity_card_no.required' => 'Identity Card No is required',
            'identity_card_no.exists' => 'Employee with this Identity Card does not exist',
            'course_name.required' => 'Course Name is required',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $rows = $validator->getData();

            foreach ($rows as $rowIndex => $row) {
                // Skip if essential data is missing
                if (
                    empty($row['full_name']) || empty($row['identity_card_no']) ||
                    empty($row['course_name'])
                ) {
                    continue;
                }

                // Validate employee exists with matching name and identity card
                $employee = $this->employees->where('fullname', $row['full_name'])
                    ->where('identity_card', $row['identity_card_no'])
                    ->first();

                if (!$employee) {
                    $validator->errors()->add(
                        $rowIndex . '.full_name',
                        "No employee found with name '{$row['full_name']}' and Identity Card No '{$row['identity_card_no']}'. Please check the employee data in the personal sheet."
                    );
                    continue;
                }

                // If ID is provided, validate that the course record exists
                if (!empty($row['id'])) {
                    $existingCourse = Course::where('id', $row['id'])->first();

                    if (!$existingCourse) {
                        $validator->errors()->add(
                            $rowIndex . '.id',
                            "Course record with ID {$row['id']} not found"
                        );
                    }
                }
            }
        });
    }

    public function model(array $row)
    {
        $this->rowNumber++;

        // Skip empty rows
        if (
            empty($row['full_name']) || empty($row['identity_card_no']) ||
            empty($row['course_name'])
        ) {
            return null;
        }

        try {
            // Find the employee based on name and identity card
            $employee = $this->employees->where('fullname', $row['full_name'])
                ->where('identity_card', $row['identity_card_no'])
                ->first();

            // Prepare data for course record
            $courseData = [
                'employee_id' => $employee->id,
                'course_name' => $row['course_name'],
                'course_address' => $row['course_address'] ?? null,
                'course_year' => $row['course_year'] ?? null,
                'course_remarks' => $row['remarks'] ?? null,
            ];

            // Use updateOrCreate to handle both new and existing records
            return Course::updateOrCreate(
                ['id' => $row['id'] ?? null],
                $courseData
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $message = strpos($e->getMessage(), 'Duplicate entry') !== false
                ? "Duplicate course record found. Please check if this course record already exists."
                : 'Database error: ' . $e->getMessage();

            $attribute = strpos($e->getMessage(), 'Duplicate entry') !== false ? 'course_name' : 'system_error';
            $value = strpos($e->getMessage(), 'Duplicate entry') !== false ? $row['course_name'] : null;

            $this->failures[] = [
                'sheet' => $this->getSheetName(),
                'row' => $this->getRowNumber(),
                'attribute' => $attribute,
                'value' => $value,
                'errors' => $message
            ];

            return null;
        } catch (\Exception $e) {
            $this->failures[] = [
                'sheet' => $this->getSheetName(),
                'row' => $this->getRowNumber(),
                'attribute' => 'system_error',
                'value' => null,
                'errors' => 'Error: ' . $e->getMessage()
            ];
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
