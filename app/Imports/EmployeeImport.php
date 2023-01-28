<?php

namespace App\Imports;

use App\Models\Bank;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Religion;
use App\Models\Employeebank;
use App\Models\Additionaldata;
use App\Models\Administration;
use App\Models\Education;
use App\Models\Emrgcall;
use App\Models\Taxidentification;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeeImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use Importable, SkipsErrors, SkipsFailures;

    private $employees, $religions, $positions, $projects, $banks;

    public function __construct()
    {
        $this->employees = Employee::select('id', 'identity_card', 'fullname')->get();
        $this->religions = Religion::select('id', 'religion_name')->get();
        $this->positions = Position::select('id', 'position_name')->get();
        $this->projects = Project::select('id', 'project_code', 'project_name')->get();
        $this->banks = Bank::select('id', 'bank_name')->get();
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $get_religion = $this->religions->where('religion_name', $row['religion_name'])->first();
        $get_employee = $this->employees->where('identity_card', $row['identity_card'])->where('fullname', $row['fullname'])->first();
        $get_position = $this->positions->where('position_name', $row['position_name'])->first();
        $get_project = $this->projects->where('project_code', $row['project_code'])->first();
        $get_bank = $this->banks->where('bank_name', $row['bank_name'])->first();

        $employee = new Employee;
        $employee->fullname = $row['fullname'];
        $employee->identity_card = $row['identity_card'];
        $employee->emp_pob = $row['emp_pob'];
        if ($row['emp_dob'] == NULL) {
            $employee->emp_dob = NULL;
        } else {
            $employee->emp_dob = Date::excelToDateTimeObject($row['emp_dob']);
        }
        $employee->gender = $row['gender'] ?? NULL;
        $employee->blood_type = $row['blood_type'] ?? NULL;
        $employee->religion_id = $get_religion->id ?? NULL;
        $employee->nationality = $row['nationality'] ?? NULL;
        $employee->marital = $row['marital'] ?? NULL;
        $employee->address = $row['address'] ?? NULL;
        $employee->village = $row['village'] ?? NULL;
        $employee->ward = $row['ward'] ?? NULL;
        $employee->district = $row['district'] ?? NULL;
        $employee->city = $row['city'] ?? NULL;
        $employee->phone = $row['phone'] ?? NULL;
        $employee->email = $row['email'] ?? NULL;
        $employee->user_id = auth()->user()->id;
        $employee->save();

        $administration = new Administration();
        $administration->employee_id = $employee->id;
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
        $administration->company_program = $row['company_program'] ?? NULL;
        $administration->no_fptk = $row['no_fptk'] ?? NULL;
        $administration->no_sk_active = $row['no_sk_active'] ?? NULL;
        $administration->basic_salary = $row['basic_salary'] ?? NULL;
        $administration->site_allowance = $row['site_allowance'] ?? NULL;
        $administration->other_allowance = $row['other_allowance'] ?? NULL;
        $administration->is_active = '1';
        $administration->user_id = auth()->user()->id;
        $administration->save();

        $checkBank = $get_bank == null ? false : true;
        if ($checkBank == true) {
            $bank = new Employeebank();
            $bank->employee_id = $employee->id;
            $bank->bank_id = $get_bank->id;
            $bank->bank_account_no = $row['bank_account_no'];
            $bank->bank_account_name = $row['bank_account_name'];
            $bank->bank_account_branch = $row['bank_account_branch'];
            $bank->save();
        }

        $checkTaxidentification = $row['tax_no'] == null ? false : true;
        if ($checkTaxidentification == true) {
            $taxidentification = new Taxidentification();
            $taxidentification->employee_id = $employee->id;
            $taxidentification->tax_no = $row['tax_no'] ?? NULL;
            if ($row['tax_valid_date'] == NULL) {
                $taxidentification->tax_valid_date = NULL;
            } else {
                $taxidentification->tax_valid_date = Date::excelToDateTimeObject($row['tax_valid_date']);
            }
            $taxidentification->save();
        }

        $checkAdditionalData = $row['cloth_size'] == null ? false : true;
        if ($checkAdditionalData == true) {
            $additional = new Additionaldata();
            $additional->employee_id = $employee->id;
            $additional->cloth_size = $row['cloth_size'] ?? NULL;
            $additional->pants_size = $row['pants_size'] ?? NULL;
            $additional->shoes_size = $row['shoes_size'] ?? NULL;
            $additional->height = $row['height'] ?? NULL;
            $additional->weight = $row['weight'] ?? NULL;
            $additional->glasses = $row['glasses'] ?? NULL;
            $additional->save();
        }

        $checkEducation = $row['education_name'] == null ? false : true;
        if ($checkEducation == true) {
            $education = new Education();
            $education->employee_id = $employee->id;
            $education->education_name = $row['education_name'] ?? NULL;
            $education->education_address = $row['education_address'] ?? NULL;
            $education->education_year = $row['education_year'] ?? NULL;
            $education->education_remarks = $row['education_remarks'] ?? NULL;
            $education->save();
        }

        $checkEmergency = $row['emrg_call_name'] == null ? false : true;
        if ($checkEmergency == true) {
            $emergency = new Emrgcall();
            $emergency->employee_id = $employee->id;
            $emergency->emrg_call_name = $row['emrg_call_name'] ?? NULL;
            $emergency->emrg_call_relation = $row['emrg_call_relation'] ?? NULL;
            $emergency->emrg_call_phone = $row['emrg_call_phone'] ?? NULL;
            $emergency->emrg_call_address = $row['emrg_call_address'] ?? NULL;
            $emergency->save();
        }
    }

    public function rules(): array
    {
        return [
            '*.fullname' => ['required'],
            '*.identity_card' => ['required', 'unique:employees,identity_card'],
            '*.emp_pob' => ['required'],
            '*.emp_dob' => ['required'],
            '*.nik' => ['required', 'unique:administrations,nik'],
            '*.poh' => ['required'],
            '*.doh' => ['required'],
            '*.class' => ['required'],
            '*.position_name' => ['required', 'exists:positions,position_name'],
            '*.project_code' => ['required', 'exists:projects,project_code'],
            '*.bank_name' => ['required', 'exists:banks,bank_name'],
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
