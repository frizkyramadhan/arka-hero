<?php

namespace App\Services;

use App\Models\SupplyStockInItem;
use App\Models\SupplyStockOutItem;

class SupplyStock
{
    /**
     * @return array{in: int, out: int, ending: int}
     */
    public static function totals(string $supplyItemId, int $projectId): array
    {
        $in = (int) SupplyStockInItem::query()
            ->where('supply_item_id', $supplyItemId)
            ->whereHas('stockIn', fn ($q) => $q->where('project_id', $projectId))
            ->sum('quantity');

        $out = (int) SupplyStockOutItem::query()
            ->where('supply_item_id', $supplyItemId)
            ->whereHas('stockOut', fn ($q) => $q->where('project_id', $projectId))
            ->sum('quantity');

        return [
            'in' => $in,
            'out' => $out,
            'ending' => $in - $out,
        ];
    }

    public static function endingBalance(string $supplyItemId, int $projectId): int
    {
        return self::totals($supplyItemId, $projectId)['ending'];
    }

    /**
     * Batch stock totals for every catalog item that has movement on a project.
     * Items with no movement are omitted (caller treats missing as zeros).
     *
     * @return array<string, array{in: int, out: int, ending: int}>
     */
    public static function totalsByProject(int $projectId): array
    {
        $ins = SupplyStockInItem::query()
            ->selectRaw('supply_item_id, SUM(quantity) as total_in')
            ->whereHas('stockIn', fn ($q) => $q->where('project_id', $projectId))
            ->groupBy('supply_item_id')
            ->pluck('total_in', 'supply_item_id');

        $outs = SupplyStockOutItem::query()
            ->selectRaw('supply_item_id, SUM(quantity) as total_out')
            ->whereHas('stockOut', fn ($q) => $q->where('project_id', $projectId))
            ->groupBy('supply_item_id')
            ->pluck('total_out', 'supply_item_id');

        $itemIds = $ins->keys()->merge($outs->keys())->unique();
        $result = [];

        foreach ($itemIds as $itemId) {
            $in = (int) ($ins[$itemId] ?? 0);
            $out = (int) ($outs[$itemId] ?? 0);
            $result[(string) $itemId] = [
                'in' => $in,
                'out' => $out,
                'ending' => $in - $out,
            ];
        }

        return $result;
    }

    public static function receivedForOrderItem(string $supplyOrderItemId): int
    {
        return (int) SupplyStockInItem::query()
            ->where('supply_order_item_id', $supplyOrderItemId)
            ->sum('quantity');
    }
}
