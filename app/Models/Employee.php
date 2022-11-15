<?php

namespace App\Models;

use App\Models\School;
use App\Models\Religion;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;
    use Sluggable;
    use SoftDeletes;
    
    
    protected $fillable = [
            'fullname',
            'slug', 
            'emp_pob',
            'emp_dob',
            'blood_type',
            'religion_id',
            'nationality',
            'gender_id',
            'marital',
            'address',
            'village',
            'ward',
            'district',
            'city',
            'phone',
            'identity_card',
            'image' 
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'fullname'
            ]
        ];
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class);
    }


    public function genders()
    {
        return $this->belongsTo(Gender::class);
    }

    public function schools()
    {
        return $this->hasMany(School::class);
    }
}
