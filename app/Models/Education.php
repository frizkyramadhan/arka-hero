<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Education extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $table = 'educations';

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }
}
