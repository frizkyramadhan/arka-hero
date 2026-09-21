<?php

namespace App\Exports;

use App\Models\SupplyItem;
use App\Services\SupplyStock;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class SupplyItemExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    protected Builder $query;

    protected ?int $projectId;

    /** @var array<string, array{in: int, out: int, ending: int}>|null */
    private ?array $stockByItem = null;

    public function __construct(?Builder $query = null, ?int $projectId = null)
    {
        $this->query = $query ?? SupplyItem::query()
            ->with('category')
            ->orderBy('code');
        $this->projectId = $projectId;
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
            'stock_in',
            'stock_out',
            'balance',
            'status',
        ];
    }

    public function map($item): array
    {
        /** @var SupplyItem $item */
        $stock = $this->stockFor((string) $item->id);

        return [
            $item->code,
            $item->category->name ?? '',
            $item->category->prefix ?? '',
            $item->name,
            $item->description ?? '',
            $item->stock_unit,
            $stock['in'],
            $stock['out'],
            $stock['ending'],
            $item->status,
        ];
    }

    /**
     * @return array{in: int, out: int, ending: int}
     */
    private function stockFor(string $itemId): array
    {
        // Without a project there is no per-project stock — still write 0 so cells are never blank.
        if ($this->projectId === null) {
            return ['in' => 0, 'out' => 0, 'ending' => 0];
        }

        if ($this->stockByItem === null) {
            $this->stockByItem = SupplyStock::totalsByProject($this->projectId);
        }

        $row = $this->stockByItem[$itemId] ?? ['in' => 0, 'out' => 0, 'ending' => 0];

        return [
            'in' => (int) $row['in'],
            'out' => (int) $row['out'],
            'ending' => (int) $row['ending'],
        ];
    }
}
