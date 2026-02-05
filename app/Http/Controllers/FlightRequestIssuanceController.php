<?php

namespace App\Http\Controllers;

use App\Models\FlightRequest;
use App\Models\FlightRequestIssuance;
use App\Models\FlightRequestIssuanceDetail;
use App\Models\BusinessPartner;
use App\Models\LetterNumber;
use App\Models\ApprovalPlan;
use App\Http\Controllers\ApprovalPlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlightRequestIssuanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:flight-issuances.create')->only('create', 'store');
        $this->middleware('permission:flight-issuances.edit')->only('edit', 'update');
        $this->middleware('permission:flight-issuances.show')->only('index', 'data', 'show');
        $this->middleware('permission:flight-issuances.delete')->only('destroy');
    }

    /**
     * Display a listing of the issuances
     */
    public function index()
    {
        $title = 'Flight Request Issuances';
        return view('flight-issuances.index', compact('title'));
    }

    /**
     * Get data for DataTables (server-side)
     */
    public function data(Request $request)
    {
        $query = FlightRequestIssuance::with(['businessPartner', 'issuedBy', 'letterNumber', 'issuanceDetails', 'flightRequests'])
            ->select('flight_request_issuances.*')
            ->orderBy('issued_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('issued_number')) {
            $query->where('issued_number', 'like', "%{$request->issued_number}%");
        }

        if ($request->filled('fr_number')) {
            $query->whereHas('flightRequests', function ($q) use ($request) {
                $q->where('form_number', 'like', "%{$request->fr_number}%");
            });
        }

        if ($request->filled('business_partner_id')) {
            $query->where('business_partner_id', $request->business_partner_id);
        }

        if ($request->filled('date_from')) {
            $query->where('issued_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('issued_date', '<=', $request->date_to);
        }

        // Get total records count
        $totalRecords = FlightRequestIssuance::count();
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $issuances = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = $issuances->map(function ($issuance, $index) use ($start) {
            // Get FR Number from related flight requests
            $frNumber = '-';
            if ($issuance->flightRequests && $issuance->flightRequests->count() > 0) {
                $frNumbers = $issuance->flightRequests->pluck('form_number')->filter()->unique()->values();
                $frNumber = $frNumbers->isNotEmpty() ? $frNumbers->join(', ') : '-';
            }

            return [
                'DT_RowIndex' => $start + $index + 1,
                'issued_number' => $issuance->issued_number,
                'issued_date' => $issuance->issued_date ? $issuance->issued_date->format('d/m/Y') : '-',
                'fr_number' => $frNumber,
                'business_partner' => $issuance->businessPartner->bp_name ?? '-',
                'total_tickets' => $issuance->issuanceDetails->count(),
                'total_price' => number_format($issuance->total_ticket_price ?? 0, 0, ',', '.'),
                'issued_by' => $issuance->issuedBy->name ?? '-',
                'actions' => view('flight-issuances.actions', compact('issuance'))->render(),
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
     * Show page to select Flight Requests for creating issuance
     */
    public function selectFlightRequests(Request $request)
    {
        $title = 'Select Flight Requests for LG';
        return view('flight-issuances.select-flight-requests', compact('title'));
    }

    /**
     * Store selected Flight Request IDs in session and redirect to create page
     */
    public function storeSelectedFrs(Request $request)
    {
        $flightRequestIds = $request->input('flight_request_ids', []);

        if (empty($flightRequestIds) || !is_array($flightRequestIds)) {
            return redirect()->route('flight-issuances.select-flight-requests')
                ->with('toast_error', 'Please select at least one Flight Request.');
        }

        // Validate that all FR IDs exist
        $validIds = FlightRequest::whereIn('id', $flightRequestIds)->pluck('id')->toArray();
        if (count($validIds) !== count($flightRequestIds)) {
            return redirect()->route('flight-issuances.select-flight-requests')
                ->with('toast_error', 'One or more selected Flight Requests are invalid.');
        }

        // Validate all can be issued
        $flightRequests = FlightRequest::whereIn('id', $flightRequestIds)->get();
        $invalidRequests = $flightRequests->filter(function ($fr) {
            return !$fr->canBeIssued();
        });

        if ($invalidRequests->count() > 0) {
            $invalidNumbers = $invalidRequests->pluck('form_number')->join(', ');
            return redirect()->route('flight-issuances.select-flight-requests')
                ->with('toast_error', "Flight Request(s) must be approved or issued: {$invalidNumbers}");
        }

        // Store selected FR IDs in session
        session(['selected_flight_request_ids' => $flightRequestIds]);

        // Redirect to create page with clean URL
        return redirect()->route('flight-issuances.create');
    }

    /**
     * Show the form for creating a new issuance
     */
    public function create(Request $request)
    {
        $title = 'Create Flight Issuance';

        // Get FR IDs from session first (from select-flight-requests), then from request (backward compatibility)
        $flightRequestIds = session('selected_flight_request_ids', []);

        // Clear session after reading
        session()->forget('selected_flight_request_ids');

        // Support single flight_request_id for backward compatibility
        if (empty($flightRequestIds) && $request->has('flight_request_id')) {
            $flightRequestIds = [$request->get('flight_request_id')];
        }

        // Support flight_request_ids from query string (backward compatibility)
        if (empty($flightRequestIds)) {
            $flightRequestIds = $request->get('flight_request_ids', []);
        }

        if (empty($flightRequestIds) || !is_array($flightRequestIds)) {
            return redirect()->route('flight-issuances.select-flight-requests')
                ->with('toast_error', 'Please select at least one Flight Request.');
        }

        // Get all selected flight requests
        $flightRequests = FlightRequest::with(['details', 'employee', 'administration'])
            ->whereIn('id', $flightRequestIds)
            ->get();

        // Validate all can be issued
        $invalidRequests = $flightRequests->filter(function ($fr) {
            return !$fr->canBeIssued();
        });

        if ($invalidRequests->count() > 0) {
            $invalidNumbers = $invalidRequests->pluck('form_number')->join(', ');
            return redirect()->route('flight-issuances.select-flight-requests')
                ->with('toast_error', "Flight Request(s) must be approved: {$invalidNumbers}");
        }

        $businessPartners = BusinessPartner::active()->get();
        $letterNumbers = LetterNumber::where('status', 'reserved')
            ->orWhere('status', 'available')
            ->orderBy('letter_number', 'desc')
            ->get();

        return view('flight-issuances.create', compact('flightRequests', 'businessPartners', 'letterNumbers', 'title'));
    }

    /**
     * Store a newly created issuance
     */
    public function store(Request $request)
    {
        // Support both single flight_request_id (backward compatibility) and multiple flight_request_ids
        $flightRequestIds = $request->input('flight_request_ids', []);
        if (empty($flightRequestIds) && $request->has('flight_request_id')) {
            $flightRequestIds = [$request->input('flight_request_id')];
        }

        $validated = $request->validate([
            'flight_request_ids' => 'required|array|min:1',
            'flight_request_ids.*' => 'required|exists:flight_requests,id',
            'issued_number' => 'required|string|max:100|unique:flight_request_issuances,issued_number',
            'issued_date' => 'required|date',
            'letter_number_id' => 'nullable|exists:letter_numbers,id',
            'business_partner_id' => 'nullable|exists:business_partners,id',
            'notes' => 'nullable|string',
            'manual_approvers' => 'nullable|array',
            'manual_approvers.*' => 'exists:users,id',
            'details' => 'required|array|min:1|max:100',
            'details.*.ticket_order' => 'required|integer|min:1',
            'details.*.booking_code' => 'nullable|string|max:50',
            'details.*.detail_reservation' => 'nullable|string',
            'details.*.passenger_name' => 'required|string|max:255',
            'details.*.ticket_price' => 'nullable|numeric|min:0',
            'details.*.service_charge' => 'nullable|numeric|min:0',
            'details.*.service_vat' => 'nullable|numeric|min:0',
            'details.*.company_amount' => 'nullable|numeric|min:0',
            'details.*.employee_amount' => 'nullable|numeric|min:0',
        ]);

        // Get all flight requests
        $flightRequests = FlightRequest::whereIn('id', $validated['flight_request_ids'])->get();

        // Validate all can be issued
        $invalidRequests = $flightRequests->filter(function ($fr) {
            return !$fr->canBeIssued();
        });

        if ($invalidRequests->count() > 0) {
            $invalidNumbers = $invalidRequests->pluck('form_number')->join(', ');
            return back()->withInput()
                ->with('toast_error', "Flight Request(s) must be approved: {$invalidNumbers}");
        }

        DB::beginTransaction();
        try {
            // Create issuance
            $issuance = FlightRequestIssuance::create([
                'issued_number' => $validated['issued_number'],
                'issued_date' => $validated['issued_date'],
                'letter_number_id' => $validated['letter_number_id'] ?? null,
                'letter_number' => $validated['letter_number_id']
                    ? LetterNumber::find($validated['letter_number_id'])->letter_number
                    : null,
                'business_partner_id' => $validated['business_partner_id'] ?? null,
                'issued_by' => Auth::id(),
                'issued_at' => now(),
                'notes' => $validated['notes'] ?? null,
                'manual_approvers' => $validated['manual_approvers'] ?? null,
                'status' => FlightRequestIssuance::STATUS_PENDING,
            ]);

            // Attach to all selected flight requests (Many-to-Many)
            $issuance->flightRequests()->attach($validated['flight_request_ids']);

            // Assign letter number if provided
            if ($validated['letter_number_id']) {
                $issuance->assignLetterNumber($validated['letter_number_id']);
            }

            // Create all ticket details (supports multiple); DB column is advance_amount (form: employee_amount)
            foreach ($validated['details'] as $detail) {
                FlightRequestIssuanceDetail::create([
                    'flight_request_issuance_id' => $issuance->id,
                    'ticket_order' => $detail['ticket_order'],
                    'booking_code' => $detail['booking_code'] ?? null,
                    'detail_reservation' => $detail['detail_reservation'] ?? null,
                    'passenger_name' => $detail['passenger_name'],
                    'ticket_price' => $detail['ticket_price'] ?? null,
                    'service_charge' => $detail['service_charge'] ?? null,
                    'service_vat' => $detail['service_vat'] ?? null,
                    'company_amount' => $detail['company_amount'] ?? null,
                    'advance_amount' => $detail['employee_amount'] ?? null,
                ]);
            }

            // Create approval plans if manual approvers are set
            if (!empty($validated['manual_approvers'])) {
                $response = app(ApprovalPlanController::class)->create_manual_approval_plan('flight_request_issuance', $issuance->id);

                if (!$response || $response === 0) {
                    Log::warning("Failed to create approval plans for flight_request_issuance {$issuance->id}");
                    // Don't rollback, just log warning - approval plans are optional
                }
            }

            // Update all flight requests status to issued
            FlightRequest::whereIn('id', $validated['flight_request_ids'])
                ->update(['status' => FlightRequest::STATUS_ISSUED]);

            DB::commit();

            return redirect()->route('flight-issuances.show', $issuance->id)
                ->with('toast_success', 'Letter of Guarantee issued successfully for ' . $flightRequests->count() . ' Flight Request(s).');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flight Request Issuance creation failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('toast_error', 'Failed to create Issuance: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified issuance
     */
    public function show($id)
    {
        $title = 'Flight Issuance Details';
        $issuance = FlightRequestIssuance::with([
            'flightRequests.details',
            'flightRequests.employee',
            'businessPartner',
            'issuedBy',
            'letterNumber',
            'issuanceDetails'
        ])->findOrFail($id);

        return view('flight-issuances.show', compact('issuance', 'title'));
    }

    /**
     * Show the form for editing the specified issuance
     */
    public function edit($id)
    {
        $title = 'Edit Flight Issuance';
        $issuance = FlightRequestIssuance::with([
            'issuanceDetails',
            'businessPartner',
            'flightRequests.details',
            'flightRequests.employee',
            'flightRequests.administration',
        ])->findOrFail($id);
        $businessPartners = BusinessPartner::active()->get();
        $letterNumbers = LetterNumber::where('status', 'reserved')
            ->orWhere('status', 'available')
            ->orWhere('id', $issuance->letter_number_id)
            ->orderBy('letter_number', 'desc')
            ->get();
        $flightRequests = $issuance->flightRequests;

        return view('flight-issuances.edit', compact('issuance', 'businessPartners', 'letterNumbers', 'title', 'flightRequests'));
    }

    /**
     * Update the specified issuance
     */
    public function update(Request $request, $id)
    {
        $issuance = FlightRequestIssuance::findOrFail($id);

        $validated = $request->validate([
            'issued_number' => 'required|string|max:100|unique:flight_request_issuances,issued_number,' . $id,
            'issued_date' => 'required|date',
            'letter_number_id' => 'nullable|exists:letter_numbers,id',
            'business_partner_id' => 'nullable|exists:business_partners,id',
            'notes' => 'nullable|string',
            'manual_approvers' => 'nullable|array',
            'manual_approvers.*' => 'exists:users,id',
            'details' => 'required|array|min:1|max:100',
            'details.*.id' => 'nullable|exists:flight_request_issuance_details,id',
            'details.*.ticket_order' => 'required|integer|min:1',
            'details.*.booking_code' => 'nullable|string|max:50',
            'details.*.detail_reservation' => 'nullable|string',
            'details.*.passenger_name' => 'required|string|max:255',
            'details.*.ticket_price' => 'nullable|numeric|min:0',
            'details.*.service_charge' => 'nullable|numeric|min:0',
            'details.*.service_vat' => 'nullable|numeric|min:0',
            'details.*.company_amount' => 'nullable|numeric|min:0',
            'details.*.employee_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Check if manual approvers changed
            $currentApprovers = $issuance->manual_approvers ?? [];
            $newApprovers = $validated['manual_approvers'] ?? [];
            $approversChanged = json_encode($currentApprovers) !== json_encode($newApprovers);

            // Update issuance
            $issuance->update([
                'issued_number' => $validated['issued_number'],
                'issued_date' => $validated['issued_date'],
                'business_partner_id' => $validated['business_partner_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'manual_approvers' => !empty($validated['manual_approvers']) ? $validated['manual_approvers'] : null,
            ]);

            // Handle letter number change
            if ($validated['letter_number_id'] != $issuance->letter_number_id) {
                if ($issuance->letter_number_id) {
                    $issuance->releaseLetterNumber();
                }
                if ($validated['letter_number_id']) {
                    $issuance->assignLetterNumber($validated['letter_number_id']);
                }
            }

            // Handle manual approvers change - delete old approval plans and create new ones
            if ($approversChanged) {
                // Delete existing approval plans
                ApprovalPlan::where('document_id', $issuance->id)
                    ->where('document_type', 'flight_request_issuance')
                    ->delete();
                Log::info("Deleted existing approval plans for flight_request_issuance {$issuance->id} due to approver changes");

                // Create new approval plans if manual approvers are set
                if (!empty($validated['manual_approvers'])) {
                    $response = app(ApprovalPlanController::class)->create_manual_approval_plan('flight_request_issuance', $issuance->id);

                    if (!$response || $response === 0) {
                        Log::warning("Failed to create approval plans for flight_request_issuance {$issuance->id}");
                        // Don't rollback, just log warning - approval plans are optional
                    }
                }
            }

            // Update or create details (support multiple ticket details; only trust ids that belong to this issuance)
            $existingIds = [];
            foreach ($validated['details'] as $detail) {
                if (!empty($detail['id'])) {
                    $existingDetail = FlightRequestIssuanceDetail::where('id', $detail['id'])
                        ->where('flight_request_issuance_id', $issuance->id)
                        ->first();
                    if ($existingDetail) {
                        $existingDetail->update([
                            'ticket_order' => $detail['ticket_order'],
                            'booking_code' => $detail['booking_code'] ?? null,
                            'detail_reservation' => $detail['detail_reservation'] ?? null,
                            'passenger_name' => $detail['passenger_name'],
                            'ticket_price' => $detail['ticket_price'] ?? null,
                            'service_charge' => $detail['service_charge'] ?? null,
                            'service_vat' => $detail['service_vat'] ?? null,
                            'company_amount' => $detail['company_amount'] ?? null,
                            'advance_amount' => $detail['employee_amount'] ?? null,
                        ]);
                        $existingIds[] = $existingDetail->id;
                    }
                    // If id was sent but doesn't belong to this issuance, skip (ignore tampered data)
                } else {
                    // Create new ticket detail and add to existingIds so it is not deleted below
                    $newDetail = FlightRequestIssuanceDetail::create([
                        'flight_request_issuance_id' => $issuance->id,
                        'ticket_order' => $detail['ticket_order'],
                        'booking_code' => $detail['booking_code'] ?? null,
                        'detail_reservation' => $detail['detail_reservation'] ?? null,
                        'passenger_name' => $detail['passenger_name'],
                        'ticket_price' => $detail['ticket_price'] ?? null,
                        'service_charge' => $detail['service_charge'] ?? null,
                        'service_vat' => $detail['service_vat'] ?? null,
                        'company_amount' => $detail['company_amount'] ?? null,
                        'advance_amount' => $detail['employee_amount'] ?? null,
                    ]);
                    $existingIds[] = $newDetail->id;
                }
            }

            // Delete details that were removed from the form (only those belonging to this issuance)
            FlightRequestIssuanceDetail::where('flight_request_issuance_id', $issuance->id)
                ->whereNotIn('id', $existingIds)
                ->delete();

            DB::commit();

            return redirect()->route('flight-issuances.show', $issuance->id)
                ->with('toast_success', 'Issuance updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flight Request Issuance update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('toast_error', 'Failed to update Issuance: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified issuance and related approval plans.
     * Jika FR setelah delete tidak punya issuance lagi, status FR di-rollback jadi approved.
     */
    public function destroy($id)
    {
        $issuance = FlightRequestIssuance::with('flightRequests')->findOrFail($id);

        // Simpan ID FR yang terhubung sebelum delete (untuk cek setelah delete)
        $linkedFlightRequestIds = $issuance->flightRequests->pluck('id')->toArray();

        DB::beginTransaction();
        try {
            // Hapus approval plans yang berkaitan
            $deletedPlans = ApprovalPlan::where('document_id', $issuance->id)
                ->where('document_type', 'flight_request_issuance')
                ->delete();

            if ($deletedPlans > 0) {
                Log::info("Deleted {$deletedPlans} approval plan(s) for flight_request_issuance {$issuance->id}");
            }

            // Detach flight requests (pivot)
            $issuance->flightRequests()->detach();

            // Hapus detail tiket
            $issuance->issuanceDetails()->delete();

            // Hapus issuance (HasLetterNumber trait akan release letter number saat deleting)
            $issuance->delete();

            // Jika FR yang tadinya terhubung sekarang tidak punya issuance lagi, rollback status jadi approved
            foreach ($linkedFlightRequestIds as $frId) {
                $fr = FlightRequest::find($frId);
                if (!$fr) {
                    continue;
                }
                $issuanceCount = $fr->issuances()->count();
                if ($issuanceCount === 0 && $fr->status === 'issued') {
                    $fr->update(['status' => 'approved']);
                    Log::info("Rolled back Flight Request {$fr->id} ({$fr->form_number}) status to approved (no issuances left).");
                }
            }

            DB::commit();

            return redirect()->route('flight-issuances.index')
                ->with('toast_success', 'Letter of Guarantee deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flight Request Issuance delete failed: ' . $e->getMessage());

            return back()->with('toast_error', 'Failed to delete Issuance: ' . $e->getMessage());
        }
    }

    /**
     * Get available letter numbers (AJAX)
     */
    public function getLetterNumbers(Request $request)
    {
        $letterNumbers = LetterNumber::where('status', 'reserved')
            ->orWhere('status', 'available')
            ->orderBy('letter_number', 'desc')
            ->get()
            ->map(function ($ln) {
                return [
                    'id' => $ln->id,
                    'letter_number' => $ln->letter_number,
                    'status' => $ln->status,
                ];
            });

        return response()->json($letterNumbers);
    }

    /**
     * Print Letter of Guarantee
     */
    public function print($id)
    {
        $issuance = FlightRequestIssuance::with([
            'flightRequests.details',
            'businessPartner',
            'issuedBy',
            'issuanceDetails'
        ])->findOrFail($id);

        return view('flight-issuances.print', compact('issuance'));
    }

}
