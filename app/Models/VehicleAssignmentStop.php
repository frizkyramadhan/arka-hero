<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleAssignmentStop extends Model
{
    use Uuids;

    /** @deprecated Origin is header-only; kept for legacy cleanup only */
    public const TYPE_ORIGIN = 'origin';

    public const TYPE_DESTINATION = 'destination';

    public const TYPE_RETURN = 'return';

    protected $fillable = [
        'assignment_id',
        'sequence',
        'stop_type',
        'destination',
        'is_manual',
        'depart_time',
        'depart_km',
        'arrive_time',
        'arrive_km',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'is_manual' => 'boolean',
        'sequence' => 'integer',
        'depart_km' => 'integer',
        'arrive_km' => 'integer',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VehicleAssignment::class, 'assignment_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Paper-form style label:
     * sequence 0 → "Jam Berangkat/Tiba"
     * sequence 1 → "Jam Berangkat/Tiba Tujuan I"
     * return → "Jam Berangkat/Tiba Pulang"
     */
    public function legLabel(): string
    {
        if ($this->stop_type === self::TYPE_RETURN) {
            return 'Jam Berangkat/Tiba Pulang';
        }

        $seq = (int) $this->sequence;
        if ($seq <= 0) {
            return 'Jam Berangkat/Tiba';
        }

        return 'Jam Berangkat/Tiba Tujuan '.$this->toRoman($seq);
    }

    public function typeLabel(): string
    {
        return $this->legLabel();
    }

    public function formatTime(?string $time): string
    {
        if (! $time) {
            return '—';
        }

        return substr((string) $time, 0, 5);
    }

    /** Locked for itinerary adjust once jam/KM is filled (like LOT checkpoint). */
    public function hasTripActivity(): bool
    {
        return $this->depart_time !== null
            || $this->arrive_time !== null
            || $this->depart_km !== null
            || $this->arrive_km !== null;
    }

    protected function toRoman(int $number): string
    {
        $map = [
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I',
        ];
        $result = '';
        foreach ($map as $value => $numeral) {
            while ($number >= $value) {
                $result .= $numeral;
                $number -= $value;
            }
        }

        return $result !== '' ? $result : (string) $number;
    }
}
