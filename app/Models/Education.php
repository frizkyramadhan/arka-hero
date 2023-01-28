<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Education extends Model
{
    use HasFactory;

    public $table = 'educations';

    protected $guarded = [];

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
