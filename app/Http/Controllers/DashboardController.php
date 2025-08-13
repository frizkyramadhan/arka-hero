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
use App\Models\RecruitmentSession;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentCandidate;
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
        // Redirect root dashboard to Employee dashboard for clarity
        return redirect()->route('dashboard.employees');
    }

    /**
     * Dedicated Employee Dashboard view
     */
    public function employee()
    {
        // Employee metrics
        $totalEmployees = Employee::whereHas('administration', function ($query) {
            $query->where('is_active', '1');
        })->count();

        $employeesByDepartment = Department::withCount(['administrations' => function ($query) {
            $query->where('is_active', '1');
        }])
            ->where('department_status', '1')
            ->orderBy('administrations_count', 'desc')
            ->get();

        $employeesByProject = Project::where('project_status', '1')
            ->withCount(['administrations' => function ($query) {
                $query->where('is_active', '1');
            }])
            ->orderBy('administrations_count', 'desc')
            ->get();

        $newEmployees = Employee::join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('projects', 'administrations.project_id', '=', 'projects.id')
            ->select('employees.*', 'administrations.doh', 'administrations.nik', 'positions.position_name', 'projects.project_code')
            ->where('administrations.is_active', '1')
            ->where('doh', '>=', now()->subDays(30))
            ->orderBy('administrations.doh', 'desc')
            ->limit(5)
            ->get();

        $expiringContracts = Administration::with(['employee', 'position'])
            ->where('administrations.is_active', '1')
            ->whereNotNull('administrations.foc')
            ->whereRaw('foc <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)')
            ->orderBy('administrations.foc', 'asc')
            ->get();

        $staffEmployees = Administration::where('is_active', '1')->where('class', 'staff')->count();
        $nonStaffEmployees = Administration::where('is_active', '1')->where('class', '!=', 'staff')->count();
        $permanentEmployees = Administration::where('is_active', '1')->whereNull('foc')->count();
        $contractEmployees = Administration::where('is_active', '1')->whereNotNull('foc')->count();
        $employeesWithLicense = License::distinct('employee_id')->count('employee_id');

        $birthdayEmployees = DB::table('employees')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.is_active', '1')
            ->whereMonth('employees.emp_dob', date('m'))
            ->count();

        return view('dashboard.employee', [
            'title' => 'Employee Dashboard',
            'subtitle' => 'Employee Overview',
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
            'contractEmployees' => $contractEmployees,
        ]);
    }

    /**
     * Dedicated Official Travel Dashboard view
     */
    public function officialTravel()
    {
        $user = Auth::user();

        $pendingApprovals = 0;
        if ($user->can('official-travels.approve')) {
            $pendingApprovals = Officialtravel::where('recommendation_status', 'approved')
                ->where('approval_status', 'pending')
                ->where('approval_by', $user->id)
                ->count();
        }

        $pendingArrivals = 0;
        if ($user->can('official-travels.stamp')) {
            $pendingArrivals = Officialtravel::where('status', 'approved')
                ->whereNull('arrival_at_destination')
                ->count();
        }

        $pendingDepartures = 0;
        if ($user->can('official-travels.stamp')) {
            $pendingDepartures = Officialtravel::where('status', 'approved')
                ->whereNotNull('arrival_at_destination')
                ->whereNull('departure_from_destination')
                ->count();
        }

        $totalTravels = Officialtravel::count();
        $activeTravels = Officialtravel::where('status', 'approved')->count();
        $draftTravels = Officialtravel::where('status', 'draft')->count();
        $submittedTravels = Officialtravel::where('status', 'submitted')->count();
        $approvedTravels = Officialtravel::where('status', 'approved')->count();
        $rejectedTravels = Officialtravel::where('status', 'rejected')->count();

        $thisMonthTravels = Officialtravel::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthTravels = Officialtravel::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $monthlyGrowth = $lastMonthTravels > 0 ? round((($thisMonthTravels - $lastMonthTravels) / $lastMonthTravels) * 100, 1) : 0;

        $topDestinations = Officialtravel::select('destination', DB::raw('count(*) as count'))
            ->groupBy('destination')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $departmentStats = Officialtravel::join('employees', 'officialtravels.traveler_id', '=', 'employees.id')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->select('departments.department_name', DB::raw('count(*) as count'))
            ->groupBy('departments.id', 'departments.department_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $openTravel = Officialtravel::where('status', 'approved')->count();
        $openTravels = Officialtravel::with('traveler.employee')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.official-travel', [
            'title' => 'Official Travel Dashboard',
            'subtitle' => 'Official Travel Overview',
            'totalTravels' => $totalTravels,
            'activeTravels' => $activeTravels,
            'draftTravels' => $draftTravels,
            'submittedTravels' => $submittedTravels,
            'approvedTravels' => $approvedTravels,
            'rejectedTravels' => $rejectedTravels,
            'thisMonthTravels' => $thisMonthTravels,
            'monthlyGrowth' => $monthlyGrowth,
            'topDestinations' => $topDestinations,
            'departmentStats' => $departmentStats,
            'pendingApprovals' => $pendingApprovals,
            'pendingArrivals' => $pendingArrivals,
            'pendingDepartures' => $pendingDepartures,
            'openTravel' => $openTravel,
            'openTravels' => $openTravels,
        ]);
    }

    /**
     * Dedicated Recruitment Dashboard view
     */
    public function recruitment()
    {
        $title = 'Recruitment Dashboard';
        $subtitle = 'Recruitment Analytics and Overview';

        $stats = [
            // Sessions
            'total_sessions' => RecruitmentSession::count(),
            'active_sessions' => RecruitmentSession::where('status', 'in_process')->count(),
            'completed_sessions' => RecruitmentSession::where('status', 'hired')->count(),
            'rejected_sessions' => RecruitmentSession::where('status', 'rejected')->count(),
            'sessions_this_month' => RecruitmentSession::whereMonth('applied_date', now()->month)
                ->whereYear('applied_date', now()->year)
                ->count(),
            // FPTK / Requests
            'active_fptk' => RecruitmentRequest::active()->count(), // submitted or approved
            // Candidates
            'candidate_pool' => RecruitmentCandidate::whereIn('global_status', ['available', 'in_process'])->count(),
        ];

        $sessionsByStage = RecruitmentSession::where('status', 'in_process')
            ->selectRaw('current_stage, COUNT(*) as count')
            ->groupBy('current_stage')
            ->get();

        $recentSessions = RecruitmentSession::with(['fptk.position', 'candidate'])
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.recruitment', compact('title', 'subtitle', 'stats', 'sessionsByStage', 'recentSessions'));
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
            ->where('status', 'draft')
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
            ->where('status', 'approved')
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
            ->where('status', 'approved')
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
