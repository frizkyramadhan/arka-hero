<?php

namespace App\Exports;

use App\Models\Jobexperience;
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

class JobExperienceExport implements
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
        return 'job experience';
    }

    public function headings(): array
    {
        return [
            'ID No',
            'Full Name',
            'Company Name',
            'Company Address',
            'Position',
            'Duration',
            'Quit Reason',
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
        return Jobexperience::query()
            ->leftJoin('employees', 'employees.id', '=', 'jobexperiences.employee_id')
            ->select('jobexperiences.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');
    }

    public function map($jobexperience): array
    {
        return [
            $jobexperience->identity_card,
            $jobexperience->fullname,
            $jobexperience->company_name,
            $jobexperience->company_address,
            $jobexperience->job_position,
            $jobexperience->job_duration,
            $jobexperience->quit_reason,
        ];
    }
}
