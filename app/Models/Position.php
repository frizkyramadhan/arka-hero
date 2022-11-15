<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Position extends Model
{
    use HasFactory;
    use Sluggable;
    

    // protected $guarded = [];

    protected $fillable = [
        'position_name',
            'department_id'

    ];


    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'position_name'
            ]
        ];
    }

    public function departments(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
