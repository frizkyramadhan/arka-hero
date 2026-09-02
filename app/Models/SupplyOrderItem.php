<?php

namespace App\Models;

use App\Services\SupplyStock;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyOrderItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'supply_order_id',
        'supply_item_id',
        'quantity_ordered',
        'remarks',
    ];

    protected $casts = [
        'quantity_ordered' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(SupplyOrder::class, 'supply_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(SupplyItem::class, 'supply_item_id');
    }

    public function stockInItems(): HasMany
    {
        return $this->hasMany(SupplyStockInItem::class, 'supply_order_item_id');
    }

    public function quantityReceived(): int
    {
        return SupplyStock::receivedForOrderItem($this->id);
    }

    public function quantityOutstanding(): int
    {
        return max(0, $this->quantity_ordered - $this->quantityReceived());
    }
}
