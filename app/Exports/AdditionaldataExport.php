<?php

namespace App\Exports;

use App\Exports\Concerns\ExportForEmployeeIds;
use App\Models\Additionaldata;
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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdditionaldataExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;
    use ExportForEmployeeIds;

    public function title(): string
    {
        return 'additional data';
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Identity Card No',
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
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function query()
    {
        $query = Additionaldata::query()
            ->leftJoin('employees', 'employees.id', '=', 'additionaldatas.employee_id')
            ->select('additionaldatas.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');

        $this->applyEmployeeIdFilter($query, 'employees.id');

        return $query;
    }

    public function map($additional): array
    {
        return [
            $additional->fullname,
            $additional->identity_card,
            $additional->cloth_size,
            $additional->pants_size,
            $additional->shoes_size,
            $additional->height,
            $additional->weight,
            $additional->glasses,
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
