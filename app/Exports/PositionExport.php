<?php

namespace App\Exports;

use App\Models\Position;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PositionExport implements
    FromQuery,
    ShouldAutoSize,
    WithMapping,
    WithHeadings,
    WithTitle,
    WithStyles
{
    use Exportable;

    public function title(): string
    {
        return 'positions';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Position Name',
            'Department Name',
            'Position Status',
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
        return Position::query()
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('positions.*', 'departments.department_name')
            ->orderBy('positions.position_name', 'asc');
    }

    public function map($position): array
    {
        return [
            $position->id,
            $position->position_name,
            $position->department_name,
            $position->position_status == '1' ? 'Active' : 'Inactive',
        ];
    }
}
