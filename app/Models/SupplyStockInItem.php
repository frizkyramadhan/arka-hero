<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyStockInItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'supply_stock_in_id',
        'supply_item_id',
        'quantity',
        'remarks',
        'supply_order_item_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(SupplyStockIn::class, 'supply_stock_in_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(SupplyItem::class, 'supply_item_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(SupplyOrderItem::class, 'supply_order_item_id');
    }
}
