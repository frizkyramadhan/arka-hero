<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    use HasFactory;

    use Sluggable;
   

    // protected $guarded = [];

    protected $fillable = [
        'employee_id' ,
            'education_level' ,
            'education_name' ,
            'education_year' ,
            'education_remarks'

    ];


    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'education_name'
            ]
        ];
    }

    /**
     * Get the user that owns the School
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employees(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
