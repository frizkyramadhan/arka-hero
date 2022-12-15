<?php

namespace App\Models;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxidentification extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $fillable= [
        'employee_id',
        'tax_no',
        'tax_valid_date',
    ];


    public function employees()
    {
        return $this->belongsTo(Employee::class);
    }

    protected $dates = ['tax_valid_date'];



}
