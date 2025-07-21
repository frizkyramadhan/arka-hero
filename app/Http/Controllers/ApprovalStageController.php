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
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Controller for Approval Stage Management
 */
class ApprovalStageController extends Controller
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
     * Display a listing of stages for a specific flow
     */
    public function index(ApprovalFlow $flow): View
    {
        $flow->load(['stages.approvers']);

        return view('approval.stages.index', compact('flow'));
    }

    /**
     * Show the form for creating a new stage
     */
    public function create(ApprovalFlow $flow): View
    {
        // Get users, roles, and departments for approver assignment
        $users = User::orderBy('name')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        // Get next stage order
        $nextOrder = $flow->stages()->max('stage_order') + 1;

        return view('approval.stages.create', compact('flow', 'users', 'roles', 'departments', 'nextOrder'));
    }

    /**
     * Store a newly created stage
     */
    public function store(Request $request, ApprovalFlow $flow): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:255',
            'stage_order' => 'required|integer|min:1',
            'stage_type' => 'required|in:sequential,parallel',
            'is_mandatory' => 'boolean',
            'escalation_hours' => 'integer|min:1',
            'approvers' => 'array',
            'approvers.*.approver_type' => 'required|in:user,role,department',
            'approvers.*.approver_id' => 'required|integer',
            'approvers.*.is_backup' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Adjust stage orders if necessary
            $this->adjustStageOrders($flow->id, $request->stage_order);

            $stageData = $request->only(['stage_name', 'stage_order', 'stage_type', 'is_mandatory', 'escalation_hours']);
            $stageData['approval_flow_id'] = $flow->id;

            $stage = $this->flowService->createStage($flow->id, $stageData);

            // Create approvers if provided
            if ($request->has('approvers')) {
                foreach ($request->approvers as $approverData) {
                    $this->flowService->createApprover($stage->id, $approverData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stage created successfully',
                'data' => $stage->load(['approvers']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create approval stage', [
                'flow_id' => $flow->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create stage: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified stage
     */
    public function show(ApprovalFlow $flow, ApprovalStage $stage): View
    {
        $stage->load(['approvers', 'approvalActions']);

        return view('approval.stages.show', compact('flow', 'stage'));
    }

    /**
     * Show the form for editing the specified stage
     */
    public function edit(ApprovalFlow $flow, ApprovalStage $stage): View
    {
        $stage->load(['approvers']);

        // Get users, roles, and departments for approver assignment
        $users = User::orderBy('name')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('approval.stages.edit', compact('flow', 'stage', 'users', 'roles', 'departments'));
    }

    /**
     * Update the specified stage
     */
    public function update(Request $request, ApprovalFlow $flow, ApprovalStage $stage): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:255',
            'stage_order' => 'required|integer|min:1',
            'stage_type' => 'required|in:sequential,parallel',
            'is_mandatory' => 'boolean',
            'escalation_hours' => 'integer|min:1',
            'approvers' => 'array',
            'approvers.*.approver_type' => 'required|in:user,role,department',
            'approvers.*.approver_id' => 'required|integer',
            'approvers.*.is_backup' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check if stage order changed
            if ($stage->stage_order != $request->stage_order) {
                $this->adjustStageOrders($flow->id, $request->stage_order, $stage->id);
            }

            // Update stage
            $stage->update($request->only(['stage_name', 'stage_order', 'stage_type', 'is_mandatory', 'escalation_hours']));

            // Update approvers - delete existing and create new ones
            $stage->approvers()->delete();

            if ($request->has('approvers')) {
                foreach ($request->approvers as $approverData) {
                    $this->flowService->createApprover($stage->id, $approverData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stage updated successfully',
                'data' => $stage->load(['approvers']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update approval stage', [
                'stage_id' => $stage->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update stage: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified stage
     */
    public function destroy(ApprovalFlow $flow, ApprovalStage $stage): JsonResponse
    {
        try {
            // Check if stage has active approvals
            $activeApprovals = $stage->documentApprovals()->where('overall_status', 'pending')->count();
            if ($activeApprovals > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete stage: {$activeApprovals} active approvals are at this stage",
                    'active_approvals_count' => $activeApprovals,
                ], 400);
            }

            DB::beginTransaction();

            $stageOrder = $stage->stage_order;

            // Delete the stage (approvers will be deleted via cascade)
            $stage->delete();

            // Reorder remaining stages
            $flow->stages()->where('stage_order', '>', $stageOrder)->decrement('stage_order');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stage deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete approval stage', [
                'stage_id' => $stage->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete stage: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder stages within a flow
     */
    public function reorder(Request $request, ApprovalFlow $flow): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stages' => 'required|array',
            'stages.*.id' => 'required|integer|exists:approval_stages,id',
            'stages.*.stage_order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->stages as $stageData) {
                ApprovalStage::where('id', $stageData['id'])
                    ->where('approval_flow_id', $flow->id)
                    ->update(['stage_order' => $stageData['stage_order']]);
            }

            DB::commit();

            // Log audit
            $this->auditService->logApprovalAction(0, auth()->id(), 'stages_reordered', [
                'flow_id' => $flow->id,
                'stages' => $request->stages,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stages reordered successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reorder approval stages', [
                'flow_id' => $flow->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder stages: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get stage configuration data
     */
    public function getConfiguration(ApprovalFlow $flow, ApprovalStage $stage): JsonResponse
    {
        try {
            $stage->load(['approvers']);

            $configuration = [
                'stage' => $stage,
                'approvers' => $stage->approvers->map(function ($approver) {
                    return [
                        'id' => $approver->id,
                        'approver_type' => $approver->approver_type,
                        'approver_id' => $approver->approver_id,
                        'approver_name' => $this->getApproverName($approver),
                        'is_backup' => $approver->is_backup,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $configuration,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get stage configuration', [
                'stage_id' => $stage->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get stage configuration',
            ], 500);
        }
    }

    /**
     * Duplicate a stage
     */
    public function duplicate(ApprovalFlow $flow, ApprovalStage $stage): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get the next available stage order
            $nextOrder = $flow->stages()->max('stage_order') + 1;

            // Create new stage data
            $newStageData = [
                'stage_name' => $stage->stage_name . ' (Copy)',
                'stage_order' => $nextOrder,
                'stage_type' => $stage->stage_type,
                'is_mandatory' => $stage->is_mandatory,
                'escalation_hours' => $stage->escalation_hours,
            ];

            $newStage = $this->flowService->createStage($flow->id, $newStageData);

            // Duplicate approvers
            foreach ($stage->approvers as $approver) {
                $approverData = [
                    'approver_type' => $approver->approver_type,
                    'approver_id' => $approver->approver_id,
                    'is_backup' => $approver->is_backup,
                ];
                $this->flowService->createApprover($newStage->id, $approverData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stage duplicated successfully',
                'data' => $newStage->load(['approvers']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to duplicate approval stage', [
                'stage_id' => $stage->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate stage: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Adjust stage orders when inserting a new stage
     */
    private function adjustStageOrders(int $flowId, int $newOrder, ?int $excludeStageId = null): void
    {
        $query = ApprovalStage::where('approval_flow_id', $flowId)
            ->where('stage_order', '>=', $newOrder);

        if ($excludeStageId) {
            $query->where('id', '!=', $excludeStageId);
        }

        $query->increment('stage_order');
    }

    /**
     * Get approver name based on type
     */
    private function getApproverName(ApprovalStageApprover $approver): string
    {
        switch ($approver->approver_type) {
            case 'user':
                return $approver->userApprover->name ?? 'Unknown User';
            case 'role':
                return $approver->roleApprover->name ?? 'Unknown Role';
            case 'department':
                return $approver->departmentApprover->name ?? 'Unknown Department';
            default:
                return 'Unknown';
        }
    }
}
