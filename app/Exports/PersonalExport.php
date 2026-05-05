<?php

namespace App\Exports;

use App\Exports\Concerns\ExportForEmployeeIds;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PersonalExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;
    use ExportForEmployeeIds;

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
            'B' => '@',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function query()
    {
        $query = Employee::query()->with('religion')->orderBy('fullname', 'asc');
        $this->applyEmployeeIdFilter($query, 'employees.id');

        return $query;
    }

    public function map($employee): array
    {
        return [
            $employee->fullname,
            $employee->identity_card,
            $employee->emp_pob,
            $employee->emp_dob ? date('d F Y', strtotime($employee->emp_dob)) : '',
            $employee->blood_type,
            $employee->religion?->religion_name ?? '',
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
