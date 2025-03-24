<?php

namespace App\Models;

use App\Models\User;
use App\Traits\Uuids;
use App\Models\Project;
use App\Models\Accommodation;
use App\Models\Administration;
use App\Models\Transportation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Officialtravel extends Model
{
    use HasFactory;
    use Uuids;

    protected $guarded = [];

    // Relationships
    public function traveler()
    {
        return $this->belongsTo(Administration::class, 'traveler_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'official_travel_origin');
    }

    public function transportation()
    {
        return $this->belongsTo(Transportation::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function details()
    {
        return $this->hasMany(Officialtravel_detail::class, 'official_travel_id');
    }

    public function arrivalChecker()
    {
        return $this->belongsTo(User::class, 'arrival_check_by');
    }

    public function departureChecker()
    {
        return $this->belongsTo(User::class, 'departure_check_by');
    }

    public function recommender()
    {
        return $this->belongsTo(User::class, 'recommendation_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approval_by');
    }
}
