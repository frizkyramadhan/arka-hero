<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Family extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable= [
             'employee_id',
             'family_name',
             'family_relationship',
             'family_birthplace',
             'family_birthdate',
             'family_remarks',
    ];

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }

    protected $dates = ['family_birthdate'];
}
