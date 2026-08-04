<?php

namespace App\Models;

use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use Uuids;

    protected $fillable = [
        'kode',
        'license_plate',
        'pic',
        'lokasi',
        'keterangan',
        'brand',
        'model',
        'description',
        'year',
        'color',
        'type',
        'ownership',
        'vin',
        'engine_number',
        'transmission',
        'fuel_type',
        'capacity',
        'status',
        'odometer',
        'assigned_to',
        'arkfleet_equipment_id',
        'arkfleet_sync_at',
        'arkfleet_status',
        'project_code',
    ];

    protected $casts = [
        'arkfleet_sync_at' => 'datetime',
        'year' => 'integer',
        'capacity' => 'integer',
        'odometer' => 'integer',
        'arkfleet_equipment_id' => 'integer',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecord::class);
    }

    public function activeDocument(string $type): ?VehicleDocument
    {
        return $this->documents
            ->where('document_type', $type)
            ->whereIn('status', ['active', 'expired', 'pending_renewal'])
            ->sortByDesc(fn (VehicleDocument $doc) => $doc->expiry_date?->timestamp ?? 0)
            ->first();
    }

    public function documentExpiry(string $type): ?Carbon
    {
        return $this->activeDocument($type)?->expiry_date;
    }

    /**
     * Days remaining until expiry (negative if overdue). Null if no date.
     */
    public function daysRemainingFor(string $type): ?int
    {
        $expiry = $this->documentExpiry($type);
        if (! $expiry) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($expiry->copy()->startOfDay(), false);
    }

    public static function formatExpiryCell(?Carbon $expiry, ?int $daysRemaining): string
    {
        if (! $expiry) {
            return '<span class="text-muted">—</span>';
        }

        $dateLabel = $expiry->format('d F Y');
        $isExpired = $daysRemaining !== null && $daysRemaining < 0;
        $dotClass = $isExpired ? 'text-danger' : 'text-success';
        $daysLabel = $daysRemaining === null ? '' : (string) $daysRemaining;

        return '<div class="small">'
            .e($dateLabel)
            .'<br><i class="fas fa-circle '.$dotClass.'" style="font-size:0.65rem"></i> '
            .'<span class="'.$dotClass.' font-weight-bold">'.e($daysLabel).'</span>'
            .'</div>';
    }
}
