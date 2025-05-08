<?php

namespace App\Imports;

use App\Models\Family;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
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
use Maatwebsite\Excel\Validators\Failure;

class FamilyImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithEvents, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsErrors, SkipsFailures;

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
            'family' => $this,
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
            'family_relationship' => ['required', 'in:Husband,Wife,Child,Parent,Sibling'],
            'family_name' => ['required', 'string']
        ];
    }

    public function customValidationMessages()
    {
        return [
            'full_name.required' => 'Full Name is required',
            'full_name.exists' => 'Employee with this name does not exist',
            'identity_card_no.required' => 'Identity Card No is required',
            'identity_card_no.exists' => 'Employee with this Identity Card does not exist',
            'family_relationship.required' => 'Family Relationship is required',
            'family_relationship.in' => 'Family Relationship must be one of: Husband, Wife, Child, Parent, Sibling',
            'family_name.required' => 'Family Name is required',
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
                    empty($row['family_name']) || empty($row['family_relationship'])
                ) {
                    continue;
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
        if (empty($row['full_name']) || empty($row['identity_card_no']) || empty($row['family_name']) || empty($row['family_relationship'])) {
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

            // Prepare data for family record
            $familyData = [
                'employee_id' => $employee->id,
                'family_relationship' => $row['family_relationship'],
                'family_name' => $row['family_name'],
                'family_birthplace' => $row['family_birthplace'] ?? null,
                'family_remarks' => $row['remarks'] ?? null,
                'bpjsks_no' => $row['insurance_no'] ?? null,
            ];

            // Process birthdate
            if (!empty($row['family_birthdate'])) {
                if (is_numeric($row['family_birthdate'])) {
                    $familyData['family_birthdate'] = Date::excelToDateTimeObject($row['family_birthdate']);
                } else {
                    $familyData['family_birthdate'] = \Carbon\Carbon::parse($row['family_birthdate']);
                }
            }

            // Use updateOrCreate to handle both new and existing records
            return Family::updateOrCreate(
                ['id' => $row['id'] ?? null],
                $familyData
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $attribute = 'family_name';
            $errorMessage = "Duplicate family record found. Please check if this family record already exists.";

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
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
