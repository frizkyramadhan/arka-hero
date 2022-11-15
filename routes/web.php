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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login', [AuthController::class, 'getLogin'])->name('getLogin');
Route::post('/admin/login', [AuthController::class, 'postLogin'])->name('postLogin');

Route::group(['middleware'=>['admin_auth']], function(){

        Route::get('/admin/dashboard', [ProfileController::class, 'dashboard'])->name('dashboard');
        Route::get('/admin/users', [UserController::class, 'index'])->name('users.list');
        Route::get('/admin/logout', [ProfileController::class, 'logout'])->name('logout');

        Route::get('/admin/projects', [ProjectController::class, 'projects'])->name('projects');
        Route::get('/admin/addProjects', [ProjectController::class, 'addProjects'])->name('addProjects');
        Route::post('/admin/addProjects', [ProjectController::class, 'store'])->name('store');
        Route::get('/admin/editProject/{slug}', [ProjectController::class, 'editProject'])->name('editProject');
        Route::put('/updateProject/{slug}', [ProjectController::class, 'updateProject'])->name('updateProject');
        Route::delete('/admin/deleteProject/{slug}', [ProjectController::class, 'deleteProject'])->name('deleteProject');

        Route::get('/admin/banks', [BankController::class, 'banks'])->name('banks');
        Route::get('/admin/addBanks', [BankController::class, 'addBanks'])->name('addBanks');
        Route::post('/admin/addBanks', [BankController::class, 'store'])->name('store');
        Route::get('/admin/editBanks/{slug}', [BankController::class, 'editBanks'])->name('editBanks');
        Route::put('/updateBanks/{slug}', [BankController::class, 'updateBanks'])->name('updateBanks');
        Route::delete('/admin/deleteBanks/{slug}', [BankController::class, 'deleteBanks'])->name('deleteBanks');

        Route::get('/admin/departments', [DepartmentController::class, 'departments'])->name('departments');
        Route::get('/admin/addDept', [DepartmentController::class, 'addDept'])->name('addDept');
        Route::post('/admin/addDept', [DepartmentController::class, 'store'])->name('store');
        Route::get('/admin/editDept/{slug}', [DepartmentController::class, 'editDept'])->name('editDept');
        Route::put('/updateDept/{slug}', [DepartmentController::class, 'updateDept'])->name('updateDept');
        Route::delete('/admin/deleteDept/{slug}', [DepartmentController::class, 'deleteDept'])->name('deleteDept');

        Route::get('/admin/positions', [PositionController::class, 'positions'])->name('positions');
        Route::get('/admin/addPost', [PositionController::class, 'addPost'])->name('addPost');
        Route::post('/admin/addPost', [PositionController::class, 'store'])->name('store');
        Route::get('/admin/editPost/{slug}', [PositionController::class, 'editPost'])->name('editPost');
        Route::put('/updatePost/{slug}', [PositionController::class, 'updatePost'])->name('updatePost');
        Route::delete('/admin/deletePost/{slug}', [PositionController::class, 'deletePost'])->name('deletePost');

        Route::get('/admin/employees', [EmployeeController::class, 'employees'])->name('employees');
        Route::get('/admin/addEmployee', [EmployeeController::class, 'addEmployee'])->name('addEmployee');
        Route::post('/admin/addEmployee', [EmployeeController::class, 'store'])->name('store');
        Route::get('/admin/editEmployee/{slug}', [EmployeeController::class, 'editEmployee'])->name('editEmployee');
        Route::put('/updateEmployee/{slug}', [EmployeeController::class, 'updateEmployee'])->name('updateEmployee');
        Route::get('/admin/deleteEmployee/{slug}', [EmployeeController::class, 'deleteEmployee'])->name('deleteEmployee');
        Route::get('/destroyEmployee/{slug}', [EmployeeController::class, 'destroyEmployee'])->name('destroyEmployee');
        Route::get('/admin/detailEmployee/{slug}', [EmployeeController::class, 'detailEmployee'])->name('detailEmployee');

       
        Route::get('/admin/licenses', [LicenseController::class, 'licenses'])->name('licenses');
        Route::get('/admin/addLicense', [LicenseController::class, 'addLicense'])->name('addLicense');
        Route::post('/admin/addLicense', [LicenseController::class, 'store'])->name('store');
        Route::get('/admin/editLicense/{slug}', [LicenseController::class, 'editLicense'])->name('editLicense');
        Route::put('/updateLicense/{slug}', [LicenseController::class, 'updateLicense'])->name('updateLicense');
        Route::delete('/admin/deleteLicense/{slug}', [LicenseController::class, 'deleteLicense'])->name('deleteLicense');
        
        Route::get('/admin/insurances', [InsuranceController::class, 'insurances'])->name('insurances');
        Route::get('/admin/addInsurance', [InsuranceController::class, 'addInsurance'])->name('addInsurance');
        Route::post('/admin/addInsurance', [InsuranceController::class, 'store'])->name('store');
        Route::get('/admin/editInsurance/{slug}', [InsuranceController::class, 'editInsurance'])->name('editInsurance');
        Route::put('/updateInsurance/{slug}', [InsuranceController::class, 'updateInsurance'])->name('updateInsurance');
        Route::delete('/admin/deleteInsurance/{slug}', [InsuranceController::class, 'deleteInsurance'])->name('deleteInsurance');

        Route::get('/admin/families', [FamilieController::class, 'families'])->name('families');
        Route::get('/admin/addFamilie', [FamilieController::class, 'addFamilie'])->name('addFamilie');
        Route::post('/admin/addFamilie', [FamilieController::class, 'store'])->name('store');
        Route::get('/admin/editFamilie/{slug}', [FamilieController::class, 'editFamilie'])->name('editFamilie');
        Route::put('/updateFamilie/{slug}', [FamilieController::class, 'updateFamilie'])->name('updateFamilie');
        Route::delete('/admin/deleteFamilie/{slug}', [FamilieController::class, 'deleteFamilie'])->name('deleteFamilie');

        // Route::get('/admin/pendidikans', [PendidikanController::class, 'pendidikans'])->name('educations');
        // Route::get('/admin/AddPendidikan', [PendidikanController::class, 'AddPendidikan'])->name('AddEducations');
        // Route::post('/admin/AddPendidikan', [PendidikanController::class, 'store'])->name('store');
        // Route::get('/admin/editPendidikan/{slug}', [PendidikanController::class, 'editPendidikan'])->name('editPendidikan');
        // Route::put('/updatePendidikan/{slug}', [PendidikanController::class, 'updatePendidikan'])->name('updatePendidikan');
        // Route::delete('/admin/deletePendidikan/{slug}', [PendidikanController::class, 'deletePendidikan'])->name('deletePendidikan');

        Route::get('/admin/courses', [CourseController::class, 'courses'])->name('courses');
        Route::get('/admin/addCourse', [CourseController::class, 'addCourse'])->name('addCourse');
        Route::post('/admin/addCourse', [CourseController::class, 'store'])->name('store');
        Route::get('/admin/editCourse/{slug}', [CourseController::class, 'editCourse'])->name('editCourse');
        Route::put('/updateCourse/{slug}', [CourseController::class, 'updateCourse'])->name('updateCourse');
        Route::delete('/admin/deleteCourse/{slug}', [CourseController::class, 'deleteCourse'])->name('deleteCourse');
    
        Route::get('/admin/emrgcalls', [EmrgcallController::class, 'emrgcalls'])->name('emrgcalls');
        Route::get('/admin/addEmrgcall', [EmrgcallController::class, 'addEmrgcall'])->name('addEmrgcall');
        Route::post('/admin/addEmrgcall', [EmrgcallController::class, 'store'])->name('store');
        Route::get('/admin/editEmrgcall/{slug}', [EmrgcallController::class, 'editEmrgcall'])->name('editEmrgcall');
        Route::put('/updateEmrgcall/{slug}', [EmrgcallController::class, 'updateEmrgcall'])->name('updateEmrgcall');
        Route::delete('/admin/deleteEmrgcall/{slug}', [EmrgcallController::class, 'deleteEmrgcall'])->name('deleteEmrgcall');

        Route::get('/admin/additionaldatas', [AdditionaldataController::class, 'additionaldatas'])->name('additionaldatas');
        Route::get('/admin/Addadditionaldata', [AdditionaldataController::class, 'Addadditionaldata'])->name('Addadditionaldata');
        Route::post('/admin/Addadditionaldata', [AdditionaldataController::class, 'store'])->name('store');
        Route::get('/admin/editAdditionaldata/{slug}', [AdditionaldataController::class, 'editAdditionaldata'])->name('editAdditionaldata');
        Route::put('/updateAdditionaldata/{slug}', [AdditionaldataController::class, 'updateAdditionaldata'])->name('updateAdditionaldata');
        Route::delete('/admin/deleteAdditionaldata/{slug}', [AdditionaldataController::class, 'deleteAdditionaldata'])->name('deleteAdditionaldata');

        Route::get('/admin/employeebanks', [EmployeebankController::class, 'employeebanks'])->name('employeebanks');
        Route::get('/admin/AddEmployeebank', [EmployeebankController::class, 'AddEmployeebank'])->name('AddEmployeebank');
        Route::post('/admin/AddEmployeebank', [EmployeebankController::class, 'store'])->name('store');
        Route::get('/admin/editEmployeebank/{slug}', [EmployeebankController::class, 'editEmployeebank'])->name('editEmployeebank');
        Route::put('/updateEmployeebank/{slug}', [EmployeebankController::class, 'updateEmployeebank'])->name('updateEmployeebank');
        Route::delete('/admin/deleteEmployeebank/{slug}', [EmployeebankController::class, 'deleteEmployeebank'])->name('deleteEmployeebank');

        Route::get('/admin/administrations', [AdministrationController::class, 'administrations'])->name('administrations');
        Route::get('/admin/AddAdministration', [AdministrationController::class, 'AddAdministration'])->name('AddAdministration');
        Route::post('/admin/AddAdministration', [AdministrationController::class, 'store'])->name('store');
        Route::get('/admin/editAdministration/{slug}', [AdministrationController::class, 'editAdministration'])->name('editAdministration');
        Route::put('/updateAdministration/{slug}', [AdministrationController::class, 'updateAdministration'])->name('updateAdministration');
        Route::delete('/admin/deleteAdministration/{slug}', [AdministrationController::class, 'deleteAdministration'])->name('deleteAdministration');

        Route::get('/admin/schools', [SchoolController::class, 'schools'])->name('schools');
        Route::get('/admin/AddSchool', [SchoolController::class, 'AddSchool'])->name('AddSchool');
        Route::post('/admin/AddSchool', [SchoolController::class, 'store'])->name('store');
        Route::get('/admin/EditSchool/{slug}', [SchoolController::class, 'EditSchool'])->name('EditSchool');
        Route::put('/UpdateSchool/{slug}', [SchoolController::class, 'UpdateSchool'])->name('UpdateSchool');
        Route::delete('admin/deleteSchool/{slug}', [SchoolController::class, 'deleteSchool'])->name('deleteSchool');
        Route::post('/admin/ImportSchool', [SchoolController::class, 'ImportSchool'])->name('ImportSchool');
        Route::get('/admin/ExportSchool', [SchoolController::class, 'ExportSchool'])->name('ExportSchool');



});