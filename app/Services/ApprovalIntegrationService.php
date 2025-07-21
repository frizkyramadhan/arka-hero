<?php

namespace App\Services;

use App\Models\DocumentApproval;
use App\Models\ApprovalFlow;
use App\Contracts\ApprovableDocument;
use App\Traits\HasApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

/**
 * Service class for handling approval system integration with documents.
 */
class ApprovalIntegrationService
{
    protected ApprovalEngineService $engineService;
    protected ApprovalFlowService $flowService;

    public function __construct(
        ApprovalEngineService $engineService,
        ApprovalFlowService $flowService
    ) {
        $this->engineService = $engineService;
        $this->flowService = $flowService;
    }

    /**
     * Register a document type with the approval system.
     *
     * @param string $documentType The document type
     * @param array $config The configuration
     * @return bool True if registration was successful
     */
    public function registerDocumentType(string $documentType, array $config = []): bool
    {
        try {
            // Check if document type is already registered
            $existingFlow = $this->flowService->getFlowByDocumentType($documentType);
            if ($existingFlow) {
                Log::warning('Document type already registered', [
                    'document_type' => $documentType,
                ]);
                return false;
            }

            // Create default approval flow if not provided
            if (empty($config['approval_flow'])) {
                $config['approval_flow'] = $this->createDefaultApprovalFlow($documentType);
            }

            Log::info('Document type registered with approval system', [
                'document_type' => $documentType,
                'config' => $config,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to register document type', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Unregister a document type from the approval system.
     *
     * @param string $documentType The document type
     * @return bool True if unregistration was successful
     */
    public function unregisterDocumentType(string $documentType): bool
    {
        try {
            $flow = $this->flowService->getFlowByDocumentType($documentType);
            if ($flow) {
                $this->flowService->deleteFlow($flow->id);
            }

            Log::info('Document type unregistered from approval system', [
                'document_type' => $documentType,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to unregister document type', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Integrate a document model with the approval system.
     *
     * @param Model $document The document model
     * @param string $documentType The document type
     * @return bool True if integration was successful
     */
    public function integrateDocument(Model $document, string $documentType): bool
    {
        try {
            // Check if document implements ApprovableDocument interface
            if (!$document instanceof ApprovableDocument) {
                Log::warning('Document does not implement ApprovableDocument interface', [
                    'document_type' => $documentType,
                    'document_id' => $document->id,
                ]);
                return false;
            }

            // Check if document uses HasApproval trait
            if (!in_array(HasApproval::class, class_uses($document))) {
                Log::warning('Document does not use HasApproval trait', [
                    'document_type' => $documentType,
                    'document_id' => $document->id,
                ]);
                return false;
            }

            // Verify approval flow exists
            $flow = $this->flowService->getFlowByDocumentType($documentType);
            if (!$flow) {
                Log::error('No approval flow found for document type', [
                    'document_type' => $documentType,
                ]);
                return false;
            }

            Log::info('Document integrated with approval system', [
                'document_type' => $documentType,
                'document_id' => $document->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to integrate document with approval system', [
                'document_type' => $documentType,
                'document_id' => $document->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Migrate existing document approval data.
     *
     * @param string $documentType The document type
     * @param array $migrationData The migration data
     * @return int Number of documents migrated
     */
    public function migrateExistingApprovalData(string $documentType, array $migrationData): int
    {
        try {
            DB::beginTransaction();

            $migratedCount = 0;

            foreach ($migrationData as $data) {
                // Create document approval record
                $documentApproval = DocumentApproval::create([
                    'document_type' => $documentType,
                    'document_id' => $data['document_id'],
                    'approval_flow_id' => $data['approval_flow_id'],
                    'current_stage_id' => $data['current_stage_id'] ?? null,
                    'overall_status' => $data['status'],
                    'submitted_by' => $data['submitted_by'],
                    'submitted_at' => $data['submitted_at'],
                    'completed_at' => $data['completed_at'] ?? null,
                    'metadata' => $data['metadata'] ?? [],
                ]);

                // Create approval actions if provided
                if (isset($data['actions'])) {
                    foreach ($data['actions'] as $actionData) {
                        $documentApproval->approvalActions()->create([
                            'approval_stage_id' => $actionData['stage_id'],
                            'approver_id' => $actionData['approver_id'],
                            'action' => $actionData['action'],
                            'comments' => $actionData['comments'] ?? null,
                            'action_date' => $actionData['action_date'],
                            'is_automatic' => $actionData['is_automatic'] ?? false,
                            'metadata' => $actionData['metadata'] ?? [],
                        ]);
                    }
                }

                $migratedCount++;
            }

            DB::commit();

            Log::info('Existing approval data migrated', [
                'document_type' => $documentType,
                'migrated_count' => $migratedCount,
            ]);

            return $migratedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to migrate existing approval data', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Get integration status for a document type.
     *
     * @param string $documentType The document type
     * @return array The integration status
     */
    public function getIntegrationStatus(string $documentType): array
    {
        try {
            $flow = $this->flowService->getFlowByDocumentType($documentType);
            $pendingApprovals = DocumentApproval::where('document_type', $documentType)
                ->where('overall_status', 'pending')
                ->count();
            $completedApprovals = DocumentApproval::where('document_type', $documentType)
                ->whereIn('overall_status', ['approved', 'rejected'])
                ->count();

            return [
                'document_type' => $documentType,
                'has_approval_flow' => $flow ? true : false,
                'flow_id' => $flow ? $flow->id : null,
                'flow_name' => $flow ? $flow->name : null,
                'pending_approvals' => $pendingApprovals,
                'completed_approvals' => $completedApprovals,
                'total_approvals' => $pendingApprovals + $completedApprovals,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get integration status', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all integrated document types.
     *
     * @return array Array of integrated document types
     */
    public function getIntegratedDocumentTypes(): array
    {
        try {
            $flows = ApprovalFlow::where('is_active', true)->get();
            $integratedTypes = [];

            foreach ($flows as $flow) {
                $integratedTypes[] = $this->getIntegrationStatus($flow->document_type);
            }

            return $integratedTypes;
        } catch (\Exception $e) {
            Log::error('Failed to get integrated document types', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Validate document integration.
     *
     * @param Model $document The document model
     * @param string $documentType The document type
     * @return array The validation results
     */
    public function validateDocumentIntegration(Model $document, string $documentType): array
    {
        $results = [
            'document_type' => $documentType,
            'document_id' => $document->id,
            'validations' => [],
        ];

        // Check if document implements ApprovableDocument
        $results['validations']['implements_approvable_document'] = $document instanceof ApprovableDocument;

        // Check if document uses HasApproval trait
        $results['validations']['uses_has_approval_trait'] = in_array(HasApproval::class, class_uses($document));

        // Check if approval flow exists
        $flow = $this->flowService->getFlowByDocumentType($documentType);
        $results['validations']['has_approval_flow'] = $flow ? true : false;

        // Check if document can be approved
        if ($document instanceof ApprovableDocument) {
            $results['validations']['can_be_approved'] = $document->canBeApproved();
        }

        // Check if document has required methods
        $results['validations']['has_required_methods'] = method_exists($document, 'getApprovalDocumentType') &&
            method_exists($document, 'getApprovalDocumentId') &&
            method_exists($document, 'getApprovalMetadata');

        $results['is_valid'] = !in_array(false, $results['validations']);

        return $results;
    }

    /**
     * Create a default approval flow for a document type.
     *
     * @param string $documentType The document type
     * @return ApprovalFlow The created approval flow
     */
    private function createDefaultApprovalFlow(string $documentType): ApprovalFlow
    {
        $flowData = [
            'name' => ucfirst($documentType) . ' Approval Flow',
            'description' => 'Default approval flow for ' . $documentType,
            'document_type' => $documentType,
            'is_active' => true,
        ];

        return $this->flowService->createFlow($flowData);
    }

    /**
     * Sync document approval status.
     *
     * @param Model $document The document model
     * @param string $documentType The document type
     * @return bool True if sync was successful
     */
    public function syncDocumentApprovalStatus(Model $document, string $documentType): bool
    {
        try {
            if (!$document instanceof ApprovableDocument) {
                return false;
            }

            $approval = $document->approval;
            if (!$approval) {
                return false;
            }

            // Update document status based on approval status
            $statusMapping = [
                'pending' => 'submitted',
                'approved' => 'approved',
                'rejected' => 'rejected',
                'cancelled' => 'cancelled',
            ];

            $newStatus = $statusMapping[$approval->overall_status] ?? 'draft';

            // Update document status
            if (method_exists($document, 'updateApprovalStatus')) {
                $document->updateApprovalStatus($newStatus);
            }

            Log::info('Document approval status synced', [
                'document_type' => $documentType,
                'document_id' => $document->id,
                'approval_status' => $approval->overall_status,
                'document_status' => $newStatus,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to sync document approval status', [
                'document_type' => $documentType,
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get approval statistics for a document type.
     *
     * @param string $documentType The document type
     * @param array $filters Optional filters
     * @return array The statistics
     */
    public function getDocumentTypeApprovalStatistics(string $documentType, array $filters = []): array
    {
        try {
            $query = DocumentApproval::where('document_type', $documentType);

            // Apply filters
            if (isset($filters['date_from'])) {
                $query->where('submitted_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('submitted_at', '<=', $filters['date_to']);
            }

            $totalApprovals = $query->count();
            $pendingApprovals = $query->where('overall_status', 'pending')->count();
            $approvedApprovals = $query->where('overall_status', 'approved')->count();
            $rejectedApprovals = $query->where('overall_status', 'rejected')->count();
            $cancelledApprovals = $query->where('overall_status', 'cancelled')->count();

            // Calculate average approval time
            $completedApprovals = $query->whereIn('overall_status', ['approved', 'rejected'])
                ->whereNotNull('completed_at')
                ->get();

            $totalApprovalTime = 0;
            $completedCount = 0;

            foreach ($completedApprovals as $approval) {
                $approvalTime = $approval->submitted_at->diffInHours($approval->completed_at);
                $totalApprovalTime += $approvalTime;
                $completedCount++;
            }

            $averageApprovalTime = $completedCount > 0 ? round($totalApprovalTime / $completedCount, 2) : 0;

            return [
                'document_type' => $documentType,
                'total_approvals' => $totalApprovals,
                'pending_approvals' => $pendingApprovals,
                'approved_approvals' => $approvedApprovals,
                'rejected_approvals' => $rejectedApprovals,
                'cancelled_approvals' => $cancelledApprovals,
                'approval_rate' => $totalApprovals > 0 ? round(($approvedApprovals / $totalApprovals) * 100, 2) : 0,
                'average_approval_time_hours' => $averageApprovalTime,
                'completed_count' => $completedCount,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get document type approval statistics', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get registered document types.
     *
     * @return array The registered document types
     */
    public function getRegisteredDocumentTypes(): array
    {
        try {
            $flows = ApprovalFlow::select('document_type')
                ->distinct()
                ->where('is_active', true)
                ->get();

            $documentTypes = [];
            foreach ($flows as $flow) {
                $documentTypes[] = [
                    'document_type' => $flow->document_type,
                    'flow_id' => $flow->id,
                    'is_active' => $flow->is_active,
                ];
            }

            return $documentTypes;
        } catch (\Exception $e) {
            Log::error('Failed to get registered document types', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Migrate existing data for a document type.
     *
     * @param string $documentType The document type
     * @param int $batchSize The batch size
     * @return bool True if migration was successful
     */
    public function migrateExistingData(string $documentType, int $batchSize = 100): bool
    {
        try {
            // Get existing approval data for this document type
            $existingData = DocumentApproval::where('document_type', $documentType)
                ->whereNull('approval_flow_id')
                ->get();

            $migratedCount = 0;
            foreach ($existingData->chunk($batchSize) as $chunk) {
                foreach ($chunk as $approval) {
                    // Get or create approval flow for this document type
                    $flow = $this->flowService->getFlowByDocumentType($documentType);
                    if (!$flow) {
                        $flow = $this->createDefaultApprovalFlow($documentType);
                    }

                    // Update approval with flow ID
                    $approval->approval_flow_id = $flow->id;
                    $approval->save();

                    $migratedCount++;
                }
            }

            Log::info('Existing data migrated successfully', [
                'document_type' => $documentType,
                'migrated_count' => $migratedCount,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to migrate existing data', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Validate document type integration.
     *
     * @param string $documentType The document type
     * @return array The validation result
     */
    public function validateDocumentTypeIntegration(string $documentType): array
    {
        try {
            $validation = [
                'document_type' => $documentType,
                'is_registered' => false,
                'has_flow' => false,
                'has_approvals' => false,
                'is_valid' => false,
                'issues' => [],
            ];

            // Check if document type is registered
            $flow = $this->flowService->getFlowByDocumentType($documentType);
            if ($flow) {
                $validation['is_registered'] = true;
                $validation['has_flow'] = true;
            } else {
                $validation['issues'][] = 'No approval flow found for this document type';
            }

            // Check if there are existing approvals
            $approvalCount = DocumentApproval::where('document_type', $documentType)->count();
            if ($approvalCount > 0) {
                $validation['has_approvals'] = true;
            }

            // Check if integration is valid
            if ($validation['is_registered'] && $validation['has_flow']) {
                $validation['is_valid'] = true;
            }

            return $validation;
        } catch (\Exception $e) {
            Log::error('Failed to validate document type integration', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);

            return [
                'document_type' => $documentType,
                'is_registered' => false,
                'has_flow' => false,
                'has_approvals' => false,
                'is_valid' => false,
                'issues' => ['Validation failed: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Get document type statistics.
     *
     * @param string $documentType The document type
     * @return array The statistics
     */
    public function getDocumentTypeStatistics(string $documentType): array
    {
        return $this->getDocumentTypeApprovalStatistics($documentType);
    }
}
