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
            'dailyTrend'
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
}
