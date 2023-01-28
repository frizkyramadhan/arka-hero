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

class FamilyExport implements
    FromQuery,
    ShouldAutoSize,
    WithMapping,
    WithHeadings,
    WithTitle,
    WithColumnFormatting,
    WithStyles
{
    use Exportable;

    public function title(): string
    {
        return 'family';
    }

    public function headings(): array
    {
        return [
            'ID No',
            'Full Name',
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
            'A' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER
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
            $family->identity_card,
            $family->fullname,
            $family->family_relationship,
            $family->family_name,
            $family->family_birthplace,
            $family->family_birthdate ? date('d F Y', strtotime($family->family_birthdate)) : 'n/a',
            $family->family_remarks,
            $family->bpjsks_no,
        ];
    }
}
