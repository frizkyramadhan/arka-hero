<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function administrations()
    {
        return $this->hasMany(Administration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
