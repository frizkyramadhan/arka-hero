<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\FamilieController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmrgcallController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PendidikanController;
use App\Http\Controllers\EmployeebankController;
use App\Http\Controllers\Admin\ProfileController;
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

Route::get('login', [AuthController::class, 'getLogin'])->name('getLogin')->middleware('guest');
Route::post('login', [AuthController::class, 'postLogin'])->name('postLogin');

Route::group(['middleware' => ['admin_auth']], function () {

    Route::get('/', [ProfileController::class, 'dashboard'])->name('dashboard');
    Route::get('users', [UserController::class, 'index'])->name('users.list');
    Route::get('logout', [ProfileController::class, 'logout'])->name('logout');

    Route::get('projects', [ProjectController::class, 'projects'])->name('projects');
    Route::get('addProjects', [ProjectController::class, 'addProjects'])->name('addProjects');
    Route::post('addProjects', [ProjectController::class, 'store'])->name('store');
    Route::get('editProject/{slug}', [ProjectController::class, 'editProject'])->name('editProject');
    Route::put('/updateProject/{slug}', [ProjectController::class, 'updateProject'])->name('updateProject');
    Route::delete('deleteProject/{slug}', [ProjectController::class, 'deleteProject'])->name('deleteProject');

    Route::get('banks', [BankController::class, 'banks'])->name('banks');
    Route::get('addBanks', [BankController::class, 'addBanks'])->name('addBanks');
    Route::post('addBanks', [BankController::class, 'store'])->name('store');
    Route::get('editBanks/{slug}', [BankController::class, 'editBanks'])->name('editBanks');
    Route::put('/updateBanks/{slug}', [BankController::class, 'updateBanks'])->name('updateBanks');
    Route::delete('deleteBanks/{slug}', [BankController::class, 'deleteBanks'])->name('deleteBanks');

    Route::get('departments', [DepartmentController::class, 'departments'])->name('departments');
    Route::get('addDept', [DepartmentController::class, 'addDept'])->name('addDept');
    Route::post('addDept', [DepartmentController::class, 'store'])->name('store');
    Route::get('editDept/{slug}', [DepartmentController::class, 'editDept'])->name('editDept');
    Route::put('/updateDept/{slug}', [DepartmentController::class, 'updateDept'])->name('updateDept');
    Route::delete('deleteDept/{slug}', [DepartmentController::class, 'deleteDept'])->name('deleteDept');

    Route::get('positions', [PositionController::class, 'positions'])->name('positions');
    Route::get('addPost', [PositionController::class, 'addPost'])->name('addPost');
    Route::post('addPost', [PositionController::class, 'store'])->name('store');
    Route::get('editPost/{slug}', [PositionController::class, 'editPost'])->name('editPost');
    Route::put('/updatePost/{slug}', [PositionController::class, 'updatePost'])->name('updatePost');
    Route::delete('deletePost/{slug}', [PositionController::class, 'deletePost'])->name('deletePost');

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

    // Route::get('pendidikans', [PendidikanController::class, 'pendidikans'])->name('educations');
    // Route::get('AddPendidikan', [PendidikanController::class, 'AddPendidikan'])->name('AddEducations');
    // Route::post('AddPendidikan', [PendidikanController::class, 'store'])->name('store');
    // Route::get('editPendidikan/{slug}', [PendidikanController::class, 'editPendidikan'])->name('editPendidikan');
    // Route::put('/updatePendidikan/{slug}', [PendidikanController::class, 'updatePendidikan'])->name('updatePendidikan');
    // Route::delete('deletePendidikan/{slug}', [PendidikanController::class, 'deletePendidikan'])->name('deletePendidikan');

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
