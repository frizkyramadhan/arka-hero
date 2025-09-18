<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeBond;
use App\Models\BondViolation;
use App\Models\LetterNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeeBondController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:employee-bonds.show')->only('index', 'show');
        // $this->middleware('permission:employee-bonds.create')->only('create', 'store');
        // $this->middleware('permission:employee-bonds.edit')->only('edit', 'update');
        // $this->middleware('permission:employee-bonds.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = 'Employee Bonds (Ikatan Dinas Karyawan)';
        $subtitle = 'Employee Bond Management';

        $employees = Employee::join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.is_active', 1)
            ->select('employees.*', 'administrations.nik')
            ->orderBy('administrations.nik', 'asc')
            ->get();

        return view('employee-bonds.index', compact('title', 'subtitle', 'employees'));
    }

    /**
     * Get all employee bonds for DataTables
     */
    public function getBonds(Request $request)
    {
        $bonds = EmployeeBond::with(['employee.administrations', 'letterNumber']);

        // Filter by status
        if (!empty($request->get('status'))) {
            $bonds->where('status', $request->get('status'));
        }

        // Filter by employee
        if (!empty($request->get('employee_id'))) {
            $bonds->where('employee_id', $request->get('employee_id'));
        }

        // Filter by bond name
        if (!empty($request->get('bond_name'))) {
            $bonds->where('bond_name', 'LIKE', '%' . $request->get('bond_name') . '%');
        }

        // Filter by bond number
        if (!empty($request->get('bond_number'))) {
            $bonds->where('employee_bond_number', 'LIKE', '%' . $request->get('bond_number') . '%');
        }

        // Filter by date range
        if (!empty($request->get('date_from')) && !empty($request->get('date_to'))) {
            $bonds->whereBetween('start_date', [
                $request->get('date_from'),
                $request->get('date_to')
            ]);
        }

        // Global search
        if (!empty($request->get('search'))) {
            $search = $request->get('search');
            $bonds->where(function ($query) use ($search) {
                $query->where('bond_name', 'LIKE', "%$search%")
                    ->orWhere('employee_bond_number', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%")
                    ->orWhereHas('employee', function ($q) use ($search) {
                        $q->where('fullname', 'LIKE', "%$search%");
                    })
                    ->orWhereHas('employee.administrations', function ($q) use ($search) {
                        $q->where('nik', 'LIKE', "%$search%");
                    });
            });
        }

        $bonds->orderBy('created_at', 'desc');

        return datatables()->of($bonds)
            ->addIndexColumn()
            ->addColumn('employee', function ($bond) {
                $employee = $bond->employee;
                $nik = $employee->administrations->isNotEmpty() ? $employee->administrations->first()->nik : '-';
                return '<strong>' . $employee->fullname . '</strong><br><small class="text-muted">' . $nik . '</small>';
            })
            ->addColumn('bond_name', function ($bond) {
                return $bond->bond_name ?? '-';
            })
            ->addColumn('bond_number', function ($bond) {
                return $bond->employee_bond_number ?? '-';
            })
            ->addColumn('start_date', function ($bond) {
                return $bond->start_date->format('d/m/Y');
            })
            ->addColumn('end_date', function ($bond) {
                return $bond->end_date->format('d/m/Y');
            })
            ->addColumn('duration', function ($bond) {
                return $bond->total_bond_duration_months . ' months';
            })
            ->addColumn('investment_value', function ($bond) {
                return 'Rp ' . number_format($bond->total_investment_value, 0, ',', '.');
            })
            ->addColumn('status', function ($bond) {
                switch ($bond->status) {
                    case 'active':
                        return '<span class="badge badge-success">Active</span>';
                    case 'completed':
                        return '<span class="badge badge-info">Completed</span>';
                    case 'violated':
                        return '<span class="badge badge-danger">Violated</span>';
                    case 'cancelled':
                        return '<span class="badge badge-secondary">Cancelled</span>';
                    default:
                        return '<span class="badge badge-light">' . ucfirst($bond->status) . '</span>';
                }
            })
            ->addColumn('remaining_days', function ($bond) {
                if ($bond->status == 'active') {
                    return '<strong>' . $bond->remaining_days . '</strong> days';
                }
                return '-';
            })
            ->addColumn('action', function ($bond) {
                $html = '<div class="btn-group" role="group">';
                $html .= '<a href="' . route('employee-bonds.show', $bond->id) . '" class="btn btn-sm btn-info mr-1" title="View"><i class="fas fa-eye"></i></a>';
                $html .= '<a href="' . route('employee-bonds.edit', $bond->id) . '" class="btn btn-sm btn-warning mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
                $html .= '<button class="btn btn-sm btn-danger" onclick="deleteBond(' . $bond->id . ')" title="Delete"><i class="fas fa-trash"></i></button>';
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['employee', 'bond_name', 'status', 'remaining_days', 'action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Create Employee Bond';
        $employees = Employee::join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.is_active', 1)
            ->select('employees.*', 'administrations.nik')
            ->orderBy('administrations.nik', 'asc')
            ->get();

        return view('employee-bonds.create', compact('title', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'bond_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'letter_number_id' => 'required|exists:letter_numbers,id',
            'employee_bond_number' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_bond_duration_months' => 'required|integer|min:1',
            'total_investment_value' => 'required|numeric|min:0',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Handle letter number integration
            $letterNumberRecord = null;
            if ($request->letter_number_id) {
                $letterNumberRecord = LetterNumber::find($request->letter_number_id);
                if (!$letterNumberRecord || $letterNumberRecord->status !== 'reserved') {
                    return redirect()->back()
                        ->with('toast_error', 'Selected letter number is not available or already used')
                        ->withInput();
                }
            }

            $bond = new EmployeeBond();
            $bond->employee_id = $request->employee_id;
            $bond->bond_name = $request->bond_name;
            $bond->description = $request->description;
            $bond->letter_number_id = $request->letter_number_id;
            $bond->employee_bond_number = $request->employee_bond_number;
            $bond->start_date = $request->start_date;
            $bond->end_date = $request->end_date;
            $bond->total_bond_duration_months = $request->total_bond_duration_months;
            $bond->total_investment_value = $request->total_investment_value;
            $bond->status = 'active';

            // Handle document upload
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $timestamp = now()->format('YmdHis');
                $fileName = $originalName . '_' . $timestamp . '.' . $extension;
                $path = $file->storeAs('bonds', $fileName, 'private');
                $bond->document_path = $path;
            }

            $bond->save();

            // Mark letter number as used jika ada
            if ($letterNumberRecord) {
                $letterNumberRecord->markAsUsed('employee_bond', $bond->id);

                // Log the letter number usage for debugging
                Log::info('Letter Number marked as used for Employee Bond', [
                    'letter_number_id' => $letterNumberRecord->id,
                    'letter_number' => $letterNumberRecord->letter_number,
                    'employee_bond_id' => $bond->id,
                    'employee_bond_number' => $bond->employee_bond_number
                ]);
            }

            DB::commit();

            return redirect()->route('employee-bonds.index')
                ->with('toast_success', 'Employee bond created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to create employee bond: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeBond $employeeBond)
    {
        $title = 'Employee Bond Details';
        $employeeBond->load(['employee', 'letterNumber', 'violations']);

        return view('employee-bonds.show', compact('title', 'employeeBond'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeBond $employeeBond)
    {
        $title = 'Edit Employee Bond';
        $employees = Employee::join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.is_active', 1)
            ->select('employees.*', 'administrations.nik')
            ->orderBy('administrations.nik', 'asc')
            ->get();

        return view('employee-bonds.edit', compact('title', 'employeeBond', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeBond $employeeBond)
    {
        $request->validate([
            'bond_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'letter_number_id' => 'required|exists:letter_numbers,id',
            'employee_bond_number' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_bond_duration_months' => 'required|integer|min:1',
            'total_investment_value' => 'required|numeric|min:0',
            'status' => 'required|in:active,completed,violated,cancelled',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Handle letter number change
            $oldLetterNumberId = $employeeBond->letter_number_id;
            $newLetterNumberRecord = null;

            if ($request->letter_number_id && $request->letter_number_id != $oldLetterNumberId) {
                $newLetterNumberRecord = LetterNumber::find($request->letter_number_id);
                if (!$newLetterNumberRecord || $newLetterNumberRecord->status !== 'reserved') {
                    return redirect()->back()
                        ->with('toast_error', 'Selected letter number is not available or already used')
                        ->withInput();
                }
            }

            $employeeBond->bond_name = $request->bond_name;
            $employeeBond->description = $request->description;
            $employeeBond->letter_number_id = $request->letter_number_id;
            $employeeBond->letter_number = $request->letter_number;
            $employeeBond->employee_bond_number = $request->employee_bond_number;
            $employeeBond->start_date = $request->start_date;
            $employeeBond->end_date = $request->end_date;
            $employeeBond->total_bond_duration_months = $request->total_bond_duration_months;
            $employeeBond->total_investment_value = $request->total_investment_value;
            $employeeBond->status = $request->status;

            // Handle document upload
            if ($request->hasFile('document')) {
                // Delete old document
                if ($employeeBond->document_path) {
                    Storage::disk('private')->delete($employeeBond->document_path);
                }

                $file = $request->file('document');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $timestamp = now()->format('YmdHis');
                $fileName = $originalName . '_' . $timestamp . '.' . $extension;
                $path = $file->storeAs('bonds', $fileName, 'private');
                $employeeBond->document_path = $path;
            }

            $employeeBond->save();

            // Handle letter number changes
            if ($newLetterNumberRecord) {
                // Mark new letter number as used
                $newLetterNumberRecord->markAsUsed('employee_bond', $employeeBond->id);

                // Release old letter number if it exists
                if ($oldLetterNumberId) {
                    $oldLetterNumberRecord = LetterNumber::find($oldLetterNumberId);
                    if ($oldLetterNumberRecord && $oldLetterNumberRecord->related_document_type === 'employee_bond' && $oldLetterNumberRecord->related_document_id == $employeeBond->id) {
                        $oldLetterNumberRecord->update([
                            'status' => 'reserved',
                            'related_document_type' => null,
                            'related_document_id' => null,
                            'used_at' => null,
                            'used_by' => null,
                        ]);
                    }
                }

                // Log the letter number usage for debugging
                Log::info('Letter Number changed for Employee Bond', [
                    'old_letter_number_id' => $oldLetterNumberId,
                    'new_letter_number_id' => $newLetterNumberRecord->id,
                    'new_letter_number' => $newLetterNumberRecord->letter_number,
                    'employee_bond_id' => $employeeBond->id,
                    'employee_bond_number' => $employeeBond->employee_bond_number
                ]);
            }

            DB::commit();

            return redirect()->route('employee-bonds.index')
                ->with('toast_success', 'Employee bond updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to update employee bond: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeBond $employeeBond)
    {
        DB::beginTransaction();
        try {
            // Release letter number if it exists
            if ($employeeBond->letter_number_id) {
                $letterNumberRecord = LetterNumber::find($employeeBond->letter_number_id);
                if ($letterNumberRecord && $letterNumberRecord->related_document_type === 'employee_bond' && $letterNumberRecord->related_document_id == $employeeBond->id) {
                    $letterNumberRecord->update([
                        'status' => 'reserved',
                        'related_document_type' => null,
                        'related_document_id' => null,
                        'used_at' => null,
                        'used_by' => null,
                    ]);

                    // Log the letter number release for debugging
                    Log::info('Letter Number released from Employee Bond', [
                        'letter_number_id' => $letterNumberRecord->id,
                        'letter_number' => $letterNumberRecord->letter_number,
                        'employee_bond_id' => $employeeBond->id,
                        'employee_bond_number' => $employeeBond->employee_bond_number
                    ]);
                }
            }

            // Delete all related violations first
            BondViolation::where('employee_bond_id', $employeeBond->id)->delete();

            // Delete document if exists
            if ($employeeBond->document_path) {
                Storage::disk('private')->delete($employeeBond->document_path);
            }

            // Delete the bond
            $employeeBond->delete();

            DB::commit();

            return redirect()->route('employee-bonds.index')
                ->with('toast_success', 'Employee bond and all related violations deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to delete employee bond: ' . $e->getMessage());
        }
    }

    /**
     * Get bonds for specific employee
     */
    public function getEmployeeBonds(Request $request, $employeeId)
    {
        $bonds = EmployeeBond::with(['violations'])
            ->where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bonds);
    }

    /**
     * Check for expiring bonds
     */
    public function checkExpiringBonds()
    {
        $expiringBonds = EmployeeBond::expiringSoon(30)->get();

        return response()->json([
            'count' => $expiringBonds->count(),
            'bonds' => $expiringBonds
        ]);
    }

    /**
     * Download bond document
     */
    public function downloadDocument(EmployeeBond $employeeBond)
    {
        if (!$employeeBond->document_path || !Storage::disk('private')->exists($employeeBond->document_path)) {
            return redirect()->back()->with('toast_error', 'Document not found!');
        }

        return response()->download(storage_path('app/private/' . $employeeBond->document_path));
    }

    /**
     * Mark bond as completed
     */
    public function markAsCompleted(EmployeeBond $employeeBond)
    {
        $employeeBond->update(['status' => 'completed']);

        return redirect()->route('employee-bonds.show', $employeeBond->id)
            ->with('toast_success', 'Bond marked as completed successfully!');
    }

    /**
     * Delete bond document
     */
    public function deleteDocument(EmployeeBond $employeeBond)
    {
        if ($employeeBond->document_path && Storage::disk('private')->exists($employeeBond->document_path)) {
            Storage::disk('private')->delete($employeeBond->document_path);
            $employeeBond->update(['document_path' => null]);
        }

        return redirect()->route('employee-bonds.show', $employeeBond->id)
            ->with('toast_success', 'Document deleted successfully!');
    }
}
