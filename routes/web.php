<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FamilieController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmrgcallController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\PHPMailerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TerminationController;
use App\Http\Controllers\EmployeebankController;
use App\Http\Controllers\OperableunitController;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\JobexperienceController;
use App\Http\Controllers\AdditionaldataController;
use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\OfficialtravelController;
use App\Http\Controllers\TransportationController;
use App\Http\Controllers\TaxidentificationController;

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
Route::post('login', [AuthController::class, 'postLogin']);

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [ProfileController::class, 'dashboard'])->name('dashboard');
    Route::get('dashboard/getHobpn', [ProfileController::class, 'getHobpn'])->name('hobpn.list');
    Route::get('dashboard/getBojkt', [ProfileController::class, 'getBojkt'])->name('bojkt.list');
    Route::get('dashboard/getKpuc', [ProfileController::class, 'getKpuc'])->name('kpuc.list');
    Route::get('dashboard/getSbi', [ProfileController::class, 'getSbi'])->name('sbi.list');
    Route::get('dashboard/getGpk', [ProfileController::class, 'getGpk'])->name('gpk.list');
    Route::get('dashboard/getBek', [ProfileController::class, 'getBek'])->name('bek.list');
    Route::get('dashboard/getAps', [ProfileController::class, 'getAps'])->name('aps.list');
    Route::get('dashboard/getEmployee', [ProfileController::class, 'getEmployee'])->name('employee.list');
    Route::get('dashboard/getTermination', [ProfileController::class, 'getTermination'])->name('termination.list');
    Route::get('dashboard/getContract', [ProfileController::class, 'getContract'])->name('contract.list');
    Route::post('dashboard/sendEmail', [ProfileController::class, 'sendEmail'])->name('sendEmail');


    Route::post('logout', [AuthController::class, 'logout']);

    // ADMINISTRATOR ROUTES

    Route::get('users/data', [UserController::class, 'getUsers'])->name('users.data');
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);

    Route::get('roles/data', [RoleController::class, 'getRoles'])->name('roles.data');
    Route::resource('roles', RoleController::class);

    Route::get('permissions/data', [PermissionController::class, 'getPermissions'])->name('permissions.data');
    Route::resource('permissions', PermissionController::class);

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
    Route::resource('positions', PositionController::class)->except(['show', 'create', 'edit']);

    Route::get('transportations/data', [TransportationController::class, 'getTransportations'])->name('transportations.data');
    Route::resource('transportations', TransportationController::class)->except(['show', 'create', 'edit']);

    Route::get('accommodations/data', [AccommodationController::class, 'getAccommodations'])->name('accommodations.data');
    Route::resource('accommodations', AccommodationController::class);

    // APPS

    // OFFICIAL TRAVEL ROUTES
    Route::get('officialtravels/data', [OfficialtravelController::class, 'getOfficialtravels'])->name('officialtravels.data');
    Route::resource('officialtravels', OfficialtravelController::class);
    Route::get('officialtravels/{officialtravel}/recommend', [OfficialtravelController::class, 'showRecommendForm'])->name('officialtravels.showRecommendForm');
    Route::post('officialtravels/{officialtravel}/recommend', [OfficialtravelController::class, 'recommend'])->name('officialtravels.recommend');
    Route::get('officialtravels/{officialtravel}/approve', [OfficialtravelController::class, 'showApprovalForm'])->name('officialtravels.showApprovalForm');
    Route::post('officialtravels/{officialtravel}/approve', [OfficialtravelController::class, 'approve'])->name('officialtravels.approve');
    Route::get('officialtravels/{officialtravel}/arrival', [OfficialtravelController::class, 'showArrivalForm'])->name('officialtravels.showArrivalForm');
    Route::post('officialtravels/{officialtravel}/arrival', [OfficialtravelController::class, 'arrivalStamp'])->name('officialtravels.arrivalStamp');
    Route::get('officialtravels/{officialtravel}/departure', [OfficialtravelController::class, 'showDepartureForm'])->name('officialtravels.showDepartureForm');
    Route::post('officialtravels/{officialtravel}/departure', [OfficialtravelController::class, 'departureStamp'])->name('officialtravels.departureStamp');

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
});
