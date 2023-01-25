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
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class FamilyImport implements
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

        $family = new Family();
        $family->employee_id = $employee->id ?? NULL;
        $family->family_relationship = $row['family_relationship'] ?? NULL;
        $family->family_name = $row['family_name'] ?? NULL;
        $family->family_birthplace = $row['family_birthplace'] ?? NULL;
        if ($row['family_birthdate'] == NULL) {
            $family->family_birthdate = NULL;
        } else {
            $family->family_birthdate = Date::excelToDateTimeObject($row['family_birthdate']);
        }
        $family->family_remarks = $row['family_remarks'] ?? NULL;
        $family->bpjsks_no = $row['bpjsks_no'] ?? NULL;
        $family->save();
    }

    public function rules(): array
    {
        return [
            '*.fullname' => ['required', 'exists:employees,fullname'],
            '*.identity_card' => ['required', 'exists:employees,identity_card'],
            '*.family_relationship' => ['required'],
            '*.family_name' => ['required'],
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
