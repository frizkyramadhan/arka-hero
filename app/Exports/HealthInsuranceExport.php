<?php

namespace App\Exports;

use App\Models\Insurance;
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

class HealthInsuranceExport implements
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
        return 'health insurance';
    }

    public function headings(): array
    {
        return [
            'ID No',
            'Full Name',
            'Health Insurance',
            'Health Insurance No',
            'Health Facility',
            'Remarks'
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
        return Insurance::query()
            ->leftJoin('employees', 'employees.id', '=', 'insurances.employee_id')
            ->select('employees.identity_card', 'employees.fullname', 'insurances.health_insurance_type', 'insurances.health_insurance_no', 'insurances.health_facility', 'insurances.health_insurance_remarks')
            ->orderBy('fullname', 'asc');
    }

    public function map($insurance): array
    {
        return [
            $insurance->identity_card,
            $insurance->fullname,
            $insurance->health_insurance_type == 'bpjsks' ? 'BPJS Kesehatan' : 'BPJS Ketenagakerjaan',
            $insurance->health_insurance_no,
            $insurance->health_facility,
            $insurance->health_insurance_remarks
        ];
    }
}
