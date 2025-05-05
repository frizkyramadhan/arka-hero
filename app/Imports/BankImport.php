<?php

namespace App\Imports;

use App\Models\Employeebank;
use App\Models\Employee;
use App\Models\Bank;
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

class BankImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, WithEvents, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsFailures, SkipsErrors;

    private $banks;
    private $employees;
    private $rowNumber = 0;
    private $sheetName;

    public function __construct()
    {
        $this->banks = Bank::select('id', 'bank_name')->get();
        $this->employees = Employee::select('id', 'fullname', 'identity_card')->get();
    }

    public function sheets(): array
    {
        return [
            'bank accounts' => $this,
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
            'bank_name' => ['required', 'string', 'exists:banks,bank_name']
        ];
    }

    public function customValidationMessages()
    {
        return [
            'full_name.required' => 'Full Name is required',
            'full_name.exists' => 'Employee with this name does not exist',
            'identity_card_no.required' => 'Identity Card No is required',
            'identity_card_no.exists' => 'Employee with this Identity Card does not exist',
            'bank_name.required' => 'Bank Name is required',
            'bank_name.exists' => 'Bank Name does not exist'
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
                    empty($row['bank_name'])
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
                        "No employee found with name '{$row['full_name']}' and Identity Card No '{$row['identity_card_no']}. Please check the employee data in the personal sheet."
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
        if (empty($row['full_name']) || empty($row['identity_card_no']) || empty($row['bank_name'])) {
            return null;
        }

        try {
            // Find the employee based on name and identity card
            $employee = $this->employees->where('fullname', $row['full_name'])
                ->where('identity_card', $row['identity_card_no'])
                ->first();

            // Find the bank by bank name
            $bank = $this->banks->where('bank_name', $row['bank_name'])->first();

            // Prepare data for bank record
            $bankData = [
                'employee_id' => $employee->id,
                'bank_id' => $bank->id,
                'bank_account_no' => $row['bank_account'],
                'bank_account_name' => $row['account_name'],
                'bank_account_branch' => $row['branch'],
            ];

            // Use updateOrCreate to handle both insert and update scenarios
            $bank = Employeebank::updateOrCreate(
                [
                    'employee_id' => $employee->id
                ],
                $bankData
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $message = strpos($e->getMessage(), 'Duplicate entry') !== false
                ? "Duplicate bank account found for employee. Please check if this bank record already exists."
                : 'Database error: ' . $e->getMessage();

            $attribute = strpos($e->getMessage(), 'Duplicate entry') !== false ? 'account_number' : 'system_error';
            $value = strpos($e->getMessage(), 'Duplicate entry') !== false ? $row['account_number'] : null;

            $this->failures[] = new \Maatwebsite\Excel\Validators\Failure(
                $this->getRowNumber(),
                $attribute,
                [$message],
                [$value]
            );

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
