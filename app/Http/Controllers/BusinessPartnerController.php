<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BusinessPartner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BusinessPartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:business-partners.show')->only('index', 'data');
        $this->middleware('permission:business-partners.create')->only('create', 'store');
        $this->middleware('permission:business-partners.edit')->only('edit', 'update');
        $this->middleware('permission:business-partners.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('business-partners.index')->with('title', 'Business Partners');
    }

    /**
     * Get data for DataTables (server-side)
     */
    public function data(Request $request)
    {
        $query = BusinessPartner::select('business_partners.*')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bp_code')) {
            $query->where('bp_code', 'like', "%{$request->bp_code}%");
        }

        if ($request->filled('bp_name')) {
            $query->where('bp_name', 'like', "%{$request->bp_name}%");
        }

        // Get total records count
        $totalRecords = BusinessPartner::count();
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $businessPartners = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = $businessPartners->map(function ($bp, $index) use ($start) {
            $statusBadge = $bp->status === 'active'
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-secondary">Inactive</span>';

            $actions = '<div class="btn-group">';
            $actions .= '<a href="' . route('business-partners.edit', $bp->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
            $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteBusinessPartner(\'' . $bp->id . '\', \'' . addslashes($bp->bp_name) . '\')" title="Delete"><i class="fas fa-trash"></i></button>';
            $actions .= '</div>';

            return [
                'DT_RowIndex' => $start + $index + 1,
                'bp_code' => $bp->bp_code,
                'bp_name' => $bp->bp_name,
                'bp_phone' => $bp->bp_phone ?? '-',
                'bp_address' => Str::limit($bp->bp_address ?? '-', 50),
                'status' => $statusBadge,
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Create Business Partner';

        return view('business-partners.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bp_code' => 'required|string|max:50|unique:business_partners,bp_code',
            'bp_name' => 'required|string|max:255',
            'bp_address' => 'nullable|string',
            'bp_phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            BusinessPartner::create($validated);

            return redirect()->route('business-partners.index')
                ->with('toast_success', 'Business Partner created successfully.');
        } catch (\Exception $e) {
            Log::error('Business Partner creation failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('toast_error', 'Failed to create Business Partner.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $title = 'Edit Business Partner';
        $businessPartner = BusinessPartner::findOrFail($id);
        return view('business-partners.edit', compact('businessPartner', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $businessPartner = BusinessPartner::findOrFail($id);

        $validated = $request->validate([
            'bp_code' => 'required|string|max:50|unique:business_partners,bp_code,' . $id,
            'bp_name' => 'required|string|max:255',
            'bp_address' => 'nullable|string',
            'bp_phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            $businessPartner->update($validated);

            return redirect()->route('business-partners.index')
                ->with('toast_success', 'Business Partner updated successfully.');
        } catch (\Exception $e) {
            Log::error('Business Partner update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('toast_error', 'Failed to update Business Partner.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $businessPartner = BusinessPartner::findOrFail($id);

        // Check if used in issuances
        if ($businessPartner->issuances()->exists()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete Business Partner that is used in issuances.'
                ], 422);
            }
            return back()->with('toast_error', 'Cannot delete Business Partner that is used in issuances.');
        }

        try {
            $businessPartner->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Business Partner deleted successfully.'
                ]);
            }

            return redirect()->route('business-partners.index')
                ->with('toast_success', 'Business Partner deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Business Partner deletion failed: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete Business Partner.'
                ], 500);
            }

            return back()->with('toast_error', 'Failed to delete Business Partner.');
        }
    }
}
