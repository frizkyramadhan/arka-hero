<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\Administration;
use Illuminate\Support\Facades\DB;

class AdministrationController extends Controller
{

    public function index()
    {
        $title = ' Employee Administration';
        $subtitle = ' Employee Administration';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('administration.index', compact('title', 'subtitle', 'employees'));
    }

    public function getAdministration(Request $request)
    {

        $administrations = Administration::join('projects', 'administrations.project_id', '=', 'projects.id')
                                            ->join('employees', 'administrations.employee_id', '=', 'employees.id')
                                            ->join('positions', 'administrations.position_id', '=', 'positions.id')
                                            ->select('administrations.*', 'fullname', 'position_name','project_name')
                                            ->orderBy('fullname', 'asc');
                                       
        // $insurances = Administration::leftJoin('employees', 'insurances.employee_id', '=', 'employees.id')
        //     ->select('insurances.*', 'employees.fullname')
        //     ->orderBy('insurances.health_insurance_no', 'asc');

        // $administrations = DB::table('administrations')
        //     ->join('projects', 'administrations.project_id', '=', 'projects.id')
        //     ->join('employees', 'administrations.employee_id', '=', 'employees.id')
        //     ->join('positions', 'administrations.position_id', '=', 'positions.id')
        //     ->select('administrations.*', 'fullname', 'position_name','project_name')
        //     ->orderBy('fullname', 'asc');

            // $administrations = Administration::leftJoin('employees', 'administrations.id', '=', 'employees.employee_id')
            // ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            // ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            // ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            // ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            // ->orderBy('administrations.nik', 'desc');


            // $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            // ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            // ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            // ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            // ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            // ->orderBy('administrations.nik', 'desc');

        return datatables()->of($administrations)
            ->addIndexColumn()
            ->addColumn('fullname', function ($administrations) {
                return $administrations->fullname;
            })
            ->addColumn('project_name', function ($administrations) {
                return $administrations->project_name;
            })
            ->addColumn('position_name', function ($administrations) {
                return $administrations->position_name;
            })
            ->addColumn('nik', function ($administrations) {
                return $administrations->nik;
            })
            ->addColumn('class', function ($administrations) {
                return $administrations->class;
            })
            ->addColumn('doh', function ($administrations) {
                return $administrations->doh;
            })
            ->addColumn('poh', function ($administrations) {
                return $administrations->poh;
            })
            ->addColumn('basic_salary', function ($administrations) {
                return $administrations->basic_salary;
            })
            ->addColumn('site_allowance', function ($administrations) {
                return $administrations->site_allowance;
            })
            ->addColumn('other_allowance', function ($administrations) {
                return $administrations->other_allowance;
            })
            
            // ->addColumn('position_status', function ($position) {
            //     if ($position->position_status == '1') {
            //         return '<span class="badge badge-success">Active</span>';
            //     } elseif ($position->position_status == '0') {
            //         return '<span class="badge badge-danger">Inactive</span>';
            //     }
            // })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('project_name', 'LIKE', "%$search%")
                            ->orWhere('position_name', 'LIKE', "%$search%")
                            ->orWhere('nik', 'LIKE', "%$search%")
                            ->orWhere('doh', 'LIKE', "%$search%")
                            ->orWhere('class', 'LIKE', "%$search%")
                            ->orWhere('poh', 'LIKE', "%$search%");
                           
                    });
                }
            })
            ->addColumn('action', function ($administrations) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('administration.action', compact('employees', 'administrations'));
            })
            ->addColumn('doh', function($administrations){
                $date = date("d F Y", strtotime($administrations->doh));
                return $date;

            })
            ->rawColumns(['fullname', 'action'])
            ->toJson();
    }
    // public function administrations(Request $request)
    // {
    //     $keyword = $request->keyword;
    //     $administrations = Administration::with(['projects','employees','positions'])
    //                                         ->where('nik', 'LIKE', '%'.$keyword.'%')
    //                                         ->orWhere('class', 'LIKE', '%'.$keyword.'%')
    //                                         ->orWhere('doh', 'LIKE', '%'.$keyword.'%')
    //                                         ->orWhere('poh', 'LIKE', '%'.$keyword.'%')
    //                                         ->orWhereHas('employees', function($query) use($keyword){
    //                                             $query->where('fullname', 'LIKE', '%'.$keyword.'%');
    //                                         })                        
    //                                         ->paginate(5);
      

    //     // $administrations = DB::table('administrations')
    //     //     ->join('projects', 'administrations.project_id', '=', 'projects.id')
    //     //     ->join('employees', 'administrations.employee_id', '=', 'employees.id')
    //     //     ->join('positions', 'administrations.position_id', '=', 'positions.id')
    //     //     ->select('administrations.*', 'fullname', 'position_name','project_name')
    //     //     ->orderBy('fullname', 'asc')
    //     //     ->simplePaginate(10);
    //     return view('administration.index', ['administrations' => $administrations]);
    // }

    // public function AddAdministration()
    // {
    //     $employee = Employee::orderBy('id', 'asc')->get();
    //     $projects = Project::orderBy('id', 'asc')->get();
    //     $positions = Position::orderBy('id', 'asc')->get();
    //     return view('administration.create', compact('employee','projects','positions'));
    // }

    // public function store(Request $request)
    // {

       
    //     $validated = $request->validate([
    //         'employee_id' => 'required',
    //         'project_id' => 'required',
    //         'position_id' => 'required',
    //         'nik' => 'required',
    //         'class' => 'required',
    //         'doh' => 'required',
    //         'poh' => 'required',
    //         'basic_salary' => 'required',
    //         'site_allowance' => 'required',
    //         'other_allowance' => 'required',

    //     ]);
    //     $administration = Administration::create($request->all());
    //     return redirect('admin/administrations')->with('status', 'Administration Employee Add Successfully');
    // }

    // public function editAdministration($slug)
    // {
    //     $administrations = Administration::where('slug', $slug)->first();
    //     $employee = Employee::orderBy('id', 'asc')->get();
    //     $projects = Project::orderBy('id', 'asc')->get();
    //     $positions = Position::orderBy('id', 'asc')->get();
    //     return view('administration.edit', compact('administrations', 'projects', 'positions','employee'));
    // }

    // public function updateAdministration(Request $request, $slug)
    // {
    //     $administrations = Administration::where('slug', $slug)->first();
    //     $rules = [
    //         'employee_id' => 'required',
    //         'project_id' => 'required',
    //         'position_id' => 'required',
    //         'nik' => 'required',
    //         'class' => 'required',
    //         'doh' => 'required',
    //         'poh' => 'required',
    //         'basic_salary' => 'required',
    //         'site_allowance' => 'required',
    //         'other_allowance' => 'required',

    //     ];

    //     $validatedData = $request->validate($rules);
    //     Administration::where('slug', $slug)->update($validatedData);

    //     return redirect('admin/administrations')->with('status', 'Administration Employee Update Successfully');
    // }

    // public function deleteAdministration($slug)
    // {

    //     $administrations = Administration::where('slug', $slug)->first();
    //     $administrations->delete();
    //     return redirect('admin/administrations')->with('status', 'Administration Employee Delete Successfully');
    // }
}
