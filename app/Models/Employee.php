<?php

namespace App\Models;

use App\Models\User;
use App\Models\Image;
use App\Traits\Uuids;
use App\Models\Course;
use App\Models\Family;
use App\Models\License;
use App\Models\Emrgcall;
use App\Models\Religion;
use App\Models\Education;
use App\Models\Insurance;
use App\Models\Termination;
use App\Models\Employeebank;
use App\Models\Operableunit;
use App\Models\Jobexperience;
use App\Models\Additionaldata;
use App\Models\Administration;
use App\Models\Taxidentification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;
    use Uuids;

    protected $guarded = [];

    public function religion()
    {
        return $this->belongsTo(Religion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employeebank()
    {
        return $this->hasMany(Employeebank::class);
    }

    public function insurance()
    {
        return $this->hasMany(Insurance::class);
    }

    public function family()
    {
        return $this->hasMany(Family::class);
    }

    public function education()
    {
        return $this->hasMany(Education::class);
    }

    public function course()
    {
        return $this->hasMany(Course::class);
    }

    public function jobexperience()
    {
        return $this->hasMany(Jobexperience::class);
    }

    public function operableunit()
    {
        return $this->hasMany(Operableunit::class);
    }

    public function license()
    {
        return $this->hasMany(License::class);
    }

    public function emrgcall()
    {
        return $this->hasMany(Emrgcall::class);
    }

    public function additionaldata()
    {
        return $this->hasMany(Additionaldata::class);
    }

    public function administration()
    {
        return $this->hasMany(Administration::class);
    }

    public function administrations()
    {
        return $this->hasMany(Administration::class);
    }

    public function image()
    {
        return $this->hasMany(Image::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    // public function termination()
    // {
    //     return $this->hasMany(Termination::class);
    // }

    public function taxidentification()
    {
        return $this->hasMany(Taxidentification::class);
    }
}
