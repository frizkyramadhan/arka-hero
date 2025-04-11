<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Officialtravel;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller
{
    /**
     * Display the official travel dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        // Get counts for the dashboard
        $user = Auth::user();

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
                ->whereNull('departure_at_destination')
                ->count();
        }

        // Count open travels
        $openTravel = Officialtravel::where('official_travel_status', 'open')->count();

        // Get recent travels
        $openTravels = Officialtravel::with('traveler.employee')
            ->where('official_travel_status', 'open')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('officialtravels.dashboard', [
            'title' => 'Dashboard',
            'subtitle' => 'Dashboard',
            'pendingRecommendations' => $pendingRecommendations,
            'pendingApprovals' => $pendingApprovals,
            'pendingArrivals' => $pendingArrivals,
            'pendingDepartures' => $pendingDepartures,
            'openTravel' => $openTravel,
            'openTravels' => $openTravels,
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
            ->whereNull('departure_at_destination');

        return DataTables::of($query)
            ->addIndexColumn()
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
}
