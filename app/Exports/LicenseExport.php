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
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class LicenseExport extends DefaultValueBinder implements
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
        return 'license';
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Identity Card No',
            'Driver License Type',
            'Driver License No',
            'Valid Date'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '@',
            'D' => '@'
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
            $license->fullname,
            $license->identity_card,
            $license->driver_license_type,
            $license->driver_license_no,
            $license->driver_license_exp ? date('d F Y', strtotime($license->driver_license_exp)) : ''
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'B') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        if ($cell->getColumn() === 'D') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
