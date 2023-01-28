<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Administration extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function projects()
    {
        return $this->belongsTo(Project::class);
    }

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }

    public function positions()
    {
        return $this->belongsTo(Position::class);
    }
}
