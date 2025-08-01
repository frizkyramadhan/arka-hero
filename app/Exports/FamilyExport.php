<?php

namespace App\Exports;

use App\Models\Family;
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

class FamilyExport extends DefaultValueBinder implements
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
            'G' => '@'
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
        return Family::query()
            ->leftJoin('employees', 'employees.id', '=', 'families.employee_id')
            ->select('families.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');
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
