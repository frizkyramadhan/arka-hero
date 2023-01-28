<?php

namespace App\Exports;

use App\Exports\TaxExport;
use App\Exports\BankExport;
use App\Exports\CourseExport;
use App\Exports\FamilyExport;
use App\Exports\LicenseExport;
use App\Exports\PersonalExport;
use App\Exports\EducationExport;
use App\Exports\TerminationExport;
use App\Exports\OperableunitExport;
use App\Exports\EmergencycallExport;
use App\Exports\JobExperienceExport;
use App\Exports\AdditionaldataExport;
use App\Exports\AdministrationExport;
use App\Exports\HealthInsuranceExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultipleSheetExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        // $sheets = ['personal', 'administration', 'bank accounts', 'tax identification no', 'health insurance', 'license', 'family', 'education', 'course', 'job experience', 'operable unit', 'emergency call', 'additional data'];

        $sheets[0] = new PersonalExport;
        $sheets[1] = new AdministrationExport;
        $sheets[2] = new BankExport;
        $sheets[3] = new TaxExport;
        $sheets[4] = new HealthInsuranceExport;
        $sheets[5] = new LicenseExport;
        $sheets[6] = new FamilyExport;
        $sheets[7] = new EducationExport;
        $sheets[8] = new CourseExport;
        $sheets[9] = new JobExperienceExport;
        $sheets[10] = new OperableunitExport;
        $sheets[11] = new EmergencycallExport;
        $sheets[12] = new AdditionaldataExport;
        $sheets[13] = new TerminationExport;

        return $sheets;
    }
}
