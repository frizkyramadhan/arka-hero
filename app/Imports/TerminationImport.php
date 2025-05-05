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

    public function __construct()
    {
        $this->employees = Employee::select('id', 'identity_card', 'fullname')->get();
        $this->positions = Position::select('id', 'position_name')->get();
        $this->projects = Project::select('id', 'project_code', 'project_name')->get();
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
            'project_code' => ['required', 'string', 'exists:projects,project_code'],
            'position' => ['required', 'string', 'exists:positions,position_name'],
            'nik' => ['required'],
            'class' => ['required', 'in:Staff,Non Staff'],
            'doh' => ['required'],
            'poh' => ['required', 'string'],
            'foc' => ['nullable'],
            'termination_date' => ['required'],
            'termination_reason' => ['required', 'in:End of Contract,End of Project,Resign,Termination'],
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
            'project_code.required' => 'Project Code is required',
            'project_code.exists' => 'Project Code does not exist',
            'position.required' => 'Position is required',
            'position.exists' => 'Position does not exist',
            'nik.required' => 'NIK is required',
            'class.required' => 'Class is required',
            'class.in' => 'Class must be either "Staff" or "Non Staff" (case sensitive)',
            'doh.required' => 'Date of Hire is required',
            'poh.required' => 'Place of Hire is required',
            'termination_date.required' => 'Termination Date is required',
            'termination_reason.required' => 'Termination Reason is required',
            'termination_reason.in' => 'Termination Reason must be one of: End of Contract, End of Project, Resign, Termination',
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
                    empty($row['position']) || empty($row['project_code'])
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
                        "No employee found with name '{$row['full_name']}' and Identity Card No '{$row['identity_card_no']}'. Please check at personal sheet."
                    );
                    continue;
                }

                // Validate NIK uniqueness only for new records
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

            // Find the position by name
            $position = $this->positions->where('position_name', $row['position'])->first();

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
                'is_active' => 0, // Set to 0 for termination
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

            // Process termination date
            if (!empty($row['termination_date'])) {
                if (is_numeric($row['termination_date'])) {
                    $administrationData['termination_date'] = Date::excelToDateTimeObject($row['termination_date']);
                } else {
                    $administrationData['termination_date'] = \Carbon\Carbon::parse($row['termination_date']);
                }
            }

            // Add termination specific fields
            $administrationData['termination_reason'] = $row['termination_reason'];
            $administrationData['coe_no'] = $row['coe_no'] ?? null;

            // Use updateOrCreate to handle both insert and update scenarios
            $administration = Administration::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'is_active' => 0
                ],
                $administrationData
            );

            return $administration;
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
