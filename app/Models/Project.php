<?php

namespace App\Models;

use App\Models\Administration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function administrations()
    {
        return $this->hasMany(Administration::class);
    }

    /**
     * Get the users that belong to the project.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_project');
    }
}
