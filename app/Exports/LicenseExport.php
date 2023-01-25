<?php

namespace App\Exports;

use App\Models\License;
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

class LicenseExport implements
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
        return 'license';
    }

    public function headings(): array
    {
        return [
            'ID No',
            'Full Name',
            'Driver License',
            'Driver License No',
            'Valid Date'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
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
        return License::query()
            ->leftJoin('employees', 'employees.id', '=', 'licenses.employee_id')
            ->select('licenses.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');
    }

    public function map($license): array
    {
        return [
            $license->identity_card,
            $license->fullname,
            $license->driver_license_type,
            $license->driver_license_no,
            $license->driver_license_exp ? date('d F Y', strtotime($license->driver_license_exp)) : 'n/a'
        ];
    }
}
