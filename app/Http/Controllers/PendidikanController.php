<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendidikanController extends Controller
{
    public function pendidikans()
    {   
        $pendidikans = DB::table('pendidikans')
            ->join('employees', 'pendidikans.employee_id', '=', 'pendidikans.id')
            ->select('pendidikans.*', 'fullname')
            ->orderBy('fullname', 'asc')
            ->paginate(10);
        return view('pendidikan.index', ['pendidikans' => $pendidikans]);
       
    }
}
