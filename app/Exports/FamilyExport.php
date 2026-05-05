<?php

namespace App\Exports;

use App\Exports\Concerns\ExportForEmployeeIds;
use App\Models\Family;
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

class FamilyExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;
    use ExportForEmployeeIds;

    public function title(): string
    {
        return 'family';
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Identity Card No',
            'Family Relationship',
            'Family Name',
            'Family Birthplace',
            'Family Birthdate',
            'Remarks',
            'Insurance No',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '@',
            'G' => '@',
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
        $query = Family::query()
            ->leftJoin('employees', 'employees.id', '=', 'families.employee_id')
            ->select('families.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');

        $this->applyEmployeeIdFilter($query, 'employees.id');

        return $query;
    }

    public function map($family): array
    {
        return [
            $family->fullname,
            $family->identity_card,
            $family->family_relationship,
            $family->family_name,
            $family->family_birthplace,
            $family->family_birthdate ? date('d F Y', strtotime($family->family_birthdate)) : '',
            $family->family_remarks,
            $family->bpjsks_no,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'B') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        if ($cell->getColumn() === 'G') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
