<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class License extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }
}
