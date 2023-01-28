<?php

namespace App\Exports;

use App\Models\Emrgcall;
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

class EmergencycallExport implements
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
        return 'emergency call';
    }

    public function headings(): array
    {
        return [
            'ID No',
            'Full Name',
            'Status',
            'Name',
            'Address',
            'Phone'
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
        return Emrgcall::query()
            ->leftJoin('employees', 'employees.id', '=', 'emrgcalls.employee_id')
            ->select('emrgcalls.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');
    }

    public function map($emrgcall): array
    {
        return [
            $emrgcall->identity_card,
            $emrgcall->fullname,
            $emrgcall->emrg_call_relation,
            $emrgcall->emrg_call_name,
            $emrgcall->emrg_call_address,
            $emrgcall->emrg_call_phone,
        ];
    }
}
