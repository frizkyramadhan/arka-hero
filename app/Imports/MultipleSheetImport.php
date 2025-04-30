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
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class MultipleSheetImport implements WithMultipleSheets, WithValidation, SkipsOnFailure, SkipsOnError, WithEvents
{
    use Importable, SkipsFailures, SkipsErrors;

    private $sheetName;
    private $personalImport;
    private $administrationImport;
    public function __construct()
    {
        $this->personalImport = new PersonalImport();
        $this->administrationImport = new AdministrationImport();
    }

    public function getSheetName()
    {
        return $this->sheetName;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->sheetName = $event->getSheet()->getTitle();
            }
        ];
    }

    public function sheets(): array
    {
        return [
            'personal' => $this->personalImport,
            'administration' => $this->administrationImport,
        ];
    }

    public function rules(): array
    {
        return [];
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        // Forward failures to the PersonalImport instance
        $this->personalImport->onFailure(...$failures);
        $this->administrationImport->onFailure(...$failures);
    }
}
