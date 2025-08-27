<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxidentification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tax_valid_date' => 'date',
    ];

    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }
}
