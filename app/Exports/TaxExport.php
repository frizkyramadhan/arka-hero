<?php

namespace App\Exports;

use App\Models\Taxidentification;
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

class TaxExport implements
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
        return 'tax identification no';
    }

    public function headings(): array
    {
        return [
            'ID No',
            'Full Name',
            'Tax Identification No',
            'Valid Date'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER
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
        return Taxidentification::query()
            ->leftJoin('employees', 'taxidentifications.employee_id', '=', 'employees.id')
            ->select('taxidentifications.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');
    }

    public function map($taxidentification): array
    {
        return [
            $taxidentification->identity_card,
            $taxidentification->fullname,
            $taxidentification->tax_no,
            $taxidentification->tax_valid_date ? date('d F Y', strtotime($taxidentification->tax_valid_date)) : 'n/a',
        ];
    }
}
