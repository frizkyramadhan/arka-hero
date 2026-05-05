<?php

namespace App\Exports;

use App\Exports\Concerns\ExportForEmployeeIds;
use App\Models\Education;
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

class EducationExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;
    use ExportForEmployeeIds;

    public function title(): string
    {
        return 'education';
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Identity Card No',
            'Education Name',
            'Education Address',
            'Education Year',
            'Remarks',
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
        $query = Education::query()
            ->leftJoin('employees', 'employees.id', '=', 'educations.employee_id')
            ->select('educations.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');

        $this->applyEmployeeIdFilter($query, 'employees.id');

        return $query;
    }

    public function map($education): array
    {
        return [
            $education->fullname,
            $education->identity_card,
            $education->education_name,
            $education->education_address,
            $education->education_year,
            $education->education_remarks,
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
