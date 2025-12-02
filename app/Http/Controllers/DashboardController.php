<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\License;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Education;
use App\Models\Employeebank;
use App\Models\Taxidentification;
use App\Models\Insurance;
use App\Models\Family;
use App\Models\Course;
use App\Models\Jobexperience;
use App\Models\Emrgcall;
use App\Models\Operableunit;
use App\Models\Additionaldata;
use App\Models\LeaveType;
use App\Models\Department;
use App\Models\Permission;
use Illuminate\Support\Str;
use App\Models\ApprovalPlan;
use App\Models\EmployeeBond;
use App\Models\LeaveRequest;
use App\Models\LetterNumber;
use Illuminate\Http\Request;
use App\Models\BondViolation;
use App\Models\LetterSubject;
use App\Models\Administration;
use App\Models\LetterCategory;
use App\Models\Officialtravel;
use App\Models\LeaveEntitlement;
use Yajra\DataTables\DataTables;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentSession;
use Illuminate\Support\Facades\DB;
use App\Models\RecruitmentCandidate;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequestCancellation;

class DashboardController extends Controller
{
    /**
     * Display the dashboard page with both Official Travel and Employee data.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Redirect based on user role
        if ($user && $user->hasRole('user')) {
            return redirect()->route('dashboard.personal');
        }

        // Default redirect to Employee dashboard for other roles
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
            'leaveRequest.administration',
            'requestedBy'
        ])
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->get();

        // Paid Leave Without Supporting Documents
        $paidLeaveWithoutDocs = LeaveRequest::with(['employee', 'leaveType', 'administration'])
            ->whereHas('leaveType', function ($query) {
                $query->where('category', 'paid');
            })
            ->whereNull('supporting_document')
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('auto_conversion_at', 'asc')
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
        // remaining_days is now accessor, calculate from collection
        $remainingEntitlements = LeaveEntitlement::get()->sum('remaining_days');

        // Upcoming Expiring Entitlements (only not expired yet)
        $today = now()->startOfDay();
        $thirtyDaysLater = now()->addDays(30)->endOfDay();

        $expiringEntitlements = LeaveEntitlement::with(['employee', 'leaveType'])
            ->where('period_end', '>=', $today)
            ->where('period_end', '<=', $thirtyDaysLater)
            ->whereRaw('(entitled_days - taken_days) > 0') // remaining_days is now accessor
            ->orderBy('period_end', 'asc')
            ->limit(10)
            ->get();

        // Employees without entitlements
        $employeesWithoutEntitlements = Employee::with(['administrations' => function ($query) {
            $query->where('is_active', '1')
                ->with(['position.department', 'project']);
        }])
            ->whereHas('administrations', function ($query) {
                $query->where('is_active', '1');
            })
            ->whereDoesntHave('leaveEntitlements')
            ->orderBy('fullname', 'asc')
            ->get();

        // Employees with entitlements expiring soon (within 30 days, not expired yet)
        $employeesWithExpiringEntitlements = Employee::with([
            'administrations' => function ($query) {
                $query->where('is_active', '1')
                    ->with(['position.department']);
            },
            'leaveEntitlements' => function ($query) use ($today, $thirtyDaysLater) {
                $query->with('leaveType')
                    ->where('period_end', '>=', $today)
                    ->where('period_end', '<=', $thirtyDaysLater)
                    ->whereRaw('(entitled_days - taken_days) > 0'); // remaining_days is now accessor
            }
        ])
            ->whereHas('administrations', function ($query) {
                $query->where('is_active', '1');
            })
            ->whereHas('leaveEntitlements', function ($query) use ($today, $thirtyDaysLater) {
                $query->where('period_end', '>=', $today)
                    ->where('period_end', '<=', $thirtyDaysLater)
                    ->whereRaw('(entitled_days - taken_days) > 0'); // remaining_days is now accessor
            })
            ->orderBy('fullname', 'asc')
            ->get();

        // Recent Activity
        $recentLeaveRequests = LeaveRequest::with(['employee', 'leaveType', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Format data for client-side DataTables
        $openLeaveRequestsData = $openLeaveRequests->map(function ($request) {
            return [
                'employee_name' => $request->employee->fullname ?? 'N/A',
                'leave_type' => $request->leaveType->name ?? 'N/A',
                'leave_period' => $request->start_date->format('d M Y') . ' - ' . $request->end_date->format('d M Y'),
                'total_days' => $request->total_days . ' days',
                'action' => '<a href="' . route('leave.requests.show', $request->id) . '" class="btn btn-xs btn-info mr-1">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-xs btn-success" onclick="closeLeaveRequest(\'' . $request->id . '\')">
                            <i class="fas fa-check"></i>
                        </button>'
            ];
        });

        $pendingCancellationsData = $pendingCancellations->map(function ($cancellation) {
            $request = $cancellation->leaveRequest;
            $administration = $request->administration;
            $employeeNik = 'N/A';
            if ($administration) {
                $employeeNik = $administration->nik ?? 'N/A';
            } else {
                $employee = $request->employee;
                if ($employee) {
                    $activeAdmin = $employee->administrations->where('is_active', '1')->first();
                    $employeeNik = $activeAdmin->nik ?? 'N/A';
                }
            }
            return [
                'employee_name' => $request->employee->fullname ?? 'N/A',
                'leave_type' => $request->leaveType->name ?? 'N/A',
                'days_to_cancel' => $cancellation->days_to_cancel . ' days',
                'reason' => Str::limit($cancellation->reason, 50),
                'action' => '<button class="btn btn-xs btn-success mr-1" onclick="approveCancellation(\'' . $cancellation->id . '\')">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-xs btn-danger" onclick="rejectCancellation(\'' . $cancellation->id . '\')">
                            <i class="fas fa-times"></i>
                        </button>'
            ];
        });

        $paidLeaveWithoutDocsData = $paidLeaveWithoutDocs->map(function ($request) {
            $daysRemaining = 'N/A';
            if ($request->auto_conversion_at) {
                $days = now()->diffInDays($request->auto_conversion_at, false);
                if ($days < 0) {
                    $days = 0;
                }
                $badgeClass = $days <= 3 ? 'badge-danger' : ($days <= 7 ? 'badge-warning' : 'badge-info');
                $daysRemaining = '<span class="badge ' . $badgeClass . '">' . $days . ' days</span>';
            } else {
                $daysRemaining = '<span class="badge badge-secondary">N/A</span>';
            }
            $statusBadge = [
                'pending' => '<span class="badge badge-warning">Pending</span>',
                'approved' => '<span class="badge badge-success">Approved</span>',
            ];
            return [
                'employee_name' => $request->employee->fullname ?? 'N/A',
                'leave_type' => $request->leaveType->name ?? 'N/A',
                'leave_period' => $request->start_date->format('d M Y') . ' - ' . $request->end_date->format('d M Y'),
                'total_days' => $request->total_days . ' days',
                'days_remaining' => $daysRemaining,
                'status_badge' => $statusBadge[$request->status] ?? '<span class="badge badge-secondary">Unknown</span>',
                'action' => '<a href="' . route('leave.requests.show', $request->id) . '" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                        </a>'
            ];
        });

        $employeesWithoutEntitlementsData = $employeesWithoutEntitlements->map(function ($employee) {
            $administration = $employee->administrations->where('is_active', '1')->first();
            return [
                'employee_name' => $employee->fullname ?? 'N/A',
                'employee_nik' => $administration->nik ?? 'N/A',
                'doh' => $administration->doh ? $administration->doh->format('d M Y') : 'N/A',
                'position' => $administration->position->position_name ?? 'N/A',
                'department' => $administration->position->department->department_name ?? 'N/A',
                'project' => ($administration && $administration->project) ? $administration->project->project_code : 'N/A',
                'action' => '<a href="' . route('leave.entitlements.employee.show', $employee->id) . '" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i></a>'
            ];
        });

        $employeesWithExpiringEntitlementsData = $employeesWithExpiringEntitlements->map(function ($employee) use ($today, $thirtyDaysLater) {
            $administration = $employee->administrations->where('is_active', '1')->first();
            $expiringEntitlements = $employee->leaveEntitlements->filter(function ($entitlement) use ($today, $thirtyDaysLater) {
                return $entitlement->period_end >= $today &&
                    $entitlement->period_end <= $thirtyDaysLater &&
                    ($entitlement->entitled_days - $entitlement->taken_days) > 0;
            });

            $expiresText = '';
            if ($expiringEntitlements->isNotEmpty()) {
                $firstExpiring = $expiringEntitlements->sortBy('period_end')->first();
                $daysUntilExpiry = now()->diffInDays($firstExpiring->period_end, false);
                $expiresText = $firstExpiring->period_end->format('d M Y') . ' EXPIRES (' . $daysUntilExpiry . ' days)';
            }

            return [
                'employee_name' => $employee->fullname ?? 'N/A',
                'employee_nik' => $administration->nik ?? 'N/A',
                'expires' => $expiresText,
                'action' => '<a href="' . route('leave.entitlements.employee.show', $employee->id) . '" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i></a>'
            ];
        });

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
            'openLeaveRequestsData',
            'pendingCancellationsData',
            'paidLeaveWithoutDocsData',
            'leaveTypeStats',
            'departmentLeaveStats',
            'totalEntitlements',
            'usedEntitlements',
            'remainingEntitlements',
            'expiringEntitlements',
            'employeesWithoutEntitlementsData',
            'employeesWithExpiringEntitlementsData',
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
            'leaveRequest.administration',
            'requestedBy'
        ])
            ->where('status', 'pending');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                return $row->leaveRequest->employee->fullname ?? 'N/A';
            })
            ->addColumn('employee_nik', function ($row) {
                $administration = $row->leaveRequest->administration;
                if ($administration) {
                    return $administration->nik ?? 'N/A';
                }
                // Fallback: get from employee's active administration
                $employee = $row->leaveRequest->employee;
                if ($employee) {
                    $activeAdmin = $employee->administrations->where('is_active', '1')->first();
                    return $activeAdmin->nik ?? 'N/A';
                }
                return 'N/A';
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
                    // If auto_conversion_at is in the past, show 0 days remaining
                    if ($days < 0) {
                        $days = 0;
                    }
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
     * Get employees without entitlements data for DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function employeesWithoutEntitlements()
    {
        $query = Employee::with(['administrations' => function ($query) {
            $query->where('is_active', '1')
                ->with(['position.department', 'project']);
        }])
            ->whereHas('administrations', function ($query) {
                $query->where('is_active', '1');
            })
            ->whereDoesntHave('leaveEntitlements')
            ->orderBy('fullname', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                return $row->fullname ?? 'N/A';
            })
            ->addColumn('employee_nik', function ($row) {
                $administration = $row->administrations->where('is_active', '1')->first();
                return $administration->nik ?? 'N/A';
            })
            ->addColumn('doh', function ($row) {
                $administration = $row->administrations->where('is_active', '1')->first();
                return $administration->doh ? $administration->doh->format('d M Y') : 'N/A';
            })
            ->addColumn('position', function ($row) {
                $administration = $row->administrations->where('is_active', '1')->first();
                return $administration->position->position_name ?? 'N/A';
            })
            ->addColumn('department', function ($row) {
                $administration = $row->administrations->where('is_active', '1')->first();
                return $administration->position->department->department_name ?? 'N/A';
            })
            ->addColumn('project', function ($row) {
                $administration = $row->administrations->where('is_active', '1')->first();
                if ($administration && $administration->project) {
                    return $administration->project->project_code;
                }
                return 'N/A';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('leave.entitlements.employee.show', $row->id) . '" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get employees with expiring entitlements data for DataTable.
     * Shows entitlements that:
     * 1. Will expire within 30 days (not expired yet), OR
     * 2. Already expired but no new period entitlement exists yet
     *
     * @return \Illuminate\Http\Response
     */
    public function employeesWithExpiringEntitlements()
    {
        $today = now()->startOfDay();
        $thirtyDaysLater = now()->addDays(30)->endOfDay();
        $thirtyDaysAgo = now()->subDays(30)->startOfDay();

        $query = Employee::with([
            'administrations' => function ($query) {
                $query->where('is_active', '1')
                    ->with(['position.department']);
            },
            'leaveEntitlements' => function ($query) use ($today, $thirtyDaysLater, $thirtyDaysAgo) {
                $query->with('leaveType')
                    ->where(function ($q) use ($today, $thirtyDaysLater) {
                        // Will expire within 30 days (not expired yet)
                        $q->where('period_end', '>=', $today)
                            ->where('period_end', '<=', $thirtyDaysLater)
                            ->whereRaw('(entitled_days - taken_days) > 0'); // remaining_days is now accessor
                    })
                    ->orWhere(function ($q) use ($today, $thirtyDaysAgo) {
                        // Already expired within last 30 days
                        // (Filtering for new period will be done in collection level)
                        $q->where('period_end', '>=', $thirtyDaysAgo)
                            ->where('period_end', '<', $today);
                    });
            }
        ])
            ->whereHas('administrations', function ($query) {
                $query->where('is_active', '1');
            })
            ->whereHas('leaveEntitlements', function ($query) use ($today, $thirtyDaysLater, $thirtyDaysAgo) {
                $query->where(function ($q) use ($today, $thirtyDaysLater) {
                    // Will expire within 30 days (not expired yet)
                    $q->where('period_end', '>=', $today)
                        ->where('period_end', '<=', $thirtyDaysLater)
                        ->whereRaw('(entitled_days - taken_days) > 0'); // remaining_days is now accessor
                })
                    ->orWhere(function ($q) use ($today, $thirtyDaysAgo) {
                        // Already expired within last 30 days, but check if no new period exists
                        $q->where('period_end', '>=', $thirtyDaysAgo)
                            ->where('period_end', '<', $today)
                            ->whereNotExists(function ($subQuery) {
                                // Check if there's a newer entitlement with same leave_type_id
                                $subQuery->select(DB::raw(1))
                                    ->from('leave_entitlements as le2')
                                    ->whereColumn('le2.employee_id', 'leave_entitlements.employee_id')
                                    ->whereColumn('le2.leave_type_id', 'leave_entitlements.leave_type_id')
                                    ->whereColumn('le2.period_start', '>', 'leave_entitlements.period_end');
                            });
                    });
            })
            ->orderBy('fullname', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                return $row->fullname ?? 'N/A';
            })
            ->addColumn('employee_nik', function ($row) {
                $administration = $row->administrations->where('is_active', '1')->first();
                return $administration->nik ?? 'N/A';
            })
            ->addColumn('expires', function ($row) use ($today) {
                $entitlements = $row->leaveEntitlements;
                if ($entitlements->isEmpty()) {
                    return 'N/A';
                }

                // Filter entitlements: remove expired ones that already have new period
                $filteredEntitlements = $entitlements->filter(function ($entitlement) use ($row, $today) {
                    // If not expired yet, keep it
                    if ($entitlement->period_end >= $today) {
                        return true;
                    }

                    // If expired, check if there's a newer entitlement with same leave_type_id
                    // Query directly to ensure we check all entitlements, not just eager-loaded ones
                    $hasNewPeriod = LeaveEntitlement::where('employee_id', $row->id)
                        ->where('leave_type_id', $entitlement->leave_type_id)
                        ->where('period_start', '>', $entitlement->period_end)
                        ->exists();

                    // Only keep if no new period exists
                    return !$hasNewPeriod;
                });

                if ($filteredEntitlements->isEmpty()) {
                    return 'N/A';
                }

                // Ambil entitlement yang paling cepat habis (period_end terdekat)
                $nearestExpiry = $filteredEntitlements->sortBy('period_end')->first();
                $daysUntilExpiry = now()->diffInDays($nearestExpiry->period_end, false);

                // Tentukan badge class berdasarkan status
                if ($daysUntilExpiry < 0) {
                    // Sudah lewat
                    $badgeClass = 'badge-dark';
                    $statusText = 'EXPIRED';
                    $daysText = abs($daysUntilExpiry) . ' days ago';
                } elseif ($daysUntilExpiry == 0) {
                    // Hari ini habis
                    $badgeClass = 'badge-danger';
                    $statusText = 'EXPIRES TODAY';
                    $daysText = '0 days';
                } elseif ($daysUntilExpiry <= 7) {
                    // Akan habis dalam 7 hari
                    $badgeClass = 'badge-danger';
                    $statusText = 'EXPIRES SOON';
                    $daysText = $daysUntilExpiry . ' days';
                } elseif ($daysUntilExpiry <= 15) {
                    // Akan habis dalam 15 hari
                    $badgeClass = 'badge-warning';
                    $statusText = 'EXPIRES SOON';
                    $daysText = $daysUntilExpiry . ' days';
                } else {
                    // Masih lama
                    $badgeClass = 'badge-info';
                    $statusText = 'EXPIRES';
                    $daysText = $daysUntilExpiry . ' days';
                }

                return '<span class="badge ' . $badgeClass . '">' .
                    $nearestExpiry->period_end->format('d M Y') .
                    '<br><small>' . $statusText . '</small>' .
                    '<br><small>(' . $daysText . ')</small></span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('leave.entitlements.employee.show', $row->id) . '" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i></a>';
                return $btn;
            })
            ->rawColumns(['expires', 'action'])
            ->make(true);
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

    // ========================================
    // PERSONAL DASHBOARD FOR USER ROLE
    // ========================================

    /**
     * Personal Dashboard for users with 'user' role
     */
    public function personal()
    {
        $this->authorize('personal.dashboard.view');

        $user = Auth::user();

        // Leave Requests Summary
        $leaveStats = [
            'total_requests' => LeaveRequest::where('employee_id', $user->employee_id)->count(),
            'pending_requests' => LeaveRequest::where('employee_id', $user->employee_id)->where('status', 'pending')->count(),
            'approved_requests' => LeaveRequest::where('employee_id', $user->employee_id)->where('status', 'approved')->count(),
            'this_month_requests' => LeaveRequest::where('employee_id', $user->employee_id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Official Travel Summary
        $administrationId = $user->administration_id;
        $travelStats = [
            'total_travels' => $administrationId ? Officialtravel::where(function ($q) use ($administrationId) {
                $q->where('traveler_id', $administrationId)
                    ->orWhereHas('details', function ($detailQuery) use ($administrationId) {
                        $detailQuery->where('follower_id', $administrationId);
                    });
            })->count() : 0,
            'upcoming_travels' => $administrationId ? Officialtravel::where(function ($q) use ($administrationId) {
                $q->where('traveler_id', $administrationId)
                    ->orWhereHas('details', function ($detailQuery) use ($administrationId) {
                        $detailQuery->where('follower_id', $administrationId);
                    });
            })
                ->where('status', 'approved')
                ->where('official_travel_date', '>=', now())
                ->count() : 0,
            'this_month_travels' => $administrationId ? Officialtravel::where(function ($q) use ($administrationId) {
                $q->where('traveler_id', $administrationId)
                    ->orWhereHas('details', function ($detailQuery) use ($administrationId) {
                        $detailQuery->where('follower_id', $administrationId);
                    });
            })
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count() : 0,
        ];

        // Recruitment Requests Summary
        $recruitmentStats = [
            'total_requests' => RecruitmentRequest::where('created_by', $user->id)->count(),
            'pending_requests' => RecruitmentRequest::where('created_by', $user->id)->where('status', 'draft')->count(),
            'approved_requests' => RecruitmentRequest::where('created_by', $user->id)->where('status', 'approved')->count(),
        ];

        // Pending Approvals
        $pendingApprovals = ApprovalPlan::where('approver_id', $user->id)
            ->where('is_open', true)
            ->where('status', 0)
            ->count();

        // Recent Leave Requests
        $recentLeaveRequests = LeaveRequest::with(['leaveType'])
            ->where('employee_id', $user->employee_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Official Travels
        $administrationId = $user->administration_id;
        $recentTravels = $administrationId ? Officialtravel::with(['project', 'stops' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
            ->where(function ($q) use ($administrationId) {
                $q->where('traveler_id', $administrationId)
                    ->orWhereHas('details', function ($detailQuery) use ($administrationId) {
                        $detailQuery->where('follower_id', $administrationId);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get() : collect();

        // Leave Entitlements Summary
        // Calculate taken_days from approved leave requests (considering cancellations)
        $leaveEntitlements = LeaveEntitlement::with(['leaveType'])
            ->where('employee_id', $user->employee_id)
            ->where('period_end', '>=', now())
            ->orderBy('period_end', 'asc')
            ->get()
            ->map(function ($entitlement) {
                // Recalculate taken_days from approved leave requests (effective days after cancellation)
                $approvedRequests = LeaveRequest::where('employee_id', $entitlement->employee_id)
                    ->where('leave_type_id', $entitlement->leave_type_id)
                    ->whereIn('status', ['approved', 'auto_approved'])
                    ->whereBetween('start_date', [$entitlement->period_start, $entitlement->period_end])
                    ->get();

                // Calculate effective taken days (total_days - cancelled_days)
                $effectiveTakenDays = $approvedRequests->sum(function ($request) {
                    return $request->getEffectiveDays();
                });

                // Update taken_days if different (to keep it in sync)
                if ($entitlement->taken_days != $effectiveTakenDays) {
                    $entitlement->taken_days = $effectiveTakenDays;
                    $entitlement->save();
                }

                return $entitlement;
            });

        // Profile Completeness Check
        $employee = Employee::where('id', $user->employee_id)->first();
        $profileCompleteness = [
            'bank' => Employeebank::where('employee_id', $user->employee_id)->exists(),
            'tax' => Taxidentification::where('employee_id', $user->employee_id)->exists(),
            'insurance' => Insurance::where('employee_id', $user->employee_id)->exists(),
            'license' => License::where('employee_id', $user->employee_id)->exists(),
            'family' => Family::where('employee_id', $user->employee_id)->exists(),
            'education' => Education::where('employee_id', $user->employee_id)->exists(),
            'course' => Course::where('employee_id', $user->employee_id)->exists(),
            'job' => Jobexperience::where('employee_id', $user->employee_id)->exists(),
            'emergency' => Emrgcall::where('employee_id', $user->employee_id)->exists(),
            'unit' => Operableunit::where('employee_id', $user->employee_id)->exists(),
            'additional' => Additionaldata::where('employee_id', $user->employee_id)->exists(),
        ];

        $missingSections = collect($profileCompleteness)->filter(function ($exists) {
            return !$exists;
        })->keys()->toArray();

        $completenessPercentage = round((count($profileCompleteness) - count($missingSections)) / count($profileCompleteness) * 100);

        return view('dashboard.personal', [
            'title' => 'My Dashboard',
            'subtitle' => 'Personal Overview',
            'leaveStats' => $leaveStats,
            'travelStats' => $travelStats,
            'recruitmentStats' => $recruitmentStats,
            'pendingApprovals' => $pendingApprovals,
            'recentLeaveRequests' => $recentLeaveRequests,
            'recentTravels' => $recentTravels,
            'leaveEntitlements' => $leaveEntitlements,
            'profileCompleteness' => $profileCompleteness,
            'missingSections' => $missingSections,
            'completenessPercentage' => $completenessPercentage,
        ]);
    }
}
