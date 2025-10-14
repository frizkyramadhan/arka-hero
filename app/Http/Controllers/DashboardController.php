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
use App\Models\LetterNumber;
use App\Models\LetterCategory;
use App\Models\LetterSubject;
use App\Models\EmployeeBond;
use App\Models\BondViolation;
use App\Models\LeaveRequest;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\LeaveRequestCancellation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        // Recent active employee bonds
        $recentActiveEmployeeBonds = EmployeeBond::with(['employee.administrations'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pending bond violations
        $pendingBondViolations = BondViolation::with(['employeeBond.employee.administrations'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

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
            // Employee Bonds Data
            'recentActiveEmployeeBonds' => $recentActiveEmployeeBonds,
            'pendingBondViolations' => $pendingBondViolations,
        ]);
    }

    /**
     * Dedicated Official Travel Dashboard view
     */
    public function officialTravel()
    {
        $user = Auth::user();

        // Remove legacy approval system - no more pending approvals
        $pendingApprovals = 0;

        // New logic for pending arrivals based on stops
        $pendingArrivals = 0;
        if ($user->can('official-travels.stamp')) {
            $pendingArrivals = Officialtravel::where('status', 'approved')
                ->whereDoesntHave('stops')
                ->count();
        }

        // New logic for pending departures based on stops
        $pendingDepartures = 0;
        if ($user->can('official-travels.stamp')) {
            $pendingDepartures = Officialtravel::where('status', 'approved')
                ->whereHas('stops', function ($query) {
                    $query->whereNotNull('arrival_at_destination')
                        ->whereNull('departure_from_destination');
                })
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
            ->whereNot('status', 'closed')
            ->orderBy('created_at', 'desc')
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
     * Dedicated Letter Administration Dashboard view
     */
    public function letterAdministration()
    {
        $title = 'Letter Administration Dashboard';
        $subtitle = 'Letter Numbering System Overview';

        // Basic Statistics
        $totalLetters = LetterNumber::count();
        $reservedLetters = LetterNumber::where('status', 'reserved')->count();
        $usedLetters = LetterNumber::where('status', 'used')->count();
        $cancelledLetters = LetterNumber::where('status', 'cancelled')->count();

        // Monthly Statistics
        $thisMonthLetters = LetterNumber::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthLetters = LetterNumber::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $monthlyGrowth = $lastMonthLetters > 0 ? round((($thisMonthLetters - $lastMonthLetters) / $lastMonthLetters) * 100, 1) : 0;

        // Category Statistics
        $categoriesStats = LetterCategory::withCount(['letterNumbers'])
            ->where('is_active', 1)
            ->orderBy('letter_numbers_count', 'desc')
            ->get();

        $usageByCategory = LetterNumber::select('letter_category_id', 'status', DB::raw('COUNT(*) as count'))
            ->join('letter_categories', 'letter_numbers.letter_category_id', '=', 'letter_categories.id')
            ->groupBy('letter_category_id', 'status')
            ->with('category')
            ->get()
            ->groupBy('letter_category_id');

        // Usage Efficiency - Letters that are reserved vs actually used
        $usageEfficiency = $totalLetters > 0 ? round(($usedLetters / $totalLetters) * 100, 1) : 0;

        // Integration Statistics
        $integratedLetters = LetterNumber::whereNotNull('related_document_type')->count();
        $officialTravelLetters = LetterNumber::where('related_document_type', 'officialtravel')->count();
        $fptkLetters = LetterNumber::where('related_document_type', 'recruitment_request')->count();
        $pkwtLetters = LetterNumber::where('related_document_type', 'recruitment_hiring')->count();
        $offeringLetters = LetterNumber::where('related_document_type', 'recruitment_offering')->count();

        // Integration breakdown
        $integrationBreakdown = [
            'officialtravel' => $officialTravelLetters,
            'fptk' => $fptkLetters,
            'pkwt' => $pkwtLetters,
            'offering' => $offeringLetters
        ];

        // Recent Activity
        $recentLetters = LetterNumber::with(['category', 'subject', 'user', 'administration.employee'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top Users by Letter Generation
        $topUsers = LetterNumber::select('user_id', DB::raw('COUNT(*) as count'))
            ->join('users', 'letter_numbers.user_id', '=', 'users.id')
            ->groupBy('user_id')
            ->with('user')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Year-over-Year Statistics
        $currentYear = now()->year;
        $yearlyStats = LetterNumber::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "used" THEN 1 ELSE 0 END) as used'),
            DB::raw('SUM(CASE WHEN status = "reserved" THEN 1 ELSE 0 END) as reserved')
        )
            ->whereYear('created_at', '>=', $currentYear - 2)
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy('year', 'desc')
            ->get();

        // Daily generation trend for current month
        $dailyTrend = LetterNumber::select(
            DB::raw('DAY(created_at) as day'),
            DB::raw('COUNT(*) as count')
        )
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw('DAY(created_at)'))
            ->orderBy('day')
            ->get();

        // Get estimated next numbers for all categories
        $estimatedNextNumbers = LetterNumber::getEstimatedNextNumbersForAllCategories();

        // Get last numbers for each category for context
        $categories = LetterCategory::where('is_active', 1)->orderBy('category_code', 'asc')->get();
        $lastNumbersByCategory = [];
        $letterCountsByCategory = [];
        foreach ($categories as $category) {
            $lastNumbersByCategory[$category->id] = LetterNumber::getLastNumbersForCategory($category->id, 3);
            $letterCountsByCategory[$category->id] = LetterNumber::getLetterCountForCategory($category->id);
        }

        return view('dashboard.letter-administration', compact(
            'title',
            'subtitle',
            'totalLetters',
            'reservedLetters',
            'usedLetters',
            'cancelledLetters',
            'thisMonthLetters',
            'monthlyGrowth',
            'categoriesStats',
            'usageByCategory',
            'usageEfficiency',
            'integratedLetters',
            'officialTravelLetters',
            'fptkLetters',
            'pkwtLetters',
            'offeringLetters',
            'integrationBreakdown',
            'recentLetters',
            'topUsers',
            'yearlyStats',
            'dailyTrend',
            'categories',
            'estimatedNextNumbers',
            'lastNumbersByCategory',
            'letterCountsByCategory'
        ));
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
            ->whereDoesntHave('stops');

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
            ->whereHas('stops', function ($query) {
                $query->whereNotNull('arrival_at_destination')
                    ->whereNull('departure_from_destination');
            });

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

    /**
     * Get letter numbers by category for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function lettersByCategory()
    {
        $query = LetterCategory::withCount(['letterNumbers'])
            ->where('is_active', 1)
            ->orderBy('letter_numbers_count', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('category_name', function ($row) {
                return '<strong>' . $row->category_code . '</strong><br><small>' . $row->category_name . '</small>';
            })
            ->addColumn('total_letters', function ($row) {
                return '<span class="badge badge-primary">' . $row->letter_numbers_count . '</span>';
            })
            ->addColumn('status_breakdown', function ($row) {
                $reserved = LetterNumber::where('letter_category_id', $row->id)->where('status', 'reserved')->count();
                $used = LetterNumber::where('letter_category_id', $row->id)->where('status', 'used')->count();
                $cancelled = LetterNumber::where('letter_category_id', $row->id)->where('status', 'cancelled')->count();

                return '<small>' .
                    '<span class="badge badge-warning">R: ' . $reserved . '</span> ' .
                    '<span class="badge badge-success">U: ' . $used . '</span> ' .
                    '<span class="badge badge-danger">C: ' . $cancelled . '</span>' .
                    '</small>';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('letter-numbers.index') . '?letter_category_id=' . $row->id . '" class="btn btn-sm btn-info">
                            <i class="fas fa-list"></i> View Letters
                        </a>';
            })
            ->rawColumns(['category_name', 'total_letters', 'status_breakdown', 'action'])
            ->make(true);
    }

    /**
     * Get recent letter numbers for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function recentLetters()
    {
        $query = LetterNumber::with(['category', 'subject', 'user', 'administration.employee'])
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('letter_number', function ($row) {
                return '<strong>' . $row->letter_number . '</strong>';
            })
            ->addColumn('category', function ($row) {
                return '<span class="badge badge-info">' . $row->category->category_code . '</span>';
            })
            ->addColumn('subject_display', function ($row) {
                return $row->subject ? $row->subject->subject_name : ($row->custom_subject ?? '-');
            })
            ->addColumn('created_by', function ($row) {
                return $row->user->name ?? 'System';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'reserved' => '<span class="badge badge-warning">Reserved</span>',
                    'used' => '<span class="badge badge-success">Used</span>',
                    'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
                ];
                return $badges[$row->status] ?? '<span class="badge badge-secondary">Unknown</span>';
            })
            ->addColumn('created_date', function ($row) {
                return $row->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('letter-numbers.show', $row->id) . '" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>';
            })
            ->rawColumns(['letter_number', 'category', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Dedicated Leave Management Dashboard view
     */
    public function leaveManagement()
    {
        $title = 'Leave Management Dashboard';
        $subtitle = 'Leave Analytics and Management Overview';

        // Basic Statistics
        $totalLeaveRequests = LeaveRequest::count();
        $pendingRequests = LeaveRequest::where('status', 'pending')->count();
        $approvedRequests = LeaveRequest::where('status', 'approved')->count();
        $closedRequests = LeaveRequest::where('status', 'closed')->count();
        $cancelledRequests = LeaveRequest::where('status', 'cancelled')->count();

        // Monthly Statistics
        $thisMonthRequests = LeaveRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthRequests = LeaveRequest::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $monthlyGrowth = $lastMonthRequests > 0 ? round((($thisMonthRequests - $lastMonthRequests) / $lastMonthRequests) * 100, 1) : 0;

        // Open Leave Requests (Approved but not closed)
        $openLeaveRequests = LeaveRequest::with(['employee', 'leaveType', 'administration'])
            ->where('status', 'approved')
            ->where('end_date', '<=', now()->addDay()) // Can be closed
            ->orderBy('end_date', 'asc')
            ->limit(10)
            ->get();

        // Cancellation Requests
        $pendingCancellations = LeaveRequestCancellation::with([
            'leaveRequest.employee',
            'leaveRequest.leaveType',
            'requestedBy'
        ])
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->limit(10)
            ->get();

        // Paid Leave Without Supporting Documents
        $paidLeaveWithoutDocs = LeaveRequest::with(['employee', 'leaveType', 'administration'])
            ->whereHas('leaveType', function ($query) {
                $query->where('category', 'paid');
            })
            ->whereNull('supporting_document')
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('auto_conversion_at', 'asc')
            ->limit(10)
            ->get();

        // Calculate days remaining for auto-conversion
        foreach ($paidLeaveWithoutDocs as $request) {
            if ($request->auto_conversion_at) {
                $request->days_remaining = now()->diffInDays($request->auto_conversion_at, false);
            } else {
                $request->days_remaining = null;
            }
        }

        // Leave Type Statistics
        $leaveTypeStats = LeaveType::withCount(['leaveRequests'])
            ->where('is_active', 1)
            ->orderBy('leave_requests_count', 'desc')
            ->get();

        // Department Leave Statistics
        $departmentLeaveStats = DB::table('leave_requests')
            ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->select('departments.department_name', DB::raw('COUNT(*) as count'))
            ->where('administrations.is_active', '1')
            ->where('departments.department_status', '1')
            ->groupBy('departments.id', 'departments.department_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Entitlement Overview
        $totalEntitlements = LeaveEntitlement::sum('entitled_days');
        $usedEntitlements = LeaveEntitlement::sum('taken_days');
        $remainingEntitlements = LeaveEntitlement::sum('remaining_days');

        // Upcoming Expiring Entitlements
        $expiringEntitlements = LeaveEntitlement::with(['employee', 'leaveType'])
            ->where('period_end', '<=', now()->addDays(30))
            ->where('remaining_days', '>', 0)
            ->orderBy('period_end', 'asc')
            ->limit(10)
            ->get();

        // Recent Activity
        $recentLeaveRequests = LeaveRequest::with(['employee', 'leaveType', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.leave-management', compact(
            'title',
            'subtitle',
            'totalLeaveRequests',
            'pendingRequests',
            'approvedRequests',
            'closedRequests',
            'cancelledRequests',
            'thisMonthRequests',
            'monthlyGrowth',
            'openLeaveRequests',
            'pendingCancellations',
            'paidLeaveWithoutDocs',
            'leaveTypeStats',
            'departmentLeaveStats',
            'totalEntitlements',
            'usedEntitlements',
            'remainingEntitlements',
            'expiringEntitlements',
            'recentLeaveRequests'
        ));
    }

    /**
     * Get letter administration statistics API.
     *
     * @return \Illuminate\Http\Response
     */
    public function letterAdministrationStats()
    {
        // Monthly trend for the last 12 months
        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = LetterNumber::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyTrend[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }

        // Status distribution
        $statusStats = LetterNumber::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Category distribution
        $categoryStats = LetterNumber::select('letter_categories.category_code as category', DB::raw('COUNT(*) as count'))
            ->join('letter_categories', 'letter_numbers.letter_category_id', '=', 'letter_categories.id')
            ->groupBy('letter_categories.category_code')
            ->orderBy('count', 'desc')
            ->get();

        // Integration distribution
        $integrationStats = LetterNumber::select('related_document_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('related_document_type')
            ->groupBy('related_document_type')
            ->get()
            ->map(function ($item) {
                // Map to user-friendly names
                $names = [
                    'officialtravel' => 'Official Travel',
                    'recruitment_request' => 'FPTK',
                    'recruitment_hiring' => 'PKWT',
                    'recruitment_offering' => 'Offering'
                ];
                return [
                    'type' => $names[$item->related_document_type] ?? $item->related_document_type,
                    'count' => $item->count
                ];
            });

        return response()->json([
            'monthlyTrend' => $monthlyTrend,
            'statusStats' => $statusStats,
            'categoryStats' => $categoryStats,
            'integrationStats' => $integrationStats
        ]);
    }

    /**
     * Get open leave requests data for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function openLeaveRequests()
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'administration'])
            ->where('status', 'approved')
            ->where('end_date', '<=', now()->addDay());

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                return $row->employee->fullname ?? 'N/A';
            })
            ->addColumn('employee_nik', function ($row) {
                return $row->administration->nik ?? 'N/A';
            })
            ->addColumn('leave_type', function ($row) {
                return $row->leaveType->name ?? 'N/A';
            })
            ->addColumn('leave_period', function ($row) {
                return date('d M Y', strtotime($row->start_date)) . ' - ' . date('d M Y', strtotime($row->end_date));
            })
            ->addColumn('total_days', function ($row) {
                return $row->total_days . ' days';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('leave.requests.show', $row->id) . '" class="btn btn-xs btn-info mr-1">
                            <i class="fas fa-eye"></i>
                        </a>';
                $btn .= '<button class="btn btn-xs btn-success" onclick="closeLeaveRequest(\'' . $row->id . '\')">
                            <i class="fas fa-check"></i>
                        </button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get pending cancellation requests data for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingCancellations()
    {
        $query = LeaveRequestCancellation::with([
            'leaveRequest.employee',
            'leaveRequest.leaveType',
            'requestedBy'
        ])
            ->where('status', 'pending');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                return $row->leaveRequest->employee->fullname ?? 'N/A';
            })
            ->addColumn('employee_nik', function ($row) {
                return $row->leaveRequest->administration->nik ?? 'N/A';
            })
            ->addColumn('leave_type', function ($row) {
                return $row->leaveRequest->leaveType->name ?? 'N/A';
            })
            ->addColumn('original_period', function ($row) {
                $request = $row->leaveRequest;
                return date('d M Y', strtotime($request->start_date)) . ' - ' . date('d M Y', strtotime($request->end_date));
            })
            ->addColumn('days_to_cancel', function ($row) {
                return $row->days_to_cancel . ' days';
            })
            ->addColumn('reason', function ($row) {
                return Str::limit($row->reason, 50);
            })
            ->addColumn('requested_by', function ($row) {
                return $row->requestedBy->name ?? 'N/A';
            })
            ->addColumn('requested_at', function ($row) {
                return $row->requested_at->format('d M Y H:i');
            })
            ->addColumn('action', function ($row) {
                $btn = '<button class="btn btn-xs btn-success mr-1" onclick="approveCancellation(\'' . $row->id . '\')">
                            <i class="fas fa-check"></i>
                        </button>';
                $btn .= '<button class="btn btn-xs btn-danger" onclick="rejectCancellation(\'' . $row->id . '\')">
                            <i class="fas fa-times"></i>
                        </button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get paid leave without supporting documents data for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function paidLeaveWithoutDocs()
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'administration'])
            ->whereHas('leaveType', function ($query) {
                $query->where('category', 'paid');
            })
            ->whereNull('supporting_document')
            ->whereIn('status', ['pending', 'approved']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                return $row->employee->fullname ?? 'N/A';
            })
            ->addColumn('employee_nik', function ($row) {
                return $row->administration->nik ?? 'N/A';
            })
            ->addColumn('leave_type', function ($row) {
                return $row->leaveType->name ?? 'N/A';
            })
            ->addColumn('leave_period', function ($row) {
                return date('d M Y', strtotime($row->start_date)) . ' - ' . date('d M Y', strtotime($row->end_date));
            })
            ->addColumn('total_days', function ($row) {
                return $row->total_days . ' days';
            })
            ->addColumn('days_remaining', function ($row) {
                if ($row->auto_conversion_at) {
                    $days = now()->diffInDays($row->auto_conversion_at, false);
                    $badgeClass = $days <= 3 ? 'badge-danger' : ($days <= 7 ? 'badge-warning' : 'badge-info');
                    return '<span class="badge ' . $badgeClass . '">' . $days . ' days</span>';
                }
                return '<span class="badge badge-secondary">N/A</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'pending' => '<span class="badge badge-warning">Pending</span>',
                    'approved' => '<span class="badge badge-success">Approved</span>',
                ];
                return $badges[$row->status] ?? '<span class="badge badge-secondary">Unknown</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('leave.requests.show', $row->id) . '" class="btn btn-xs btn-info mr-1">
                            <i class="fas fa-eye"></i>
                        </a>';
                $btn .= '<button class="btn btn-xs btn-warning" onclick="sendReminder(\'' . $row->id . '\')">
                            <i class="fas fa-bell"></i>
                        </button>';
                return $btn;
            })
            ->rawColumns(['days_remaining', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Search employee entitlements for quick search.
     *
     * @return \Illuminate\Http\Response
     */
    public function searchEmployeeEntitlements(Request $request)
    {
        $search = $request->get('q');
        $employeeId = $request->get('employee_id');

        // If employee_id is provided, return specific employee entitlements
        if ($employeeId) {
            $employee = Employee::with([
                'administrations' => function ($query) {
                    $query->where('is_active', '1')
                        ->with(['position.department']);
                },
                'leaveEntitlements.leaveType'
            ])->find($employeeId);

            if (!$employee) {
                return response()->json([]);
            }

            $administration = $employee->administrations->where('is_active', '1')->first();

            return response()->json([[
                'id' => $employee->id,
                'name' => $employee->fullname,
                'nik' => $administration->nik ?? 'N/A',
                'position' => $administration->position->position_name ?? 'N/A',
                'department' => $administration->position->department->department_name ?? 'N/A',
                'entitlements' => $employee->leaveEntitlements->map(function ($entitlement) {
                    return [
                        'leave_type' => $entitlement->leaveType->name,
                        'entitled_days' => $entitlement->entitled_days,
                        'remaining_days' => $entitlement->remaining_days,
                        'used_days' => $entitlement->entitled_days - $entitlement->remaining_days,
                        'period' => $entitlement->period_start->format('M Y') . ' - ' . $entitlement->period_end->format('M Y'),
                        'period_start' => $entitlement->period_start->format('Y-m-d'),
                        'period_end' => $entitlement->period_end->format('Y-m-d')
                    ];
                })
            ]]);
        }

        // If search query is provided, return search results
        if (empty($search)) {
            return response()->json([]);
        }

        $employees = Employee::with([
            'administrations' => function ($query) {
                $query->where('is_active', '1')
                    ->with(['position.department']);
            },
            'leaveEntitlements.leaveType'
        ])
            ->whereHas('administrations', function ($query) {
                $query->where('is_active', '1');
            })
            ->where(function ($query) use ($search) {
                $query->where('fullname', 'like', "%{$search}%")
                    ->orWhereHas('administrations', function ($q) use ($search) {
                        $q->where('nik', 'like', "%{$search}%");
                    });
            })
            ->limit(10)
            ->get();

        $results = [];
        foreach ($employees as $employee) {
            $administration = $employee->administrations->where('is_active', '1')->first();
            $results[] = [
                'id' => $employee->id,
                'text' => $employee->fullname . ' (' . ($administration->nik ?? 'N/A') . ')',
                'nik' => $administration->nik ?? 'N/A',
                'name' => $employee->fullname,
                'position' => $administration->position->position_name ?? 'N/A',
                'department' => $administration->position->department->department_name ?? 'N/A'
            ];
        }

        return response()->json($results);
    }

    /**
     * Get leave management statistics API.
     *
     * @return \Illuminate\Http\Response
     */
    public function leaveManagementStats()
    {
        // Monthly trend for the last 12 months
        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = LeaveRequest::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyTrend[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }

        // Status distribution
        $statusStats = LeaveRequest::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Leave type distribution
        $leaveTypeStats = LeaveRequest::select('leave_types.name as type', DB::raw('COUNT(*) as count'))
            ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
            ->groupBy('leave_types.name')
            ->orderBy('count', 'desc')
            ->get();

        // Department distribution
        $departmentStats = LeaveRequest::select('departments.department_name as department', DB::raw('COUNT(*) as count'))
            ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->where('administrations.is_active', '1')
            ->where('departments.department_status', '1')
            ->groupBy('departments.department_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'monthlyTrend' => $monthlyTrend,
            'statusStats' => $statusStats,
            'leaveTypeStats' => $leaveTypeStats,
            'departmentStats' => $departmentStats
        ]);
    }
}
