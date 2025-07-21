<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentApproval;
use App\Services\ApprovalPermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval Permissions
 */
class ApprovalPermissionApiController extends Controller
{
    protected ApprovalPermissionService $permissionService;

    public function __construct(ApprovalPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Get user permissions
     *
     * @return JsonResponse
     */
    public function getUserPermissions(): JsonResponse
    {
        try {
            $user = auth()->user();
            $permissions = $this->permissionService->getUserApprovalPermissions($user);

            return response()->json([
                'success' => true,
                'data' => $permissions,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user permissions', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user permissions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if user can approve
     *
     * @param int $approvalId
     * @return JsonResponse
     */
    public function canApprove(int $approvalId): JsonResponse
    {
        try {
            $user = auth()->user();
            $canApprove = $this->permissionService->canUserApprove($approvalId, $user);

            return response()->json([
                'success' => true,
                'data' => [
                    'can_approve' => $canApprove,
                    'approval_id' => $approvalId,
                    'user_id' => $user->id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check approval permission', [
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check approval permission',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if user can view
     *
     * @param int $approvalId
     * @return JsonResponse
     */
    public function canView(int $approvalId): JsonResponse
    {
        try {
            $user = auth()->user();
            $canView = $this->permissionService->canUserViewApproval($approvalId, $user);

            return response()->json([
                'success' => true,
                'data' => [
                    'can_view' => $canView,
                    'approval_id' => $approvalId,
                    'user_id' => $user->id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check view permission', [
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check view permission',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if user can manage flows
     *
     * @return JsonResponse
     */
    public function canManageFlows(): JsonResponse
    {
        try {
            $user = auth()->user();
            $canManage = $this->permissionService->canUserManageApprovalFlows($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'can_manage_flows' => $canManage,
                    'user_id' => $user->id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check flow management permission', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check flow management permission',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approvers for document type
     *
     * @param string $documentType
     * @return JsonResponse
     */
    public function getApprovers(string $documentType): JsonResponse
    {
        try {
            $approvers = $this->permissionService->getApproversForDocumentType($documentType);

            return response()->json([
                'success' => true,
                'data' => $approvers,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approvers for document type', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approvers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
