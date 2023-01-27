<?php

use App\Models\Jobexperience;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailController;
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
use App\Http\Controllers\sendMailController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\PHPMailerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EducationsController;
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

    Route::get('licenses/getLicenses', [LicenseController::class, 'getLicenses'])->name('licenses.list');
    Route::resource('licenses', LicenseController::class)->except(['store', 'show', 'create', 'edit']);
    Route::post('licenses/{employee_id}', [LicenseController::class, 'store'])->name('licenses.store');
    Route::delete('licenses/{employee_id}/{id}', [LicenseController::class, 'delete'])->name('licenses.delete');

    Route::get('insurances/getInsurances', [InsuranceController::class, 'getInsurances'])->name('insurances.list');
    Route::resource('insurances', InsuranceController::class)->except(['store', 'show', 'create', 'edit']);
    Route::post('insurances/{employee_id}', [InsuranceController::class, 'store'])->name('insurances.store');
    Route::delete('insurances/{employee_id}/{id}', [InsuranceController::class, 'delete'])->name('insurances.delete');

    Route::get('families/getFamilies', [FamilieController::class, 'getFamilies'])->name('families.list');
    Route::resource('families', FamilieController::class)->except(['store', 'show', 'create', 'edit']);
    Route::post('families/{employee_id}', [FamilieController::class, 'store'])->name('families.store');
    Route::delete('families/{employee_id}/{id}', [FamilieController::class, 'delete'])->name('families.delete');

    Route::resource('courses', CourseController::class)->except(['store', 'show', 'create', 'edit']); 
    Route::get('courses/getCourse', [CourseController::class, 'getCourse'])->name('courses.list');
    Route::post('courses/{employee_id}', [CourseController::class, 'store'])->name('courses.store');
    Route::delete('courses/{employee_id}/{id}', [CourseController::class, 'delete'])->name('courses.delete');

    Route::get('emrgcalls/getEmrgcall', [EmrgcallController::class, 'getEmrgcall'])->name('emrgcalls.list');
    Route::resource('emrgcalls', EmrgcallController::class)->except(['store', 'show', 'create', 'edit']);
    Route::post('emrgcalls/{employee_id}', [EmrgcallController::class, 'store'])->name('emrgcalls.store');
    Route::delete('emrgcalls/{employee_id}/{id}', [EmrgcallController::class, 'delete'])->name('emrgcalls.delete');

    Route::get('additionaldatas/getAdditionaldata', [AdditionaldataController::class, 'getAdditionaldata'])->name('additionaldatas.list');
    Route::resource('additionaldatas', AdditionaldataController::class)->except(['show', 'create', 'edit']);

    Route::resource('employeebanks', EmployeebankController::class)->except(['show', 'create', 'edit']);
    Route::get('employeebanks/getEmployeebank', [EmployeebankController::class, 'getEmployeebank'])->name('employeebanks.list');
    Route::resource('employeebanks', EmployeebankController::class);
   
    Route::resource('administrations', AdministrationController::class)->except(['show', 'create', 'edit']);
    Route::get('administrations/getAdministration', [AdministrationController::class, 'getAdministration'])->name('administrations.list');
    // Route::resource('administrations', AdministrationController::class)->except(['store', 'show', 'create', 'edit']);
    // Route::post('administrations/{employee_id}', [AdministrationController::class, 'store'])->name('administrations.store');
    // Route::delete('administrations/{employee_id}/{id}', [AdministrationController::class, 'delete'])->name('administrations.delete');

    Route::resource('schools', SchoolController::class)->except(['store', 'show', 'create', 'edit']);
    Route::get('schools/getSchool', [SchoolController::class, 'getSchool'])->name('schools.list');

    Route::resource('educations', EducationsController::class)->except(['store', 'show', 'create', 'edit']);
    Route::get('educations/getEducations', [EducationsController::class, 'getEducations'])->name('educations.list');
   
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

    // Route::get('taxidentifications', [TaxidentificationController::class, 'index'])->name('index');
    Route::resource('taxidentifications', TaxidentificationController::class)->except(['show', 'create', 'edit']);
    Route::get('taxidentifications/getTaxidentifications', [TaxidentificationController::class, 'getTaxidentifications'])->name('taxidentifications.list');

    
    Route::get('terminations/getTerminations', [TerminationController::class, 'getTerminations'])->name('terminations.list');
    Route::resource('terminations', TerminationController::class)->except(['create', 'show', 'edit']);


    Route::resource('emails', EmailController::class)->except(['create', 'show', 'edit']);
    Route::post("emails", [EmailController::class, "sendMail"])->name("sendMail");

});
