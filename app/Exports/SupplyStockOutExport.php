<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SupplyStockOutExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'document_number',
            'project_code',
            'stock_date',
            'notes',
            'item_code',
            'stock_unit',
            'quantity',
            'location',
            'person_in_charge',
        ];
    }

    public function map($row): array
    {
        return [
            $row->document_number,
            $row->project_code,
            $row->stock_date,
            $row->notes,
            $row->item_code,
            $row->stock_unit,
            $row->quantity,
            $row->location,
            $row->person_in_charge,
        ];
    }
}
