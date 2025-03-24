<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Officialtravel_detail extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relationships
    public function officialtravel()
    {
        return $this->belongsTo(Officialtravel::class, 'official_travel_id');
    }

    public function follower()
    {
        return $this->belongsTo(Administration::class, 'follower_id');
    }
}
