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
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class TerminationExport extends DefaultValueBinder implements
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
        return 'termination';
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
            'B' => '@'
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
            ->where('is_active', 0)
            ->orderBy('nik', 'asc');
    }

    public function map($administration): array
    {
        return [
            $administration->fullname,
            $administration->identity_card,
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
            $administration->other_allowance,
            $administration->termination_date,
            $administration->termination_reason,
            $administration->coe_no
        ];
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Identity Card No',
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
            'Other Allowance',
            'Termination Date',
            'Termination Reason',
            'COE No'
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'B') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
