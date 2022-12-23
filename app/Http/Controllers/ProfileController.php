<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Religion;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard'
        ];
        $departmentCount = Department::count();
        $projectCount = Project::count();
        $religionCount = Religion::count();
        $bankCount = Bank::count();
        $positionCount = Position::count();
        $employeeCount = Employee::count();
        return view('dashboard', $data, ['departmentCount' => $departmentCount, 'projectCount' => $projectCount, 'religionCount'=> $religionCount,
        'bankCount'=> $bankCount, 'positionCount'=> $positionCount, 'employeeCount'=> $employeeCount]);
    }


    public function logout()
    {
        auth()->logout();
        return redirect()->route('getLogin')->with('success', 'You have been successfully logged out');
    }
}
