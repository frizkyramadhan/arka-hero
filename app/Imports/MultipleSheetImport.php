<?php

namespace App\Imports;

use App\Imports\TaxImport;
use App\Imports\BankImport;
use App\Imports\CourseImport;
use App\Imports\FamilyImport;
use App\Imports\LicenseImport;
use App\Imports\PersonalImport;
use App\Imports\EducationImport;
use App\Imports\TerminationImport;
use App\Imports\OperableunitImport;
use App\Imports\EmergencycallImport;
use App\Imports\JobExperienceImport;
use App\Imports\AdditionaldataImport;
use App\Imports\AdministrationImport;
use App\Imports\HealthInsuranceImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

class MultipleSheetImport implements WithMultipleSheets, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function sheets(): array
    {
        $sheets = [];

        // Personal Data
        $sheets['personal'] = new PersonalImport();

        // Administration Data
        $sheets['administration'] = new AdministrationImport();

        // Bank Accounts
        $sheets['bank'] = new BankImport();

        // Tax Identification
        $sheets['tax'] = new TaxImport();

        // Health Insurance
        $sheets['health insurance'] = new HealthInsuranceImport();

        // License
        $sheets['license'] = new LicenseImport();

        // Family
        $sheets['family'] = new FamilyImport();

        // Education
        $sheets['education'] = new EducationImport();

        // Course
        $sheets['course'] = new CourseImport();

        // Job Experience
        $sheets['job experience'] = new JobExperienceImport();

        // Operable Unit
        $sheets['operable unit'] = new OperableunitImport();

        // Emergency Call
        $sheets['emergency call'] = new EmergencycallImport();

        // Additional Data
        $sheets['additional data'] = new AdditionaldataImport();

        // Termination
        $sheets['termination'] = new TerminationImport();

        return $sheets;
    }

    public function rules(): array
    {
        return [];
    }
}
