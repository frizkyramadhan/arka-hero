<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level_order' => 'integer'
    ];

    public function administrations()
    {
        return $this->hasMany(Administration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
