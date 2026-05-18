<?php

namespace App\Exports;

use App\Exports\Concerns\ExportForEmployeeIds;
use App\Models\Course;
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

class CourseExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;
    use ExportForEmployeeIds;

    public function title(): string
    {
        return 'course';
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Identity Card No',
            'Course Name',
            'Course Address',
            'Course Year',
            'Remarks',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '@',
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
        $query = Course::query()
            ->leftJoin('employees', 'employees.id', '=', 'courses.employee_id')
            ->select('courses.*', 'employees.identity_card', 'employees.fullname')
            ->orderBy('fullname', 'asc');

        $this->applyEmployeeIdFilter($query, 'employees.id');

        return $query;
    }

    public function map($course): array
    {
        return [
            $course->fullname,
            $course->identity_card,
            $course->course_name,
            $course->course_address,
            $course->course_year,
            $course->course_remarks,
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
