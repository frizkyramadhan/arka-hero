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
            ->addColumn('foc', function ($administrations) {
                return $administrations->foc;
            })
            ->addColumn('agreement', function ($administrations) {
                return $administrations->agreement;
            })
            ->addColumn('company_program', function ($administrations) {
                return $administrations->company_program;
            })
            ->addColumn('no_fptk', function ($administrations) {
                return $administrations->no_fptk;
            })
            ->addColumn('no_sk_active', function ($administrations) {
                return $administrations->no_sk_active;
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

            
            ->addColumn('is_active', function ($administrations) {
                if ($administrations->is_active == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } elseif ($administrations->is_active == '0') {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })

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
                            ->orWhere('doh', 'LIKE', "%$search%")
                            ->orWhere('poh', 'LIKE', "%$search%");
                            ->orWhere('poh', 'LIKE', "%$search%")
                            ->orWhere('foc', 'LIKE', "%$search%")
                            ->orWhere('agreement', 'LIKE', "%$search%")
                            ->orWhere('company_program', 'LIKE', "%$search%")
                            ->orWhere('no_fptk', 'LIKE', "%$search%")
                            ->orWhere('no_sk_active', 'LIKE', "%$search%")
                            ->orWhere('is_active', 'LIKE', "%$search%");
                           
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
            ->addColumn('foc', function($administrations){
                $date = date("d F Y", strtotime($administrations->foc));
                return $date;

            })
            ->rawColumns(['is_active', 'action'])
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

    public function store($employee_id, Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'project_id' => 'required',
            'position_id' => 'required',
            'nik' => 'required|unique:administrations',
            'class' => 'required',
            'doh' => 'required',
            'poh' => 'required',
            'basic_salary' => 'required',
            'site_allowance' => 'required',
            'other_allowance' => 'required',

        ]);
        $administration = new Administration;
        $administration->employee_id = $request->employee_id;
        $administration->project_id = $request->project_id;
        $administration->position_id = $request->position_id;
        $administration->nik = $request->nik;
        $administration->class = $request->class;
        $administration->doh = $request->doh;
        $administration->poh = $request->poh;
        $administration->basic_salary = $request->basic_salary;
        $administration->site_allowance = $request->site_allowance;
        $administration->other_allowance = $request->other_allowance;
        $administration->is_active = $request->is_active;
        $administration->save();

        return redirect('employees/' . $employee_id)->with('toast_success', 'Administration Added Successfully');
    }

    // public function editAdministration($slug)
    // {
    //     $administrations = Administration::where('slug', $slug)->first();
    //     $employee = Employee::orderBy('id', 'asc')->get();
    //     $projects = Project::orderBy('id', 'asc')->get();
    //     $positions = Position::orderBy('id', 'asc')->get();
    //     return view('administration.edit', compact('administrations', 'projects', 'positions','employee'));
    // }

    public function update(Request $request, $id)
    {
        // $administrations = Administration::where('id', $id)->first();
        $rules = [
            'employee_id' => 'required',
            'project_id' => 'required',
            'position_id' => 'required',
            'nik' => 'required',
            'class' => 'required',
            'doh' => 'required',
            'poh' => 'required',
            'basic_salary' => 'required',
            'site_allowance' => 'required',
            'other_allowance' => 'required',

        ];

        $validatedData = $request->validate($rules);
        Administration::where('id', $id)->update($validatedData);

        return redirect('employees/' . $request->employee_id)->with('toast_success', 'Administration Updated Successfully');
    }

    public function delete($employee_id, $id)
    {
        $administrations = Administration::where('id', $id)->first();
        $administrations->delete();
        return redirect('employees/' . $employee_id)->with('toast_success', 'Administration Deleted Successfully');
    }
}
