<?php

namespace App\Exports;

use App\Models\Employee;
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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class PersonalExport extends DefaultValueBinder implements
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
        return 'personal';
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Identity Card No',
            'Place of Birth',
            'Date of Birth',
            'Blood Type',
            'Religion',
            'Nationality',
            'Gender',
            'Marital Status',
            'Address',
            'Village',
            'Ward',
            'District',
            'City',
            'Phone',
            'Email',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '@'
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
        return Employee::query()->with('religion')->orderBy('fullname', 'asc');
    }

    public function map($employee): array
    {
        return [
            $employee->fullname,
            $employee->identity_card,
            $employee->emp_pob,
            $employee->emp_dob ? date('d F Y', strtotime($employee->emp_dob)) : 'n/a',
            $employee->blood_type,
            $employee->religion->religion_name,
            $employee->nationality,
            $employee->gender,
            $employee->marital,
            $employee->address,
            $employee->village,
            $employee->ward,
            $employee->district,
            $employee->city,
            $employee->phone,
            $employee->email,
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
