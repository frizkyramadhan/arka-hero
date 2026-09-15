<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightRequestDetail extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    protected $casts = [
        'flight_date' => 'date',
        'flight_time' => 'datetime',
    ];

    // Segment Type Constants
    public const TYPE_DEPARTURE = 'departure';
    public const TYPE_RETURN = 'return';

    public static function getSegmentTypeOptions()
    {
        return [
            self::TYPE_DEPARTURE => 'Departure',
            self::TYPE_RETURN => 'Return',
        ];
    }

    public function typeLabel(): string
    {
        return self::getSegmentTypeOptions()[$this->segment_type]
            ?? ucfirst((string) $this->segment_type);
    }

    /** Dropdown / report label, e.g. "FRF001 · Departure · 10 Feb 2026 · CGK → SUB". */
    public function optionLabel(?string $formNumber = null): string
    {
        $date = $this->flight_date ? $this->flight_date->format('d M Y') : '-';
        $route = trim(($this->departure_city ?? '').' → '.($this->arrival_city ?? ''));
        $parts = array_filter([
            $formNumber ?: null,
            $this->typeLabel(),
            $date,
            $route !== '→' ? $route : null,
        ]);

        return implode(' · ', $parts);
    }

    /** Default Detail Reservation text from this flight segment. */
    public function reservationText(): string
    {
        $departure = strtoupper((string) ($this->departure_city ?? ''));
        $arrival = strtoupper((string) ($this->arrival_city ?? ''));
        $depCode = strlen($departure) >= 3 ? substr($departure, 0, 3) : $departure;
        $arrCode = strlen($arrival) >= 3 ? substr($arrival, 0, 3) : $arrival;
        $dateStr = $this->flight_date ? strtoupper($this->flight_date->format('d M Y')) : '-';
        $timeStr = $this->flight_time
            ? \Carbon\Carbon::parse($this->flight_time)->format('H.i')
            : '-';

        return trim("{$dateStr} // {$depCode} {$arrCode} // {$timeStr}");
    }

    // Relationships
    public function flightRequest()
    {
        return $this->belongsTo(FlightRequest::class);
    }

    public function issuanceDetails()
    {
        return $this->hasMany(FlightRequestIssuanceDetail::class, 'flight_request_detail_id');
    }

    public function passengerAdministration()
    {
        return $this->belongsTo(Administration::class, 'passenger_administration_id');
    }

    // Scopes
    public function scopeDeparture($query)
    {
        return $query->where('segment_type', self::TYPE_DEPARTURE);
    }

    public function scopeReturn($query)
    {
        return $query->where('segment_type', self::TYPE_RETURN);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('segment_order', 'asc');
    }
}
