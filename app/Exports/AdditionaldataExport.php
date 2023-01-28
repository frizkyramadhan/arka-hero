<?php

namespace App\Exports;

use App\Models\Additionaldata;
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

class AdditionaldataExport implements
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
        return 'additional data';
    }

    public function headings(): array
    {
        return [
            'ID No',
            'Full Name',
            'Cloth Size',
            'Pants Size',
            'Shoes Size',
            'Height',
            'Weight',
            'Glasses',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER
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
        return Additionaldata::query()
            ->leftJoin('employees', 'employees.id', '=', 'additionaldatas.employee_id')
            ->select('additionaldatas.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');
    }

    public function map($additional): array
    {
        return [
            $additional->identity_card,
            $additional->fullname,
            $additional->cloth_size,
            $additional->pants_size,
            $additional->shoes_size,
            $additional->height,
            $additional->weight,
            $additional->glasses,
        ];
    }
}
