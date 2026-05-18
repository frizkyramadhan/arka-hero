<?php

namespace App\Exports;

use App\Exports\Concerns\ExportForEmployeeIds;
use App\Models\Administration;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdministrationExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;
    use ExportForEmployeeIds;

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
            'B' => '@',
        ];
    }

    public function query()
    {
        $query = Administration::query()
            ->leftJoin('employees', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'projects.id', '=', 'administrations.project_id')
            ->leftJoin('positions', 'positions.id', '=', 'administrations.position_id')
            ->leftJoin('departments', 'departments.id', '=', 'positions.department_id')
            ->leftJoin('grades', 'grades.id', '=', 'administrations.grade_id')
            ->leftJoin('levels', 'levels.id', '=', 'administrations.level_id')
            ->select('administrations.*', 'employees.identity_card', 'employees.fullname', 'positions.position_name', 'projects.project_code', 'projects.project_name', 'departments.department_name', 'grades.name as grade_name', 'levels.name as level_name')
            ->where('administrations.is_active', 1)
            ->orderBy('nik', 'asc');

        $this->applyEmployeeIdFilter($query, 'administrations.employee_id');

        return $query;
    }

    public function map($administration): array
    {
        return [
            $administration->fullname,
            $administration->identity_card,
            $administration->nik,
            $administration->poh,
            $administration->doh ? date('d F Y', strtotime($administration->doh)) : '',
            $administration->foc ? date('d F Y', strtotime($administration->foc)) : '',
            $administration->department_name,
            $administration->position_name,
            $administration->grade_name,
            $administration->level_name,
            $administration->project_code,
            $administration->project_name,
            $administration->class,
            $administration->agreement,
            $administration->company_program,
            $administration->no_fptk,
            $administration->no_sk_active,
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
            'Grade',
            'Level',
            'Project Code',
            'Project Name',
            'Class',
            'Agreement',
            'Company Program',
            'FPTK No.',
            'SK Active No',
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
