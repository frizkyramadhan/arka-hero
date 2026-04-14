<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->hasOne(User::class);
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

    public function bonds()
    {
        return $this->hasMany(EmployeeBond::class);
    }

    public function activeBonds()
    {
        return $this->hasMany(EmployeeBond::class)->where('status', 'active');
    }

    public function activeAdministration()
    {
        return $this->hasOne(Administration::class)->where('is_active', 1);
    }

    public function leaveEntitlements()
    {
        return $this->hasMany(LeaveEntitlement::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
