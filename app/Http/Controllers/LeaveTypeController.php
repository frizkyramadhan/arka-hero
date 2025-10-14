<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:leave-types.show')->only('index', 'show', 'data');
        $this->middleware('permission:leave-types.create')->only('create', 'store');
        $this->middleware('permission:leave-types.edit')->only('edit', 'update', 'toggleStatus');
        $this->middleware('permission:leave-types.delete')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('leave-types.index')->with('title', 'Leave Types');
    }

    /**
     * Get leave types data for DataTable
     */
    public function data(Request $request)
    {
        $leaveTypes = LeaveType::query();

        // Filter by category
        if (!empty($request->get('category'))) {
            $leaveTypes->where('category', $request->get('category'));
        }

        // Filter by status
        if (!empty($request->get('status'))) {
            $leaveTypes->where('is_active', $request->get('status') === 'active');
        }

        // Global search
        if (!empty($request->get('search'))) {
            $search = $request->get('search');
            $leaveTypes->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%")
                    ->orWhere('code', 'LIKE', "%$search%")
                    ->orWhere('category', 'LIKE', "%$search%")
                    ->orWhere('remarks', 'LIKE', "%$search%");
            });
        }

        $leaveTypes->orderBy('code');

        return datatables()->of($leaveTypes)
            ->addIndexColumn()
            ->addColumn('name', function ($leaveType) {
                return $leaveType->name;
            })
            ->addColumn('code', function ($leaveType) {
                return '<span class="badge badge-info">' . $leaveType->code . '</span>';
            })
            ->addColumn('category', function ($leaveType) {
                switch ($leaveType->category) {
                    case 'annual':
                        return '<span class="badge badge-success">Annual</span>';
                    case 'paid':
                        return '<span class="badge badge-warning">Paid</span>';
                    case 'unpaid':
                        return '<span class="badge badge-danger">Unpaid</span>';
                    case 'lsl':
                        return '<span class="badge badge-primary">LSL</span>';
                    case 'periodic':
                        return '<span class="badge badge-info">Periodic</span>';
                    default:
                        return '<span class="badge badge-secondary">' . ucfirst($leaveType->category) . '</span>';
                }
            })
            ->addColumn('default_days', function ($leaveType) {
                return $leaveType->default_days . ' days';
            })
            ->addColumn('eligible_after', function ($leaveType) {
                return $leaveType->eligible_after_years . ' years';
            })
            ->addColumn('deposit_days', function ($leaveType) {
                return $leaveType->deposit_days_first . ' days';
            })
            ->addColumn('carry_over', function ($leaveType) {
                return $leaveType->carry_over ?
                    '<span class="badge badge-success">Yes</span>' :
                    '<span class="badge badge-danger">No</span>';
            })
            ->addColumn('status', function ($leaveType) {
                return $leaveType->is_active ?
                    '<span class="badge badge-success">Active</span>' :
                    '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($leaveType) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<a href="' . route('leave.types.show', $leaveType) . '" class="btn btn-info btn-sm mr-1" title="View"><i class="fas fa-eye"></i></a>';
                $actions .= '<a href="' . route('leave.types.edit', $leaveType) . '" class="btn btn-warning btn-sm mr-1" title="Edit"><i class="fas fa-edit"></i></a>';

                if ($leaveType->leaveEntitlements()->count() == 0 && $leaveType->leaveRequests()->count() == 0) {
                    $actions .= '<form method="POST" action="' . route('leave.types.destroy', $leaveType) . '" style="display: inline-block;" onsubmit="return confirm(\'Are you sure you want to delete this leave type?\')">';
                    $actions .= csrf_field();
                    $actions .= method_field('DELETE');
                    $actions .= '<button type="submit" class="btn btn-danger btn-sm mr-1" title="Delete"><i class="fas fa-trash"></i></button>';
                    $actions .= '</form>';
                }

                $actions .= '<form method="POST" action="' . route('leave.types.toggle-status', $leaveType) . '" style="display: inline-block;" onsubmit="return confirm(\'Are you sure you want to toggle the status of this leave type?\')">';
                $actions .= csrf_field();
                $actions .= '<button type="submit" class="btn btn-' . ($leaveType->is_active ? 'success' : 'secondary') . ' btn-sm" title="' . ($leaveType->is_active ? 'Deactivate' : 'Activate') . '"><i class="fas fa-toggle-' . ($leaveType->is_active ? 'on' : 'off') . '"></i></button>';
                $actions .= '</form>';
                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns(['code', 'category', 'carry_over', 'status', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('leave-types.create')->with('title', 'Create Leave Type');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:leave_types,code',
            'category' => 'required|in:annual,paid,unpaid,lsl,periodic',
            'default_days' => 'required|integer|min:0',
            'eligible_after_years' => 'required|integer|min:0',
            'deposit_days_first' => 'nullable|integer|min:0',
            'carry_over' => 'boolean',
            'remarks' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            $leaveType = LeaveType::create([
                'name' => $request->name,
                'code' => $request->code,
                'category' => $request->category,
                'default_days' => $request->default_days,
                'eligible_after_years' => $request->eligible_after_years,
                'deposit_days_first' => $request->deposit_days_first ?? 0,
                'carry_over' => $request->boolean('carry_over'),
                'remarks' => $request->remarks,
                'is_active' => true
            ]);

            DB::commit();

            // return redirect()->route('leave.types.show', $leaveType)
            return redirect()->route('leave.types.index')
                ->with('toast_success', 'Leave type created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with(['toast_error' => 'Failed to create leave type: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        $leaveType->load(['leaveEntitlements.employee', 'leaveRequests.employee']);

        return view('leave-types.show', compact('leaveType'))->with('title', 'Leave Type Details');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveType $leaveType)
    {
        return view('leave-types.edit', compact('leaveType'))->with('title', 'Edit Leave Type');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:leave_types,code,' . $leaveType->id,
            'category' => 'required|in:annual,paid,unpaid,lsl,periodic',
            'default_days' => 'required|integer|min:0',
            'eligible_after_years' => 'required|integer|min:0',
            'deposit_days_first' => 'nullable|integer|min:0',
            'carry_over' => 'boolean',
            'remarks' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $leaveType->update([
                'name' => $request->name,
                'code' => $request->code,
                'category' => $request->category,
                'default_days' => $request->default_days,
                'eligible_after_years' => $request->eligible_after_years,
                'deposit_days_first' => $request->deposit_days_first ?? 0,
                'carry_over' => $request->boolean('carry_over'),
                'remarks' => $request->remarks,
                'is_active' => $request->boolean('is_active')
            ]);

            DB::commit();

            // return redirect()->route('leave.types.show', $leaveType)
            return redirect()->route('leave.types.index')
                ->with('toast_success', 'Leave type updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with(['toast_error' => 'Failed to update leave type: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        try {
            // Check if there are any leave entitlements or requests for this type
            if ($leaveType->leaveEntitlements()->count() > 0 || $leaveType->leaveRequests()->count() > 0) {
                return redirect()->route('leave.types.index')
                    ->with('toast_error', 'Cannot delete leave type with existing entitlements or requests.');
            }

            $leaveType->delete();

            return redirect()->route('leave.types.index')
                ->with('toast_success', 'Leave type deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('leave.types.index')
                ->with('toast_error', 'Failed to delete leave type: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(LeaveType $leaveType)
    {
        try {
            $leaveType->update(['is_active' => !$leaveType->is_active]);

            $status = $leaveType->is_active ? 'activated' : 'deactivated';

            return redirect()->route('leave.types.index')
                ->with('toast_success', "Leave type {$status} successfully.");
        } catch (\Exception $e) {
            return redirect()->route('leave.types.index')
                ->with('toast_error', 'Failed to update leave type status: ' . $e->getMessage());
        }
    }

    /**
     * Get leave types for API
     */
    public function apiIndex(Request $request)
    {
        $query = LeaveType::where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $leaveTypes = $query->orderBy('name')->get();

        return response()->json($leaveTypes);
    }

    /**
     * Get leave type statistics
     */
    public function statistics(LeaveType $leaveType)
    {
        $stats = [
            'total_entitlements' => $leaveType->leaveEntitlements()->count(),
            'total_requests' => $leaveType->leaveRequests()->count(),
            'pending_requests' => $leaveType->leaveRequests()->where('status', 'pending')->count(),
            'approved_requests' => $leaveType->leaveRequests()->where('status', 'approved')->count(),
            'rejected_requests' => $leaveType->leaveRequests()->where('status', 'rejected')->count(),
            'total_days_taken' => $leaveType->leaveRequests()->where('status', 'approved')->sum('total_days'),
            'average_days_per_request' => $leaveType->leaveRequests()->where('status', 'approved')->avg('total_days')
        ];

        return response()->json($stats);
    }
}
