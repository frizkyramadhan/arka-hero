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

class AdministrationImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, WithEvents
{
    use Importable, SkipsFailures, SkipsErrors;

    private $projects;
    private $positions;
    private $departments;
    private $rowNumber = 0;
    private $sheetName;

    public function __construct()
    {
        $this->projects = Project::select('id', 'project_code', 'project_name')->get();
        $this->positions = Position::select('id', 'position_name', 'department_id')->get();
        $this->departments = Department::select('id', 'department_name')->get();
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

    /**
     * Extends the validator to check data consistency
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $rows = $validator->getData();

            foreach ($rows as $rowIndex => $row) {
                // Skip if essential data is missing
                if (
                    empty($row['full_name']) || empty($row['identity_card_no']) ||
                    empty($row['position']) || empty($row['project_code'])
                ) {
                    continue;
                }

                // Validate employee exists with matching name and identity card
                $employee = Employee::where('fullname', $row['full_name'])
                    ->where('identity_card', $row['identity_card_no'])
                    ->first();

                if (!$employee) {
                    $validator->errors()->add(
                        $rowIndex . '.full_name',
                        "No employee found with name '{$row['full_name']}' and Identity Card No '{$row['identity_card_no']}'"
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
                    continue;
                }

                // Validate project name if provided
                if (!empty($row['project_name']) && $row['project_name'] !== $correctProjectName) {
                    $validator->errors()->add(
                        $rowIndex . '.project_name',
                        "Project name '{$row['project_name']}' does not match the expected name for code '{$row['project_code']}'. It should be '{$correctProjectName}'"
                    );
                }
            }
        });
    }

    // Implementasi SkipsOnFailure
    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->failures[] = $failure;
        }
    }

    public function failures(): array
    {
        return $this->failures;
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
            $employee = Employee::where('fullname', $row['full_name'])
                ->where('identity_card', $row['identity_card_no'])
                ->first();

            if (!$employee) {
                $this->failures[] = new \Maatwebsite\Excel\Validators\Failure(
                    $this->getRowNumber(),
                    'full_name',
                    ["No employee found with name '{$row['full_name']}' and Identity Card No '{$row['identity_card_no']}'"],
                    [$row['full_name']]
                );
                return null;
            }

            // Find the project by project code
            $projectFound = false;
            $project = null;
            $correctProjectName = '';

            foreach ($this->projects as $proj) {
                if ($proj->project_code === $row['project_code']) {
                    $projectFound = true;
                    $project = $proj;
                    $correctProjectName = $proj->project_name;
                    break;
                }
            }

            if (!$projectFound) {
                $this->failures[] = new \Maatwebsite\Excel\Validators\Failure(
                    $this->getRowNumber(),
                    'project_code',
                    ["Project with code '{$row['project_code']}' does not exist"],
                    [$row['project_code']]
                );
                return null;
            }

            // Validate project name if provided
            if (!empty($row['project_name']) && $row['project_name'] !== $correctProjectName) {
                $this->failures[] = new \Maatwebsite\Excel\Validators\Failure(
                    $this->getRowNumber(),
                    'project_name',
                    ["Project name '{$row['project_name']}' does not match the expected name for code '{$row['project_code']}'. It should be '{$correctProjectName}'"],
                    [$row['project_name']]
                );
                return null;
            }

            // Find the position by name and department if provided
            $positionQuery = $this->positions->filter(function ($item) use ($row) {
                return $item->position_name === $row['position'];
            })->values();

            if ($positionQuery->isEmpty()) {
                $this->failures[] = new \Maatwebsite\Excel\Validators\Failure(
                    $this->getRowNumber(),
                    'position',
                    ["Position '{$row['position']}' does not exist"],
                    [$row['position']]
                );
                return null;
            }

            $position = null;
            if (!empty($row['department'])) {
                // Find department ID for given name
                $department = $this->departments->where('department_name', $row['department'])->first();

                if (!$department) {
                    $this->failures[] = new \Maatwebsite\Excel\Validators\Failure(
                        $this->getRowNumber(),
                        'department',
                        ["Department '{$row['department']}' does not exist"],
                        [$row['department']]
                    );
                    return null;
                }

                // Filter positions by department
                $specificPosition = $positionQuery->first(function ($item) use ($department) {
                    return $item->department_id == $department->id;
                });

                if ($specificPosition) {
                    $position = $specificPosition;
                } else {
                    // Get all valid departments for this position
                    $validDepartmentIds = $positionQuery->pluck('department_id')->unique()->filter()->toArray();
                    $validDepartments = $this->departments->whereIn('id', $validDepartmentIds)->pluck('department_name')->toArray();
                    $validDepartmentsStr = implode(', ', $validDepartments);

                    $this->failures[] = new \Maatwebsite\Excel\Validators\Failure(
                        $this->getRowNumber(),
                        'department',
                        ["Department '{$row['department']}' is not valid for position '{$row['position']}'. Valid departments are: {$validDepartmentsStr}"],
                        [$row['department']]
                    );
                    return null;
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

            // Check if administration record already exists for this employee
            $existingAdmin = Administration::where('employee_id', $employee->id)
                ->where('is_active', 1)
                ->first();

            if ($existingAdmin) {
                // Update the existing record
                $existingAdmin->update($administrationData);
                return $existingAdmin;
            } else {
                // Create a new administration record
                return new Administration($administrationData);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $message = strpos($e->getMessage(), 'Duplicate entry') !== false
                ? "Duplicate NIK '{$row['nik']}' found. Please check if this administration record already exists."
                : 'Database error: ' . $e->getMessage();

            $attribute = strpos($e->getMessage(), 'Duplicate entry') !== false ? 'nik' : 'system_error';
            $value = strpos($e->getMessage(), 'Duplicate entry') !== false ? $row['nik'] : null;

            $this->failures[] = new \Maatwebsite\Excel\Validators\Failure(
                $this->getRowNumber(),
                $attribute,
                [$message],
                [$value]
            );

            return null;
        } catch (\Exception $e) {
            $this->failures[] = new \Maatwebsite\Excel\Validators\Failure(
                $this->getRowNumber(),
                'system_error',
                ['Error: ' . $e->getMessage()],
                [null]
            );
            return null;
        }
    }
}
