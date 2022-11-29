<?php

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
use App\Http\Controllers\AdditionaldataController;
use App\Http\Controllers\AdministrationController;

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

    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
    Route::get('users/getUsers', [UserController::class, 'getUsers'])->name('users.list');

    Route::resource('banks', BankController::class)->except(['show', 'create', 'edit']);
    Route::get('banks/getBanks', [BankController::class, 'getBanks'])->name('banks.list');

    Route::resource('religions', ReligionController::class)->except(['show', 'create', 'edit']);
    Route::get('religions/getReligions', [ReligionController::class, 'getReligions'])->name('religions.list');

    Route::resource('projects', ProjectController::class)->except(['show', 'create', 'edit']);
    Route::get('projects/getProjects', [ProjectController::class, 'getProjects'])->name('projects.list');

    Route::resource('departments', DepartmentController::class)->except(['show', 'create', 'edit']);
    Route::get('departments/getDepartments', [DepartmentController::class, 'getDepartments'])->name('departments.list');
    Route::post('departments/import', [DepartmentController::class, 'import'])->name('departments.import');

    Route::resource('positions', PositionController::class)->except(['show', 'create', 'edit']);
    Route::get('positions/getPositions', [PositionController::class, 'getPositions'])->name('positions.list');
    Route::post('positions/import', [PositionController::class, 'import'])->name('positions.import');

    Route::get('employees', [EmployeeController::class, 'employees'])->name('employees');
    Route::get('addEmployee', [EmployeeController::class, 'addEmployee'])->name('addEmployee');
    Route::post('addEmployee', [EmployeeController::class, 'store'])->name('store');
    Route::get('editEmployee/{slug}', [EmployeeController::class, 'editEmployee'])->name('editEmployee');
    Route::put('/updateEmployee/{slug}', [EmployeeController::class, 'updateEmployee'])->name('updateEmployee');
    Route::get('deleteEmployee/{slug}', [EmployeeController::class, 'deleteEmployee'])->name('deleteEmployee');
    Route::get('/destroyEmployee/{slug}', [EmployeeController::class, 'destroyEmployee'])->name('destroyEmployee');
    Route::get('detailEmployee/{slug}', [EmployeeController::class, 'detailEmployee'])->name('detailEmployee');


    Route::get('licenses', [LicenseController::class, 'licenses'])->name('licenses');
    Route::get('addLicense', [LicenseController::class, 'addLicense'])->name('addLicense');
    Route::post('addLicense', [LicenseController::class, 'store'])->name('store');
    Route::get('editLicense/{slug}', [LicenseController::class, 'editLicense'])->name('editLicense');
    Route::put('/updateLicense/{slug}', [LicenseController::class, 'updateLicense'])->name('updateLicense');
    Route::delete('deleteLicense/{slug}', [LicenseController::class, 'deleteLicense'])->name('deleteLicense');

    Route::get('insurances', [InsuranceController::class, 'insurances'])->name('insurances');
    Route::get('addInsurance', [InsuranceController::class, 'addInsurance'])->name('addInsurance');
    Route::post('addInsurance', [InsuranceController::class, 'store'])->name('store');
    Route::get('editInsurance/{slug}', [InsuranceController::class, 'editInsurance'])->name('editInsurance');
    Route::put('/updateInsurance/{slug}', [InsuranceController::class, 'updateInsurance'])->name('updateInsurance');
    Route::delete('deleteInsurance/{slug}', [InsuranceController::class, 'deleteInsurance'])->name('deleteInsurance');

    Route::get('families', [FamilieController::class, 'families'])->name('families');
    Route::get('addFamilie', [FamilieController::class, 'addFamilie'])->name('addFamilie');
    Route::post('addFamilie', [FamilieController::class, 'store'])->name('store');
    Route::get('editFamilie/{slug}', [FamilieController::class, 'editFamilie'])->name('editFamilie');
    Route::put('/updateFamilie/{slug}', [FamilieController::class, 'updateFamilie'])->name('updateFamilie');
    Route::delete('deleteFamilie/{slug}', [FamilieController::class, 'deleteFamilie'])->name('deleteFamilie');

    Route::get('courses', [CourseController::class, 'courses'])->name('courses');
    Route::get('addCourse', [CourseController::class, 'addCourse'])->name('addCourse');
    Route::post('addCourse', [CourseController::class, 'store'])->name('store');
    Route::get('editCourse/{slug}', [CourseController::class, 'editCourse'])->name('editCourse');
    Route::put('/updateCourse/{slug}', [CourseController::class, 'updateCourse'])->name('updateCourse');
    Route::delete('deleteCourse/{slug}', [CourseController::class, 'deleteCourse'])->name('deleteCourse');

    Route::get('emrgcalls', [EmrgcallController::class, 'emrgcalls'])->name('emrgcalls');
    Route::get('addEmrgcall', [EmrgcallController::class, 'addEmrgcall'])->name('addEmrgcall');
    Route::post('addEmrgcall', [EmrgcallController::class, 'store'])->name('store');
    Route::get('editEmrgcall/{slug}', [EmrgcallController::class, 'editEmrgcall'])->name('editEmrgcall');
    Route::put('/updateEmrgcall/{slug}', [EmrgcallController::class, 'updateEmrgcall'])->name('updateEmrgcall');
    Route::delete('deleteEmrgcall/{slug}', [EmrgcallController::class, 'deleteEmrgcall'])->name('deleteEmrgcall');

    Route::get('additionaldatas', [AdditionaldataController::class, 'additionaldatas'])->name('additionaldatas');
    Route::get('Addadditionaldata', [AdditionaldataController::class, 'Addadditionaldata'])->name('Addadditionaldata');
    Route::post('Addadditionaldata', [AdditionaldataController::class, 'store'])->name('store');
    Route::get('editAdditionaldata/{slug}', [AdditionaldataController::class, 'editAdditionaldata'])->name('editAdditionaldata');
    Route::put('/updateAdditionaldata/{slug}', [AdditionaldataController::class, 'updateAdditionaldata'])->name('updateAdditionaldata');
    Route::delete('deleteAdditionaldata/{slug}', [AdditionaldataController::class, 'deleteAdditionaldata'])->name('deleteAdditionaldata');

    Route::get('employeebanks', [EmployeebankController::class, 'employeebanks'])->name('employeebanks');
    Route::get('AddEmployeebank', [EmployeebankController::class, 'AddEmployeebank'])->name('AddEmployeebank');
    Route::post('AddEmployeebank', [EmployeebankController::class, 'store'])->name('store');
    Route::get('editEmployeebank/{slug}', [EmployeebankController::class, 'editEmployeebank'])->name('editEmployeebank');
    Route::put('/updateEmployeebank/{slug}', [EmployeebankController::class, 'updateEmployeebank'])->name('updateEmployeebank');
    Route::delete('deleteEmployeebank/{slug}', [EmployeebankController::class, 'deleteEmployeebank'])->name('deleteEmployeebank');

    Route::get('administrations', [AdministrationController::class, 'administrations'])->name('administrations');
    Route::get('AddAdministration', [AdministrationController::class, 'AddAdministration'])->name('AddAdministration');
    Route::post('AddAdministration', [AdministrationController::class, 'store'])->name('store');
    Route::get('editAdministration/{slug}', [AdministrationController::class, 'editAdministration'])->name('editAdministration');
    Route::put('/updateAdministration/{slug}', [AdministrationController::class, 'updateAdministration'])->name('updateAdministration');
    Route::delete('deleteAdministration/{slug}', [AdministrationController::class, 'deleteAdministration'])->name('deleteAdministration');

    Route::get('schools', [SchoolController::class, 'schools'])->name('schools');
    Route::get('AddSchool', [SchoolController::class, 'AddSchool'])->name('AddSchool');
    Route::post('AddSchool', [SchoolController::class, 'store'])->name('store');
    Route::get('EditSchool/{slug}', [SchoolController::class, 'EditSchool'])->name('EditSchool');
    Route::put('/UpdateSchool/{slug}', [SchoolController::class, 'UpdateSchool'])->name('UpdateSchool');
    Route::delete('admin/deleteSchool/{slug}', [SchoolController::class, 'deleteSchool'])->name('deleteSchool');
    Route::post('ImportSchool', [SchoolController::class, 'ImportSchool'])->name('ImportSchool');
    Route::get('ExportSchool', [SchoolController::class, 'ExportSchool'])->name('ExportSchool');
});
