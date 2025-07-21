<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApprovalBackupService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval Backup and Restore
 */
class ApprovalBackupApiController extends Controller
{
    protected ApprovalBackupService $backupService;

    public function __construct(ApprovalBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Create backup
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createBackup(Request $request): JsonResponse
    {
        try {
            $backupName = $request->input('backup_name', 'backup_' . now()->format('Y-m-d_H-i-s'));
            $includeData = $request->input('include_data', true);
            $includeSettings = $request->input('include_settings', true);

            $result = $this->backupService->createBackup($backupName, $includeData, $includeSettings);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create backup',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'data' => [
                    'backup_name' => $backupName,
                    'created_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create backup', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore backup
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function restoreBackup(Request $request): JsonResponse
    {
        try {
            $backupName = $request->input('backup_name');
            $overwrite = $request->input('overwrite', false);

            if (!$backupName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup name is required',
                ], 422);
            }

            $result = $this->backupService->restoreBackup($backupName, $overwrite);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to restore backup',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup restored successfully',
                'data' => [
                    'backup_name' => $backupName,
                    'restored_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to restore backup', [
                'backup_name' => $request->input('backup_name'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore backup',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List backups
     *
     * @return JsonResponse
     */
    public function listBackups(): JsonResponse
    {
        try {
            $backups = $this->backupService->listBackups();

            return response()->json([
                'success' => true,
                'data' => $backups,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to list backups', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to list backups',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete backup
     *
     * @param string $backupName
     * @return JsonResponse
     */
    public function deleteBackup(string $backupName): JsonResponse
    {
        try {
            $result = $this->backupService->deleteBackup($backupName);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete backup',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully',
                'data' => [
                    'backup_name' => $backupName,
                    'deleted_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete backup', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export to CSV
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportToCsv(Request $request): JsonResponse
    {
        try {
            $dataType = $request->input('data_type', 'all');
            $filters = $request->only(['date_from', 'date_to', 'document_type']);

            $csvContent = $this->backupService->exportToCsv($dataType, $filters);

            if (!$csvContent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to export data to CSV',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data exported to CSV successfully',
                'data' => [
                    'csv_content' => $csvContent,
                    'filename' => 'approval_data_' . now()->format('Y-m-d_H-i-s') . '.csv',
                    'data_type' => $dataType,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to export data to CSV', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export data to CSV',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
