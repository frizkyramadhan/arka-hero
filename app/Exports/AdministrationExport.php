<?php

namespace App\Exports;

use App\Models\Administration;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdministrationExport implements
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
        return 'administration';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER
        ];
    }

    public function query()
    {
        return Administration::query()
            ->leftJoin('employees', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'projects.id', '=', 'administrations.project_id')
            ->leftJoin('positions', 'positions.id', '=', 'administrations.position_id')
            ->leftJoin('departments', 'departments.id', '=', 'positions.department_id')
            ->select('administrations.*', 'employees.identity_card', 'employees.fullname', 'positions.position_name', 'projects.project_code', 'projects.project_name', 'departments.department_name')
            ->where('is_active', 1)
            ->orderBy('nik', 'asc');
    }

    public function map($administration): array
    {
        return [
            $administration->identity_card,
            $administration->fullname,
            $administration->nik,
            $administration->poh,
            $administration->doh ? date('d F Y', strtotime($administration->doh)) : 'n/a',
            $administration->foc ? date('d F Y', strtotime($administration->foc)) : 'n/a',
            $administration->department_name,
            $administration->position_name,
            $administration->project_code,
            $administration->project_name,
            $administration->class,
            $administration->agreement,
            $administration->company_program,
            $administration->no_fptk,
            $administration->no_sk_active,
            $administration->basic_salary,
            $administration->site_allowance,
            $administration->other_allowance
        ];
    }

    public function headings(): array
    {
        return [
            'ID No',
            'Full Name',
            'NIK',
            'POH',
            'DOH',
            'FOC',
            'Department',
            'Position',
            'Project Code',
            'Project Name',
            'Class',
            'Agreement',
            'Company Program',
            'FPTK No.',
            'SK Active No',
            'Basic Salary',
            'Site Allowance',
            'Other Allowance'
        ];
    }
}
