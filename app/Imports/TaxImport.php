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
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;

class TaxImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, WithEvents, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsFailures, SkipsErrors;

    private $employees;
    private $rowNumber = 0;
    private $sheetName;
    private $parent = null;

    public function __construct()
    {
        $this->employees = Employee::select('id', 'fullname', 'identity_card')->get();
    }

    /**
     * Set the parent import
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
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
            'tax_identification_no' => ['required'],
            'valid_date' => ['nullable'],
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

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $rows = $validator->getData();

            // Get pending personal rows if parent is available
            $pendingPersonalRows = $this->parent ? $this->parent->getPendingPersonalRows() : [];

            foreach ($rows as $rowIndex => $row) {
                // Skip if essential data is missing
                if (
                    empty($row['full_name']) || empty($row['identity_card_no']) ||
                    empty($row['tax_identification_no'])
                ) {
                    continue;
                }

                // Validate valid_date field
                if (!empty($row['valid_date'])) {
                    $validDate = $row['valid_date'];

                    // Check if it's a valid Excel date serial number
                    if (is_numeric($validDate)) {
                        // Excel dates start from 1900-01-01, so check reasonable range
                        if ($validDate < 1 || $validDate > 999999) {
                            $validator->errors()->add(
                                $rowIndex . '.valid_date',
                                "Valid Date must be a valid date. Excel date value '{$validDate}' is out of valid range."
                            );
                        }
                    } else {
                        // Try to parse as regular date string
                        try {
                            $parsedDate = \Carbon\Carbon::parse($validDate);
                            if (!$parsedDate->isValid()) {
                                $validator->errors()->add(
                                    $rowIndex . '.valid_date',
                                    "Valid Date must be a valid date. Value '{$validDate}' could not be parsed."
                                );
                            }
                        } catch (\Exception $e) {
                            $validator->errors()->add(
                                $rowIndex . '.valid_date',
                                "Valid Date must be a valid date. Value '{$validDate}' could not be parsed."
                            );
                        }
                    }
                }

                // First check if employee exists in database
                $employee = $this->employees->where('fullname', $row['full_name'])
                    ->where('identity_card', $row['identity_card_no'])
                    ->first();

                // If not in database, check if it's in the pending personal rows
                if (!$employee && !empty($pendingPersonalRows)) {
                    $existsInPending = collect($pendingPersonalRows)->first(function ($pendingRow) use ($row) {
                        return ($pendingRow['full_name'] ?? null) === $row['full_name'] &&
                            ($pendingRow['identity_card_no'] ?? null) === $row['identity_card_no'];
                    });

                    // If found in pending rows, consider it valid
                    if ($existsInPending) {
                        continue;
                    }
                }

                // Only add error if employee doesn't exist in database or pending rows
                if (!$employee) {
                    $validator->errors()->add(
                        $rowIndex . '.full_name',
                        "No employee found with name '{$row['full_name']}' and Identity Card No '{$row['identity_card_no']}'. Please check the employee data in the personal sheet."
                    );
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

            // If employee is not found in database, it might be a new one from personal sheet
            // We need to query for it again as it may have been created by now
            if (!$employee) {
                $employee = Employee::where('fullname', $row['full_name'])
                    ->where('identity_card', $row['identity_card_no'])
                    ->first();

                if (!$employee) {
                    // Still not found, skip this row
                    return null;
                }
            }

            // Prepare data for tax record
            $taxData = [
                'employee_id' => $employee->id,
                'tax_no' => (string) $row['tax_identification_no'],
            ];

            // Process valid date
            if (!empty($row['valid_date'])) {
                try {
                    if (is_numeric($row['valid_date'])) {
                        // Convert Excel serial number to date
                        $excelDate = (float) $row['valid_date'];

                        // Validate Excel date range (Excel dates start from 1900-01-01)
                        if ($excelDate >= 1 && $excelDate <= 999999) {
                            $taxData['tax_valid_date'] = Date::excelToDateTimeObject($excelDate);
                        } else {
                            // Log invalid Excel date but continue without setting the date
                            Log::warning("Invalid Excel date value in TaxImport: {$excelDate} for employee {$employee->fullname}");
                        }
                    } else {
                        // Try to parse as regular date string
                        $parsedDate = \Carbon\Carbon::parse($row['valid_date']);
                        if ($parsedDate->isValid()) {
                            $taxData['tax_valid_date'] = $parsedDate;
                        } else {
                            Log::warning("Invalid date string in TaxImport: {$row['valid_date']} for employee {$employee->fullname}");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error parsing date in TaxImport: {$row['valid_date']} for employee {$employee->fullname}. Error: " . $e->getMessage());
                    // Continue without setting the date rather than failing the entire import
                }
            }

            // Use updateOrCreate to handle both insert and update scenarios
            $tax = Taxidentification::updateOrCreate(
                ['employee_id' => $employee->id],
                $taxData
            );

            return $tax;
        } catch (\Illuminate\Database\QueryException $e) {
            $attribute = 'tax_identification_no';
            $errorMessage = "Duplicate tax identification number found. Please check if this tax record already exists.";

            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                $attribute = 'system_error';
                $errorMessage = 'Database error: ' . $e->getMessage();
            }

            $this->onFailure(new Failure(
                $this->rowNumber,
                $attribute,
                [$errorMessage],
                $row
            ));

            return null;
        } catch (\Exception $e) {
            $this->onFailure(new Failure(
                $this->rowNumber,
                'system_error',
                ['Error: ' . $e->getMessage()],
                $row
            ));

            return null;
        }
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
