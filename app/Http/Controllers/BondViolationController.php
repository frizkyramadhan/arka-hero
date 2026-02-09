<?php

namespace App\Http\Controllers;

use App\Models\EmployeeBond;
use App\Models\BondViolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class BondViolationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role_or_permission:employees.show')->only('index', 'show', 'getViolations');
        $this->middleware('role_or_permission:employees.create')->only('create');
        $this->middleware('role_or_permission:employees.edit')->only('edit');
        $this->middleware('role_or_permission:employees.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = 'Bond Violations';
        $subtitle = 'Bond Violation Management';

        return view('employee-bonds.index-violations', compact('title', 'subtitle'));
    }

    /**
     * Get violations data for DataTable
     */
    public function getViolations(Request $request)
    {
        $query = BondViolation::with(['employeeBond.employee.administrations'])
            ->select('bond_violations.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($violation) {
                return $violation->employeeBond->employee->fullname ?? '-';
            })
            ->addColumn('employee_nik', function ($violation) {
                $employee = $violation->employeeBond->employee ?? null;
                if (!$employee) return '-';

                $administrations = $employee->administrations ?? collect();
                return $administrations->isNotEmpty()
                    ? ($administrations->first()->nik ?? '-')
                    : '-';
            })
            ->addColumn('bond_name', function ($violation) {
                return $violation->employeeBond->bond_name ?? '-';
            })
            ->addColumn('violation_date_formatted', function ($violation) {
                return $violation->violation_date ? $violation->violation_date->format('d/m/Y') : '-';
            })
            ->addColumn('reason_short', function ($violation) {
                return $violation->reason ? Str::limit($violation->reason, 30) : '-';
            })
            ->addColumn('days_worked', function ($violation) {
                return $violation->days_worked . ' days';
            })
            ->addColumn('days_remaining', function ($violation) {
                return $violation->days_remaining . ' days';
            })
            ->addColumn('penalty_amount', function ($violation) {
                return $violation->formatted_calculated_penalty;
            })
            ->addColumn('paid_amount', function ($violation) {
                return $violation->formatted_paid_penalty;
            })
            ->addColumn('payment_status', function ($violation) {
                $status = $violation->payment_status;
                if ($status == 'paid') {
                    return '<span class="badge badge-success">Paid</span>';
                } elseif ($status == 'partial') {
                    return '<span class="badge badge-warning">Partial</span>';
                } else {
                    return '<span class="badge badge-danger">Pending</span>';
                }
            })
            ->addColumn('actions', function ($violation) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<a href="' . route('bond-violations.show', $violation->id) . '" class="btn btn-sm btn-info mr-1" title="View"><i class="fas fa-eye"></i></a>';
                $actions .= '<a href="' . route('bond-violations.edit', $violation->id) . '" class="btn btn-sm btn-warning mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
                $actions .= '<button class="btn btn-sm btn-danger" onclick="deleteViolation(' . $violation->id . ')" title="Delete"><i class="fas fa-trash"></i></button>';
                $actions .= '</div>';
                return $actions;
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('employee_id') && $request->employee_id) {
                    $query->whereHas('employeeBond', function ($q) use ($request) {
                        $q->where('employee_id', $request->employee_id);
                    });
                }

                if ($request->has('status') && $request->status) {
                    $query->where('payment_status', $request->status);
                }

                if ($request->has('bond_name') && $request->bond_name) {
                    $query->whereHas('employeeBond', function ($q) use ($request) {
                        $q->where('bond_name', 'like', "%{$request->bond_name}%");
                    });
                }

                if ($request->has('reason') && $request->reason) {
                    $query->where('reason', 'like', "%{$request->reason}%");
                }

                if ($request->has('date_from') && $request->date_from) {
                    $query->whereDate('violation_date', '>=', $request->date_from);
                }

                if ($request->has('date_to') && $request->date_to) {
                    $query->whereDate('violation_date', '<=', $request->date_to);
                }

                if ($request->has('search') && $request->search['value']) {
                    $searchValue = $request->search['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->whereHas('employeeBond.employee', function ($subQ) use ($searchValue) {
                            $subQ->where('fullname', 'like', "%{$searchValue}%");
                        })
                            ->orWhereHas('employeeBond', function ($subQ) use ($searchValue) {
                                $subQ->where('bond_name', 'like', "%{$searchValue}%");
                            })
                            ->orWhere('reason', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->order(function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->rawColumns(['payment_status', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $title = 'Create Bond Violation';
        $selectedBondId = $request->get('employee_bond_id');

        $employeeBonds = EmployeeBond::with(['employee'])
            ->join('employees', 'employee_bonds.employee_id', '=', 'employees.id')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.is_active', 1)
            ->where('employee_bonds.status', 'active')
            ->select('employee_bonds.*', 'administrations.nik')
            ->orderBy('administrations.nik', 'asc')
            ->get();

        return view('employee-bonds.create-violation', compact('title', 'employeeBonds', 'selectedBondId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_bond_id' => 'required|exists:employee_bonds,id',
            'violation_date' => 'required|date',
            'reason' => 'nullable|string|max:1000',
            'payment_due_date' => 'nullable|date|after:violation_date'
        ]);

        DB::beginTransaction();
        try {
            $employeeBond = EmployeeBond::findOrFail($request->employee_bond_id);
            $violationDate = Carbon::parse($request->violation_date);

            // Create violation with prorate calculation
            $violation = $employeeBond->createViolation(
                $violationDate,
                $request->reason
            );

            // Set payment due date if provided
            if ($request->payment_due_date) {
                $violation->payment_due_date = $request->payment_due_date;
                $violation->save();
            }

            // Update bond status to violated
            $employeeBond->update(['status' => 'violated']);

            DB::commit();

            return redirect()->route('bond-violations.index')
                ->with('toast_success', 'Bond violation created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to create bond violation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BondViolation $bondViolation)
    {
        $title = 'Bond Violation Details';
        $bondViolation->load(['employeeBond.employee']);

        return view('employee-bonds.show-violation', compact('title', 'bondViolation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BondViolation $bondViolation)
    {
        $title = 'Edit Bond Violation';
        $employeeBonds = EmployeeBond::with(['employee'])
            ->join('employees', 'employee_bonds.employee_id', '=', 'employees.id')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.is_active', 1)
            ->whereIn('employee_bonds.status', ['active', 'violated'])
            ->select('employee_bonds.*', 'administrations.nik')
            ->orderBy('administrations.nik', 'asc')
            ->get();

        return view('employee-bonds.edit-violation', compact('title', 'bondViolation', 'employeeBonds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BondViolation $bondViolation)
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
            'penalty_paid_amount' => 'nullable|numeric|min:0',
            'payment_due_date' => 'nullable|date',
        ]);

        try {
            $bondViolation->update([
                'reason' => $request->reason,
                'penalty_paid_amount' => $request->penalty_paid_amount ?? 0,
                'payment_due_date' => $request->payment_due_date ? Carbon::parse($request->payment_due_date) : null
            ]);

            return redirect()->route('bond-violations.index')
                ->with('toast_success', 'Bond violation updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('toast_error', 'Failed to update bond violation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BondViolation $bondViolation)
    {
        DB::beginTransaction();
        try {
            $employeeBond = $bondViolation->employeeBond;

            // Delete the violation
            $bondViolation->delete();

            // Check if there are any remaining violations for this bond
            $remainingViolations = BondViolation::where('employee_bond_id', $employeeBond->id)->count();

            // If no violations left, update bond status back to active
            if ($remainingViolations == 0) {
                $employeeBond->update(['status' => 'active']);
            }

            DB::commit();

            return redirect()->route('bond-violations.index')
                ->with('toast_success', 'Bond violation deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to delete bond violation: ' . $e->getMessage());
        }
    }

    /**
     * Calculate penalty for specific bond and violation date.
     * Kebijakan: penalty jumlah tetap = total investment value (tidak prorate). Logika di EmployeeBond::calculateProratePenalty().
     */
    public function calculatePenalty(Request $request)
    {
        $request->validate([
            'employee_bond_id' => 'required|exists:employee_bonds,id',
            'violation_date' => 'required|date'
        ]);

        try {
            $employeeBond = EmployeeBond::findOrFail($request->employee_bond_id);
            $violationDate = Carbon::parse($request->violation_date);

            $calculation = $employeeBond->calculateProratePenalty($violationDate);

            return response()->json($calculation);
        } catch (\Exception $e) {
            return response()->json([
                'is_valid' => false,
                'message' => $e->getMessage(),
                'penalty_amount' => 0,
                'calculation_details' => []
            ], 400);
        }
    }
}
