<?php

use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EmployeeApiController;
use App\Http\Controllers\Api\V1\EmployeeWorkforceApiController;
use App\Http\Controllers\Api\V1\ItWoZoomCallbackController;
use App\Http\Controllers\Api\V1\LeaveReportController;
use App\Http\Controllers\Api\V1\LeaveTypeController;
use App\Http\Controllers\Api\V1\LetterNumberApiController;
use App\Http\Controllers\Api\V1\LetterNumberController;
use App\Http\Controllers\Api\V1\LetterSubjectController;
use App\Http\Controllers\Api\V1\OfficialtravelApiController;
use App\Http\Controllers\Api\V1\PositionController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\FuelClaimApiController;
use App\Http\Controllers\Api\V1\VehicleApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Loaded with prefix "api" and "api" middleware (see RouteServiceProvider).
| Versioned surface: /api/v1/...
| Legacy (no version segment): /api/{resource}
|
*/

/*
|--------------------------------------------------------------------------
| Master data — apiResource (legacy paths: /api/positions, etc.)
|--------------------------------------------------------------------------
*/

Route::apiResource('positions', PositionController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('projects', ProjectController::class);

/*
|--------------------------------------------------------------------------
| Workforce — employee + administration + leave / LOT / overtime
|--------------------------------------------------------------------------
*/
Route::prefix('workforce')->group(function () {
    Route::get('activity', [EmployeeWorkforceApiController::class, 'activityTimelineAll'])
        ->name('api.workforce.activity');
    Route::get('leave-requests', [EmployeeWorkforceApiController::class, 'leaveRequestsAll'])
        ->name('api.workforce.leave-requests');
    Route::get('official-travels', [EmployeeWorkforceApiController::class, 'officialTravelsAll'])
        ->name('api.workforce.official-travels');
    Route::get('overtime-requests', [EmployeeWorkforceApiController::class, 'overtimeRequestsAll'])
        ->name('api.workforce.overtime-requests');
    Route::get('employees/by-nik/{nik}/profile', [EmployeeWorkforceApiController::class, 'showFullByNik'])
        ->name('api.workforce.employees.profile-by-nik');
    Route::get('employees/by-nik/{nik}/activity', [EmployeeWorkforceApiController::class, 'activityTimelineByNik'])
        ->name('api.workforce.employees.activity-by-nik');
    Route::get('employees/by-nik/{nik}/leave-requests', [EmployeeWorkforceApiController::class, 'leaveRequestsByNik'])
        ->name('api.workforce.employees.leave-by-nik');
    Route::get('employees/by-nik/{nik}/official-travels', [EmployeeWorkforceApiController::class, 'officialTravelsByNik'])
        ->name('api.workforce.employees.lot-by-nik');
    Route::get('employees/by-nik/{nik}/overtime-requests', [EmployeeWorkforceApiController::class, 'overtimeRequestsByNik'])
        ->name('api.workforce.employees.overtime-by-nik');

    Route::get('employees/{employee}/profile', [EmployeeWorkforceApiController::class, 'showFull'])
        ->name('api.workforce.employees.profile');
    Route::get('employees/{employee}/activity', [EmployeeWorkforceApiController::class, 'activityTimeline'])
        ->name('api.workforce.employees.activity');
    Route::get('employees/{employee}/leave-requests', [EmployeeWorkforceApiController::class, 'leaveRequests'])
        ->name('api.workforce.employees.leave-requests');
    Route::get('employees/{employee}/official-travels', [EmployeeWorkforceApiController::class, 'officialTravels'])
        ->name('api.workforce.employees.official-travels');
    Route::get('employees/{employee}/overtime-requests', [EmployeeWorkforceApiController::class, 'overtimeRequests'])
        ->name('api.workforce.employees.overtime-requests');
});

/*
|--------------------------------------------------------------------------
| Employees
|--------------------------------------------------------------------------
*/
Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeApiController::class, 'index'])->name('api.employees.index');
    Route::get('/list', [EmployeeApiController::class, 'getEmployees'])->name('api.employees.list');
    Route::get('/active', [EmployeeApiController::class, 'activeEmployees']);
    Route::post('/search', [EmployeeApiController::class, 'search']);
    Route::get('/by-nik/{nik}', [EmployeeApiController::class, 'showByNik'])->name('api.employees.show-by-nik');
    Route::get('/{id}', [EmployeeApiController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Official travels
|--------------------------------------------------------------------------
*/
Route::prefix('official-travels')->group(function () {
    Route::post('/search', [OfficialtravelApiController::class, 'search']);
    // Backward compatibility: returns already-claimed records
    Route::post('/search-claimed', [OfficialtravelApiController::class, 'search_claimed']);
    // Claimable: finished destination and not yet claimed
    Route::post('/search-claimable', [OfficialtravelApiController::class, 'search_claimable']);
    Route::post('/detail', [OfficialtravelApiController::class, 'show']);
    Route::put('/claim', [OfficialtravelApiController::class, 'updateClaim']);
});

/*
|--------------------------------------------------------------------------
| Letter numbering — /api/v1/letter-numbers
|--------------------------------------------------------------------------
*/

Route::prefix('letter-numbers')->group(function () {
    Route::post('/request', [LetterNumberApiController::class, 'requestNumber']);
    Route::post('/{id}/mark-as-used', [LetterNumberApiController::class, 'markAsUsed']);
    Route::post('/{id}/cancel', [LetterNumberApiController::class, 'cancelNumber']);

    // Numeric category id (must not share path with /available/{categoryCode} on LetterNumberController)
    Route::get('/available/id/{categoryId}', [LetterNumberApiController::class, 'getAvailableNumbers']);
    Route::get('/subjects/{categoryId}', [LetterNumberApiController::class, 'getSubjectsByCategory']);
    Route::get('/{id}', [LetterNumberApiController::class, 'getLetterNumber']);

    Route::post('/check-availability', [LetterNumberApiController::class, 'checkAvailability']);
    Route::post('/preview-next-number', [LetterNumberApiController::class, 'previewNextNumber']);
});

Route::prefix('letter-subjects')->group(function () {
    Route::get('/by-category/{categoryId}', [LetterSubjectController::class, 'getByCategory'])
        ->name('api.letter-subjects.by-category');
});

/*
|--------------------------------------------------------------------------
| Letter helpers — legacy paths (App\Http\Controllers\Api\V1 copies of web controllers)
|--------------------------------------------------------------------------
*/
Route::get('letter-subjects/available/{documentType}/{categoryId}', [LetterSubjectController::class, 'getAvailableSubjectsForDocument']);
Route::get('letter-numbers/available/{categoryCode}', [LetterNumberController::class, 'getAvailableNumbers'])
    ->name('api.letter-numbers.available');

/*
|--------------------------------------------------------------------------
| Integrations — IT WO Zoom callback
|--------------------------------------------------------------------------
*/
Route::prefix('v1/integrations/it-wo')->group(function () {
    Route::post('zoom-callback', ItWoZoomCallbackController::class)
        ->name('api.v1.integrations.it-wo.zoom-callback');
});

/*
|--------------------------------------------------------------------------
| Vehicles — document validity (GAMMA)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    Route::get('vehicles', [VehicleApiController::class, 'index'])
        ->name('api.v1.vehicles.index');
    Route::get('vehicles/{vehicle}', [VehicleApiController::class, 'show'])
        ->name('api.v1.vehicles.show');
    Route::get('vehicle-documents/expiring', [VehicleApiController::class, 'expiringDocuments'])
        ->name('api.v1.vehicle-documents.expiring');

    // Fuel claims — external fund realization app
    Route::get('fuel-claims', [FuelClaimApiController::class, 'index'])
        ->name('api.v1.fuel-claims.index');
    Route::get('fuel-claims/{fuelClaim}', [FuelClaimApiController::class, 'show'])
        ->name('api.v1.fuel-claims.show');
    Route::put('fuel-claims/{fuelClaim}/sent', [FuelClaimApiController::class, 'markSent'])
        ->name('api.v1.fuel-claims.sent');
    Route::put('fuel-claims/{fuelClaim}/realized', [FuelClaimApiController::class, 'markRealized'])
        ->name('api.v1.fuel-claims.realized');
});

/*
|--------------------------------------------------------------------------
| Leave
|--------------------------------------------------------------------------
*/
Route::prefix('leave')->group(function () {
    Route::get('types', [LeaveTypeController::class, 'apiIndex'])->name('api.leave.types');
    Route::get('types/{leaveType}', [LeaveTypeController::class, 'show']);
    Route::get('types/{leaveType}/statistics', [LeaveTypeController::class, 'statistics']);

    Route::get('employees/{employee}/balance', [LeaveReportController::class, 'getEmployeeLeaveBalance']);
});
