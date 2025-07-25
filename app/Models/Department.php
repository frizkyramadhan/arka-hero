<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function administrations()
    {
        return $this->hasManyThrough(Administration::class, Position::class);
    }

    /**
     * Get the users that belong to the department.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_department');
    }
}
