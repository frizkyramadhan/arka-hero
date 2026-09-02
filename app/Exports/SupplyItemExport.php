<?php

namespace App\Exports;

use App\Models\SupplyItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SupplyItemExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected Builder $query;

    public function __construct(?Builder $query = null)
    {
        $this->query = $query ?? SupplyItem::query()
            ->with('category')
            ->orderBy('code');
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'code',
            'category_name',
            'category_prefix',
            'name',
            'description',
            'stock_unit',
            'status',
        ];
    }

    public function map($item): array
    {
        /** @var SupplyItem $item */
        return [
            $item->code,
            $item->category->name ?? '',
            $item->category->prefix ?? '',
            $item->name,
            $item->description ?? '',
            $item->stock_unit,
            $item->status,
        ];
    }
}
