<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultipleSheetExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @param  list<string>  $employeeIds
     */
    public function __construct(
        private readonly array $employeeIds = []
    ) {}

    public function sheets(): array
    {
        $ids = $this->employeeIds;

        return [
            new PersonalExport($ids),
            new AdministrationExport($ids),
            new BankExport($ids),
            new TaxExport($ids),
            new HealthInsuranceExport($ids),
            new LicenseExport($ids),
            new FamilyExport($ids),
            new EducationExport($ids),
            new CourseExport($ids),
            new JobExperienceExport($ids),
            new OperableunitExport($ids),
            new EmergencycallExport($ids),
            new AdditionaldataExport($ids),
            new TerminationExport($ids),
        ];
    }
}
