<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyStockOutItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'supply_stock_out_id',
        'supply_item_id',
        'quantity',
        'location',
        'person_in_charge',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function stockOut(): BelongsTo
    {
        return $this->belongsTo(SupplyStockOut::class, 'supply_stock_out_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(SupplyItem::class, 'supply_item_id');
    }
}
