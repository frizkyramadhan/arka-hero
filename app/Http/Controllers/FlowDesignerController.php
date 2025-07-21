<?php

namespace App\Http\Controllers;

use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FlowDesignerController extends Controller
{
    /**
     * Show flow designer interface
     */
    public function index(ApprovalFlow $flow)
    {
        $flow->load(['stages.approvers.user', 'stages.approvers.role', 'stages.approvers.department']);

        return view('approval.designer.index', compact('flow'));
    }

    /**
     * Create new flow from designer
     */
    public function create()
    {
        $templates = $this->getFlowTemplates();

        return view('approval.designer.create', compact('templates'));
    }

    /**
     * Store new flow from designer
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'document_type' => 'required|string|max:100',
                'stages' => 'required|array|min:1',
                'stages.*.stage_name' => 'required|string|max:255',
                'stages.*.stage_order' => 'required|integer|min:1',
                'stages.*.stage_type' => 'required|in:sequential,parallel',
                'stages.*.is_mandatory' => 'boolean',
                'stages.*.escalation_hours' => 'nullable|integer|min:1',
                'stages.*.approvers' => 'nullable|array',
                'stages.*.approvers.*.approver_type' => 'required_with:stages.*.approvers|in:user,role,department',
                'stages.*.approvers.*.approver_id' => 'required_with:stages.*.approvers|integer',
                'stages.*.approvers.*.is_backup' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create approval flow
            $flow = ApprovalFlow::create([
                'name' => $request->name,
                'description' => $request->description,
                'document_type' => $request->document_type,
                'created_by' => auth()->id(),
            ]);

            // Create stages
            foreach ($request->stages as $stageData) {
                $stage = ApprovalStage::create([
                    'approval_flow_id' => $flow->id,
                    'stage_name' => $stageData['stage_name'],
                    'stage_order' => $stageData['stage_order'],
                    'stage_type' => $stageData['stage_type'],
                    'is_mandatory' => $stageData['is_mandatory'] ?? true,
                    'escalation_hours' => $stageData['escalation_hours'] ?? 72,
                ]);

                // Create approvers for this stage
                if (isset($stageData['approvers']) && is_array($stageData['approvers'])) {
                    foreach ($stageData['approvers'] as $approverData) {
                        ApprovalStageApprover::create([
                            'approval_stage_id' => $stage->id,
                            'approver_type' => $approverData['approver_type'],
                            'approver_id' => $approverData['approver_id'],
                            'is_backup' => $approverData['is_backup'] ?? false,
                            'approval_condition' => $approverData['approval_condition'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            // Log the creation
            Log::info('Approval flow created from designer', [
                'flow_id' => $flow->id,
                'flow_name' => $flow->name,
                'stages_count' => count($request->stages),
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Approval flow created successfully',
                'flow_id' => $flow->id,
                'redirect_url' => route('approval.flows.show', $flow)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create approval flow from designer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create approval flow: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update flow from designer
     */
    public function update(Request $request, ApprovalFlow $flow): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'stages' => 'required|array|min:1',
                'stages.*.id' => 'nullable|integer|exists:approval_stages,id',
                'stages.*.stage_name' => 'required|string|max:255',
                'stages.*.stage_order' => 'required|integer|min:1',
                'stages.*.stage_type' => 'required|in:sequential,parallel',
                'stages.*.is_mandatory' => 'boolean',
                'stages.*.escalation_hours' => 'nullable|integer|min:1',
                'stages.*.approvers' => 'nullable|array',
                'stages.*.approvers.*.approver_type' => 'required_with:stages.*.approvers|in:user,role,department',
                'stages.*.approvers.*.approver_id' => 'required_with:stages.*.approvers|integer',
                'stages.*.approvers.*.is_backup' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update flow
            $flow->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // Get existing stage IDs
            $existingStageIds = $flow->stages->pluck('id')->toArray();
            $newStageIds = [];

            // Process stages
            foreach ($request->stages as $stageData) {
                if (isset($stageData['id']) && $stageData['id']) {
                    // Update existing stage
                    $stage = ApprovalStage::find($stageData['id']);
                    if ($stage) {
                        $stage->update([
                            'stage_name' => $stageData['stage_name'],
                            'stage_order' => $stageData['stage_order'],
                            'stage_type' => $stageData['stage_type'],
                            'is_mandatory' => $stageData['is_mandatory'] ?? true,
                            'escalation_hours' => $stageData['escalation_hours'] ?? 72,
                        ]);
                        $newStageIds[] = $stage->id;
                    }
                } else {
                    // Create new stage
                    $stage = ApprovalStage::create([
                        'approval_flow_id' => $flow->id,
                        'stage_name' => $stageData['stage_name'],
                        'stage_order' => $stageData['stage_order'],
                        'stage_type' => $stageData['stage_type'],
                        'is_mandatory' => $stageData['is_mandatory'] ?? true,
                        'escalation_hours' => $stageData['escalation_hours'] ?? 72,
                    ]);
                    $newStageIds[] = $stage->id;
                }

                // Handle approvers for this stage
                if (isset($stageData['approvers']) && is_array($stageData['approvers'])) {
                    // Remove existing approvers
                    $stage->approvers()->delete();

                    // Create new approvers
                    foreach ($stageData['approvers'] as $approverData) {
                        ApprovalStageApprover::create([
                            'approval_stage_id' => $stage->id,
                            'approver_type' => $approverData['approver_type'],
                            'approver_id' => $approverData['approver_id'],
                            'is_backup' => $approverData['is_backup'] ?? false,
                            'approval_condition' => $approverData['approval_condition'] ?? null,
                        ]);
                    }
                }
            }

            // Remove stages that are no longer in the flow
            $stagesToDelete = array_diff($existingStageIds, $newStageIds);
            if (!empty($stagesToDelete)) {
                ApprovalStage::whereIn('id', $stagesToDelete)->delete();
            }

            DB::commit();

            // Log the update
            Log::info('Approval flow updated from designer', [
                'flow_id' => $flow->id,
                'flow_name' => $flow->name,
                'stages_count' => count($request->stages),
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Approval flow updated successfully',
                'flow_id' => $flow->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update approval flow from designer', [
                'flow_id' => $flow->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update approval flow: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get flow data for designer
     */
    public function getFlowData(ApprovalFlow $flow): JsonResponse
    {
        try {
            $flow->load(['stages.approvers.user', 'stages.approvers.role', 'stages.approvers.department']);

            $flowData = [
                'id' => $flow->id,
                'name' => $flow->name,
                'description' => $flow->description,
                'document_type' => $flow->document_type,
                'stages' => $flow->stages->map(function ($stage) {
                    return [
                        'id' => $stage->id,
                        'stage_name' => $stage->stage_name,
                        'stage_order' => $stage->stage_order,
                        'stage_type' => $stage->stage_type,
                        'is_mandatory' => $stage->is_mandatory,
                        'escalation_hours' => $stage->escalation_hours,
                        'approvers' => $stage->approvers->map(function ($approver) {
                            return [
                                'id' => $approver->id,
                                'approver_type' => $approver->approver_type,
                                'approver_id' => $approver->approver_id,
                                'is_backup' => $approver->is_backup,
                                'approval_condition' => $approver->approval_condition,
                                'approver_name' => $this->getApproverName($approver),
                            ];
                        })
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'flow' => $flowData
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get flow data for designer', [
                'flow_id' => $flow->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get flow data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test flow with sample data
     */
    public function testFlow(Request $request, ApprovalFlow $flow): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'test_data' => 'required|array',
                'test_data.document_type' => 'required|string',
                'test_data.document_id' => 'required|string',
                'test_data.submitted_by' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simulate approval flow
            $testResult = $this->simulateApprovalFlow($flow, $request->test_data);

            return response()->json([
                'success' => true,
                'test_result' => $testResult
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to test approval flow', [
                'flow_id' => $flow->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to test approval flow: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get flow templates
     */
    private function getFlowTemplates(): array
    {
        return [
            'linear' => [
                'name' => 'Linear Approval',
                'description' => 'Sequential approval flow with multiple stages',
                'stages' => [
                    ['name' => 'Initial Review', 'type' => 'sequential'],
                    ['name' => 'Manager Approval', 'type' => 'sequential'],
                    ['name' => 'Final Approval', 'type' => 'sequential'],
                ]
            ],
            'parallel' => [
                'name' => 'Parallel Approval',
                'description' => 'Multiple approvers can approve simultaneously',
                'stages' => [
                    ['name' => 'Department Heads', 'type' => 'parallel'],
                    ['name' => 'Final Approval', 'type' => 'sequential'],
                ]
            ],
            'conditional' => [
                'name' => 'Conditional Approval',
                'description' => 'Approval flow with conditional stages',
                'stages' => [
                    ['name' => 'Initial Review', 'type' => 'sequential'],
                    ['name' => 'Finance Review', 'type' => 'sequential'],
                    ['name' => 'Executive Approval', 'type' => 'sequential'],
                ]
            ],
        ];
    }

    /**
     * Get approver name
     */
    private function getApproverName($approver): string
    {
        if ($approver->user) return $approver->user->name;
        if ($approver->role) return $approver->role->name;
        if ($approver->department) return $approver->department->name;
        return 'Unknown';
    }

    /**
     * Simulate approval flow
     */
    private function simulateApprovalFlow(ApprovalFlow $flow, array $testData): array
    {
        $result = [
            'flow_name' => $flow->name,
            'total_stages' => $flow->stages->count(),
            'current_stage' => 1,
            'stages' => [],
            'estimated_time' => 0,
        ];

        foreach ($flow->stages as $index => $stage) {
            $stageResult = [
                'stage_name' => $stage->stage_name,
                'stage_order' => $stage->stage_order,
                'stage_type' => $stage->stage_type,
                'approvers_count' => $stage->approvers->count(),
                'estimated_hours' => $stage->escalation_hours ?? 72,
            ];

            $result['stages'][] = $stageResult;
            $result['estimated_time'] += $stageResult['estimated_hours'];
        }

        return $result;
    }
}
