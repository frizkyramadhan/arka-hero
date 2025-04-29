<?php

namespace App\Exports;

use App\Models\Employeebank;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class BankExport extends DefaultValueBinder implements
    FromQuery,
    ShouldAutoSize,
    WithMapping,
    WithHeadings,
    WithTitle,
    WithColumnFormatting,
    WithStyles,
    WithCustomValueBinder
{

    use Exportable;

    public function title(): string
    {
        return 'bank accounts';
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'ID No',
            'Bank Name',
            'Bank Account',
            'Account Name',
            'Branch'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '@',
            'D' => NumberFormat::FORMAT_NUMBER
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]]
        ];
    }

    public function query()
    {
        return Employeebank::query()
            ->leftJoin('employees', 'employeebanks.employee_id', '=', 'employees.id')
            ->leftJoin('banks', 'employeebanks.bank_id', '=', 'banks.id')
            ->select('employeebanks.*', 'employees.identity_card', 'employees.fullname', 'banks.bank_name')
            ->orderBy('fullname', 'asc');
    }

    public function map($employeebank): array
    {
        return [
            $employeebank->fullname,
            $employeebank->identity_card,
            $employeebank->bank_name,
            $employeebank->bank_account_no,
            $employeebank->bank_account_name,
            $employeebank->bank_account_branch
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'B') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
