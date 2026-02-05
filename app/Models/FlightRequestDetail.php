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

    // Relationships
    public function flightRequest()
    {
        return $this->belongsTo(FlightRequest::class);
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
