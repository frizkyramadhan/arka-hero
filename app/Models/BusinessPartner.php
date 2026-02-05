<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPartner extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function issuances()
    {
        return $this->hasMany(FlightRequestIssuance::class, 'business_partner_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
