<?php

namespace App\Exports;

use App\Models\EmployeeDisciplinary;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EmployeeDisciplinaryExport extends DefaultValueBinder implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable;

    protected Builder $query;

    public function __construct(?Builder $query = null)
    {
        $this->query = $query ?? EmployeeDisciplinary::query()
            ->with(['employee.administrations', 'criteria'])
            ->orderByDesc('effective_date')
            ->orderByDesc('id');
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'identity_card',
            'nik',
            'full_name',
            'type',
            'effective_date',
            'end_date',
            'remaining_days',
            'status',
            'reason',
            'pp_notes',
            'criterion_codes',
            'Imported (doc later)',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        // Force identity_card (A) and nik (B) as text so Excel does not coerce to number
        // (which breaks leading zeros / large NIK KTP and fails import string validation).
        if (in_array($cell->getColumn(), ['A', 'B'], true)) {
            $cell->setValueExplicit($value === null ? '' : (string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function map($row): array
    {
        /** @var EmployeeDisciplinary $row */
        $employee = $row->employee;
        $administration = $employee
            ? ($employee->administrations->firstWhere('is_active', 1) ?? $employee->administrations->first())
            : null;

        $remainingDays = $row->status === EmployeeDisciplinary::STATUS_ACTIVE
            ? $row->remaining_days
            : '';

        return [
            (string) ($employee->identity_card ?? ''),
            (string) ($administration->nik ?? ''),
            $employee->fullname ?? '',
            $row->type,
            optional($row->effective_date)->format('Y-m-d'),
            optional($row->end_date)->format('Y-m-d'),
            $remainingDays,
            $row->status,
            $row->reason,
            $row->pp_notes,
            $row->criteria->pluck('code')->implode(','),
            $row->isImported() ? 'Yes' : 'No',
        ];
    }
}
