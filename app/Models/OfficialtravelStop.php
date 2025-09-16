<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialtravelStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'official_travel_id',
        'arrival_at_destination',
        'arrival_check_by',
        'arrival_remark',
        'arrival_timestamps',
        'departure_from_destination',
        'departure_check_by',
        'departure_remark',
        'departure_timestamps'
    ];

    protected $casts = [
        'arrival_at_destination' => 'datetime',
        'departure_from_destination' => 'datetime',
        'arrival_timestamps' => 'datetime',
        'departure_timestamps' => 'datetime'
    ];

    // Relationships
    public function officialtravel()
    {
        return $this->belongsTo(Officialtravel::class);
    }

    public function arrivalChecker()
    {
        return $this->belongsTo(User::class, 'arrival_check_by');
    }

    public function departureChecker()
    {
        return $this->belongsTo(User::class, 'departure_check_by');
    }

    // Helper methods
    public function hasArrival()
    {
        return !is_null($this->arrival_at_destination);
    }

    public function hasDeparture()
    {
        return !is_null($this->departure_from_destination);
    }

    public function isComplete()
    {
        return $this->hasArrival() && $this->hasDeparture();
    }

    public function isArrivalOnly()
    {
        return $this->hasArrival() && !$this->hasDeparture();
    }

    public function isDepartureOnly()
    {
        return !$this->hasArrival() && $this->hasDeparture();
    }
}
