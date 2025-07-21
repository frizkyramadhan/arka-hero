<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Officialtravel;
use App\Models\User;
use App\Models\Permission;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Education;
use App\Models\License;
use App\Models\Administration;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller
{
    /**
     * Display the dashboard page with both Official Travel and Employee data.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        // Get the authenticated user
        $user = Auth::user();

        // === OFFICIAL TRAVEL DATA ===

        // Count pending recommendations for this user
        $pendingRecommendations = 0;
        if ($user->can('official-travels.recommend')) {
            $pendingRecommendations = Officialtravel::where('official_travel_status', 'draft')
                ->where('recommendation_status', 'pending')
                ->where('recommendation_by', $user->id)
                ->count();
        }

        // Count pending approvals for this user
        $pendingApprovals = 0;
        if ($user->can('official-travels.approve')) {
            $pendingApprovals = Officialtravel::where('official_travel_status', 'draft')
                ->where('recommendation_status', 'approved')
                ->where('approval_status', 'pending')
                ->where('approval_by', $user->id)
                ->count();
        }

        // Count pending arrivals
        $pendingArrivals = 0;
        if ($user->can('official-travels.stamp')) {
            $pendingArrivals = Officialtravel::where('official_travel_status', 'open')
                ->whereNull('arrival_at_destination')
                ->count();
        }

        // Count pending departures
        $pendingDepartures = 0;
        if ($user->can('official-travels.stamp')) {
            $pendingDepartures = Officialtravel::where('official_travel_status', 'open')
                ->whereNotNull('arrival_at_destination')
                ->whereNull('departure_from_destination')
                ->count();
        }

        // Count open travels
        $openTravel = Officialtravel::where('official_travel_status', 'open')->count();

        // Get recent travels
        $openTravels = Officialtravel::with('traveler.employee')
            ->where('official_travel_status', 'open')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // === EMPLOYEE DATA ===

        // Get total employees count
        $totalEmployees = Employee::whereHas('administration', function ($query) {
            $query->where('is_active', '1');
        })->count();

        // Get employees by department
        $employeesByDepartment = Department::withCount(['administrations' => function ($query) {
            $query->where('is_active', '1');
        }])
            ->where('department_status', '1')
            ->orderBy('administrations_count', 'desc')
            // ->limit(5)
            ->get();

        // Get employees by project
        $employeesByProject = Project::where('project_status', '1')
            ->withCount(['administrations' => function ($query) {
                $query->where('is_active', '1');
            }])
            ->orderBy('administrations_count', 'desc')
            // ->limit(5)
            ->get();

        // Get newest employees (joined in last 30 days)
        $newEmployees = Employee::join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('projects', 'administrations.project_id', '=', 'projects.id')
            ->select('employees.*', 'administrations.doh', 'administrations.nik', 'positions.position_name', 'projects.project_code')
            ->where('administrations.is_active', '1')
            ->where('doh', '>=', now()->subDays(30))
            ->orderBy('administrations.doh', 'desc')
            ->limit(5)
            ->get();

        // Get employees with contract ending in next 30 days
        $expiringContracts = Administration::with(['employee', 'position'])
            ->where('administrations.is_active', '1')
            ->whereNotNull('administrations.foc')
            ->whereRaw('foc <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)')
            ->orderBy('administrations.foc', 'asc')
            ->get();


        // Get staff employees
        $staffEmployees = Administration::where('is_active', '1')->where('class', 'staff')->count();

        // Get non-staff employees
        $nonStaffEmployees = Administration::where('is_active', '1')->where('class', '!=', 'staff')->count();

        // Get permanent employees
        $permanentEmployees = Administration::where('is_active', '1')->whereNull('foc')->count();

        // Get contract employees
        $contractEmployees = Administration::where('is_active', '1')->whereNotNull('foc')->count();

        // Get employees with license
        $employeesWithLicense = License::distinct('employee_id')->count('employee_id');

        // Get employees with birthday in this month
        // $birthdayEmployees = Employee::with(['administration' => function ($query) {
        //     $query->where('is_active', '1');
        // }])->whereMonth('emp_dob', date('m'))->count();

        $birthdayEmployees = DB::table('employees')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id',)
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->join('projects', 'administrations.project_id', '=', 'projects.id')
            ->select(
                'employees.id',
                'administrations.nik',
                'employees.fullname',
                'employees.emp_dob',
                'projects.project_code',
                'positions.position_name',
                'administrations.class'
            )
            ->where('administrations.is_active', '1')
            ->whereMonth('employees.emp_dob', date('m'))
            ->count();

        return view('dashboard', [
            'title' => 'Dashboard',
            'subtitle' => 'Dashboard',
            // Official Travel data
            'pendingRecommendations' => $pendingRecommendations,
            'pendingApprovals' => $pendingApprovals,
            'pendingArrivals' => $pendingArrivals,
            'pendingDepartures' => $pendingDepartures,
            'openTravel' => $openTravel,
            'openTravels' => $openTravels,
            // Employee data
            'totalEmployees' => $totalEmployees,
            'employeesByDepartment' => $employeesByDepartment,
            'employeesByProject' => $employeesByProject,
            'newEmployees' => $newEmployees,
            'expiringContracts' => $expiringContracts,
            'employeesWithLicense' => $employeesWithLicense,
            'birthdayEmployees' => $birthdayEmployees,
            'staffEmployees' => $staffEmployees,
            'nonStaffEmployees' => $nonStaffEmployees,
            'permanentEmployees' => $permanentEmployees,
            'contractEmployees' => $contractEmployees
        ]);
    }

    /**
     * Get pending recommendations data for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingRecommendations()
    {
        $user = Auth::user();

        // if (!$user->can('official-travels.recommend')) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $query = Officialtravel::with('traveler.employee')
            ->where('official_travel_status', 'draft')
            ->where('recommendation_status', 'pending')
            ->where('recommendation_by', $user->id);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('official_travel_date', function ($row) {
                return date('d M Y', strtotime($row->official_travel_date));
            })
            ->addColumn('traveler', function ($row) {
                return $row->traveler->employee->fullname ?? 'N/A';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('officialtravels.showRecommendForm', $row->id) . '" class="btn btn-sm btn-warning">
                            <i class="fas fa-thumbs-up"></i> Recommend
                        </a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get pending approvals data for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingApprovals()
    {
        $user = Auth::user();

        // if (!$user->can('official-travels.approve')) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $query = Officialtravel::with('traveler.employee')
            ->where('official_travel_status', 'draft')
            ->where('recommendation_status', 'approved')
            ->where('approval_status', 'pending')
            ->where('approval_by', $user->id);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('official_travel_date', function ($row) {
                return date('d M Y', strtotime($row->official_travel_date));
            })
            ->addColumn('traveler', function ($row) {
                return $row->traveler->employee->fullname ?? 'N/A';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('officialtravels.showApprovalForm', $row->id) . '" class="btn btn-sm btn-success">
                            <i class="fas fa-check-circle"></i> Approve
                        </a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get pending arrivals data for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingArrivals()
    {
        $user = Auth::user();

        // if (!$user->can('official-travels.stamp-arrival')) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $query = Officialtravel::with('traveler.employee')
            ->where('official_travel_status', 'open')
            ->whereNull('arrival_at_destination');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('official_travel_date', function ($row) {
                return date('d M Y', strtotime($row->official_travel_date));
            })
            ->addColumn('traveler', function ($row) {
                return $row->traveler->employee->fullname ?? 'N/A';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('officialtravels.showArrivalForm', $row->id) . '" class="btn btn-sm btn-info">
                            <i class="fas fa-plane-arrival"></i> Stamp Arrival
                        </a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get pending departures data for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingDepartures()
    {
        $user = Auth::user();

        // if (!$user->can('official-travels.stamp-departure')) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $query = Officialtravel::with('traveler.employee')
            ->where('official_travel_status', 'open')
            ->whereNotNull('arrival_at_destination')
            ->whereNull('departure_from_destination');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('official_travel_date', function ($row) {
                return date('d M Y', strtotime($row->official_travel_date));
            })
            ->addColumn('traveler', function ($row) {
                return $row->traveler->employee->fullname ?? 'N/A';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('officialtravels.showDepartureForm', $row->id) . '" class="btn btn-sm btn-purple">
                            <i class="fas fa-plane-departure"></i> Stamp Departure
                        </a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get employees by department for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function employeesByDepartment()
    {
        $query = Department::withCount(['administrations' => function ($query) {
            $query->where('is_active', '1');
        }])
            ->where('department_status', '1')
            ->orderBy('administrations_count', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('department', function ($row) {
                return $row->department_name;
            })
            ->addColumn('total_employees', function ($row) {
                return $row->administrations_count;
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('departments.summary', $row->id) . '" class="btn btn-sm btn-info">
                            <i class="fas fa-info-circle"></i> Details
                        </a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get employees by project for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function employeesByProject()
    {
        $query = Project::where('project_status', '1')
            ->withCount(['administrations' => function ($query) {
                $query->where('is_active', '1');
            }])
            ->orderBy('administrations_count', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('project', function ($row) {
                return $row->project_code . ' - ' . $row->project_name;
            })
            ->addColumn('total_employees', function ($row) {
                return $row->administrations_count;
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('projects.summary', $row->id) . '" class="btn btn-sm btn-info">
                            <i class="fas fa-info-circle"></i> Details
                        </a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get recent employees for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function recentEmployees()
    {
        $query = Employee::join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->select('employees.*', 'administrations.doh', 'administrations.nik', 'administrations.position_id')
            ->with(['religion', 'administration.position'])
            ->where('administrations.is_active', '1')
            ->orderBy('administrations.doh', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nik', function ($row) {
                return $row->nik;
            })
            ->addColumn('fullname', function ($row) {
                return $row->fullname;
            })
            ->addColumn('hire_date', function ($row) {
                return date('d M Y', strtotime($row->doh));
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('employees.show', $row->id) . '" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> Details
                        </a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
