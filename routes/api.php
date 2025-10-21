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
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveReportController;

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
    Route::get('/', [EmployeeApiController::class, 'index'])->name('api.employees.index');
    Route::get('/list', [EmployeeApiController::class, 'getEmployees'])->name('api.employees.list');
    Route::get('/active', [EmployeeApiController::class, 'activeEmployees']);
    Route::post('/search', [EmployeeApiController::class, 'search']);
    Route::get('/{id}', [EmployeeApiController::class, 'show']);
});
// Official Travel API Routes
Route::prefix('official-travels')->group(function () {
    Route::post('/search', [OfficialtravelApiController::class, 'search']);
    // Backward compatibility: this now returns already-claimed records
    Route::post('/search-claimed', [OfficialtravelApiController::class, 'search_claimed']);
    // New: claimable = finished trip (departure_from_destination not null) and not yet claimed
    Route::post('/search-claimable', [OfficialtravelApiController::class, 'search_claimable']);
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

    // API routes removed - system simplified
});

// Leave Management API Routes
Route::prefix('leave')->group(function () {
    // Leave Types API
    Route::get('types', [LeaveTypeController::class, 'apiIndex'])->name('api.leave.types');
    Route::get('types/{leaveType}', [LeaveTypeController::class, 'show']);
    Route::get('types/{leaveType}/statistics', [LeaveTypeController::class, 'statistics']);

    // Leave Reports API
    Route::get('employees/{employee}/balance', [LeaveReportController::class, 'getEmployeeLeaveBalance']);
});

// Additional API endpoints based on web routes
Route::prefix('v1')->group(function () {

    // Authentication API Routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [App\Http\Controllers\AuthController::class, 'apiLogin']);
        Route::post('/logout', [App\Http\Controllers\AuthController::class, 'apiLogout'])->middleware('auth:sanctum');
        Route::get('/user', [App\Http\Controllers\AuthController::class, 'apiUser'])->middleware('auth:sanctum');
    });

    // Dashboard API Routes
    Route::prefix('dashboard')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/stats', [App\Http\Controllers\DashboardController::class, 'apiStats']);
        Route::get('/pending-recommendations', [App\Http\Controllers\DashboardController::class, 'apiPendingRecommendations']);
        Route::get('/pending-approvals', [App\Http\Controllers\DashboardController::class, 'apiPendingApprovals']);
        Route::get('/pending-arrivals', [App\Http\Controllers\DashboardController::class, 'apiPendingArrivals']);
        Route::get('/pending-departures', [App\Http\Controllers\DashboardController::class, 'apiPendingDepartures']);

        // Employee dashboard
        Route::get('/employees', [App\Http\Controllers\DashboardController::class, 'apiEmployeeDashboard']);
        Route::get('/employees-by-department', [App\Http\Controllers\DashboardController::class, 'apiEmployeesByDepartment']);
        Route::get('/employees-by-project', [App\Http\Controllers\DashboardController::class, 'apiEmployeesByProject']);
        Route::get('/recent-employees', [App\Http\Controllers\DashboardController::class, 'apiRecentEmployees']);

        // Leave management dashboard
        Route::get('/leave-management/open-requests', [App\Http\Controllers\DashboardController::class, 'apiOpenLeaveRequests']);
        Route::get('/leave-management/pending-cancellations', [App\Http\Controllers\DashboardController::class, 'apiPendingCancellations']);
        Route::get('/leave-management/paid-leave-without-docs', [App\Http\Controllers\DashboardController::class, 'apiPaidLeaveWithoutDocs']);
        Route::get('/leave-management/stats', [App\Http\Controllers\DashboardController::class, 'apiLeaveManagementStats']);
    });

    // Master Data API Routes
    Route::prefix('master')->middleware(['auth:sanctum'])->group(function () {
        // Banks
        Route::get('/banks', [App\Http\Controllers\BankController::class, 'apiIndex']);
        Route::post('/banks', [App\Http\Controllers\BankController::class, 'apiStore']);
        Route::put('/banks/{id}', [App\Http\Controllers\BankController::class, 'apiUpdate']);
        Route::delete('/banks/{id}', [App\Http\Controllers\BankController::class, 'apiDestroy']);

        // Religions
        Route::get('/religions', [App\Http\Controllers\ReligionController::class, 'apiIndex']);
        Route::post('/religions', [App\Http\Controllers\ReligionController::class, 'apiStore']);
        Route::put('/religions/{id}', [App\Http\Controllers\ReligionController::class, 'apiUpdate']);
        Route::delete('/religions/{id}', [App\Http\Controllers\ReligionController::class, 'apiDestroy']);

        // Positions
        Route::get('/positions', [App\Http\Controllers\PositionController::class, 'apiIndex']);
        Route::post('/positions', [App\Http\Controllers\PositionController::class, 'apiStore']);
        Route::put('/positions/{id}', [App\Http\Controllers\PositionController::class, 'apiUpdate']);
        Route::delete('/positions/{id}', [App\Http\Controllers\PositionController::class, 'apiDestroy']);
        Route::post('/positions/import', [App\Http\Controllers\PositionController::class, 'apiImport']);
        Route::get('/positions/export', [App\Http\Controllers\PositionController::class, 'apiExport']);

        // Grades
        Route::get('/grades', [App\Http\Controllers\GradeController::class, 'apiIndex']);
        Route::post('/grades', [App\Http\Controllers\GradeController::class, 'apiStore']);
        Route::put('/grades/{id}', [App\Http\Controllers\GradeController::class, 'apiUpdate']);
        Route::delete('/grades/{id}', [App\Http\Controllers\GradeController::class, 'apiDestroy']);
        Route::post('/grades/{id}/toggle-status', [App\Http\Controllers\GradeController::class, 'apiToggleStatus']);

        // Levels
        Route::get('/levels', [App\Http\Controllers\LevelController::class, 'apiIndex']);
        Route::post('/levels', [App\Http\Controllers\LevelController::class, 'apiStore']);
        Route::put('/levels/{id}', [App\Http\Controllers\LevelController::class, 'apiUpdate']);
        Route::delete('/levels/{id}', [App\Http\Controllers\LevelController::class, 'apiDestroy']);
        Route::post('/levels/{id}/toggle-status', [App\Http\Controllers\LevelController::class, 'apiToggleStatus']);

        // Transportations
        Route::get('/transportations', [App\Http\Controllers\TransportationController::class, 'apiIndex']);
        Route::post('/transportations', [App\Http\Controllers\TransportationController::class, 'apiStore']);
        Route::put('/transportations/{id}', [App\Http\Controllers\TransportationController::class, 'apiUpdate']);
        Route::delete('/transportations/{id}', [App\Http\Controllers\TransportationController::class, 'apiDestroy']);

        // Accommodations
        Route::get('/accommodations', [App\Http\Controllers\AccommodationController::class, 'apiIndex']);
        Route::post('/accommodations', [App\Http\Controllers\AccommodationController::class, 'apiStore']);
        Route::put('/accommodations/{id}', [App\Http\Controllers\AccommodationController::class, 'apiUpdate']);
        Route::delete('/accommodations/{id}', [App\Http\Controllers\AccommodationController::class, 'apiDestroy']);
    });

    // Employee Management API Routes
    Route::prefix('employees')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [App\Http\Controllers\EmployeeController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\EmployeeController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\EmployeeController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\EmployeeController::class, 'apiUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\EmployeeController::class, 'apiDestroy']);
        Route::post('/import', [App\Http\Controllers\EmployeeController::class, 'apiImport']);
        Route::get('/export', [App\Http\Controllers\EmployeeController::class, 'apiExport']);
        Route::get('/{id}/print', [App\Http\Controllers\EmployeeController::class, 'apiPrint']);

        // Employee sub-resources
        Route::get('/{id}/licenses', [App\Http\Controllers\LicenseController::class, 'apiIndex']);
        Route::post('/{id}/licenses', [App\Http\Controllers\LicenseController::class, 'apiStore']);
        Route::delete('/{id}/licenses/{licenseId}', [App\Http\Controllers\LicenseController::class, 'apiDestroy']);

        Route::get('/{id}/insurances', [App\Http\Controllers\InsuranceController::class, 'apiIndex']);
        Route::post('/{id}/insurances', [App\Http\Controllers\InsuranceController::class, 'apiStore']);
        Route::delete('/{id}/insurances/{insuranceId}', [App\Http\Controllers\InsuranceController::class, 'apiDestroy']);

        Route::get('/{id}/families', [App\Http\Controllers\FamilieController::class, 'apiIndex']);
        Route::post('/{id}/families', [App\Http\Controllers\FamilieController::class, 'apiStore']);
        Route::delete('/{id}/families/{familyId}', [App\Http\Controllers\FamilieController::class, 'apiDestroy']);

        Route::get('/{id}/courses', [App\Http\Controllers\CourseController::class, 'apiIndex']);
        Route::post('/{id}/courses', [App\Http\Controllers\CourseController::class, 'apiStore']);
        Route::delete('/{id}/courses/{courseId}', [App\Http\Controllers\CourseController::class, 'apiDestroy']);

        Route::get('/{id}/educations', [App\Http\Controllers\EducationController::class, 'apiIndex']);
        Route::post('/{id}/educations', [App\Http\Controllers\EducationController::class, 'apiStore']);
        Route::delete('/{id}/educations/{educationId}', [App\Http\Controllers\EducationController::class, 'apiDestroy']);

        Route::get('/{id}/job-experiences', [App\Http\Controllers\JobexperienceController::class, 'apiIndex']);
        Route::post('/{id}/job-experiences', [App\Http\Controllers\JobexperienceController::class, 'apiStore']);
        Route::delete('/{id}/job-experiences/{experienceId}', [App\Http\Controllers\JobexperienceController::class, 'apiDestroy']);

        Route::get('/{id}/banks', [App\Http\Controllers\EmployeebankController::class, 'apiIndex']);
        Route::post('/{id}/banks', [App\Http\Controllers\EmployeebankController::class, 'apiStore']);
        Route::delete('/{id}/banks/{bankId}', [App\Http\Controllers\EmployeebankController::class, 'apiDestroy']);

        Route::get('/{id}/administrations', [App\Http\Controllers\AdministrationController::class, 'apiIndex']);
        Route::post('/{id}/administrations', [App\Http\Controllers\AdministrationController::class, 'apiStore']);
        Route::put('/{id}/administrations/{adminId}', [App\Http\Controllers\AdministrationController::class, 'apiUpdate']);
        Route::delete('/{id}/administrations/{adminId}', [App\Http\Controllers\AdministrationController::class, 'apiDestroy']);

        Route::get('/{id}/tax-identifications', [App\Http\Controllers\TaxidentificationController::class, 'apiIndex']);
        Route::post('/{id}/tax-identifications', [App\Http\Controllers\TaxidentificationController::class, 'apiStore']);
        Route::delete('/{id}/tax-identifications/{taxId}', [App\Http\Controllers\TaxidentificationController::class, 'apiDestroy']);

        // Employee bonds
        Route::get('/{id}/bonds', [App\Http\Controllers\EmployeeBondController::class, 'apiIndex']);
        Route::post('/{id}/bonds', [App\Http\Controllers\EmployeeBondController::class, 'apiStore']);
        Route::put('/{id}/bonds/{bondId}', [App\Http\Controllers\EmployeeBondController::class, 'apiUpdate']);
        Route::delete('/{id}/bonds/{bondId}', [App\Http\Controllers\EmployeeBondController::class, 'apiDestroy']);
        Route::patch('/{id}/bonds/{bondId}/complete', [App\Http\Controllers\EmployeeBondController::class, 'apiMarkAsCompleted']);
    });

    // Official Travel API Routes
    Route::prefix('official-travels')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [App\Http\Controllers\OfficialtravelController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\OfficialtravelController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\OfficialtravelController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\OfficialtravelController::class, 'apiUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\OfficialtravelController::class, 'apiDestroy']);
        Route::post('/{id}/submit', [App\Http\Controllers\OfficialtravelController::class, 'apiSubmitForApproval']);
        Route::post('/{id}/arrival', [App\Http\Controllers\OfficialtravelController::class, 'apiArrivalStamp']);
        Route::post('/{id}/departure', [App\Http\Controllers\OfficialtravelController::class, 'apiDepartureStamp']);
        Route::patch('/{id}/close', [App\Http\Controllers\OfficialtravelController::class, 'apiClose']);
        Route::get('/{id}/print', [App\Http\Controllers\OfficialtravelController::class, 'apiPrint']);
        Route::get('/export', [App\Http\Controllers\OfficialtravelController::class, 'apiExport']);
    });

    // Recruitment API Routes
    Route::prefix('recruitment')->middleware(['auth:sanctum'])->group(function () {
        // FPTK (Recruitment Requests)
        Route::prefix('requests')->group(function () {
            Route::get('/', [App\Http\Controllers\RecruitmentRequestController::class, 'apiIndex']);
            Route::post('/', [App\Http\Controllers\RecruitmentRequestController::class, 'apiStore']);
            Route::get('/{id}', [App\Http\Controllers\RecruitmentRequestController::class, 'apiShow']);
            Route::put('/{id}', [App\Http\Controllers\RecruitmentRequestController::class, 'apiUpdate']);
            Route::delete('/{id}', [App\Http\Controllers\RecruitmentRequestController::class, 'apiDestroy']);
            Route::post('/{id}/submit', [App\Http\Controllers\RecruitmentRequestController::class, 'apiSubmitForApproval']);
            Route::post('/{id}/acknowledge', [App\Http\Controllers\RecruitmentRequestController::class, 'apiAcknowledge']);
            Route::post('/{id}/approve-pm', [App\Http\Controllers\RecruitmentRequestController::class, 'apiApproveByPM']);
            Route::post('/{id}/approve-director', [App\Http\Controllers\RecruitmentRequestController::class, 'apiApproveByDirector']);
            Route::get('/{id}/print', [App\Http\Controllers\RecruitmentRequestController::class, 'apiPrint']);
        });

        // Candidates
        Route::prefix('candidates')->group(function () {
            Route::get('/', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiIndex']);
            Route::post('/', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiStore']);
            Route::get('/{id}', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiShow']);
            Route::put('/{id}', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiUpdate']);
            Route::delete('/{id}', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiDestroy']);
            Route::post('/{id}/apply-to-fptk', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiApplyToFPTK']);
            Route::post('/{id}/blacklist', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiBlacklist']);
            Route::post('/{id}/remove-from-blacklist', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiRemoveFromBlacklist']);
            Route::get('/{id}/download-cv', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiDownloadCV']);
            Route::delete('/{id}/delete-cv', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiDeleteCV']);
            Route::get('/{id}/print', [App\Http\Controllers\RecruitmentCandidateController::class, 'apiPrint']);
        });

        // Sessions
        Route::prefix('sessions')->group(function () {
            Route::get('/', [App\Http\Controllers\RecruitmentSessionController::class, 'apiIndex']);
            Route::post('/', [App\Http\Controllers\RecruitmentSessionController::class, 'apiStore']);
            Route::get('/{id}', [App\Http\Controllers\RecruitmentSessionController::class, 'apiShow']);
            Route::put('/{id}', [App\Http\Controllers\RecruitmentSessionController::class, 'apiUpdate']);
            Route::delete('/{id}', [App\Http\Controllers\RecruitmentSessionController::class, 'apiDestroy']);
            Route::post('/{id}/update-cv-review', [App\Http\Controllers\RecruitmentSessionController::class, 'apiUpdateCvReview']);
            Route::post('/{id}/update-psikotes', [App\Http\Controllers\RecruitmentSessionController::class, 'apiUpdatePsikotes']);
            Route::post('/{id}/update-tes-teori', [App\Http\Controllers\RecruitmentSessionController::class, 'apiUpdateTesTeori']);
            Route::post('/{id}/update-interview', [App\Http\Controllers\RecruitmentSessionController::class, 'apiUpdateInterview']);
            Route::post('/{id}/update-offering', [App\Http\Controllers\RecruitmentSessionController::class, 'apiUpdateOffering']);
            Route::post('/{id}/update-mcu', [App\Http\Controllers\RecruitmentSessionController::class, 'apiUpdateMcu']);
            Route::post('/{id}/update-hiring', [App\Http\Controllers\RecruitmentSessionController::class, 'apiUpdateHiring']);
            Route::post('/{id}/close-request', [App\Http\Controllers\RecruitmentSessionController::class, 'apiCloseRequest']);
        });
    });

    // Leave Management API Routes
    Route::prefix('leave')->middleware(['auth:sanctum'])->group(function () {
        // Leave Requests
        Route::prefix('requests')->group(function () {
            Route::get('/', [App\Http\Controllers\LeaveRequestController::class, 'apiIndex']);
            Route::post('/', [App\Http\Controllers\LeaveRequestController::class, 'apiStore']);
            Route::get('/{id}', [App\Http\Controllers\LeaveRequestController::class, 'apiShow']);
            Route::put('/{id}', [App\Http\Controllers\LeaveRequestController::class, 'apiUpdate']);
            Route::delete('/{id}', [App\Http\Controllers\LeaveRequestController::class, 'apiDestroy']);
            Route::post('/{id}/approve', [App\Http\Controllers\LeaveRequestController::class, 'apiApprove']);
            Route::post('/{id}/reject', [App\Http\Controllers\LeaveRequestController::class, 'apiReject']);
            Route::post('/{id}/close', [App\Http\Controllers\LeaveRequestController::class, 'apiClose']);
            Route::post('/{id}/cancellation', [App\Http\Controllers\LeaveRequestController::class, 'apiStoreCancellation']);
            Route::post('/cancellations/{cancellationId}/approve', [App\Http\Controllers\LeaveRequestController::class, 'apiApproveCancellation']);
            Route::post('/cancellations/{cancellationId}/reject', [App\Http\Controllers\LeaveRequestController::class, 'apiRejectCancellation']);
            Route::get('/{id}/download', [App\Http\Controllers\LeaveRequestController::class, 'apiDownload']);
            Route::post('/{id}/upload', [App\Http\Controllers\LeaveRequestController::class, 'apiUpload']);
            Route::delete('/{id}/delete-document', [App\Http\Controllers\LeaveRequestController::class, 'apiDeleteDocument']);
        });

        // Leave Entitlements
        Route::prefix('entitlements')->group(function () {
            Route::get('/', [App\Http\Controllers\LeaveEntitlementController::class, 'apiIndex']);
            Route::post('/', [App\Http\Controllers\LeaveEntitlementController::class, 'apiStore']);
            Route::get('/{id}', [App\Http\Controllers\LeaveEntitlementController::class, 'apiShow']);
            Route::put('/{id}', [App\Http\Controllers\LeaveEntitlementController::class, 'apiUpdate']);
            Route::delete('/{id}', [App\Http\Controllers\LeaveEntitlementController::class, 'apiDestroy']);
            Route::post('/generate-project', [App\Http\Controllers\LeaveEntitlementController::class, 'apiGenerateProjectEntitlements']);
            Route::post('/clear-entitlements', [App\Http\Controllers\LeaveEntitlementController::class, 'apiClearAllEntitlements']);
            Route::get('/employee/{employeeId}', [App\Http\Controllers\LeaveEntitlementController::class, 'apiShowEmployee']);
            Route::put('/employee/{employeeId}', [App\Http\Controllers\LeaveEntitlementController::class, 'apiUpdateEmployee']);
        });

        // Leave Types
        Route::prefix('types')->group(function () {
            Route::get('/', [App\Http\Controllers\LeaveTypeController::class, 'apiIndex']);
            Route::post('/', [App\Http\Controllers\LeaveTypeController::class, 'apiStore']);
            Route::get('/{id}', [App\Http\Controllers\LeaveTypeController::class, 'apiShow']);
            Route::put('/{id}', [App\Http\Controllers\LeaveTypeController::class, 'apiUpdate']);
            Route::delete('/{id}', [App\Http\Controllers\LeaveTypeController::class, 'apiDestroy']);
            Route::post('/{id}/toggle-status', [App\Http\Controllers\LeaveTypeController::class, 'apiToggleStatus']);
        });
    });

    // User Management API Routes
    Route::prefix('users')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [App\Http\Controllers\UserController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\UserController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\UserController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\UserController::class, 'apiUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\UserController::class, 'apiDestroy']);
    });

    // Role Management API Routes
    Route::prefix('roles')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [App\Http\Controllers\RoleController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\RoleController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\RoleController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\RoleController::class, 'apiUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\RoleController::class, 'apiDestroy']);
    });

    // Permission Management API Routes
    Route::prefix('permissions')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [App\Http\Controllers\PermissionController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\PermissionController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\PermissionController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\PermissionController::class, 'apiUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\PermissionController::class, 'apiDestroy']);
    });

    // Termination API Routes
    Route::prefix('terminations')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [App\Http\Controllers\TerminationController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\TerminationController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\TerminationController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\TerminationController::class, 'apiUpdate']);
        Route::post('/mass-termination', [App\Http\Controllers\TerminationController::class, 'apiMassTermination']);
        Route::delete('/{id}', [App\Http\Controllers\TerminationController::class, 'apiDestroy']);
    });

    // Bond Violation API Routes
    Route::prefix('bond-violations')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [App\Http\Controllers\BondViolationController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\BondViolationController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\BondViolationController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\BondViolationController::class, 'apiUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\BondViolationController::class, 'apiDestroy']);
        Route::post('/calculate-penalty', [App\Http\Controllers\BondViolationController::class, 'apiCalculatePenalty']);
    });

    // Email API Routes
    Route::prefix('emails')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [App\Http\Controllers\EmailController::class, 'apiIndex']);
        Route::post('/send', [App\Http\Controllers\EmailController::class, 'apiSendMail']);
    });

    // Employee Registration Admin API Routes
    Route::prefix('employee-registrations')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiIndex']);
        Route::get('/pending', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiGetPendingRegistrations']);
        Route::get('/tokens', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiGetTokens']);
        Route::post('/invite', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiInvite']);
        Route::post('/bulk-invite', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiBulkInvite']);
        Route::post('/tokens/{tokenId}/resend', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiResendInvitation']);
        Route::delete('/tokens/{tokenId}', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiDeleteToken']);
        Route::get('/stats', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiGetStats']);
        Route::post('/cleanup', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiCleanupExpiredTokens']);
        Route::get('/{id}', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiShow']);
        Route::post('/{id}/approve', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiApprove']);
        Route::post('/{id}/reject', [App\Http\Controllers\EmployeeRegistrationAdminController::class, 'apiReject']);
    });
});

// Public routes removed - system simplified
