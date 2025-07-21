<?php

namespace App\Services;

use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use App\Models\ApprovalNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

/**
 * Service class for handling approval system backup and recovery.
 */
class ApprovalBackupService
{
    protected ApprovalAuditService $auditService;

    public function __construct(ApprovalAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Create a complete backup of the approval system.
     *
     * @param string $backupName Optional backup name
     * @return array The backup information
     */
    public function createBackup(string $backupName = null): array
    {
        try {
            $backupName = $backupName ?: 'approval_backup_' . now()->format('Y-m-d_H-i-s');
            $backupData = [
                'backup_name' => $backupName,
                'created_at' => now(),
                'version' => '1.0',
                'data' => [],
            ];

            // Backup approval flows
            $backupData['data']['approval_flows'] = $this->backupApprovalFlows();

            // Backup approval stages
            $backupData['data']['approval_stages'] = $this->backupApprovalStages();

            // Backup approval stage approvers
            $backupData['data']['approval_stage_approvers'] = $this->backupApprovalStageApprovers();

            // Backup document approvals
            $backupData['data']['document_approvals'] = $this->backupDocumentApprovals();

            // Backup approval actions
            $backupData['data']['approval_actions'] = $this->backupApprovalActions();

            // Backup approval notifications
            $backupData['data']['approval_notifications'] = $this->backupApprovalNotifications();

            // Save backup to storage
            $backupPath = "backups/approval/{$backupName}.json";
            Storage::put($backupPath, json_encode($backupData, JSON_PRETTY_PRINT));

            // Log backup creation
            $this->auditService->logSecurityEvent('backup_created', [
                'backup_name' => $backupName,
                'backup_path' => $backupPath,
                'data_count' => count($backupData['data']),
            ]);

            Log::info('Approval system backup created', [
                'backup_name' => $backupName,
                'backup_path' => $backupPath,
            ]);

            return [
                'success' => true,
                'backup_name' => $backupName,
                'backup_path' => $backupPath,
                'created_at' => now(),
                'data_count' => count($backupData['data']),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create approval system backup', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restore approval system from backup.
     *
     * @param string $backupName The backup name
     * @param bool $overwriteExisting Whether to overwrite existing data
     * @return array The restore information
     */
    public function restoreBackup(string $backupName, bool $overwriteExisting = false): array
    {
        try {
            $backupPath = "backups/approval/{$backupName}.json";

            if (!Storage::exists($backupPath)) {
                throw new \Exception("Backup file not found: {$backupPath}");
            }

            $backupContent = Storage::get($backupPath);
            $backupData = json_decode($backupContent, true);

            if (!$backupData) {
                throw new \Exception('Invalid backup file format');
            }

            DB::beginTransaction();

            try {
                // Restore approval flows
                $this->restoreApprovalFlows($backupData['data']['approval_flows'], $overwriteExisting);

                // Restore approval stages
                $this->restoreApprovalStages($backupData['data']['approval_stages'], $overwriteExisting);

                // Restore approval stage approvers
                $this->restoreApprovalStageApprovers($backupData['data']['approval_stage_approvers'], $overwriteExisting);

                // Restore document approvals
                $this->restoreDocumentApprovals($backupData['data']['document_approvals'], $overwriteExisting);

                // Restore approval actions
                $this->restoreApprovalActions($backupData['data']['approval_actions'], $overwriteExisting);

                // Restore approval notifications
                $this->restoreApprovalNotifications($backupData['data']['approval_notifications'], $overwriteExisting);

                DB::commit();

                // Log restore operation
                $this->auditService->logSecurityEvent('backup_restored', [
                    'backup_name' => $backupName,
                    'overwrite_existing' => $overwriteExisting,
                ]);

                Log::info('Approval system backup restored', [
                    'backup_name' => $backupName,
                    'overwrite_existing' => $overwriteExisting,
                ]);

                return [
                    'success' => true,
                    'backup_name' => $backupName,
                    'restored_at' => now(),
                    'overwrite_existing' => $overwriteExisting,
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to restore approval system backup', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List available backups.
     *
     * @return array The list of backups
     */
    public function listBackups(): array
    {
        try {
            $backups = [];
            $backupFiles = Storage::files('backups/approval');

            foreach ($backupFiles as $backupFile) {
                if (pathinfo($backupFile, PATHINFO_EXTENSION) === 'json') {
                    $backupContent = Storage::get($backupFile);
                    $backupData = json_decode($backupContent, true);

                    if ($backupData) {
                        $backups[] = [
                            'name' => $backupData['backup_name'],
                            'created_at' => $backupData['created_at'],
                            'version' => $backupData['version'],
                            'file_path' => $backupFile,
                            'file_size' => Storage::size($backupFile),
                        ];
                    }
                }
            }

            // Sort by creation date (newest first)
            usort($backups, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return $backups;
        } catch (\Exception $e) {
            Log::error('Failed to list backups', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Delete a backup.
     *
     * @param string $backupName The backup name
     * @return array The deletion result
     */
    public function deleteBackup(string $backupName): array
    {
        try {
            $backupPath = "backups/approval/{$backupName}.json";

            if (!Storage::exists($backupPath)) {
                throw new \Exception("Backup file not found: {$backupPath}");
            }

            Storage::delete($backupPath);

            Log::info('Approval system backup deleted', [
                'backup_name' => $backupName,
            ]);

            return [
                'success' => true,
                'backup_name' => $backupName,
                'deleted_at' => now(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete backup', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Export approval data to CSV.
     *
     * @param array $filters Optional filters
     * @return array The export information
     */
    public function exportToCsv(array $filters = []): array
    {
        try {
            $exportName = 'approval_export_' . now()->format('Y-m-d_H-i-s');
            $exportData = [];

            // Export approval flows
            $exportData['approval_flows'] = $this->exportApprovalFlowsToCsv();

            // Export document approvals
            $exportData['document_approvals'] = $this->exportDocumentApprovalsToCsv($filters);

            // Export approval actions
            $exportData['approval_actions'] = $this->exportApprovalActionsToCsv($filters);

            // Save CSV files
            foreach ($exportData as $type => $csvContent) {
                $csvPath = "exports/approval/{$exportName}_{$type}.csv";
                Storage::put($csvPath, $csvContent);
            }

            Log::info('Approval data exported to CSV', [
                'export_name' => $exportName,
                'export_types' => array_keys($exportData),
            ]);

            return [
                'success' => true,
                'export_name' => $exportName,
                'export_types' => array_keys($exportData),
                'created_at' => now(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to export approval data to CSV', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Backup approval flows.
     *
     * @return array The approval flows data
     */
    private function backupApprovalFlows(): array
    {
        return ApprovalFlow::all()->toArray();
    }

    /**
     * Backup approval stages.
     *
     * @return array The approval stages data
     */
    private function backupApprovalStages(): array
    {
        return ApprovalStage::all()->toArray();
    }

    /**
     * Backup approval stage approvers.
     *
     * @return array The approval stage approvers data
     */
    private function backupApprovalStageApprovers(): array
    {
        return ApprovalStageApprover::all()->toArray();
    }

    /**
     * Backup document approvals.
     *
     * @return array The document approvals data
     */
    private function backupDocumentApprovals(): array
    {
        return DocumentApproval::all()->toArray();
    }

    /**
     * Backup approval actions.
     *
     * @return array The approval actions data
     */
    private function backupApprovalActions(): array
    {
        return ApprovalAction::all()->toArray();
    }

    /**
     * Backup approval notifications.
     *
     * @return array The approval notifications data
     */
    private function backupApprovalNotifications(): array
    {
        return ApprovalNotification::all()->toArray();
    }

    /**
     * Restore approval flows.
     *
     * @param array $flowsData The flows data
     * @param bool $overwriteExisting Whether to overwrite existing data
     */
    private function restoreApprovalFlows(array $flowsData, bool $overwriteExisting): void
    {
        if ($overwriteExisting) {
            ApprovalFlow::truncate();
        }

        foreach ($flowsData as $flowData) {
            ApprovalFlow::create($flowData);
        }
    }

    /**
     * Restore approval stages.
     *
     * @param array $stagesData The stages data
     * @param bool $overwriteExisting Whether to overwrite existing data
     */
    private function restoreApprovalStages(array $stagesData, bool $overwriteExisting): void
    {
        if ($overwriteExisting) {
            ApprovalStage::truncate();
        }

        foreach ($stagesData as $stageData) {
            ApprovalStage::create($stageData);
        }
    }

    /**
     * Restore approval stage approvers.
     *
     * @param array $approversData The approvers data
     * @param bool $overwriteExisting Whether to overwrite existing data
     */
    private function restoreApprovalStageApprovers(array $approversData, bool $overwriteExisting): void
    {
        if ($overwriteExisting) {
            ApprovalStageApprover::truncate();
        }

        foreach ($approversData as $approverData) {
            ApprovalStageApprover::create($approverData);
        }
    }

    /**
     * Restore document approvals.
     *
     * @param array $approvalsData The approvals data
     * @param bool $overwriteExisting Whether to overwrite existing data
     */
    private function restoreDocumentApprovals(array $approvalsData, bool $overwriteExisting): void
    {
        if ($overwriteExisting) {
            DocumentApproval::truncate();
        }

        foreach ($approvalsData as $approvalData) {
            DocumentApproval::create($approvalData);
        }
    }

    /**
     * Restore approval actions.
     *
     * @param array $actionsData The actions data
     * @param bool $overwriteExisting Whether to overwrite existing data
     */
    private function restoreApprovalActions(array $actionsData, bool $overwriteExisting): void
    {
        if ($overwriteExisting) {
            ApprovalAction::truncate();
        }

        foreach ($actionsData as $actionData) {
            ApprovalAction::create($actionData);
        }
    }

    /**
     * Restore approval notifications.
     *
     * @param array $notificationsData The notifications data
     * @param bool $overwriteExisting Whether to overwrite existing data
     */
    private function restoreApprovalNotifications(array $notificationsData, bool $overwriteExisting): void
    {
        if ($overwriteExisting) {
            ApprovalNotification::truncate();
        }

        foreach ($notificationsData as $notificationData) {
            ApprovalNotification::create($notificationData);
        }
    }

    /**
     * Export approval flows to CSV.
     *
     * @return string The CSV content
     */
    private function exportApprovalFlowsToCsv(): string
    {
        $flows = ApprovalFlow::all();

        $csv = [];
        $csv[] = ['ID', 'Name', 'Description', 'Document Type', 'Is Active', 'Created At', 'Updated At'];

        foreach ($flows as $flow) {
            $csv[] = [
                $flow->id,
                $flow->name,
                $flow->description,
                $flow->document_type,
                $flow->is_active ? 'Yes' : 'No',
                $flow->created_at,
                $flow->updated_at,
            ];
        }

        return $this->arrayToCsv($csv);
    }

    /**
     * Export document approvals to CSV.
     *
     * @param array $filters Optional filters
     * @return string The CSV content
     */
    private function exportDocumentApprovalsToCsv(array $filters = []): string
    {
        $query = DocumentApproval::query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('submitted_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('submitted_at', '<=', $filters['date_to']);
        }

        if (isset($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        $approvals = $query->get();

        $csv = [];
        $csv[] = ['ID', 'Document Type', 'Document ID', 'Overall Status', 'Submitted By', 'Submitted At', 'Completed At'];

        foreach ($approvals as $approval) {
            $csv[] = [
                $approval->id,
                $approval->document_type,
                $approval->document_id,
                $approval->overall_status,
                $approval->submitted_by,
                $approval->submitted_at,
                $approval->completed_at,
            ];
        }

        return $this->arrayToCsv($csv);
    }

    /**
     * Export approval actions to CSV.
     *
     * @param array $filters Optional filters
     * @return string The CSV content
     */
    private function exportApprovalActionsToCsv(array $filters = []): string
    {
        $query = ApprovalAction::query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('action_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('action_date', '<=', $filters['date_to']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        $actions = $query->get();

        $csv = [];
        $csv[] = ['ID', 'Document Approval ID', 'Approval Stage ID', 'Approver ID', 'Action', 'Comments', 'Action Date'];

        foreach ($actions as $action) {
            $csv[] = [
                $action->id,
                $action->document_approval_id,
                $action->approval_stage_id,
                $action->approver_id,
                $action->action,
                $action->comments,
                $action->action_date,
            ];
        }

        return $this->arrayToCsv($csv);
    }

    /**
     * Convert array to CSV format.
     *
     * @param array $data The data array
     * @return string The CSV content
     */
    private function arrayToCsv(array $data): string
    {
        $output = '';
        foreach ($data as $row) {
            $output .= implode(',', array_map(function ($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }
        return $output;
    }
}
