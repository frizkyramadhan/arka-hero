<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class FuelClaim extends Model
{
    use Uuids;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_READY = 'ready';

    public const STATUS_SENT = 'sent';

    public const STATUS_REALIZED = 'realized';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'claim_number',
        'period_from',
        'period_to',
        'total_quantity',
        'total_cost',
        'status',
        'notes',
        'created_by',
        'ready_at',
        'sent_at',
        'realized_at',
        'external_ref',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'total_quantity' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'ready_at' => 'datetime',
        'sent_at' => 'datetime',
        'realized_at' => 'datetime',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(FuelRecord::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateTotals(): void
    {
        $agg = $this->records()
            ->selectRaw('COALESCE(SUM(quantity), 0) as qty, COALESCE(SUM(total_cost), 0) as cost')
            ->first();

        $this->update([
            'total_quantity' => $agg->qty ?? 0,
            'total_cost' => $agg->cost ?? 0,
        ]);
    }

    public static function generateClaimNumber(): string
    {
        $prefix = 'FC-'.now()->format('Ym').'-';

        return DB::transaction(function () use ($prefix) {
            $last = static::query()
                ->where('claim_number', 'like', $prefix.'%')
                ->orderByDesc('claim_number')
                ->lockForUpdate()
                ->value('claim_number');

            $seq = 1;
            if ($last && preg_match('/(\d+)$/', $last, $m)) {
                $seq = ((int) $m[1]) + 1;
            }

            return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
