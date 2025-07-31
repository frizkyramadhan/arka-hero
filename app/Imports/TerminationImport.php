<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Administration;
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
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;

class TerminationImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithEvents,
    WithBatchInserts,
    WithChunkReading
{
    use Importable, SkipsErrors, SkipsFailures;

    private $employees;
    private $positions;
    private $projects;
    private $rowNumber = 0;
    private $sheetName;
    private $parent = null;

    public function __construct()
    {
        $this->employees = Employee::select('id', 'identity_card', 'fullname')->get();
        $this->positions = Position::select('id', 'position_name')->get();
        $this->projects = Project::select('id', 'project_code', 'project_name')->get();
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
            'termination' => $this,
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
            'nik' => ['required'],
            'department' => ['nullable', 'string'],
            'position' => ['nullable', 'string', 'exists:positions,position_name'],
            'project_code' => ['nullable', 'string', 'exists:projects,project_code'],
            'termination_date' => ['nullable'],
            'termination_reason' => ['required', 'in:End of Contract,End of Project,Resign,Termination,Retired'],
            'coe_no' => ['nullable', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'full_name.required' => 'Full Name is required',
            'full_name.exists' => 'Employee with this name does not exist',
            'identity_card_no.required' => 'Identity Card No is required',
            'identity_card_no.exists' => 'Employee with this Identity Card does not exist',
            'nik.required' => 'NIK is required',
            'position.exists' => 'Position does not exist',
            'project_code.exists' => 'Project Code does not exist',
            'termination_reason.required' => 'Termination Reason is required',
            'termination_reason.in' => 'Termination Reason must be one of: End of Contract, End of Project, Resign, Termination, Retired',
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
                    empty($row['termination_date']) || empty($row['termination_reason'])
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
        if (empty($row['full_name']) || empty($row['identity_card_no']) || empty($row['termination_date']) || empty($row['termination_reason'])) {
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

            // Find the project by project code if provided
            $project = null;
            if (!empty($row['project_code'])) {
                $project = $this->projects->where('project_code', $row['project_code'])->first();
            }

            // Find the position by name if provided
            $position = null;
            if (!empty($row['position'])) {
                $position = $this->positions->where('position_name', $row['position'])->first();
            }

            // Prepare termination data
            $terminationData = [
                'employee_id' => $employee->id,
                'project_id' => $project ? $project->id : null,
                'position_id' => $position ? $position->id : null,
                'nik' => $row['nik'],
                'is_active' => 0, // Set to 0 for termination
                'termination_reason' => $row['termination_reason'],
                'coe_no' => $row['coe_no'] ?? null,
                'user_id' => auth()->user()->id
            ];

            // Process termination date
            if (!empty($row['termination_date'])) {
                if (is_numeric($row['termination_date'])) {
                    $terminationData['termination_date'] = Date::excelToDateTimeObject($row['termination_date']);
                } else {
                    $terminationData['termination_date'] = \Carbon\Carbon::parse($row['termination_date']);
                }
            }

            // Use updateOrCreate to handle both insert and update scenarios
            $administration = Administration::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'nik' => $row['nik']
                ],
                $terminationData
            );

            return $administration;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->onFailure(new Failure(
                $this->rowNumber,
                'system_error',
                ['Database error: ' . $e->getMessage()],
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
