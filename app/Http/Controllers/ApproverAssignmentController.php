<?php

namespace App\Http\Controllers;

use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApproverAssignmentController extends Controller
{
    /**
     * Show approver assignment interface for a specific stage
     */
    public function index(ApprovalStage $stage)
    {
        $stage->load(['approvers.user', 'approvers.role', 'approvers.department']);

        $users = User::where('is_active', true)->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('approval.approvers.index', compact('stage', 'users', 'roles', 'departments'));
    }

    /**
     * Assign approver to stage
     */
    public function assign(Request $request, ApprovalStage $stage): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'approver_type' => 'required|in:user,role,department',
                'approver_id' => 'required|integer',
                'is_backup' => 'boolean',
                'approval_condition' => 'nullable|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Check if approver already exists for this stage
            $existingApprover = ApprovalStageApprover::where('approval_stage_id', $stage->id)
                ->where('approver_type', $request->approver_type)
                ->where('approver_id', $request->approver_id)
                ->first();

            if ($existingApprover) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approver already assigned to this stage'
                ], 400);
            }

            // Create new approver assignment
            $approver = ApprovalStageApprover::create([
                'approval_stage_id' => $stage->id,
                'approver_type' => $request->approver_type,
                'approver_id' => $request->approver_id,
                'is_backup' => $request->boolean('is_backup', false),
                'approval_condition' => $request->approval_condition,
            ]);

            // Load relationships for response
            $approver->load(['user', 'role', 'department']);

            DB::commit();

            // Log the assignment
            Log::info('Approver assigned to stage', [
                'stage_id' => $stage->id,
                'stage_name' => $stage->stage_name,
                'approver_type' => $request->approver_type,
                'approver_id' => $request->approver_id,
                'assigned_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Approver assigned successfully',
                'approver' => $approver
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign approver', [
                'stage_id' => $stage->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign approver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update approver assignment
     */
    public function update(Request $request, ApprovalStageApprover $approver): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_backup' => 'boolean',
                'approval_condition' => 'nullable|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $approver->update([
                'is_backup' => $request->boolean('is_backup', false),
                'approval_condition' => $request->approval_condition,
            ]);

            // Load relationships for response
            $approver->load(['user', 'role', 'department']);

            DB::commit();

            // Log the update
            Log::info('Approver assignment updated', [
                'approver_id' => $approver->id,
                'stage_id' => $approver->approval_stage_id,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Approver assignment updated successfully',
                'approver' => $approver
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update approver assignment', [
                'approver_id' => $approver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update approver assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove approver from stage
     */
    public function remove(ApprovalStageApprover $approver): JsonResponse
    {
        try {
            DB::beginTransaction();

            $stageId = $approver->approval_stage_id;
            $approverData = $approver->toArray();

            $approver->delete();

            DB::commit();

            // Log the removal
            Log::info('Approver removed from stage', [
                'approver_id' => $approverData['id'],
                'stage_id' => $stageId,
                'removed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Approver removed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove approver', [
                'approver_id' => $approver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove approver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approvers for a stage
     */
    public function getApprovers(ApprovalStage $stage): JsonResponse
    {
        try {
            $approvers = $stage->approvers()->with(['user', 'role', 'department'])->get();

            return response()->json([
                'success' => true,
                'approvers' => $approvers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approvers', [
                'stage_id' => $stage->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get approvers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users for approver assignment
     */
    public function searchUsers(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');

            $users = User::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('employee_id', 'like', "%{$query}%");
                })
                ->orderBy('name')
                ->limit(20)
                ->get(['id', 'name', 'email', 'employee_id']);

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to search users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approval matrix for a stage
     */
    public function getApprovalMatrix(ApprovalStage $stage): JsonResponse
    {
        try {
            $approvers = $stage->approvers()->with(['user', 'role', 'department'])->get();

            $matrix = [
                'primary_approvers' => $approvers->where('is_backup', false),
                'backup_approvers' => $approvers->where('is_backup', true),
                'total_approvers' => $approvers->count(),
                'stage_info' => [
                    'id' => $stage->id,
                    'name' => $stage->stage_name,
                    'order' => $stage->stage_order,
                    'type' => $stage->stage_type,
                ]
            ];

            return response()->json([
                'success' => true,
                'matrix' => $matrix
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval matrix', [
                'stage_id' => $stage->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get approval matrix: ' . $e->getMessage()
            ], 500);
        }
    }
}
