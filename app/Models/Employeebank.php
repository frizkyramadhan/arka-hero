<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employeebank extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }

    public function banks()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
