<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultipleSheetExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly Builder $employeeExportIdsQuery
    ) {}

    public function sheets(): array
    {
        $sub = $this->employeeExportIdsQuery;

        return [
            new PersonalExport($sub),
            new AdministrationExport($sub),
            new BankExport($sub),
            new TaxExport($sub),
            new HealthInsuranceExport($sub),
            new LicenseExport($sub),
            new FamilyExport($sub),
            new EducationExport($sub),
            new CourseExport($sub),
            new JobExperienceExport($sub),
            new OperableunitExport($sub),
            new EmergencycallExport($sub),
            new AdditionaldataExport($sub),
            new TerminationExport($sub),
        ];
    }
}
