<?php

namespace App\Exports;

use App\Models\RecruitmentOffering;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OfferAcceptanceRateExport extends DefaultValueBinder implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping
{
    private $rows;

    public function __construct($rows)
    {
        $this->rows = $rows instanceof Collection ? $rows : collect($rows);
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Request No',
            'Department',
            'Position',
            'Project',
            'Candidate Name',
            'Offering Date',
            'Response Date',
            'Response Time (Days)',
            'Response',
            'Offering Letter No',
            'Notes',
        ];
    }

    public function map($row): array
    {
        return [
            $row['request_no'],
            $row['department'],
            $row['position'],
            $row['project'],
            $row['candidate_name'],
            $row['offering_date'],
            $row['response_date'],
            RecruitmentOffering::responseTimeLabel($row['response_time']),
            $row['response'],
            $row['offering_letter_no'],
            $row['notes'],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        // Excel format codes treat d/m/y and an unquoted Day(s) as date tokens,
        // so the day and the unit disappear. Keep these three columns as text.
        if (in_array($cell->getColumn(), ['F', 'G', 'H'], true)) {
            $cell->setValueExplicit($value === null ? '' : (string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
