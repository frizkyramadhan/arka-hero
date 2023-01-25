<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Insurance;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class InsuranceImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use Importable, SkipsErrors, SkipsFailures;

    private $employees;

    public function __construct()
    {
        $this->employees = Employee::select('id', 'fullname', 'identity_card')->get();
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $employee = $this->employees->where('identity_card', $row['identity_card'])->where('fullname', $row['fullname'])->first();

        $insurance = new Insurance();
        $insurance->employee_id = $employee->id ?? NULL;
        if ($row['health_insurance_type'] == 'BPJS Kesehatan') {
            $insurance->health_insurance_type = 'bpjsks';
        } elseif ($row['health_insurance_type'] == 'BPJS Ketenagakerjaan') {
            $insurance->health_insurance_type = 'bpjskt';
        } else {
            $insurance->health_insurance_type = NULL;
        }
        $insurance->health_insurance_no = $row['health_insurance_no'] ?? NULL;
        $insurance->health_facility = $row['health_facility'] ?? NULL;
        $insurance->health_insurance_remarks = $row['health_insurance_remarks'] ?? NULL;
        $insurance->save();
    }

    public function rules(): array
    {
        return [
            '*.fullname' => ['required', 'exists:employees,fullname'],
            '*.identity_card' => ['required', 'exists:employees,identity_card'],
            '*.health_insurance_type' => ['required'],
            '*.health_insurance_no' => ['required']
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
