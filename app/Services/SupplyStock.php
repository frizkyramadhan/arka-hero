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

    public static function receivedForOrderItem(string $supplyOrderItemId): int
    {
        return (int) SupplyStockInItem::query()
            ->where('supply_order_item_id', $supplyOrderItemId)
            ->sum('quantity');
    }
}
