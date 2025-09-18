<?php

namespace App\Http\Controllers;

use App\Models\EmployeeBond;
use App\Models\BondViolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BondViolationController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:bond-violations.show')->only('index', 'show');
        // $this->middleware('permission:bond-violations.create')->only('create', 'store');
        // $this->middleware('permission:bond-violations.edit')->only('edit', 'update');
        // $this->middleware('permission:bond-violations.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = 'Bond Violations';
        $subtitle = 'Bond Violation Management';

        $violations = BondViolation::with(['employeeBond.employee'])
            ->when($request->employee_id, function ($query, $employeeId) {
                return $query->whereHas('employeeBond', function ($q) use ($employeeId) {
                    $q->where('employee_id', $employeeId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('employee-bonds.index-violations', compact('title', 'subtitle', 'violations'));
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
     * Calculate penalty for specific bond and violation date
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
