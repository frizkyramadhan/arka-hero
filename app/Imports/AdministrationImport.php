<?php

namespace App\Imports;

use App\Models\Administration;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Position;
use App\Models\Department;
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

class AdministrationImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, WithEvents, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsFailures, SkipsErrors;

    private $projects;
    private $positions;
    private $departments;
    private $employees;
    private $rowNumber = 0;
    private $sheetName;
    private $parent = null;

    public function __construct()
    {
        $this->projects = Project::select('id', 'project_code', 'project_name')->get();
        $this->positions = Position::select('id', 'position_name', 'department_id')->get();
        $this->departments = Department::select('id', 'department_name')->get();
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
            'administration' => $this,
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
            'project_code' => ['required', 'string', 'exists:projects,project_code'],
            'position' => ['required', 'string', 'exists:positions,position_name'],
            'nik' => ['required'],
            'class' => ['required', 'in:Staff,Non Staff'],
            'doh' => ['required'],
            'poh' => ['required', 'string'],
            'foc' => ['nullable'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'full_name.required' => 'Full Name is required',
            'full_name.exists' => 'Employee with this name does not exist',
            'identity_card_no.required' => 'Identity Card No is required',
            'identity_card_no.exists' => 'Employee with this Identity Card does not exist',
            'project_code.required' => 'Project Code is required',
            'project_code.exists' => 'Project Code does not exist',
            'position.required' => 'Position is required',
            'position.exists' => 'Position does not exist',
            'nik.required' => 'NIK is required',
            'class.required' => 'Class is required',
            'class.in' => 'Class must be either "Staff" or "Non Staff" (case sensitive)',
            'doh.required' => 'Date of Hire is required',
            'poh.required' => 'Place of Hire is required',
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
                    empty($row['position']) || empty($row['project_code'])
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
                        "No employee found with name '{$row['full_name']}' and Identity Card No '{$row['identity_card_no']}'. Please check at personal sheet."
                    );
                    continue;
                }

                // Validate position and department consistency
                $allPositionsWithName = $this->positions->filter(function ($item) use ($row) {
                    return $item->position_name === $row['position'];
                })->values();

                $validDepartmentIds = $allPositionsWithName->pluck('department_id')->unique()->filter()->toArray();
                $validDepartments = $this->departments->whereIn('id', $validDepartmentIds)->pluck('department_name')->toArray();

                if ($allPositionsWithName->isEmpty()) {
                    $validator->errors()->add(
                        $rowIndex . '.position',
                        "Position '{$row['position']}' does not exist"
                    );
                } elseif (!empty($row['department'])) {
                    // Check if the department matches any valid department for this position
                    $departmentExists = false;

                    foreach ($this->departments as $dept) {
                        if (in_array($dept->id, $validDepartmentIds) && $dept->department_name === $row['department']) {
                            $departmentExists = true;
                            break;
                        }
                    }

                    if (!$departmentExists) {
                        $validDepartmentsStr = implode(', ', $validDepartments);
                        $validator->errors()->add(
                            $rowIndex . '.department',
                            "Department '{$row['department']}' is not valid for position '{$row['position']}'. Valid departments are: {$validDepartmentsStr}"
                        );
                    }
                }

                // Validate project code and project name consistency
                $projectFound = false;
                $correctProjectName = '';

                foreach ($this->projects as $proj) {
                    if ($proj->project_code === $row['project_code']) {
                        $projectFound = true;
                        $correctProjectName = $proj->project_name;
                        break;
                    }
                }

                if (!$projectFound) {
                    $validator->errors()->add(
                        $rowIndex . '.project_code',
                        "Project with code '{$row['project_code']}' does not exist"
                    );
                } else if (!empty($row['project_name']) && $row['project_name'] !== $correctProjectName) {
                    $validator->errors()->add(
                        $rowIndex . '.project_name',
                        "Project name '{$row['project_name']}' does not match the expected name for code '{$row['project_code']}'. It should be '{$correctProjectName}'"
                    );
                }

                if (empty($row['id'])) {
                    $existingNik = Administration::where('nik', $row['nik'])
                        ->where('employee_id', '!=', $employee->id)
                        ->first();

                    if ($existingNik) {
                        $validator->errors()->add(
                            $rowIndex . '.nik',
                            "NIK '{$row['nik']}' already exists for another employee"
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
        if (empty($row['full_name']) || empty($row['identity_card_no'])) {
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

            // Find the project by project code
            $project = null;
            $correctProjectName = '';

            foreach ($this->projects as $proj) {
                if ($proj->project_code === $row['project_code']) {
                    $project = $proj;
                    $correctProjectName = $proj->project_name;
                    break;
                }
            }

            // Find the position by name and department if provided
            $positionQuery = $this->positions->filter(function ($item) use ($row) {
                return $item->position_name === $row['position'];
            })->values();

            $position = null;
            if (!empty($row['department'])) {
                // Find department ID for given name
                $department = $this->departments->where('department_name', $row['department'])->first();

                if ($department) {
                    // Filter positions by department
                    $specificPosition = $positionQuery->first(function ($item) use ($department) {
                        return $item->department_id == $department->id;
                    });

                    if ($specificPosition) {
                        $position = $specificPosition;
                    }
                }
            } else {
                // If no department specified, take the first position with that name
                $position = $positionQuery->first();
            }

            // Prepare data for administration record
            $administrationData = [
                'employee_id' => $employee->id,
                'project_id' => $project->id,
                'position_id' => $position->id,
                'nik' => $row['nik'],
                'class' => $row['class'],
                'poh' => $row['poh'],
                'agreement' => $row['agreement'] ?? null,
                'company_program' => $row['company_program'] ?? null,
                'no_fptk' => $row['fptk_no'] ?? null,
                'no_sk_active' => $row['sk_active_no'] ?? null,
                'basic_salary' => $row['basic_salary'] ?? null,
                'site_allowance' => $row['site_allowance'] ?? null,
                'other_allowance' => $row['other_allowance'] ?? null,
                'is_active' => 1, // Always set to 1 as per requirement
                'user_id' => auth()->user()->id
            ];

            // Process date of hire
            if (!empty($row['doh'])) {
                if (is_numeric($row['doh'])) {
                    $administrationData['doh'] = Date::excelToDateTimeObject($row['doh']);
                } else {
                    $administrationData['doh'] = \Carbon\Carbon::parse($row['doh']);
                }
            }

            // Process FOC date
            if (!empty($row['foc'])) {
                if (is_numeric($row['foc'])) {
                    $administrationData['foc'] = Date::excelToDateTimeObject($row['foc']);
                } else {
                    $administrationData['foc'] = \Carbon\Carbon::parse($row['foc']);
                }
            }

            // Use updateOrCreate to handle both insert and update scenarios
            $administration = Administration::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'is_active' => 1
                ],
                $administrationData
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
