<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\ProjectController;
use App\Http\Controllers\Api\v1\PositionController;
use App\Http\Controllers\Api\v1\EmployeeApiController;
use App\Http\Controllers\Api\v1\DepartmentController;
use App\Http\Controllers\Api\v1\OfficialtravelApiController;
use App\Http\Controllers\Api\v1\LetterNumberApiController;
use App\Http\Controllers\LetterSubjectController;
use App\Http\Controllers\LetterNumberController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::apiResource('positions', PositionController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('projects', ProjectController::class);
});

// Employee API Routes
Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeApiController::class, 'index']);
    Route::get('/active', [EmployeeApiController::class, 'activeEmployees']);
    Route::post('/search', [EmployeeApiController::class, 'search']);
    Route::get('/{id}', [EmployeeApiController::class, 'show']);
});
// Official Travel API Routes
Route::prefix('official-travels')->group(function () {
    Route::post('/search', [OfficialtravelApiController::class, 'search']);
    Route::post('/search-claimed', [OfficialtravelApiController::class, 'search_claimed']);
    Route::post('/detail', [OfficialtravelApiController::class, 'show']);
    Route::put('/claim', [OfficialtravelApiController::class, 'updateClaim']);
});

// Letter Numbering API Routes v1
Route::prefix('v1/letter-numbers')->group(function () {
    // Core Integration Endpoints
    Route::post('/request', [LetterNumberApiController::class, 'requestNumber']);
    Route::post('/{id}/mark-as-used', [LetterNumberApiController::class, 'markAsUsed']);
    Route::post('/{id}/cancel', [LetterNumberApiController::class, 'cancelNumber']);

    // Data Retrieval Endpoints
    Route::get('/available/{categoryId}', [LetterNumberApiController::class, 'getAvailableNumbers']);
    Route::get('/subjects/{categoryId}', [LetterNumberApiController::class, 'getSubjectsByCategory']);
    Route::get('/{id}', [LetterNumberApiController::class, 'getLetterNumber']);

    // Utility Endpoints
    Route::post('/check-availability', [LetterNumberApiController::class, 'checkAvailability']);
});

Route::prefix('v1/letter-subjects')->group(function () {
    Route::get('/by-category/{categoryId}', [LetterSubjectController::class, 'getByCategory'])->name('api.letter-subjects.by-category');
});

// Additional API routes for letter management
Route::get('letter-subjects/available/{documentType}/{categoryId}', [LetterSubjectController::class, 'getAvailableSubjectsForDocument']);
Route::get('letter-numbers/available/{categoryCode}', [LetterNumberController::class, 'getAvailableNumbers'])->name('api.letter-numbers.available');

// Approval System API Routes
Route::prefix('approval')->middleware(['auth:sanctum'])->group(function () {

    // Approval Flow Management
    Route::prefix('flows')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ApprovalFlowApiController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\ApprovalFlowApiController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\ApprovalFlowApiController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\ApprovalFlowApiController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\ApprovalFlowApiController::class, 'destroy']);
        Route::post('/{id}/clone', [App\Http\Controllers\Api\ApprovalFlowApiController::class, 'clone']);
        Route::get('/document-type/{documentType}', [App\Http\Controllers\Api\ApprovalFlowApiController::class, 'getByDocumentType']);
        Route::get('/{id}/statistics', [App\Http\Controllers\Api\ApprovalFlowApiController::class, 'statistics']);
    });

    // Approval Actions
    Route::prefix('actions')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ApprovalActionApiController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\ApprovalActionApiController::class, 'show']);
        Route::post('/submit', [App\Http\Controllers\Api\ApprovalActionApiController::class, 'submit']);
        Route::post('/{approvalId}/process', [App\Http\Controllers\Api\ApprovalActionApiController::class, 'process']);
        Route::post('/{approvalId}/cancel', [App\Http\Controllers\Api\ApprovalActionApiController::class, 'cancel']);
        Route::post('/{approvalId}/escalate', [App\Http\Controllers\Api\ApprovalActionApiController::class, 'escalate']);
        Route::get('/{approvalId}/next-approvers', [App\Http\Controllers\Api\ApprovalActionApiController::class, 'nextApprovers']);
        Route::get('/{approvalId}/actions', [App\Http\Controllers\Api\ApprovalActionApiController::class, 'approvalActions']);
        Route::post('/bulk-process', [App\Http\Controllers\Api\ApprovalActionApiController::class, 'bulkProcess']);
    });

    // Approval Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/overview', [App\Http\Controllers\Api\ApprovalDashboardApiController::class, 'overview']);
        Route::get('/pending', [App\Http\Controllers\Api\ApprovalDashboardApiController::class, 'pending']);
        Route::get('/history', [App\Http\Controllers\Api\ApprovalDashboardApiController::class, 'history']);
        Route::get('/statistics', [App\Http\Controllers\Api\ApprovalDashboardApiController::class, 'statistics']);
        Route::get('/monitoring', [App\Http\Controllers\Api\ApprovalDashboardApiController::class, 'monitoring']);
    });

    // Approval Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ApprovalNotificationApiController::class, 'index']);
        Route::get('/unread', [App\Http\Controllers\Api\ApprovalNotificationApiController::class, 'unread']);
        Route::post('/{id}/mark-read', [App\Http\Controllers\Api\ApprovalNotificationApiController::class, 'markAsRead']);
        Route::post('/mark-all-read', [App\Http\Controllers\Api\ApprovalNotificationApiController::class, 'markAllAsRead']);
        Route::delete('/{id}', [App\Http\Controllers\Api\ApprovalNotificationApiController::class, 'destroy']);
        Route::delete('/clear-old', [App\Http\Controllers\Api\ApprovalNotificationApiController::class, 'clearOld']);
    });

    // Approval Permissions
    Route::prefix('permissions')->group(function () {
        Route::get('/user', [App\Http\Controllers\Api\ApprovalPermissionApiController::class, 'getUserPermissions']);
        Route::get('/can-approve/{approvalId}', [App\Http\Controllers\Api\ApprovalPermissionApiController::class, 'canApprove']);
        Route::get('/can-view/{approvalId}', [App\Http\Controllers\Api\ApprovalPermissionApiController::class, 'canView']);
        Route::get('/can-manage-flows', [App\Http\Controllers\Api\ApprovalPermissionApiController::class, 'canManageFlows']);
        Route::get('/approvers/{documentType}', [App\Http\Controllers\Api\ApprovalPermissionApiController::class, 'getApprovers']);
    });

    // Approval Audit
    Route::prefix('audit')->group(function () {
        Route::get('/approval/{approvalId}', [App\Http\Controllers\Api\ApprovalAuditApiController::class, 'getApprovalAudit']);
        Route::get('/user/{userId}', [App\Http\Controllers\Api\ApprovalAuditApiController::class, 'getUserAudit']);
        Route::get('/system', [App\Http\Controllers\Api\ApprovalAuditApiController::class, 'getSystemAudit']);
        Route::get('/statistics', [App\Http\Controllers\Api\ApprovalAuditApiController::class, 'getAuditStatistics']);
        Route::get('/export-csv', [App\Http\Controllers\Api\ApprovalAuditApiController::class, 'exportToCsv']);
    });

    // Approval Monitoring
    Route::prefix('monitoring')->group(function () {
        Route::get('/health', [App\Http\Controllers\Api\ApprovalMonitoringApiController::class, 'getHealth']);
        Route::get('/performance', [App\Http\Controllers\Api\ApprovalMonitoringApiController::class, 'getPerformance']);
        Route::get('/alerts', [App\Http\Controllers\Api\ApprovalMonitoringApiController::class, 'getAlerts']);
        Route::get('/uptime', [App\Http\Controllers\Api\ApprovalMonitoringApiController::class, 'getUptime']);
        Route::get('/resources', [App\Http\Controllers\Api\ApprovalMonitoringApiController::class, 'getResources']);
        Route::get('/recommendations', [App\Http\Controllers\Api\ApprovalMonitoringApiController::class, 'getRecommendations']);
        Route::get('/report', [App\Http\Controllers\Api\ApprovalMonitoringApiController::class, 'generateReport']);
    });

    // Approval Backup
    Route::prefix('backup')->group(function () {
        Route::post('/create', [App\Http\Controllers\Api\ApprovalBackupApiController::class, 'createBackup']);
        Route::post('/restore', [App\Http\Controllers\Api\ApprovalBackupApiController::class, 'restoreBackup']);
        Route::get('/list', [App\Http\Controllers\Api\ApprovalBackupApiController::class, 'listBackups']);
        Route::delete('/{backupName}', [App\Http\Controllers\Api\ApprovalBackupApiController::class, 'deleteBackup']);
        Route::get('/export-csv', [App\Http\Controllers\Api\ApprovalBackupApiController::class, 'exportToCsv']);
    });

    // Approval Cache Management
    Route::prefix('cache')->group(function () {
        Route::get('/statistics', [App\Http\Controllers\Api\ApprovalCacheApiController::class, 'getStatistics']);
        Route::post('/clear-all', [App\Http\Controllers\Api\ApprovalCacheApiController::class, 'clearAll']);
        Route::post('/warm-up/user/{userId}', [App\Http\Controllers\Api\ApprovalCacheApiController::class, 'warmUpUser']);
        Route::post('/warm-up/flow/{flowId}', [App\Http\Controllers\Api\ApprovalCacheApiController::class, 'warmUpFlow']);
        Route::delete('/invalidate/approval/{approvalId}', [App\Http\Controllers\Api\ApprovalCacheApiController::class, 'invalidateApproval']);
        Route::delete('/invalidate/user/{userId}', [App\Http\Controllers\Api\ApprovalCacheApiController::class, 'invalidateUser']);
        Route::delete('/invalidate/flow/{flowId}', [App\Http\Controllers\Api\ApprovalCacheApiController::class, 'invalidateFlow']);
    });

    // Document Integration
    Route::prefix('integration')->group(function () {
        Route::get('/document-types', [App\Http\Controllers\Api\ApprovalIntegrationApiController::class, 'getDocumentTypes']);
        Route::post('/register-document-type', [App\Http\Controllers\Api\ApprovalIntegrationApiController::class, 'registerDocumentType']);
        Route::delete('/unregister-document-type/{documentType}', [App\Http\Controllers\Api\ApprovalIntegrationApiController::class, 'unregisterDocumentType']);
        Route::post('/migrate-data', [App\Http\Controllers\Api\ApprovalIntegrationApiController::class, 'migrateExistingData']);
        Route::get('/validate/{documentType}', [App\Http\Controllers\Api\ApprovalIntegrationApiController::class, 'validateIntegration']);
        Route::get('/statistics/{documentType}', [App\Http\Controllers\Api\ApprovalIntegrationApiController::class, 'getDocumentStatistics']);
    });
});

// Public routes (if any)
Route::get('/approval/health', [App\Http\Controllers\Api\ApprovalMonitoringApiController::class, 'getHealth']);
Route::get('/approval/status', [App\Http\Controllers\Api\ApprovalMonitoringApiController::class, 'getStatus']);
