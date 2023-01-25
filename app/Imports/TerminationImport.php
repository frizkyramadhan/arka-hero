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
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TerminationImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{

    use Importable, SkipsErrors, SkipsFailures;

    private $employees, $positions, $projects;

    public function __construct()
    {
        $this->employees = Employee::select('id', 'identity_card', 'fullname')->get();
        $this->positions = Position::select('id', 'position_name')->get();
        $this->projects = Project::select('id', 'project_code', 'project_name')->get();
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $employee = $this->employees->where('identity_card', $row['identity_card'])->where('fullname', $row['fullname'])->first();
        $get_position = $this->positions->where('position_name', $row['position_name'])->first();
        $get_project = $this->projects->where('project_code', $row['project_code'])->first();

        $administration = new Administration();
        $administration->employee_id = $employee->id ?? NULL;
        $administration->nik = $row['nik'] ?? NULL;
        $administration->project_id = $get_project->id;
        $administration->position_id = $get_position->id;
        $administration->class = $row['class'] ?? NULL;
        $administration->poh = $row['poh'] ?? NULL;
        if ($row['doh'] == NULL) {
            $administration->doh = NULL;
        } else {
            $administration->doh = Date::excelToDateTimeObject($row['doh']);
        }
        if ($row['foc'] == NULL) {
            $administration->foc = NULL;
        } else {
            $administration->foc = Date::excelToDateTimeObject($row['foc']);
        }
        $administration->agreement = $row['agreement'] ?? NULL;
        // $administration->company_program = $row['company_program'] ?? NULL;
        // $administration->no_fptk = $row['no_fptk'] ?? NULL;
        // $administration->no_sk_active = $row['no_sk_active'] ?? NULL;
        // $administration->basic_salary = $row['basic_salary'] ?? NULL;
        // $administration->site_allowance = $row['site_allowance'] ?? NULL;
        // $administration->other_allowance = $row['other_allowance'] ?? NULL;
        $administration->is_active = '0';
        if ($row['termination_date'] == NULL) {
            $administration->termination_date = NULL;
        } else {
            $administration->termination_date = Date::excelToDateTimeObject($row['termination_date']);
        }
        $administration->termination_reason = $row['termination_reason'] ?? NULL;
        $administration->coe_no = $row['coe_no'] ?? NULL;
        $administration->user_id = auth()->user()->id;
        $administration->save();
    }

    public function rules(): array
    {
        return [
            '*.fullname' => ['required'],
            '*.identity_card' => ['required'],
            '*.nik' => ['required', 'unique:administrations,nik'],
            '*.poh' => ['required'],
            '*.doh' => ['required'],
            '*.class' => ['required'],
            '*.position_name' => ['required', 'exists:positions,position_name'],
            '*.project_code' => ['required', 'exists:projects,project_code'],
            '*.termination_date' => ['required'],
            '*.termination_reason' => ['required'],
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
