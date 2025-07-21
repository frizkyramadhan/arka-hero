<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApprovalIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval Document Integration
 */
class ApprovalIntegrationApiController extends Controller
{
    protected ApprovalIntegrationService $integrationService;

    public function __construct(ApprovalIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Get registered document types
     *
     * @return JsonResponse
     */
    public function getDocumentTypes(): JsonResponse
    {
        try {
            $documentTypes = $this->integrationService->getRegisteredDocumentTypes();

            return response()->json([
                'success' => true,
                'data' => $documentTypes,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get document types', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve document types',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register document type
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registerDocumentType(Request $request): JsonResponse
    {
        try {
            $documentType = $request->input('document_type');
            $className = $request->input('class_name');
            $tableName = $request->input('table_name');
            $config = $request->input('config', []);

            if (!$documentType || !$className || !$tableName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document type, class name, and table name are required',
                ], 422);
            }

            $result = $this->integrationService->registerDocumentType($documentType, $className, $tableName, $config);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register document type',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Document type registered successfully',
                'data' => [
                    'document_type' => $documentType,
                    'class_name' => $className,
                    'table_name' => $tableName,
                    'registered_at' => now(),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to register document type', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register document type',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unregister document type
     *
     * @param string $documentType
     * @return JsonResponse
     */
    public function unregisterDocumentType(string $documentType): JsonResponse
    {
        try {
            $result = $this->integrationService->unregisterDocumentType($documentType);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to unregister document type',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Document type unregistered successfully',
                'data' => [
                    'document_type' => $documentType,
                    'unregistered_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to unregister document type', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unregister document type',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Migrate existing data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function migrateExistingData(Request $request): JsonResponse
    {
        try {
            $documentType = $request->input('document_type');
            $batchSize = $request->input('batch_size', 100);

            if (!$documentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document type is required',
                ], 422);
            }

            $result = $this->integrationService->migrateExistingData($documentType, $batchSize);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to migrate existing data',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Existing data migrated successfully',
                'data' => [
                    'document_type' => $documentType,
                    'migrated_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to migrate existing data', [
                'document_type' => $request->input('document_type'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to migrate existing data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate integration
     *
     * @param string $documentType
     * @return JsonResponse
     */
    public function validateIntegration(string $documentType): JsonResponse
    {
        try {
            $validation = $this->integrationService->validateDocumentTypeIntegration($documentType);

            return response()->json([
                'success' => true,
                'data' => $validation,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to validate integration', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to validate integration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get document statistics
     *
     * @param string $documentType
     * @return JsonResponse
     */
    public function getDocumentStatistics(string $documentType): JsonResponse
    {
        try {
            $statistics = $this->integrationService->getDocumentTypeStatistics($documentType);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get document statistics', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve document statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
