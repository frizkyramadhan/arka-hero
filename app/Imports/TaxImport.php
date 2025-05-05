<?php

namespace App\Imports;

use App\Models\Taxidentification;
use App\Models\Employee;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class TaxImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, WithEvents, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsFailures, SkipsErrors;

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
            'tax identification no' => $this,
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
            'tax_identification_no' => ['required', 'string'],
            'valid_date' => ['required', 'date'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'full_name.required' => 'Full Name is required',
            'full_name.exists' => 'Employee with this name does not exist',
            'identity_card_no.required' => 'Identity Card No is required',
            'identity_card_no.exists' => 'Employee with this Identity Card does not exist',
            'tax_identification_no.required' => 'Tax Identification Number is required',
            'valid_date.required' => 'Valid Date is required',
            'valid_date.date' => 'Valid Date must be a valid date',
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
                    empty($row['tax_identification_no'])
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
            }
        });
    }

    public function model(array $row)
    {
        $this->rowNumber++;

        // Skip empty rows
        if (empty($row['full_name']) || empty($row['identity_card_no']) || empty($row['tax_identification_no'])) {
            return null;
        }

        try {
            // Find the employee based on name and identity card
            $employee = $this->employees->where('fullname', $row['full_name'])
                ->where('identity_card', $row['identity_card_no'])
                ->first();

            // Prepare data for tax record
            $taxData = [
                'employee_id' => $employee->id,
                'tax_no' => $row['tax_identification_no'],
            ];

            // Process valid date
            if (!empty($row['valid_date'])) {
                if (is_numeric($row['valid_date'])) {
                    $taxData['tax_valid_date'] = Date::excelToDateTimeObject($row['valid_date']);
                } else {
                    $taxData['tax_valid_date'] = \Carbon\Carbon::parse($row['valid_date']);
                }
            }

            // Use updateOrCreate to handle both insert and update scenarios
            $tax = Taxidentification::updateOrCreate(
                ['employee_id' => $employee->id],
                $taxData
            );

            return $tax;
        } catch (\Illuminate\Database\QueryException $e) {
            $message = strpos($e->getMessage(), 'Duplicate entry') !== false
                ? "Duplicate tax identification number found. Please check if this tax record already exists."
                : 'Database error: ' . $e->getMessage();

            $attribute = strpos($e->getMessage(), 'Duplicate entry') !== false ? 'tax_identification_no' : 'system_error';
            $value = strpos($e->getMessage(), 'Duplicate entry') !== false ? $row['tax_identification_no'] : null;

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
