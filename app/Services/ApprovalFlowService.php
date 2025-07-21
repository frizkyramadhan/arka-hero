<?php

namespace App\Services;

use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use App\Exceptions\Approval\ApprovalFlowNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Service class for managing approval flows.
 */
class ApprovalFlowService
{
    /**
     * Create a new approval flow.
     *
     * @param array $flowData The flow data
     * @param array $stagesData The stages data
     * @return ApprovalFlow The created flow
     * @throws \Exception If creation fails
     */
    public function createFlow(array $flowData, array $stagesData = []): ApprovalFlow
    {
        // Validate the flow data
        $validator = Validator::make($flowData, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|string|max:100',
            'is_active' => 'boolean',
            'created_by' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Invalid flow data: ' . $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            // Create the approval flow
            $flow = ApprovalFlow::create([
                'name' => $flowData['name'],
                'description' => $flowData['description'] ?? null,
                'document_type' => $flowData['document_type'],
                'is_active' => $flowData['is_active'] ?? true,
                'created_by' => $flowData['created_by'] ?? auth()->id(),
            ]);

            // Create stages if provided
            if (!empty($stagesData)) {
                foreach ($stagesData as $stageData) {
                    $this->createStage($flow->id, $stageData);
                }
            }

            DB::commit();

            Log::info('Approval flow created', [
                'flow_id' => $flow->id,
                'name' => $flow->name,
                'document_type' => $flow->document_type,
                'created_by' => $flow->created_by,
            ]);

            return $flow;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create approval flow', [
                'flow_data' => $flowData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing approval flow.
     *
     * @param int $flowId The flow ID
     * @param array $flowData The flow data
     * @param array $stagesData The stages data
     * @return ApprovalFlow The updated flow
     * @throws ApprovalFlowNotFoundException If flow not found
     * @throws \Exception If update fails
     */
    public function updateFlow(int $flowId, array $flowData, array $stagesData = []): ApprovalFlow
    {
        $flow = ApprovalFlow::find($flowId);
        if (!$flow) {
            throw new ApprovalFlowNotFoundException('approval_flow', 'Approval flow not found');
        }

        // Validate the flow data
        $validator = Validator::make($flowData, [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'sometimes|required|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Invalid flow data: ' . $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            // Update the flow
            $flow->update($flowData);

            // Update stages if provided
            if (!empty($stagesData)) {
                $this->updateStages($flow->id, $stagesData);
            }

            DB::commit();

            Log::info('Approval flow updated', [
                'flow_id' => $flow->id,
                'name' => $flow->name,
                'document_type' => $flow->document_type,
            ]);

            return $flow->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update approval flow', [
                'flow_id' => $flowId,
                'flow_data' => $flowData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete an approval flow.
     *
     * @param int $flowId The flow ID
     * @return bool True if deletion was successful
     * @throws ApprovalFlowNotFoundException If flow not found
     */
    public function deleteFlow(int $flowId): bool
    {
        $flow = ApprovalFlow::find($flowId);
        if (!$flow) {
            throw new ApprovalFlowNotFoundException('approval_flow', 'Approval flow not found');
        }

        try {
            DB::beginTransaction();

            // Check if there are any active document approvals using this flow
            $activeApprovals = $flow->documentApprovals()->where('overall_status', 'pending')->count();
            if ($activeApprovals > 0) {
                throw new \Exception("Cannot delete flow: {$activeApprovals} active approvals are using this flow");
            }

            // Delete the flow (stages and approvers will be deleted via cascade)
            $flow->delete();

            DB::commit();

            Log::info('Approval flow deleted', [
                'flow_id' => $flowId,
                'name' => $flow->name,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete approval flow', [
                'flow_id' => $flowId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }



    /**
     * Get approval flow by document type.
     *
     * @param string $documentType The document type
     * @return ApprovalFlow|null The approval flow
     */
    public function getFlowByDocumentType(string $documentType): ?ApprovalFlow
    {
        return ApprovalFlow::forDocumentType($documentType)
            ->active()
            ->with(['stages.approvers'])
            ->first();
    }

    /**
     * Clone an approval flow.
     *
     * @param int $flowId The flow ID to clone
     * @param string $newName The new flow name
     * @return ApprovalFlow The cloned flow
     * @throws ApprovalFlowNotFoundException If flow not found
     */
    public function cloneFlow(int $flowId, string $newName): ApprovalFlow
    {
        $originalFlow = ApprovalFlow::with(['stages.approvers'])->find($flowId);
        if (!$originalFlow) {
            throw new ApprovalFlowNotFoundException('approval_flow', 'Approval flow not found');
        }

        try {
            DB::beginTransaction();

            // Create the new flow
            $newFlow = ApprovalFlow::create([
                'name' => $newName,
                'description' => $originalFlow->description . ' (Cloned)',
                'document_type' => $originalFlow->document_type,
                'is_active' => false, // Start as inactive
                'created_by' => auth()->id(),
            ]);

            // Clone stages and approvers
            foreach ($originalFlow->stages as $stage) {
                $newStage = ApprovalStage::create([
                    'approval_flow_id' => $newFlow->id,
                    'stage_name' => $stage->stage_name,
                    'stage_order' => $stage->stage_order,
                    'stage_type' => $stage->stage_type,
                    'is_mandatory' => $stage->is_mandatory,
                    'auto_approve_conditions' => $stage->auto_approve_conditions,
                    'escalation_hours' => $stage->escalation_hours,
                ]);

                // Clone approvers
                foreach ($stage->approvers as $approver) {
                    ApprovalStageApprover::create([
                        'approval_stage_id' => $newStage->id,
                        'approver_type' => $approver->approver_type,
                        'approver_id' => $approver->approver_id,
                        'is_backup' => $approver->is_backup,
                        'approval_condition' => $approver->approval_condition,
                    ]);
                }
            }

            DB::commit();

            Log::info('Approval flow cloned', [
                'original_flow_id' => $flowId,
                'new_flow_id' => $newFlow->id,
                'new_name' => $newName,
            ]);

            return $newFlow;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to clone approval flow', [
                'flow_id' => $flowId,
                'new_name' => $newName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get all approval flows.
     *
     * @param array $filters Optional filters
     * @return \Illuminate\Database\Eloquent\Collection The approval flows
     */
    public function getAllFlows(array $filters = [])
    {
        $query = ApprovalFlow::with(['stages.approvers', 'creator']);

        // Apply filters
        if (isset($filters['document_type'])) {
            $query->forDocumentType($filters['document_type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get approval flow statistics.
     *
     * @param int $flowId The flow ID
     * @return array The statistics
     */
    public function getFlowStatistics(int $flowId): array
    {
        $flow = ApprovalFlow::with(['documentApprovals', 'stages'])->find($flowId);
        if (!$flow) {
            return [];
        }

        $totalStages = $flow->stages->count();
        $totalApprovers = $flow->stages()
            ->withCount('approvers')
            ->get()
            ->sum('approvers_count');

        $activeApprovals = $flow->documentApprovals()
            ->where('overall_status', 'pending')
            ->count();

        return [
            'total_stages' => $totalStages,
            'total_approvers' => $totalApprovers,
            'active_approvals' => $activeApprovals,
            'flow_name' => $flow->name,
            'document_type' => $flow->document_type,
            'is_active' => $flow->is_active,
        ];
    }

    /**
     * Create a stage for an approval flow.
     *
     * @param int $flowId The flow ID
     * @param array $stageData The stage data
     * @return ApprovalStage The created stage
     * @throws \Exception If creation fails
     */
    public function createStage(int $flowId, array $stageData): ApprovalStage
    {
        $validator = Validator::make($stageData, [
            'stage_name' => 'required|string|max:255',
            'stage_order' => 'required|integer|min:1',
            'stage_type' => 'required|in:sequential,parallel',
            'is_mandatory' => 'boolean',
            'auto_approve_conditions' => 'nullable|array',
            'escalation_hours' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Invalid stage data: ' . $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $stage = ApprovalStage::create([
                'approval_flow_id' => $flowId,
                'stage_name' => $stageData['stage_name'],
                'stage_order' => $stageData['stage_order'],
                'stage_type' => $stageData['stage_type'],
                'is_mandatory' => $stageData['is_mandatory'] ?? true,
                'auto_approve_conditions' => $stageData['auto_approve_conditions'] ?? null,
                'escalation_hours' => $stageData['escalation_hours'] ?? 72,
            ]);

            // Create approvers if provided
            if (isset($stageData['approvers']) && is_array($stageData['approvers'])) {
                foreach ($stageData['approvers'] as $approverData) {
                    $this->createApprover($stage->id, $approverData);
                }
            }

            DB::commit();

            Log::info('Approval stage created', [
                'stage_id' => $stage->id,
                'flow_id' => $flowId,
                'stage_name' => $stage->stage_name,
            ]);

            return $stage;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create approval stage', [
                'flow_id' => $flowId,
                'stage_data' => $stageData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update stages for an approval flow.
     *
     * @param int $flowId The flow ID
     * @param array $stagesData The stages data
     * @return void
     * @throws \Exception If update fails
     */
    public function updateStages(int $flowId, array $stagesData): void
    {
        try {
            DB::beginTransaction();

            // Delete existing stages
            ApprovalStage::where('approval_flow_id', $flowId)->delete();

            // Create new stages
            foreach ($stagesData as $stageData) {
                $this->createStage($flowId, $stageData);
            }

            DB::commit();

            Log::info('Approval stages updated', [
                'flow_id' => $flowId,
                'stages_count' => count($stagesData),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update approval stages', [
                'flow_id' => $flowId,
                'stages_data' => $stagesData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create an approver for a stage.
     *
     * @param int $stageId The stage ID
     * @param array $approverData The approver data
     * @return ApprovalStageApprover The created approver
     * @throws \Exception If creation fails
     */
    public function createApprover(int $stageId, array $approverData): ApprovalStageApprover
    {
        $validator = Validator::make($approverData, [
            'approver_type' => 'required|in:user,role,department',
            'approver_id' => 'required|integer',
            'is_backup' => 'boolean',
            'approval_condition' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Invalid approver data: ' . $validator->errors()->first());
        }

        try {
            $approver = ApprovalStageApprover::create([
                'approval_stage_id' => $stageId,
                'approver_type' => $approverData['approver_type'],
                'approver_id' => $approverData['approver_id'],
                'is_backup' => $approverData['is_backup'] ?? false,
                'approval_condition' => $approverData['approval_condition'] ?? null,
            ]);

            Log::info('Approval stage approver created', [
                'approver_id' => $approver->id,
                'stage_id' => $stageId,
                'approver_type' => $approver->approver_type,
            ]);

            return $approver;
        } catch (\Exception $e) {
            Log::error('Failed to create approval stage approver', [
                'stage_id' => $stageId,
                'approver_data' => $approverData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate flow configuration.
     *
     * @param int $flowId The flow ID
     * @return array The validation result
     */
    public function validateFlowConfiguration(int $flowId): array
    {
        try {
            $flow = ApprovalFlow::find($flowId);
            if (!$flow) {
                return [
                    'is_valid' => false,
                    'errors' => ['Flow not found'],
                ];
            }

            $errors = [];
            $stages = $flow->stages()->orderBy('stage_order')->get();

            // Check if flow has stages
            if ($stages->isEmpty()) {
                $errors[] = 'Flow must have at least one stage';
            }

            // Check if each stage has approvers
            foreach ($stages as $stage) {
                if ($stage->approvers()->count() === 0) {
                    $errors[] = "Stage '{$stage->stage_name}' must have at least one approver";
                }
            }

            // Check stage order
            $expectedOrder = 1;
            foreach ($stages as $stage) {
                if ($stage->stage_order !== $expectedOrder) {
                    $errors[] = "Stage order is not sequential. Expected {$expectedOrder}, got {$stage->stage_order}";
                }
                $expectedOrder++;
            }

            return [
                'is_valid' => empty($errors),
                'errors' => $errors,
                'flow_id' => $flowId,
                'total_stages' => $stages->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to validate flow configuration', [
                'flow_id' => $flowId,
                'error' => $e->getMessage(),
            ]);

            return [
                'is_valid' => false,
                'errors' => ['Validation failed: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Activate flow.
     *
     * @param int $flowId The flow ID
     * @return bool True if successful
     */
    public function activateFlow(int $flowId): bool
    {
        try {
            $flow = ApprovalFlow::find($flowId);
            if (!$flow) {
                return false;
            }

            $flow->update(['is_active' => true]);

            Log::info('Flow activated', [
                'flow_id' => $flowId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to activate flow', [
                'flow_id' => $flowId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Deactivate flow.
     *
     * @param int $flowId The flow ID
     * @return bool True if successful
     */
    public function deactivateFlow(int $flowId): bool
    {
        try {
            $flow = ApprovalFlow::find($flowId);
            if (!$flow) {
                return false;
            }

            $flow->update(['is_active' => false]);

            Log::info('Flow deactivated', [
                'flow_id' => $flowId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to deactivate flow', [
                'flow_id' => $flowId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get active flows.
     *
     * @return \Illuminate\Database\Eloquent\Collection The active flows
     */
    public function getActiveFlows()
    {
        return ApprovalFlow::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get flows by document type.
     *
     * @param string $documentType The document type
     * @return \Illuminate\Database\Eloquent\Collection The flows
     */
    public function getFlowsByDocumentType(string $documentType)
    {
        return ApprovalFlow::where('document_type', $documentType)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
