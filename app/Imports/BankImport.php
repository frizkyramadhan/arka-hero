<?php

namespace App\Imports;

use App\Models\Bank;
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

class BankImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use Importable, SkipsErrors, SkipsFailures;

    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $bank = new Bank();
        $bank->bank_name = $row['bank_name'] ?? NULL;
        $bank->bank_status = $row['bank_status'] ?? NULL;
        $bank->save();
    }

    public function rules(): array
    {
        return [
            '*.bank_name' => ['required', 'unique:banks,bank_name'],
            '*.bank_status' => ['required'],
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
