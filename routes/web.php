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

    // Route::get('employees', [EmployeeController::class, 'employees'])->name('employees');
    // Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    // Route::post('addEmployee', [EmployeeController::class, 'store'])->name('addEmployee');
    // Route::get('edit/{slug}', [EmployeeController::class, 'editEmployee'])->name('editEmployee');
    // Route::put('updateEmployee/{slug}', [EmployeeController::class, 'updateEmployee'])->name('updateEmployee');
    // Route::get('deleteEmployee/{slug}', [EmployeeController::class, 'deleteEmployee'])->name('deleteEmployee');
    // Route::get('destroyEmployee/{slug}', [EmployeeController::class, 'destroyEmployee'])->name('destroyEmployee');
    // Route::get('detailEmployee/{slug}', [EmployeeController::class, 'detailEmployee'])->name('detailEmployee');

    Route::resource('licenses', LicenseController::class)->except(['show', 'create', 'edit']);
    // Route::get('licenses', [LicenseController::class, 'index'])->name('index');
    Route::get('licenses/getLicenses', [LicenseController::class, 'getLicenses'])->name('licenses.list');

    // Route::get('addLicense', [LicenseController::class, 'addLicense'])->name('addLicense');
    // Route::post('addLicense', [LicenseController::class, 'store'])->name('store');
    // Route::get('editLicense/{slug}', [LicenseController::class, 'editLicense'])->name('editLicense');
    // Route::put('/updateLicense/{slug}', [LicenseController::class, 'updateLicense'])->name('updateLicense');
    // Route::delete('deleteLicense/{slug}', [LicenseController::class, 'deleteLicense'])->name('deleteLicense');

    Route::resource('insurances', InsuranceController::class)->except(['show', 'create', 'edit']);
    // Route::get('insurances', [InsuranceController::class, 'index'])->name('index');
    Route::get('insurances/getInsurances', [InsuranceController::class, 'getInsurances'])->name('insurances.list');
    // Route::post('addInsurance', [InsuranceController::class, 'store'])->name('store');
    // Route::get('editInsurance/{slug}', [InsuranceController::class, 'editInsurance'])->name('editInsurance');
    // Route::put('/updateInsurance/{slug}', [InsuranceController::class, 'updateInsurance'])->name('updateInsurance');
    // Route::delete('deleteInsurance/{slug}', [InsuranceController::class, 'deleteInsurance'])->name('deleteInsurance');

    Route::resource('families', FamilieController::class)->except(['show', 'create', 'edit']);
    // Route::get('families', [FamilieController::class, 'index'])->name('index');
    Route::get('families/getFamilies', [FamilieController::class, 'getFamilies'])->name('families.list');
    // Route::post('addFamilie', [FamilieController::class, 'store'])->name('store');
    // Route::get('editFamilie/{slug}', [FamilieController::class, 'editFamilie'])->name('editFamilie');
    // Route::put('/updateFamilie/{slug}', [FamilieController::class, 'updateFamilie'])->name('updateFamilie');
    // Route::delete('deleteFamilie/{slug}', [FamilieController::class, 'deleteFamilie'])->name('deleteFamilie');

    Route::resource('courses', CourseController::class)->except(['show', 'create', 'edit']);
    // Route::get('courses', [CourseController::class, 'index'])->name('index');
    Route::get('index/getCourse', [CourseController::class, 'getCourse'])->name('courses.list');
    // Route::post('addCourse', [CourseController::class, 'store'])->name('store');
    // Route::get('editCourse/{slug}', [CourseController::class, 'editCourse'])->name('editCourse');
    // Route::put('/updateCourse/{slug}', [CourseController::class, 'updateCourse'])->name('updateCourse');
    // Route::delete('deleteCourse/{slug}', [CourseController::class, 'deleteCourse'])->name('deleteCourse');

    Route::resource('emrgcalls', EmrgcallController::class)->except(['show', 'create', 'edit']);
    // Route::get('emrgcalls', [EmrgcallController::class, 'index'])->name('index');
    Route::get('emrgcalls/getEmrgcall', [EmrgcallController::class, 'getEmrgcall'])->name('emrgcalls.list');
    // Route::post('addEmrgcall', [EmrgcallController::class, 'store'])->name('store');
    // Route::get('editEmrgcall/{slug}', [EmrgcallController::class, 'editEmrgcall'])->name('editEmrgcall');
    // Route::put('/updateEmrgcall/{slug}', [EmrgcallController::class, 'updateEmrgcall'])->name('updateEmrgcall');
    // Route::delete('deleteEmrgcall/{slug}', [EmrgcallController::class, 'deleteEmrgcall'])->name('deleteEmrgcall');

    Route::resource('additionaldatas', AdditionaldataController::class)->except(['show', 'create', 'edit']);
    // Route::get('additionaldatas', [AdditionaldataController::class, 'index'])->name('index');
    Route::get('additionaldatas/getAdditionaldata', [AdditionaldataController::class, 'getAdditionaldata'])->name('additionaldatas.list');
    // Route::post('Addadditionaldata', [AdditionaldataController::class, 'store'])->name('store');
    // Route::get('editAdditionaldata/{slug}', [AdditionaldataController::class, 'editAdditionaldata'])->name('editAdditionaldata');
    // Route::put('/updateAdditionaldata/{slug}', [AdditionaldataController::class, 'updateAdditionaldata'])->name('updateAdditionaldata');
    // Route::delete('deleteAdditionaldata/{slug}', [AdditionaldataController::class, 'deleteAdditionaldata'])->name('deleteAdditionaldata');

    Route::resource('employeebanks', EmployeebankController::class)->except(['show', 'create', 'edit']);
    // Route::get('employeebanks', [EmployeebankController::class, 'index'])->name('index');
    Route::get('employeebanks/getEmployeebank', [EmployeebankController::class, 'getEmployeebank'])->name('employeebanks.list');
    // Route::post('AddEmployeebank', [EmployeebankController::class, 'store'])->name('store');
    // Route::get('editEmployeebank/{slug}', [EmployeebankController::class, 'editEmployeebank'])->name('editEmployeebank');
    // Route::put('/updateEmployeebank/{slug}', [EmployeebankController::class, 'updateEmployeebank'])->name('updateEmployeebank');
    // Route::delete('deleteEmployeebank/{slug}', [EmployeebankController::class, 'deleteEmployeebank'])->name('deleteEmployeebank');

    Route::resource('administrations', AdministrationController::class)->except(['show', 'create', 'edit']);
    // Route::get('administrations', [AdministrationController::class, 'index'])->name('index');
    Route::get('administrations/getAdministration', [AdministrationController::class, 'getAdministration'])->name('administrations.list');
    // Route::post('AddAdministration', [AdministrationController::class, 'store'])->name('store');
    // Route::get('editAdministration/{slug}', [AdministrationController::class, 'editAdministration'])->name('editAdministration');
    // Route::put('/updateAdministration/{slug}', [AdministrationController::class, 'updateAdministration'])->name('updateAdministration');
    // Route::delete('deleteAdministration/{slug}', [AdministrationController::class, 'deleteAdministration'])->name('deleteAdministration');

    Route::resource('schools', SchoolController::class)->except(['show', 'create', 'edit']);
    // Route::get('schools', [SchoolController::class, 'index'])->name('index');
    Route::get('schools/getSchool', [SchoolController::class, 'getSchool'])->name('schools.list');
    // Route::post('AddSchool', [SchoolController::class, 'store'])->name('store');
    // Route::get('EditSchool/{slug}', [SchoolController::class, 'EditSchool'])->name('EditSchool');
    // Route::put('/UpdateSchool/{slug}', [SchoolController::class, 'UpdateSchool'])->name('UpdateSchool');
    // Route::delete('admin/deleteSchool/{slug}', [SchoolController::class, 'deleteSchool'])->name('deleteSchool');
    // Route::post('ImportSchool', [SchoolController::class, 'ImportSchool'])->name('ImportSchool');
    // Route::get('ExportSchool', [SchoolController::class, 'ExportSchool'])->name('ExportSchool');

    Route::resource('jobexperiences', JobexperienceController::class)->except(['show', 'create', 'edit']);
    // Route::get('jobexperiences', [JobexperienceController::class, 'index'])->name('index');
    Route::get('jobexperiences/getJobexperiences', [JobexperienceController::class, 'getJobexperiences'])->name('jobexperiences.list');

    Route::resource('operableunits', OperableunitController::class)->except(['show', 'create', 'edit']);
    // Route::get('operableunits', [OperableunitController::class, 'index'])->name('index');
    Route::get('operableunits/getOperableunits', [OperableunitController::class, 'getOperableunits'])->name('operableunits.list');


    Route::get('taxidentifications', [TaxidentificationController::class, 'index'])->name('index');
    Route::get('taxidentifications/getTaxidentifications', [TaxidentificationController::class, 'getTaxidentifications'])->name('taxidentifications.list');

});
