<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FuelRecord extends Model
{
    use Uuids;

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CLAIMED = 'claimed';

    protected $fillable = [
        'vehicle_id',
        'fuel_date',
        'odometer',
        'fuel_type',
        'quantity',
        'price_per_liter',
        'total_cost',
        'fuel_station',
        'driver_id',
        'receipt_number',
        'receipt_image',
        'notes',
        'created_by',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'rejected_at',
        'fuel_claim_id',
        'ai_parsed_at',
        'ai_model',
        'ai_raw_json',
    ];

    protected $casts = [
        'fuel_date' => 'date',
        'odometer' => 'integer',
        'quantity' => 'decimal:2',
        'price_per_liter' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'ai_parsed_at' => 'datetime',
        'ai_raw_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FuelRecord $record) {
            $record->deleteReceiptFile();
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(FuelClaim::class, 'fuel_claim_id');
    }

    public function isEditableByDriver(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_REJECTED], true);
    }

    /**
     * Receipt files live on the private disk under fuel_receipts/.
     * Example: storage/app/private/fuel_receipts/20260804143000_nota.jpg
     */
    public function deleteReceiptFile(): void
    {
        $path = $this->receipt_image;
        if ($path && Storage::disk('private')->exists($path)) {
            Storage::disk('private')->delete($path);
        }
    }
}
