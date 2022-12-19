<?php

use App\Models\Jobexperience;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\FamilieController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmrgcallController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TerminationController;
use App\Http\Controllers\EmployeebankController;
use App\Http\Controllers\OperableunitController;
use App\Http\Controllers\JobexperienceController;
use App\Http\Controllers\AdditionaldataController;
use App\Http\Controllers\AdministrationController;
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
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('users/data', [UserController::class, 'getUsers'])->name('users.data');
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);

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

    Route::get('employees/data', [EmployeeController::class, 'getEmployees'])->name('employees.data');
    Route::get('employees/getDepartment', [EmployeeController::class, 'getDepartment'])->name('employees.getDepartment');
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/addImages/{id}', [EmployeeController::class, 'addImages'])->name('employees.addImages');
    Route::get('employees/deleteImage/{id}', [EmployeeController::class, 'deleteImage'])->name('employees.deleteImage');
    Route::get('employees/deleteImages/{employee_id}', [EmployeeController::class, 'deleteImages'])->name('employees.deleteImages');

    // Route::get('employees', [EmployeeController::class, 'employees'])->name('employees');
    // Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    // Route::post('addEmployee', [EmployeeController::class, 'store'])->name('addEmployee');
    // Route::get('edit/{slug}', [EmployeeController::class, 'editEmployee'])->name('editEmployee');
    // Route::put('updateEmployee/{slug}', [EmployeeController::class, 'updateEmployee'])->name('updateEmployee');
    // Route::get('deleteEmployee/{slug}', [EmployeeController::class, 'deleteEmployee'])->name('deleteEmployee');
    // Route::get('destroyEmployee/{slug}', [EmployeeController::class, 'destroyEmployee'])->name('destroyEmployee');
    // Route::get('detailEmployee/{slug}', [EmployeeController::class, 'detailEmployee'])->name('detailEmployee');

    Route::get('licenses/getLicenses', [LicenseController::class, 'getLicenses'])->name('licenses.list');
    Route::resource('licenses', LicenseController::class)->except(['store', 'show', 'create', 'edit']);
    Route::post('licenses/{employee_id}', [LicenseController::class, 'store'])->name('licenses.store');
    Route::delete('licenses/{employee_id}/{id}', [LicenseController::class, 'delete'])->name('licenses.delete');

    // Route::get('addLicense', [LicenseController::class, 'addLicense'])->name('addLicense');
    // Route::post('addLicense', [LicenseController::class, 'store'])->name('store');
    // Route::get('editLicense/{slug}', [LicenseController::class, 'editLicense'])->name('editLicense');
    // Route::put('/updateLicense/{slug}', [LicenseController::class, 'updateLicense'])->name('updateLicense');
    // Route::delete('deleteLicense/{slug}', [LicenseController::class, 'deleteLicense'])->name('deleteLicense');

    Route::get('insurances/getInsurances', [InsuranceController::class, 'getInsurances'])->name('insurances.list');
    Route::resource('insurances', InsuranceController::class)->except(['store', 'show', 'create', 'edit']);
    // Route::get('insurances', [InsuranceController::class, 'index'])->name('index');
    // Route::get('editInsurance/{slug}', [InsuranceController::class, 'editInsurance'])->name('editInsurance');
    // Route::put('/updateInsurance/{slug}', [InsuranceController::class, 'updateInsurance'])->name('updateInsurance');
    Route::post('insurances/{employee_id}', [InsuranceController::class, 'store'])->name('insurances.store');
    Route::delete('insurances/{employee_id}/{id}', [InsuranceController::class, 'delete'])->name('insurances.delete');

    Route::get('families/getFamilies', [FamilieController::class, 'getFamilies'])->name('families.list');
    Route::resource('families', FamilieController::class)->except(['store', 'show', 'create', 'edit']);
    // Route::get('families', [FamilieController::class, 'index'])->name('index');
    // Route::get('editFamilie/{slug}', [FamilieController::class, 'editFamilie'])->name('editFamilie');
    // Route::put('/updateFamilie/{slug}', [FamilieController::class, 'updateFamilie'])->name('updateFamilie');
    Route::post('families/{employee_id}', [FamilieController::class, 'store'])->name('families.store');
    Route::delete('families/{employee_id}/{id}', [FamilieController::class, 'delete'])->name('families.delete');

    Route::resource('courses', CourseController::class)->except(['store', 'show', 'create', 'edit']);
    // Route::get('courses', [CourseController::class, 'index'])->name('index');
    Route::get('courses/getCourse', [CourseController::class, 'getCourse'])->name('courses.list');
    // Route::get('editCourse/{slug}', [CourseController::class, 'editCourse'])->name('editCourse');
    // Route::put('/updateCourse/{slug}', [CourseController::class, 'updateCourse'])->name('updateCourse');
    Route::post('courses/{employee_id}', [CourseController::class, 'store'])->name('courses.store');
    Route::delete('courses/{employee_id}/{id}', [CourseController::class, 'delete'])->name('courses.delete');

    Route::get('emrgcalls/getEmrgcall', [EmrgcallController::class, 'getEmrgcall'])->name('emrgcalls.list');
    Route::resource('emrgcalls', EmrgcallController::class)->except(['store', 'show', 'create', 'edit']);
    // Route::get('emrgcalls', [EmrgcallController::class, 'index'])->name('index');
    // Route::get('editEmrgcall/{slug}', [EmrgcallController::class, 'editEmrgcall'])->name('editEmrgcall');
    // Route::put('/updateEmrgcall/{slug}', [EmrgcallController::class, 'updateEmrgcall'])->name('updateEmrgcall');
    Route::post('emrgcalls/{employee_id}', [EmrgcallController::class, 'store'])->name('emrgcalls.store');
    Route::delete('emrgcalls/{employee_id}/{id}', [EmrgcallController::class, 'delete'])->name('emrgcalls.delete');

    Route::get('additionaldatas/getAdditionaldata', [AdditionaldataController::class, 'getAdditionaldata'])->name('additionaldatas.list');
    Route::resource('additionaldatas', AdditionaldataController::class)->except(['show', 'create', 'edit']);
    // Route::get('additionaldatas', [AdditionaldataController::class, 'index'])->name('index');
    // Route::post('Addadditionaldata', [AdditionaldataController::class, 'store'])->name('store');
    // Route::get('editAdditionaldata/{slug}', [AdditionaldataController::class, 'editAdditionaldata'])->name('editAdditionaldata');
    // Route::put('/updateAdditionaldata/{slug}', [AdditionaldataController::class, 'updateAdditionaldata'])->name('updateAdditionaldata');
    // Route::delete('deleteAdditionaldata/{slug}', [AdditionaldataController::class, 'deleteAdditionaldata'])->name('deleteAdditionaldata');



    Route::resource('employeebanks', EmployeebankController::class)->except(['show', 'create', 'edit']);
    // Route::get('employeebanks', [EmployeebankController::class, 'index'])->name('index');
    Route::get('employeebanks/getEmployeebank', [EmployeebankController::class, 'getEmployeebank'])->name('employeebanks.list');
    Route::resource('employeebanks', EmployeebankController::class);
    // Route::post('AddEmployeebank', [EmployeebankController::class, 'store'])->name('store');
    // Route::get('editEmployeebank/{slug}', [EmployeebankController::class, 'editEmployeebank'])->name('editEmployeebank');
    // Route::put('/updateEmployeebank/{slug}', [EmployeebankController::class, 'updateEmployeebank'])->name('updateEmployeebank');
    // Route::delete('deleteEmployeebank/{slug}', [EmployeebankController::class, 'deleteEmployeebank'])->name('deleteEmployeebank');



    Route::resource('administrations', AdministrationController::class)->except(['show', 'create', 'edit']);
    // Route::get('administrations', [AdministrationController::class, 'index'])->name('index');
    Route::get('administrations/getAdministration', [AdministrationController::class, 'getAdministration'])->name('administrations.list');
    Route::resource('administrations', AdministrationController::class)->except(['store', 'show', 'create', 'edit']);
    // Route::get('editAdministration/{slug}', [AdministrationController::class, 'editAdministration'])->name('editAdministration');
    // Route::put('/updateAdministration/{slug}', [AdministrationController::class, 'updateAdministration'])->name('updateAdministration');
    Route::post('administrations/{employee_id}', [AdministrationController::class, 'store'])->name('administrations.store');
    Route::delete('administrations/{employee_id}/{id}', [AdministrationController::class, 'delete'])->name('administrations.delete');

    Route::get('schools/getSchool', [SchoolController::class, 'getSchool'])->name('schools.list');
    Route::resource('schools', SchoolController::class)->except(['store', 'show', 'create', 'edit']);
    // Route::get('schools', [SchoolController::class, 'index'])->name('index');
    // Route::get('EditSchool/{slug}', [SchoolController::class, 'EditSchool'])->name('EditSchool');
    // Route::put('/UpdateSchool/{slug}', [SchoolController::class, 'UpdateSchool'])->name('UpdateSchool');
    Route::post('schools/{employee_id}', [SchoolController::class, 'store'])->name('schools.store');
    Route::delete('schools/{employee_id}/{id}', [SchoolController::class, 'delete'])->name('schools.delete');
    // Route::post('ImportSchool', [SchoolController::class, 'ImportSchool'])->name('ImportSchool');
    // Route::get('ExportSchool', [SchoolController::class, 'ExportSchool'])->name('ExportSchool');

    Route::get('jobexperiences/getJobexperiences', [JobexperienceController::class, 'getJobexperiences'])->name('jobexperiences.list');
    Route::resource('jobexperiences', JobexperienceController::class)->except(['store', 'show', 'create', 'edit']);
    Route::post('jobexperiences/{employee_id}', [JobexperienceController::class, 'store'])->name('jobexperiences.store');
    Route::delete('jobexperiences/{employee_id}/{id}', [JobexperienceController::class, 'delete'])->name('jobexperiences.delete');

    Route::get('operableunits/getOperableunits', [OperableunitController::class, 'getOperableunits'])->name('operableunits.list');

    Route::resource('operableunits', OperableunitController::class)->except(['store', 'show', 'create', 'edit']);
    Route::post('operableunits/{employee_id}', [OperableunitController::class, 'store'])->name('operableunits.store');
    Route::delete('operableunits/{employee_id}/{id}', [OperableunitController::class, 'delete'])->name('operableunits.delete');

    Route::get('taxidentifications', [TaxidentificationController::class, 'index'])->name('index');
    Route::resource('taxidentifications', TaxidentificationController::class)->except(['show', 'create', 'edit']);
    // Route::get('taxidentifications', [TaxidentificationController::class, 'index'])->name('index');
    Route::get('taxidentifications/getTaxidentifications', [TaxidentificationController::class, 'getTaxidentifications'])->name('taxidentifications.list');
    Route::get('terminations/getTerminations', [TerminationController::class, 'getTerminations'])->name('terminations.list');
    Route::resource('terminations', TerminationController::class)->except(['create', 'show', 'edit']);
});
