<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\FamilieController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmrgcallController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\PHPMailerController;

use App\Http\Controllers\DepartmentController;
// Removed import for deleted controller
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\LeaveReportController;
use App\Http\Controllers\TerminationController;

use App\Http\Controllers\ApprovalPlanController;
use App\Http\Controllers\EmployeebankController;
use App\Http\Controllers\EmployeeBondController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\BulkLeaveRequestController;
use App\Http\Controllers\LetterNumberController;
use App\Http\Controllers\OperableunitController;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\ApprovalStageController;
use App\Http\Controllers\BondViolationController;

// Removed import for deleted controller
use App\Http\Controllers\JobexperienceController;

use App\Http\Controllers\LetterSubjectController;
use App\Http\Controllers\AdditionaldataController;
// Removed import for deleted controller
use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\LetterCategoryController;

// Approval System Controllers
use App\Http\Controllers\OfficialtravelController;
use App\Http\Controllers\TransportationController;
use App\Http\Controllers\ApprovalRequestController;
use App\Http\Controllers\LeaveEntitlementController;
use App\Http\Controllers\RecruitmentReportController;
use App\Http\Controllers\TaxidentificationController;
use App\Http\Controllers\RecruitmentRequestController;
use App\Http\Controllers\RecruitmentSessionController;
use App\Http\Controllers\EmployeeRegistrationController;
use App\Http\Controllers\RecruitmentCandidateController;
use App\Http\Controllers\EmployeeRegistrationAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('register', [RegisterController::class, 'index'])->name('register')->middleware('guest');
Route::post('register', [RegisterController::class, 'store']);

Route::get('login', [AuthController::class, 'getLogin'])->name('login')->middleware('guest');
Route::post('login', [AuthController::class, 'postLogin'])->name('login.post');

// PUBLIC EMPLOYEE SELF-SERVICE REGISTRATION ROUTES
Route::prefix('employee-registration')->group(function () {
    Route::get('/expired', [EmployeeRegistrationController::class, 'expired'])->name('employee.registration.expired');
    Route::get('/{token}', [EmployeeRegistrationController::class, 'showForm'])->name('employee.registration.form');
    Route::post('/{token}', [EmployeeRegistrationController::class, 'store'])->name('employee.registration.store');
    Route::post('/{token}/upload', [EmployeeRegistrationController::class, 'uploadDocument'])->name('employee.registration.upload');
    Route::get('/{token}/success', [EmployeeRegistrationController::class, 'success'])->name('employee.registration.success');
})->middleware(['throttle:10,1']); // Rate limiting

Route::group(['middleware' => ['auth']], function () {
    // Route::get('/', [ProfileController::class, 'dashboard'])->name('dashboard');
    Route::get('dashboard/getHobpn', [ProfileController::class, 'getHobpn'])->name('hobpn.list');
    Route::get('dashboard/getBojkt', [ProfileController::class, 'getBojkt'])->name('bojkt.list');
    Route::get('dashboard/getKpuc', [ProfileController::class, 'getKpuc'])->name('kpuc.list');
    Route::get('dashboard/getSbi', [ProfileController::class, 'getSbi'])->name('sbi.list');
    Route::get('dashboard/getGpk', [ProfileController::class, 'getGpk'])->name('gpk.list');
    Route::get('dashboard/getBek', [ProfileController::class, 'getBek'])->name('bek.list');
    Route::get('dashboard/getAps', [ProfileController::class, 'getAps'])->name('aps.list');
    // Route::get('dashboard/getEmployee', [ProfileController::class, 'getEmployee'])->name('employee.list');
    // Route::get('dashboard/getTermination', [ProfileController::class, 'getTermination'])->name('termination.list');
    // Route::get('dashboard/getContract', [ProfileController::class, 'getContract'])->name('contract.list');
    // Route::post('dashboard/sendEmail', [ProfileController::class, 'sendEmail'])->name('sendEmail');

    // New project summary routes
    Route::get('summary/project/{projectId}', [ProfileController::class, 'projectSummary'])->name('projects.summary');
    Route::get('summary/project/{projectId}/employees', [ProfileController::class, 'getEmployeesByProject'])->name('projects.employees');

    // New department summary routes
    Route::get('summary/department/{departmentId}', [ProfileController::class, 'departmentSummary'])->name('departments.summary');
    Route::get('summary/department/{departmentId}/employees', [ProfileController::class, 'getEmployeesByDepartment'])->name('departments.employees');

    // New employee classification routes
    Route::get('summary/staff', [ProfileController::class, 'staffSummary'])->name('employees.staff');
    Route::get('summary/staff/employees', [ProfileController::class, 'getStaffEmployees'])->name('employees.staff.list');
    Route::get('summary/employment', [ProfileController::class, 'employmentSummary'])->name('employees.employment');
    Route::get('summary/employment/employees', [ProfileController::class, 'getEmploymentEmployees'])->name('employees.employment.list');
    Route::get('summary/birthday', [ProfileController::class, 'birthdaySummary'])->name('employees.birthday');
    Route::get('summary/birthday/employees', [ProfileController::class, 'getBirthdayEmployees'])->name('employees.birthday.list');

    // Profile routes
    Route::get('profile/my-profile', [ProfileController::class, 'myProfile'])->name('profile.my-profile');
    Route::get('profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password');
    Route::put('profile/change-password', [ProfileController::class, 'updatePassword'])->name('profile.change-password.update');

    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/pending-recommendations', [DashboardController::class, 'pendingRecommendations'])->name('dashboard.pendingRecommendations');
    Route::get('/dashboard/pending-approvals', [DashboardController::class, 'pendingApprovals'])->name('dashboard.pendingApprovals');
    Route::get('/dashboard/pending-arrivals', [DashboardController::class, 'pendingArrivals'])->name('dashboard.pendingArrivals');
    Route::get('/dashboard/pending-departures', [DashboardController::class, 'pendingDepartures'])->name('dashboard.pendingDepartures');

    // Split dashboard routes
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/employees', [DashboardController::class, 'employee'])->name('employees');
        Route::get('/official-travel', [DashboardController::class, 'officialTravel'])->name('officialtravel');
        Route::get('/recruitment', [DashboardController::class, 'recruitment'])->name('recruitment');
        Route::get('/letter-administration', [DashboardController::class, 'letterAdministration'])->name('letter-administration');
        Route::get('/leave-management', [DashboardController::class, 'leaveManagement'])->name('leave-management');
        Route::get('/personal', [DashboardController::class, 'personal'])->name('personal');
    });

    // Dashboard Employee routes
    Route::get('/dashboard/employees-by-department', [DashboardController::class, 'employeesByDepartment'])->name('dashboard.employeesByDepartment');
    Route::get('/dashboard/employees-by-project', [DashboardController::class, 'employeesByProject'])->name('dashboard.employeesByProject');
    Route::get('/dashboard/recent-employees', [DashboardController::class, 'recentEmployees'])->name('dashboard.recentEmployees');

    // Dashboard Letter Administration routes
    Route::get('/dashboard/letters-by-category', [DashboardController::class, 'lettersByCategory'])->name('dashboard.lettersByCategory');
    Route::get('/dashboard/recent-letters', [DashboardController::class, 'recentLetters'])->name('dashboard.recentLetters');
    Route::get('/dashboard/letter-administration-stats', [DashboardController::class, 'letterAdministrationStats'])->name('dashboard.letterAdministrationStats');

    // Dashboard Leave Management routes
    Route::get('/dashboard/leave-management/open-requests', [DashboardController::class, 'openLeaveRequests'])->name('dashboard.leave-management.open-requests');
    Route::get('/dashboard/leave-management/pending-cancellations', [DashboardController::class, 'pendingCancellations'])->name('dashboard.leave-management.pending-cancellations');
    Route::get('/dashboard/leave-management/paid-leave-without-docs', [DashboardController::class, 'paidLeaveWithoutDocs'])->name('dashboard.leave-management.paid-leave-without-docs');
    Route::get('/dashboard/leave-management/search-employees', [DashboardController::class, 'searchEmployeeEntitlements'])->name('dashboard.leave-management.search-employees');
    Route::get('/dashboard/leave-management/employees-without-entitlements', [DashboardController::class, 'employeesWithoutEntitlements'])->name('dashboard.leave-management.employees-without-entitlements');
    Route::get('/dashboard/leave-management/employees-with-expiring-entitlements', [DashboardController::class, 'employeesWithExpiringEntitlements'])->name('dashboard.leave-management.employees-with-expiring-entitlements');
    Route::get('/dashboard/leave-management/stats', [DashboardController::class, 'leaveManagementStats'])->name('dashboard.leave-management.stats');

    Route::post('logout', [AuthController::class, 'logout']);

    // ADMINISTRATOR ROUTES

    Route::get('users/data', [UserController::class, 'getUsers'])->name('users.data');
    Route::resource('users', UserController::class);

    Route::get('roles/data', [RoleController::class, 'getRoles'])->name('roles.data');
    Route::resource('roles', RoleController::class);

    Route::get('permissions/data', [PermissionController::class, 'getPermissions'])->name('permissions.data');
    Route::resource('permissions', PermissionController::class);

    // DEBUG ROUTES
    Route::prefix('debug')->name('debug.')->group(function () {
        Route::get('/', [DebugController::class, 'index'])->name('index');
        Route::post('/truncate/employees', [DebugController::class, 'truncateEmployees'])->name('truncate.employees');
        Route::post('/truncate/administrations', [DebugController::class, 'truncateAdministrations'])->name('truncate.administrations');
        Route::post('/truncate/employeebanks', [DebugController::class, 'truncateEmployeebanks'])->name('truncate.employeebanks');
        Route::post('/truncate/taxidentifications', [DebugController::class, 'truncateTaxidentifications'])->name('truncate.taxidentifications');
        Route::post('/truncate/insurances', [DebugController::class, 'truncateInsurances'])->name('truncate.insurances');
        Route::post('/truncate/licenses', [DebugController::class, 'truncateLicenses'])->name('truncate.licenses');
        Route::post('/truncate/families', [DebugController::class, 'truncateFamilies'])->name('truncate.families');
        Route::post('/truncate/educations', [DebugController::class, 'truncateEducations'])->name('truncate.educations');
        Route::post('/truncate/courses', [DebugController::class, 'truncateCourses'])->name('truncate.courses');
        Route::post('/truncate/jobexperiences', [DebugController::class, 'truncateJobexperiences'])->name('truncate.jobexperiences');
        Route::post('/truncate/operableunits', [DebugController::class, 'truncateOperableunits'])->name('truncate.operableunits');
        Route::post('/truncate/emrgcalls', [DebugController::class, 'truncateEmrgcalls'])->name('truncate.emrgcalls');
        Route::post('/truncate/additionaldatas', [DebugController::class, 'truncateAdditionaldatas'])->name('truncate.additionaldatas');

        // Recruitment tables truncate routes
        Route::post('/truncate/recruitment-requests', [DebugController::class, 'truncateRecruitmentRequests'])->name('truncate.recruitment_requests');
        Route::post('/truncate/recruitment-candidates', [DebugController::class, 'truncateRecruitmentCandidates'])->name('truncate.recruitment_candidates');
        Route::post('/truncate/recruitment-sessions', [DebugController::class, 'truncateRecruitmentSessions'])->name('truncate.recruitment_sessions');
        Route::post('/truncate/recruitment-stages', [DebugController::class, 'truncateRecruitmentStages'])->name('truncate.recruitment_stages');
        Route::post('/truncate/recruitment-all', [DebugController::class, 'truncateRecruitmentAll'])->name('truncate.recruitment_all');

        // Letter tables truncate routes
        Route::post('/truncate/letter-numbers', [DebugController::class, 'truncateLetterNumbers'])->name('truncate.letter_numbers');
        Route::post('/truncate/letter-categories', [DebugController::class, 'truncateLetterCategories'])->name('truncate.letter_categories');
        Route::post('/truncate/letter-subjects', [DebugController::class, 'truncateLetterSubjects'])->name('truncate.letter_subjects');
        Route::post('/truncate/letter-all', [DebugController::class, 'truncateLetterAll'])->name('truncate.letter_all');

        Route::post('/truncate/all', [DebugController::class, 'truncateAll'])->name('truncate.all');
    });

    // MASTER DATA ROUTES

    Route::get('banks/data', [BankController::class, 'getBanks'])->name('banks.data');
    Route::resource('banks', BankController::class)->except(['show', 'create', 'edit']);

    Route::get('religions/data', [ReligionController::class, 'getReligions'])->name('religions.data');
    Route::resource('religions', ReligionController::class)->except(['show', 'create', 'edit']);

    Route::get('projects/data', [ProjectController::class, 'getProjects'])->name('projects.data');
    Route::resource('projects', ProjectController::class)->except(['show', 'create', 'edit']);

    Route::get('departments/data', [DepartmentController::class, 'getDepartments'])->name('departments.data');
    Route::post('departments/import', [DepartmentController::class, 'import'])->name('departments.import');
    Route::resource('departments', DepartmentController::class)->except(['show', 'create', 'edit']);

    Route::get('positions/data', [PositionController::class, 'getPositions'])->name('positions.data');
    Route::post('positions/import', [PositionController::class, 'import'])->name('positions.import');
    Route::get('positions/export', [PositionController::class, 'export'])->name('positions.export');
    Route::resource('positions', PositionController::class)->except(['show', 'create', 'edit']);

    Route::get('grades/data', [GradeController::class, 'getGrades'])->name('grades.data');
    Route::get('levels/data', [LevelController::class, 'getLevels'])->name('levels.data');

    Route::get('transportations/data', [TransportationController::class, 'getTransportations'])->name('transportations.data');
    Route::resource('transportations', TransportationController::class)->except(['show', 'create', 'edit']);

    Route::get('accommodations/data', [AccommodationController::class, 'getAccommodations'])->name('accommodations.data');
    Route::resource('accommodations', AccommodationController::class);

    Route::get('grades/data', [GradeController::class, 'getGrades'])->name('grades.data');
    Route::post('grades/status/{id}', [GradeController::class, 'changeStatus'])->name('grades.status');
    Route::resource('grades', GradeController::class);

    Route::get('levels/data', [LevelController::class, 'getLevels'])->name('levels.data');
    Route::post('levels/status/{id}', [LevelController::class, 'changeStatus'])->name('levels.status');
    Route::resource('levels', LevelController::class);

    // APPS

    // OFFICIAL TRAVEL ROUTES
    // Self-service routes for user role (must be before resource route to avoid conflicts)
    Route::get('officialtravels/my-travels', [OfficialtravelController::class, 'myTravels'])->name('officialtravels.my-travels');
    Route::get('officialtravels/my-travels/data', [OfficialtravelController::class, 'myTravelsData'])->name('officialtravels.my-travels.data');
    Route::get('officialtravels/my-travels/{id}', [OfficialtravelController::class, 'myTravelsShow'])->name('officialtravels.my-travels.show');
    Route::get('officialtravels/data', [OfficialtravelController::class, 'getOfficialtravels'])->name('officialtravels.data');
    // Test route for letter number integration (development only)
    Route::get('officialtravels/test-letter-integration', [OfficialtravelController::class, 'testLetterNumberIntegration'])->name('officialtravels.testLetterIntegration');
    Route::resource('officialtravels', OfficialtravelController::class);
    Route::get('officialtravels-approver-selector', [OfficialtravelController::class, 'getApproverSelector'])->name('officialtravels.approverSelector');
    Route::post('officialtravels/{officialtravel}/submit', [OfficialtravelController::class, 'submitForApproval'])->name('officialtravels.submit');

    Route::get('officialtravels/{officialtravel}/arrival', [OfficialtravelController::class, 'showArrivalForm'])->name('officialtravels.showArrivalForm');
    Route::post('officialtravels/{officialtravel}/arrival', [OfficialtravelController::class, 'arrivalStamp'])->name('officialtravels.arrivalStamp');
    Route::get('officialtravels/{officialtravel}/departure', [OfficialtravelController::class, 'showDepartureForm'])->name('officialtravels.showDepartureForm');
    Route::post('officialtravels/{officialtravel}/departure', [OfficialtravelController::class, 'departureStamp'])->name('officialtravels.departureStamp');

    Route::get('officialtravels/{officialtravel}/print', [OfficialtravelController::class, 'print'])->name('officialtravels.print');
    Route::patch('officialtravels/{officialtravel}/close', [OfficialtravelController::class, 'close'])->name('officialtravels.close');
    Route::post('officialtravels/export', [OfficialtravelController::class, 'exportExcel'])->name('officialtravels.export');


    // LETTER NUMBERING SYSTEM ROUTES
    Route::prefix('letter-numbers')->name('letter-numbers.')->group(function () {
        Route::get('/', [LetterNumberController::class, 'index'])->name('index');
        Route::get('/data', [LetterNumberController::class, 'getLetterNumbers'])->name('data');
        Route::get('/create/{categoryId?}', [LetterNumberController::class, 'create'])->name('create');
        Route::post('/', [LetterNumberController::class, 'store'])->name('store');
        Route::get('/export', [LetterNumberController::class, 'export'])->name('export');
        Route::post('/import', [LetterNumberController::class, 'import'])->name('import');
        Route::get('/{id}', [LetterNumberController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LetterNumberController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LetterNumberController::class, 'update'])->name('update');
        Route::delete('/{id}', [LetterNumberController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/cancel', [LetterNumberController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/mark-as-used-manually', [LetterNumberController::class, 'markAsUsedManually'])->name('mark-as-used-manually');
    });

    // LETTER CATEGORY MANAGEMENT ROUTES
    Route::prefix('letter-categories')->name('letter-categories.')->group(function () {
        Route::get('/', [LetterCategoryController::class, 'index'])->name('index');
        Route::get('/data', [LetterCategoryController::class, 'getLetterCategories'])->name('data');
        Route::post('/', [LetterCategoryController::class, 'store'])->name('store');
        Route::patch('/{id}', [LetterCategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [LetterCategoryController::class, 'destroy'])->name('destroy');
    });

    // LETTER SUBJECT MANAGEMENT ROUTES (Category specific)
    Route::prefix('letter-subjects')->name('letter-subjects.')->group(function () {
        Route::get('/{categoryCode}', [LetterSubjectController::class, 'indexByCategory'])->name('index-by-category');
        Route::get('/{categoryCode}/data', [LetterSubjectController::class, 'getSubjectsByCategory'])->name('data-by-category');
        Route::post('/{categoryCode}', [LetterSubjectController::class, 'storeByCategory'])->name('store-by-category');
        Route::patch('/{categoryCode}/{id}', [LetterSubjectController::class, 'updateByCategory'])->name('update-by-category');
        Route::delete('/{id}', [LetterSubjectController::class, 'destroy'])->name('destroy');
    });

    // EMPLOYEE REGISTRATION ADMIN ROUTES
    Route::prefix('employee-registrations')->name('employee.registration.admin.')->group(function () {
        Route::get('/', [EmployeeRegistrationAdminController::class, 'index'])->name('index');
        Route::get('/pending', [EmployeeRegistrationAdminController::class, 'getPendingRegistrations'])->name('pending');
        Route::get('/tokens', [EmployeeRegistrationAdminController::class, 'getTokens'])->name('tokens');
        Route::get('/invite', [EmployeeRegistrationAdminController::class, 'showInviteForm'])->name('invite');
        Route::post('/invite', [EmployeeRegistrationAdminController::class, 'invite'])->name('invite');
        Route::post('/bulk-invite', [EmployeeRegistrationAdminController::class, 'bulkInvite'])->name('bulk-invite');
        Route::post('/tokens/{tokenId}/resend', [EmployeeRegistrationAdminController::class, 'resendInvitation'])->name('resend');
        Route::delete('/tokens/{tokenId}', [EmployeeRegistrationAdminController::class, 'deleteToken'])->name('delete-token');
        Route::get('/stats', [EmployeeRegistrationAdminController::class, 'getStats'])->name('stats');
        Route::post('/cleanup', [EmployeeRegistrationAdminController::class, 'cleanupExpiredTokens'])->name('cleanup');
        Route::get('/{id}', [EmployeeRegistrationAdminController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [EmployeeRegistrationAdminController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [EmployeeRegistrationAdminController::class, 'reject'])->name('reject');
        Route::post('/{id}/approve-form', [EmployeeRegistrationAdminController::class, 'approveForm'])->name('approve.form');
        Route::post('/{id}/reject-form', [EmployeeRegistrationAdminController::class, 'rejectForm'])->name('reject.form');
        Route::get('/{registrationId}/documents/{documentId}', [EmployeeRegistrationAdminController::class, 'downloadDocument'])->name('download.document');
    });

    // EMPLOYEE ROUTES

    Route::get('employees/data', [EmployeeController::class, 'getEmployees'])->name('employees.data');
    Route::get('employees/print/{id}', [EmployeeController::class, 'print'])->name('employees.print');
    Route::get('employees/getDepartment', [EmployeeController::class, 'getDepartment'])->name('employees.getDepartment');
    Route::get('employees/getPersonals', [EmployeeController::class, 'getPersonals'])->name('employees.getPersonals');
    Route::get('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/addImages/{id}', [EmployeeController::class, 'addImages'])->name('employees.addImages');
    Route::get('employees/setProfile/{employee_id}/{id}', [EmployeeController::class, 'setProfile'])->name('employees.setProfile');
    Route::get('employees/deleteImage/{employee_id}/{id}', [EmployeeController::class, 'deleteImage'])->name('employees.deleteImage');
    Route::get('employees/deleteImages/{employee_id}', [EmployeeController::class, 'deleteImages'])->name('employees.deleteImages');
    Route::post('employees/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::post('employees/import-complete', [EmployeeController::class, 'importComplete'])->name('employees.import-complete');

    // Employee Bond Management
    Route::get('employee-bonds/data', [EmployeeBondController::class, 'getBonds'])->name('employee-bonds.data');
    Route::get('employee-bonds/expiring', [EmployeeBondController::class, 'checkExpiringBonds'])->name('employee-bonds.expiring');
    Route::get('employees/{employee}/bonds', [EmployeeBondController::class, 'getEmployeeBonds'])->name('employees.bonds');
    Route::get('employee-bonds/{employeeBond}/download', [EmployeeBondController::class, 'downloadDocument'])->name('employee-bonds.download');
    Route::patch('employee-bonds/{employeeBond}/complete', [EmployeeBondController::class, 'markAsCompleted'])->name('employee-bonds.complete');
    Route::delete('employee-bonds/{employeeBond}/delete-document', [EmployeeBondController::class, 'deleteDocument'])->name('employee-bonds.delete-document');
    Route::resource('employee-bonds', EmployeeBondController::class);

    // Bond Violation Management
    Route::get('bond-violations/data', [BondViolationController::class, 'getViolations'])->name('bond-violations.data');
    Route::resource('bond-violations', BondViolationController::class);
    Route::post('bond-violations/calculate-penalty', [BondViolationController::class, 'calculatePenalty'])->name('bond-violations.calculate-penalty');

    Route::get('personals', [EmployeeController::class, 'personal'])->name('employees.personal');

    Route::get('licenses/getLicenses', [LicenseController::class, 'getLicenses'])->name('licenses.list');
    Route::resource('licenses', LicenseController::class)->except(['store', 'show', 'create', 'edit', 'destroy']);
    Route::post('licenses/{employee_id}', [LicenseController::class, 'store'])->name('licenses.store');
    Route::delete('licenses/{employee_id}/{id}', [LicenseController::class, 'delete'])->name('licenses.delete');
    Route::delete('licenses/{employee_id}', [LicenseController::class, 'deleteAll'])->name('licenses.deleteAll');

    Route::get('insurances/getInsurances', [InsuranceController::class, 'getInsurances'])->name('insurances.list');
    Route::resource('insurances', InsuranceController::class)->except(['store', 'show', 'create', 'edit', 'destroy']);
    Route::post('insurances/{employee_id}', [InsuranceController::class, 'store'])->name('insurances.store');
    Route::delete('insurances/{employee_id}/{id}', [InsuranceController::class, 'delete'])->name('insurances.delete');
    Route::delete('insurances/{employee_id}', [InsuranceController::class, 'deleteAll'])->name('insurances.deleteAll');

    Route::get('families/getFamilies', [FamilieController::class, 'getFamilies'])->name('families.list');
    Route::resource('families', FamilieController::class)->except(['store', 'show', 'create', 'edit', 'destroy']);
    Route::post('families/{employee_id}', [FamilieController::class, 'store'])->name('families.store');
    Route::delete('families/{employee_id}/{id}', [FamilieController::class, 'delete'])->name('families.delete');
    Route::delete('families/{employee_id}', [FamilieController::class, 'deleteAll'])->name('families.deleteAll');

    Route::resource('courses', CourseController::class)->except(['store', 'show', 'create', 'edit', 'destroy']);
    Route::get('courses/getCourse', [CourseController::class, 'getCourse'])->name('courses.list');
    Route::post('courses/{employee_id}', [CourseController::class, 'store'])->name('courses.store');
    Route::delete('courses/{employee_id}/{id}', [CourseController::class, 'delete'])->name('courses.delete');
    Route::delete('courses/{employee_id}', [CourseController::class, 'deleteAll'])->name('courses.deleteAll');

    Route::get('emrgcalls/getEmrgcall', [EmrgcallController::class, 'getEmrgcall'])->name('emrgcalls.list');
    Route::resource('emrgcalls', EmrgcallController::class)->except(['store', 'show', 'create', 'edit', 'destroy']);
    Route::post('emrgcalls/{employee_id}', [EmrgcallController::class, 'store'])->name('emrgcalls.store');
    Route::delete('emrgcalls/{employee_id}/{id}', [EmrgcallController::class, 'delete'])->name('emrgcalls.delete');
    Route::delete('emrgcalls/{employee_id}', [EmrgcallController::class, 'deleteAll'])->name('emrgcalls.deleteAll');

    Route::get('additionaldatas/getAdditionaldata', [AdditionaldataController::class, 'getAdditionaldata'])->name('additionaldatas.list');
    Route::resource('additionaldatas', AdditionaldataController::class)->except(['show', 'create', 'edit', 'destroy']);
    Route::delete('additionaldatas/{employee_id}/{id}', [AdditionaldataController::class, 'delete'])->name('additionaldatas.delete');

    Route::resource('employeebanks', EmployeebankController::class)->except(['show', 'create', 'edit', 'destroy']);
    Route::get('employeebanks/getEmployeebank', [EmployeebankController::class, 'getEmployeebank'])->name('employeebanks.list');
    Route::delete('employeebanks/{employee_id}/{id}', [EmployeebankController::class, 'delete'])->name('employeebanks.delete');

    Route::get('administrations/getAdministration', [AdministrationController::class, 'getAdministration'])->name('administrations.list');
    Route::resource('administrations', AdministrationController::class)->except(['store', 'show', 'create', 'edit', 'destroy']);
    Route::post('administrations/{employee_id}', [AdministrationController::class, 'store'])->name('administrations.store');
    Route::delete('administrations/{employee_id}/{id}', [AdministrationController::class, 'delete'])->name('administrations.delete');
    Route::delete('administrations/{employee_id}', [AdministrationController::class, 'deleteAll'])->name('administrations.deleteAll');
    Route::patch('administrations/changeStatus/{employee_id}/{id}', [AdministrationController::class, 'changeStatus'])->name('administrations.changeStatus');

    Route::get('educations/getEducation', [EducationController::class, 'getEducation'])->name('educations.list');
    Route::resource('educations', EducationController::class)->except(['store', 'show', 'create', 'edit', 'destroy']);
    Route::post('educations/{employee_id}', [EducationController::class, 'store'])->name('educations.store');
    Route::delete('educations/{employee_id}/{id}', [EducationController::class, 'delete'])->name('educations.delete');
    Route::delete('educations/{employee_id}', [EducationController::class, 'deleteAll'])->name('educations.deleteAll');

    Route::get('jobexperiences/getJobexperiences', [JobexperienceController::class, 'getJobexperiences'])->name('jobexperiences.list');
    Route::resource('jobexperiences', JobexperienceController::class)->except(['store', 'show', 'create', 'edit', 'destroy']);
    Route::post('jobexperiences/{employee_id}', [JobexperienceController::class, 'store'])->name('jobexperiences.store');
    Route::delete('jobexperiences/{employee_id}/{id}', [JobexperienceController::class, 'delete'])->name('jobexperiences.delete');
    Route::delete('jobexperiences/{employee_id}', [JobexperienceController::class, 'deleteAll'])->name('jobexperiences.deleteAll');

    Route::get('operableunits/getOperableunits', [OperableunitController::class, 'getOperableunits'])->name('operableunits.list');
    Route::resource('operableunits', OperableunitController::class)->except(['store', 'show', 'create', 'edit', 'destroy']);
    Route::post('operableunits/{employee_id}', [OperableunitController::class, 'store'])->name('operableunits.store');
    Route::delete('operableunits/{employee_id}/{id}', [OperableunitController::class, 'delete'])->name('operableunits.delete');
    Route::delete('operableunits/{employee_id}', [OperableunitController::class, 'deleteAll'])->name('operableunits.deleteAll');

    Route::get('taxidentifications/getTaxidentifications', [TaxidentificationController::class, 'getTaxidentifications'])->name('taxidentifications.list');
    Route::resource('taxidentifications', TaxidentificationController::class)->except(['show', 'create', 'edit', 'destroy']);
    Route::delete('taxidentifications/{employee_id}/{id}', [TaxidentificationController::class, 'delete'])->name('taxidentifications.delete');

    Route::resource('terminations', TerminationController::class)->except(['show', 'edit', 'destroy']);
    Route::get('terminations/getEmployees', [TerminationController::class, 'getEmployees'])->name('terminations.getActiveEmployees');
    Route::get('terminations/getTerminations', [TerminationController::class, 'getTerminations'])->name('terminations.list');
    Route::post('terminations/massTermination', [TerminationController::class, 'massTermination'])->name('terminations.massTermination');
    Route::patch('terminations/delete/{id}', [TerminationController::class, 'delete'])->name('terminations.delete');

    Route::resource('emails', EmailController::class)->except(['create', 'show', 'edit']);
    Route::post("emails", [EmailController::class, "sendMail"])->name("sendMail");

    // Recruitment Routes
    Route::prefix('recruitment')->name('recruitment.')->group(function () {
        // Self-service routes for user role
        Route::get('/my-requests', [RecruitmentRequestController::class, 'myRequests'])->name('my-requests');
        Route::get('/my-requests/data', [RecruitmentRequestController::class, 'myRequestsData'])->name('my-requests.data');
        Route::get('/my-requests/create', [RecruitmentRequestController::class, 'myRequestsCreate'])->name('my-requests.create');
        Route::post('/my-requests', [RecruitmentRequestController::class, 'myRequestsStore'])->name('my-requests.store');
        Route::get('/my-requests/{id}', [RecruitmentRequestController::class, 'myRequestsShow'])->name('my-requests.show');
        Route::get('/my-requests/{id}/edit', [RecruitmentRequestController::class, 'myRequestsEdit'])->name('my-requests.edit');
        Route::put('/my-requests/{id}', [RecruitmentRequestController::class, 'myRequestsUpdate'])->name('my-requests.update');
        Route::post('/my-requests/{id}/submit', [RecruitmentRequestController::class, 'submitForApproval'])->name('my-requests.submit');

        // FPTK (Recruitment Request) Routes
        Route::prefix('requests')->name('requests.')->group(function () {

            Route::get('/', [RecruitmentRequestController::class, 'index'])->name('index');
            Route::get('/data', [RecruitmentRequestController::class, 'getRecruitmentRequests'])->name('data');
            Route::get('/{id}/data', [RecruitmentRequestController::class, 'getFPTKData'])->name('single-data');
            Route::get('/create', [RecruitmentRequestController::class, 'create'])->name('create');
            Route::post('/', [RecruitmentRequestController::class, 'store'])->name('store');
            Route::get('/{id}', [RecruitmentRequestController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [RecruitmentRequestController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RecruitmentRequestController::class, 'update'])->name('update');
            Route::delete('/{id}', [RecruitmentRequestController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/print', [RecruitmentRequestController::class, 'print'])->name('print');

            // FPTK Actions
            Route::post('/{id}/submit', [RecruitmentRequestController::class, 'submitForApproval'])->name('submit');

            // Approval Routes - New 3-level approval system
            Route::get('/{id}/acknowledge', [RecruitmentRequestController::class, 'showAcknowledgmentForm'])->name('acknowledge-form');
            Route::post('/{id}/acknowledge', [RecruitmentRequestController::class, 'acknowledge'])->name('acknowledge');
            Route::get('/{id}/approve-pm', [RecruitmentRequestController::class, 'showPMApprovalForm'])->name('approve-pm-form');
            Route::post('/{id}/approve-pm', [RecruitmentRequestController::class, 'approveByPM'])->name('approve-pm');
            Route::get('/{id}/approve-director', [RecruitmentRequestController::class, 'showDirectorApprovalForm'])->name('approve-director-form');
            Route::post('/{id}/approve-director', [RecruitmentRequestController::class, 'approveByDirector'])->name('approve-director');

            // Legacy approval routes (kept for backward compatibility)
            Route::post('/{id}/approve', [RecruitmentRequestController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [RecruitmentRequestController::class, 'reject'])->name('reject');
            Route::post('/{id}/assign-letter-number', [RecruitmentRequestController::class, 'assignLetterNumber'])->name('assign-letter-number');


            // AJAX Routes
        });

        // Man Power Plan (MPP) Routes
        Route::prefix('mpp')->name('mpp.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ManPowerPlanController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\ManPowerPlanController::class, 'getData'])->name('data');
            Route::get('/create', [\App\Http\Controllers\ManPowerPlanController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\ManPowerPlanController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\ManPowerPlanController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\ManPowerPlanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\ManPowerPlanController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\ManPowerPlanController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/close', [\App\Http\Controllers\ManPowerPlanController::class, 'close'])->name('close');
        });

        // Candidate Routes
        Route::prefix('candidates')->name('candidates.')->group(function () {
            Route::get('/', [RecruitmentCandidateController::class, 'index'])->name('index');
            Route::get('/data', [RecruitmentCandidateController::class, 'getRecruitmentCandidates'])->name('data');
            Route::get('/search', [RecruitmentCandidateController::class, 'search'])->name('search');
            Route::get('/{id}/data', [RecruitmentCandidateController::class, 'getCandidateData'])->name('single-data');
            Route::get('/{id}/available-fptks', [RecruitmentCandidateController::class, 'getAvailableFPTKs'])->name('available-fptks');
            Route::get('/create', [RecruitmentCandidateController::class, 'create'])->name('create');
            Route::post('/', [RecruitmentCandidateController::class, 'store'])->name('store');
            Route::get('/{id}', [RecruitmentCandidateController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [RecruitmentCandidateController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RecruitmentCandidateController::class, 'update'])->name('update');
            Route::delete('/{id}', [RecruitmentCandidateController::class, 'destroy'])->name('destroy');

            // Candidate Actions
            Route::post('/{id}/apply-to-fptk', [RecruitmentCandidateController::class, 'applyToFPTK'])->name('apply-to-fptk');
            Route::post('/{id}/blacklist', [RecruitmentCandidateController::class, 'blacklist'])->name('blacklist');
            Route::post('/{id}/remove-from-blacklist', [RecruitmentCandidateController::class, 'removeFromBlacklist'])->name('remove-from-blacklist');
            Route::get('/{id}/download-cv', [RecruitmentCandidateController::class, 'downloadCV'])->name('download-cv');
            Route::delete('/{id}/delete-cv', [RecruitmentCandidateController::class, 'deleteCV'])->name('delete-cv');
            Route::get('/{id}/print', [RecruitmentCandidateController::class, 'print'])->name('print');
        });

        // Session Routes
        Route::prefix('sessions')->name('sessions.')->group(function () {
            Route::get('/', [RecruitmentSessionController::class, 'index'])->name('index');
            Route::get('/data', [RecruitmentSessionController::class, 'getSessions'])->name('data');
            Route::get('/dashboard', [RecruitmentSessionController::class, 'dashboard'])->name('dashboard');
            Route::get('/{id}', [RecruitmentSessionController::class, 'show'])->name('show');
            Route::get('/candidate/{id}', [RecruitmentSessionController::class, 'showSession'])->name('candidate');
            Route::post('/', [RecruitmentSessionController::class, 'store'])->name('store');

            // Session Actions
            Route::post('/{sessionId}/update-cv-review', [RecruitmentSessionController::class, 'updateCvReview'])->name('update-cv-review');
            Route::post('/{sessionId}/update-psikotes', [RecruitmentSessionController::class, 'updatePsikotes'])->name('update-psikotes');
            Route::post('/{sessionId}/update-tes-teori', [RecruitmentSessionController::class, 'updateTesTeori'])->name('update-tes-teori');
            Route::post('/{sessionId}/update-interview', [RecruitmentSessionController::class, 'updateInterview'])->name('update-interview');
            Route::post('/{sessionId}/update-offering', [RecruitmentSessionController::class, 'updateOffering'])->name('update-offering');
            Route::post('/{sessionId}/update-mcu', [RecruitmentSessionController::class, 'updateMcu'])->name('update-mcu');
            Route::post('/{sessionId}/update-hiring', [RecruitmentSessionController::class, 'updateHiring'])->name('update-hiring');

            Route::post('/{sessionId}/transition-stage', [RecruitmentSessionController::class, 'transitionStage'])->name('transition-stage');
            Route::post('/{sessionId}/close-request', [RecruitmentSessionController::class, 'closeRequest'])->name('close-request');
            Route::delete('/{id}', [RecruitmentSessionController::class, 'destroy'])->name('destroy');

            // AJAX Routes
            Route::get('/{id}/data', [RecruitmentSessionController::class, 'getSessionData'])->name('single-data');
            Route::get('/fptk/{fptkId}/sessions', [RecruitmentSessionController::class, 'getSessionsByFPTK'])->name('by-fptk');
            Route::get('/candidate/{candidateId}/sessions', [RecruitmentSessionController::class, 'getSessionsByCandidate'])->name('by-candidate');
        });
    });

    // Recruitment Reports
    Route::prefix('recruitment/reports')->name('recruitment.reports.')->group(function () {
        Route::get('/', [RecruitmentReportController::class, 'index'])->name('index');
        Route::get('/funnel', [RecruitmentReportController::class, 'funnel'])->name('funnel');
        Route::get('/funnel/export', [RecruitmentReportController::class, 'exportFunnel'])->name('funnel.export');
        Route::get('/funnel/stage/{stage}', [RecruitmentReportController::class, 'stageDetail'])->name('funnel.stage');
        Route::get('/funnel/stage/{stage}/data', [RecruitmentReportController::class, 'stageDetailData'])->name('stage-detail.data');

        // Special routes for magang/harian stages
        Route::get('/funnel/stage/mcu_magang_harian', [RecruitmentReportController::class, 'stageDetail'])->name('funnel.stage.mcu_magang_harian');
        Route::get('/funnel/stage/mcu_magang_harian/data', [RecruitmentReportController::class, 'stageDetailData'])->name('stage-detail.data.mcu_magang_harian');
        Route::get('/funnel/stage/hiring_magang_harian', [RecruitmentReportController::class, 'stageDetail'])->name('funnel.stage.hiring_magang_harian');
        Route::get('/funnel/stage/hiring_magang_harian/data', [RecruitmentReportController::class, 'stageDetailData'])->name('stage-detail.data.hiring_magang_harian');
        Route::get('/aging', [RecruitmentReportController::class, 'aging'])->name('aging');
        Route::get('/aging/export', [RecruitmentReportController::class, 'exportAging'])->name('aging.export');
        Route::get('/aging/data', [RecruitmentReportController::class, 'agingData'])->name('aging.data');
        Route::get('/time-to-hire', [RecruitmentReportController::class, 'timeToHire'])->name('time-to-hire');
        Route::get('/time-to-hire/export', [RecruitmentReportController::class, 'exportTimeToHire'])->name('time-to-hire.export');
        Route::get('/time-to-hire/data', [RecruitmentReportController::class, 'timeToHireData'])->name('time-to-hire.data');
        Route::get('/offer-acceptance-rate', [RecruitmentReportController::class, 'offerAcceptanceRate'])->name('offer-acceptance-rate');
        Route::get('/offer-acceptance-rate/export', [RecruitmentReportController::class, 'exportOfferAcceptanceRate'])->name('offer-acceptance-rate.export');
        Route::get('/offer-acceptance-rate/data', [RecruitmentReportController::class, 'offerAcceptanceRateData'])->name('offer-acceptance-rate.data');
        Route::get('/interview-assessment-analytics', [RecruitmentReportController::class, 'interviewAssessmentAnalytics'])->name('interview-assessment-analytics');
        Route::get('/interview-assessment-analytics/export', [RecruitmentReportController::class, 'exportInterviewAssessmentAnalytics'])->name('interview-assessment-analytics.export');
        Route::get('/interview-assessment-analytics/data', [RecruitmentReportController::class, 'interviewAssessmentAnalyticsData'])->name('interview-assessment-analytics.data');
        Route::get('/stale-candidates', [RecruitmentReportController::class, 'staleCandidates'])->name('stale-candidates');
        Route::get('/stale-candidates/export', [RecruitmentReportController::class, 'exportStaleCandidates'])->name('stale-candidates.export');
        Route::get('/stale-candidates/data', [RecruitmentReportController::class, 'staleCandidatesData'])->name('stale-candidates.data');
    });

    // Approval System Routes
    Route::prefix('approval')->name('approval.')->group(function () {
        // Approval Stages Management
        Route::prefix('stages')->name('stages.')->group(function () {
            Route::get('/', [ApprovalStageController::class, 'index'])->name('index');
            Route::get('/create', [ApprovalStageController::class, 'create'])->name('create');
            Route::post('/', [ApprovalStageController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ApprovalStageController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ApprovalStageController::class, 'update'])->name('update');
            Route::delete('/{id}', [ApprovalStageController::class, 'destroy'])->name('destroy');
            Route::get('/data', [ApprovalStageController::class, 'data'])->name('data');
            Route::get('/preview', [ApprovalStageController::class, 'preview'])->name('preview');
        });

        // Approval Plans
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::put('/{id}', [ApprovalPlanController::class, 'update'])->name('update');
            Route::post('/bulk-approve', [ApprovalPlanController::class, 'bulkApprove'])->name('bulk-approve');
        });

        // Approval Requests
        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [ApprovalRequestController::class, 'index'])->name('index');
            Route::get('/data', [ApprovalRequestController::class, 'getApprovalRequests'])->name('data');
            Route::get('/{id}', [ApprovalRequestController::class, 'show'])->name('show');
            Route::post('/{id}/process', [ApprovalRequestController::class, 'processApproval'])->name('process');
            Route::post('/bulk-approve', [ApprovalRequestController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/filter-by-type', [ApprovalRequestController::class, 'filterByType'])->name('filter-by-type');
        });
    });

    // Leave Management Routes
    Route::prefix('leave')->name('leave.')->group(function () {
        // Leave Requests
        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
            Route::get('/data', [LeaveRequestController::class, 'data'])->name('data');
            Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
            Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
            Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
            Route::get('/{leaveRequest}/edit', [LeaveRequestController::class, 'edit'])->name('edit');
            Route::put('/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('update');
            Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('destroy');
            Route::get('/{leaveRequest}/download', [LeaveRequestController::class, 'download'])->name('download');
            Route::post('/{leaveRequest}/upload', [LeaveRequestController::class, 'upload'])->name('upload');
            Route::delete('/{leaveRequest}/delete-document', [LeaveRequestController::class, 'deleteDocument'])->name('delete-document');
            Route::post('/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
            Route::post('/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('reject');


            // Close and cancellation routes
            Route::post('/{leaveRequest}/close', [LeaveRequestController::class, 'close'])->name('close');
            Route::get('/{leaveRequest}/cancellation-form', [LeaveRequestController::class, 'showCancellationForm'])->name('cancellation-form');
            Route::post('/{leaveRequest}/cancellation', [LeaveRequestController::class, 'storeCancellation'])->name('cancellation');
            Route::post('/cancellations/{cancellation}/approve', [LeaveRequestController::class, 'approveCancellation'])->name('cancellation.approve');
            Route::post('/cancellations/{cancellation}/reject', [LeaveRequestController::class, 'rejectCancellation'])->name('cancellation.reject');

            // AJAX Routes untuk dynamic loading
            Route::get('/project-info/{projectId}', [LeaveRequestController::class, 'getProjectInfo'])->name('project-info');
            Route::get('/leave-period/{employeeId}/{leaveTypeId}', [LeaveRequestController::class, 'getLeavePeriod'])->name('leave-period');

            // AJAX routes for internal calls
            Route::get('/employees/{employeeId}/leave-balance', [LeaveRequestController::class, 'getEmployeeLeaveBalance'])->name('employee.leave-balance');
            Route::get('/leave-types/{leaveTypeId}', [LeaveRequestController::class, 'getLeaveTypeInfo'])->name('leave-type.info');
            Route::get('/employees-by-project/{projectId}', [LeaveRequestController::class, 'getEmployeesByProject'])->name('employees-by-project');
            Route::get('/leave-types-by-employee/{employeeId}', [LeaveRequestController::class, 'getLeaveTypesByEmployee'])->name('leave-types-by-employee');
        });

        // Self-service routes for user role (moved outside requests prefix)
        Route::get('/my-requests', [LeaveRequestController::class, 'myRequests'])->name('my-requests');
        Route::get('/my-requests/data', [LeaveRequestController::class, 'myRequestsData'])->name('my-requests.data');
        Route::get('/my-requests/create', [LeaveRequestController::class, 'myRequestsCreate'])->name('my-requests.create');
        Route::post('/my-requests', [LeaveRequestController::class, 'myRequestsStore'])->name('my-requests.store');
        Route::get('/my-requests/{leaveRequest}', [LeaveRequestController::class, 'myRequestsShow'])->name('my-requests.show');
        Route::get('/my-requests/{leaveRequest}/edit', [LeaveRequestController::class, 'myRequestsEdit'])->name('my-requests.edit');
        Route::put('/my-requests/{leaveRequest}', [LeaveRequestController::class, 'myRequestsUpdate'])->name('my-requests.update');
        Route::get('/my-requests/{leaveRequest}/cancellation-form', [LeaveRequestController::class, 'showCancellationForm'])->name('my-requests.cancellation-form');
        Route::post('/my-requests/{leaveRequest}/cancellation', [LeaveRequestController::class, 'storeCancellation'])->name('my-requests.cancellation');
        Route::get('/my-entitlements', [LeaveRequestController::class, 'myEntitlements'])->name('my-entitlements');

        // Periodic Leave Requests
        Route::prefix('periodic-requests')->name('periodic-requests.')->group(function () {
            Route::get('/', [BulkLeaveRequestController::class, 'index'])->name('index');
            Route::get('/create', [BulkLeaveRequestController::class, 'create'])->name('create');
            Route::post('/', [BulkLeaveRequestController::class, 'store'])->name('store');
            Route::get('/{batch_id}', [BulkLeaveRequestController::class, 'show'])->name('show');
            Route::post('/{batch_id}/cancel', [BulkLeaveRequestController::class, 'cancelBatch'])->name('cancel');

            // AJAX endpoints
            Route::get('/ajax/employees-due', [BulkLeaveRequestController::class, 'getEmployeesDue'])->name('ajax.employees-due');
            Route::get('/ajax/departments', [BulkLeaveRequestController::class, 'getDepartmentsByProject'])->name('ajax.departments');
            Route::get('/ajax/approval-preview', [BulkLeaveRequestController::class, 'getBulkApprovalPreview'])->name('ajax.approval-preview');
            Route::get('/ajax/approver-selector', [BulkLeaveRequestController::class, 'getApproverSelector'])->name('ajax.approver-selector');
        });

        // Leave Entitlements
        Route::prefix('entitlements')->name('entitlements.')->group(function () {
            Route::get('/', [LeaveEntitlementController::class, 'index'])->name('index');
            Route::get('/data', [LeaveEntitlementController::class, 'data'])->name('data');
            // Export/Import
            Route::get('/export-template', [LeaveEntitlementController::class, 'exportTemplate'])->name('export-template');
            Route::post('/import-template', [LeaveEntitlementController::class, 'importTemplate'])->name('import-template');

            Route::get('/create', [LeaveEntitlementController::class, 'create'])->name('create');
            Route::post('/', [LeaveEntitlementController::class, 'store'])->name('store');
            Route::get('/{leaveEntitlement}', [LeaveEntitlementController::class, 'show'])->name('show');
            Route::get('/{leaveEntitlement}/edit', [LeaveEntitlementController::class, 'edit'])->name('edit');
            Route::put('/{leaveEntitlement}', [LeaveEntitlementController::class, 'update'])->name('update');
            Route::delete('/{leaveEntitlement}', [LeaveEntitlementController::class, 'destroy'])->name('destroy');
            Route::get('/available-leave-types', [LeaveEntitlementController::class, 'getAvailableLeaveTypes'])->name('available-leave-types');

            // Management routes
            Route::post('/generate-project', [LeaveEntitlementController::class, 'generateProjectEntitlements'])->name('generate-project');
            Route::post('/generate-selected-project', [LeaveEntitlementController::class, 'generateSelectedProjectEntitlements'])->name('generate-selected-project');
            Route::post('/clear-entitlements', [LeaveEntitlementController::class, 'clearAllEntitlements'])->name('clear-entitlements');

            // Individual employee entitlement management
            Route::get('/employee/{employee}', [LeaveEntitlementController::class, 'showEmployee'])->name('employee.show');
            Route::get('/employee/{employee}/edit', [LeaveEntitlementController::class, 'editEmployee'])->name('employee.edit');
            Route::put('/employee/{employee}', [LeaveEntitlementController::class, 'updateEmployee'])->name('employee.update');

            // Leave calculation details
            Route::get('/employee/{employee}/calculation-details', [LeaveEntitlementController::class, 'showLeaveCalculationDetails'])->name('employee.calculation-details');
            Route::post('/calculation-details-ajax', [LeaveEntitlementController::class, 'getLeaveCalculationDetailsAjax'])->name('calculation-details-ajax');
        });

        // Leave Types (Master Data)
        Route::prefix('types')->name('types.')->group(function () {
            Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
            Route::get('/data', [LeaveTypeController::class, 'data'])->name('data');
            Route::get('/create', [LeaveTypeController::class, 'create'])->name('create');
            Route::post('/', [LeaveTypeController::class, 'store'])->name('store');
            Route::get('/{leaveType}', [LeaveTypeController::class, 'show'])->name('show');
            Route::get('/{leaveType}/edit', [LeaveTypeController::class, 'edit'])->name('edit');
            Route::put('/{leaveType}', [LeaveTypeController::class, 'update'])->name('update');
            Route::delete('/{leaveType}', [LeaveTypeController::class, 'destroy'])->name('destroy');
            Route::post('/{leaveType}/toggle-status', [LeaveTypeController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Leave Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [LeaveReportController::class, 'index'])->name('index');

            // New comprehensive reports
            Route::get('/monitoring', [LeaveReportController::class, 'monitoring'])->name('monitoring');
            Route::get('/monitoring/export', [LeaveReportController::class, 'exportMonitoring'])->name('monitoring.export');

            Route::get('/cancellation', [LeaveReportController::class, 'cancellation'])->name('cancellation');
            Route::get('/cancellation/export', [LeaveReportController::class, 'exportCancellation'])->name('cancellation.export');

            Route::get('/entitlement-detailed', [LeaveReportController::class, 'entitlementDetailed'])->name('entitlement-detailed');

            Route::get('/auto-conversion', [LeaveReportController::class, 'autoConversion'])->name('auto-conversion');

            // Legacy reports
            Route::get('/by-project', [LeaveReportController::class, 'byProject'])->name('by-project');
            Route::get('/export', [LeaveReportController::class, 'export'])->name('export');
            Route::get('/statistics', [LeaveReportController::class, 'statistics'])->name('statistics');
        });
    });

    // Roster Management Routes (New Clean Structure)
    Route::prefix('rosters')->name('rosters.')->group(function () {
        // Dashboard (must be before {roster} route to avoid conflict)
        Route::get('/dashboard', [RosterController::class, 'dashboard'])->name('dashboard');

        // Export/Import (must be before {roster} route to avoid conflict)
        Route::get('/export', [RosterController::class, 'export'])->name('export');
        Route::post('/import', [RosterController::class, 'import'])->name('import');
        Route::get('/calendar', [RosterController::class, 'calendar'])->name('calendar');

        // Main routes
        Route::get('/', [RosterController::class, 'index'])->name('index');
        Route::get('/{roster}', [RosterController::class, 'show'])->name('show');
        Route::post('/', [RosterController::class, 'store'])->name('store');
        Route::delete('/{roster}', [RosterController::class, 'destroy'])->name('destroy');

        // Cycle management
        Route::get('/cycles/{cycle}', [RosterController::class, 'getCycle'])->name('cycles.show');
        Route::post('/{roster}/cycles', [RosterController::class, 'addCycle'])->name('cycles.add');
        Route::put('/cycles/{cycle}', [RosterController::class, 'updateCycle'])->name('cycles.update');
        Route::delete('/cycles/{cycle}', [RosterController::class, 'deleteCycle'])->name('cycles.delete');

        // Helper endpoints
        Route::get('/{roster}/statistics', [RosterController::class, 'getStatistics'])->name('statistics');
    });
});
