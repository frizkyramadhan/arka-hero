<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class License extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable= [
        'employee_id',
        'driver_license_no',
        'driver_license_type',
        'driver_license_exp',
    ];

   

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }


    protected $dates = ['driver_license_exp'];
}
