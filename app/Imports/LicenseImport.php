<?php

namespace App\Imports;

use App\Models\License;
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
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class LicenseImport implements
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

        $license = new License();
        $license->employee_id = $employee->id ?? NULL;
        $license->driver_license_no = $row['driver_license_no'] ?? NULL;
        $license->driver_license_type = $row['driver_license_type'] ?? NULL;
        if ($row['driver_license_exp'] == NULL) {
            $license->driver_license_exp = NULL;
        } else {
            $license->driver_license_exp = Date::excelToDateTimeObject($row['driver_license_exp']);
        }
        $license->save();
    }

    public function rules(): array
    {
        return [
            '*.fullname' => ['required', 'exists:employees,fullname'],
            '*.identity_card' => ['required', 'exists:employees,identity_card'],
            '*.driver_license_no' => ['required'],
            '*.driver_license_type' => ['required'],
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

    public function customValidationMessages()
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'Employee does not exist',
            'license_type.required' => 'License Type is required',
            'license_number.required' => 'License Number is required',
            'expiry_date.required' => 'Expiry Date is required',
            'expiry_date.date' => 'Expiry Date must be a valid date',
        ];
    }
}
