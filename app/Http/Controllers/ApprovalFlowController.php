<?php

namespace App\Http\Controllers;

use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use App\Models\User;
use App\Models\Department;
use App\Services\ApprovalFlowService;
use App\Services\ApprovalAuditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Admin Controller for Approval Flow Management
 */
class ApprovalFlowController extends Controller
{
    protected ApprovalFlowService $flowService;
    protected ApprovalAuditService $auditService;

    public function __construct(
        ApprovalFlowService $flowService,
        ApprovalAuditService $auditService
    ) {
        $this->flowService = $flowService;
        $this->auditService = $auditService;

        // Apply middleware for admin access
        $this->middleware('auth');
        $this->middleware('role:administrator');
    }

    /**
     * Display a listing of approval flows
     */
    public function index(Request $request): View
    {
        $title = 'Approval Flow Management';
        $query = ApprovalFlow::with(['stages', 'creator']);

        // Apply filters
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $flows = $query->orderBy('name')->paginate(15);

        // Get document types for filter
        $documentTypes = ApprovalFlow::distinct()->pluck('document_type')->sort();

        return view('approval.flows.index', compact('flows', 'documentTypes', 'title'));
    }

    /**
     * Show the form for creating a new approval flow
     */
    public function create(): View
    {
        $title = 'Create Approval Flow';
        // Get available document types
        $documentTypes = $this->getAvailableDocumentTypes();

        // Get users, roles, and departments for approver assignment
        $users = User::orderBy('name')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        $departments = Department::orderBy('department_name')->get();

        // Get flow templates
        $templates = $this->getFlowTemplates();

        return view('approval.flows.create', compact(
            'documentTypes',
            'users',
            'roles',
            'departments',
            'templates',
            'title'
        ));
    }

    /**
     * Store a newly created approval flow
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|string|max:100',
            'is_active' => 'boolean',
            'stages' => 'array',
            'stages.*.stage_name' => 'required|string|max:255',
            'stages.*.stage_order' => 'required|integer|min:1',
            'stages.*.stage_type' => 'required|in:sequential,parallel',
            'stages.*.is_mandatory' => 'boolean',
            'stages.*.escalation_hours' => 'integer|min:1',
            'stages.*.approvers' => 'array',
            'stages.*.approvers.*.approver_type' => 'required|in:user,role,department',
            'stages.*.approvers.*.approver_id' => 'required|integer',
            'stages.*.approvers.*.is_backup' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $flowData = $request->only(['name', 'description', 'document_type', 'is_active']);
            $flowData['created_by'] = auth()->id();
            $stagesData = $request->input('stages', []);

            $flow = $this->flowService->createFlow($flowData, $stagesData);

            // Log audit
            $this->auditService->logApprovalFlowCreation($flowData, auth()->user());

            DB::commit();

            return redirect()->route('approval.flows.index')
                ->with('toast_success', 'Approval flow created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create approval flow', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return redirect()->back()
                ->with('toast_error', 'Failed to create approval flow: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified approval flow
     */
    public function show(ApprovalFlow $flow): View
    {
        $title = 'Approval Flow Details';
        $flow->load(['stages.approvers', 'stages.actions', 'creator']);

        // Get statistics for this flow
        $statistics = $this->flowService->getFlowStatistics($flow->id);

        return view('approval.flows.show', compact('flow', 'statistics', 'title'));
    }

    /**
     * Show the form for editing the specified approval flow
     */
    public function edit(ApprovalFlow $flow): View
    {
        $title = 'Approval Flow Details';
        $flow->load(['stages.approvers']);

        // Get available document types
        $documentTypes = $this->getAvailableDocumentTypes();

        // Get users, roles, and departments for approver assignment
        $users = User::orderBy('name')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('approval.flows.edit', compact(
            'flow',
            'documentTypes',
            'users',
            'roles',
            'departments',
            'title'
        ));
    }

    /**
     * Update the specified approval flow
     */
    public function update(Request $request, ApprovalFlow $flow): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|string|max:100',
            'is_active' => 'boolean',
            'stages' => 'array',
            'stages.*.stage_name' => 'required|string|max:255',
            'stages.*.stage_order' => 'required|integer|min:1',
            'stages.*.stage_type' => 'required|in:sequential,parallel',
            'stages.*.is_mandatory' => 'boolean',
            'stages.*.escalation_hours' => 'integer|min:1',
            'stages.*.approvers' => 'array',
            'stages.*.approvers.*.approver_type' => 'required|in:user,role,department',
            'stages.*.approvers.*.approver_id' => 'required|integer',
            'stages.*.approvers.*.is_backup' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $flowData = $request->only(['name', 'description', 'document_type', 'is_active']);
            $stagesData = $request->input('stages', []);

            $oldFlowData = $flow->toArray();
            $flow = $this->flowService->updateFlow($flow->id, $flowData, $stagesData);

            // Log audit
            $this->auditService->logApprovalFlowModification(
                $flow->id,
                $oldFlowData,
                $flow->toArray(),
                auth()->user()
            );

            DB::commit();

            return redirect()->route('approval.flows.index')
                ->with('toast_success', 'Approval flow updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update approval flow', [
                'flow_id' => $flow->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return redirect()->back()
                ->with('toast_error', 'Failed to update approval flow: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified approval flow
     */
    public function destroy(ApprovalFlow $flow): RedirectResponse
    {
        try {
            // Check if flow is being used
            $activeApprovals = $flow->documentApprovals()->where('overall_status', 'pending')->count();
            if ($activeApprovals > 0) {
                return redirect()->back()
                    ->with('toast_error', "Cannot delete flow: {$activeApprovals} active approvals are using this flow.");
            }

            $flowData = $flow->toArray();
            $this->flowService->deleteFlow($flow->id);

            // Log audit
            $this->auditService->logApprovalFlowDeletion($flow->id, $flowData, auth()->user());

            return redirect()->route('approval.flows.index')
                ->with('toast_success', 'Approval flow deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete approval flow', [
                'flow_id' => $flow->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('toast_error', 'Failed to delete approval flow: ' . $e->getMessage());
        }
    }

    /**
     * Clone the specified approval flow
     */
    public function clone(Request $request, ApprovalFlow $flow): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'new_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $newName = $request->input('new_name');
            $clonedFlow = $this->flowService->cloneFlow($flow->id, $newName);

            // Log audit
            $this->auditService->logApprovalFlowCreation([
                'name' => $newName,
                'cloned_from' => $flow->id,
            ], auth()->user());

            return redirect()->route('approval.flows.index')
                ->with('toast_success', 'Approval flow cloned successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to clone approval flow', [
                'flow_id' => $flow->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('toast_error', 'Failed to clone approval flow: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get available document types
     */
    private function getAvailableDocumentTypes(): array
    {
        return [
            'officialtravel' => 'Official Travel',
            'recruitment_request' => 'Recruitment Request (FPTK)',
            'employee_registration' => 'Employee Registration',
            // 'leave_request' => 'Leave Request',
            // 'expense_claim' => 'Expense Claim',
            // 'purchase_request' => 'Purchase Request',
            // 'contract_approval' => 'Contract Approval',
            // 'policy_approval' => 'Policy Approval',
        ];
    }

    /**
     * Get flow templates
     */
    private function getFlowTemplates(): array
    {
        return [
            'simple' => [
                'name' => 'Simple Approval',
                'description' => 'Single stage approval with one approver',
                'stages' => [
                    [
                        'stage_name' => 'Approval',
                        'stage_order' => 1,
                        'stage_type' => 'sequential',
                        'is_mandatory' => true,
                        'escalation_hours' => 72,
                    ]
                ]
            ],
            'linear' => [
                'name' => 'Linear Approval',
                'description' => 'Multiple stages in sequence',
                'stages' => [
                    [
                        'stage_name' => 'First Approval',
                        'stage_order' => 1,
                        'stage_type' => 'sequential',
                        'is_mandatory' => true,
                        'escalation_hours' => 48,
                    ],
                    [
                        'stage_name' => 'Final Approval',
                        'stage_order' => 2,
                        'stage_type' => 'sequential',
                        'is_mandatory' => true,
                        'escalation_hours' => 72,
                    ]
                ]
            ],
            'parallel' => [
                'name' => 'Parallel Approval',
                'description' => 'Multiple approvers at the same stage',
                'stages' => [
                    [
                        'stage_name' => 'Parallel Approval',
                        'stage_order' => 1,
                        'stage_type' => 'parallel',
                        'is_mandatory' => true,
                        'escalation_hours' => 72,
                    ]
                ]
            ],
            'three_stage' => [
                'name' => 'Three Stage Approval',
                'description' => 'Three sequential approval stages',
                'stages' => [
                    [
                        'stage_name' => 'Initial Review',
                        'stage_order' => 1,
                        'stage_type' => 'sequential',
                        'is_mandatory' => true,
                        'escalation_hours' => 24,
                    ],
                    [
                        'stage_name' => 'Manager Approval',
                        'stage_order' => 2,
                        'stage_type' => 'sequential',
                        'is_mandatory' => true,
                        'escalation_hours' => 48,
                    ],
                    [
                        'stage_name' => 'Final Approval',
                        'stage_order' => 3,
                        'stage_type' => 'sequential',
                        'is_mandatory' => true,
                        'escalation_hours' => 72,
                    ]
                ]
            ]
        ];
    }
}
