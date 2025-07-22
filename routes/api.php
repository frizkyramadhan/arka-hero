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

    // API routes removed - system simplified
});

// Public routes removed - system simplified
