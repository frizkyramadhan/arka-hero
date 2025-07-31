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
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Events\BeforeSheet;

class MultipleSheetImport implements WithMultipleSheets, WithValidation, SkipsOnFailure, SkipsOnError, WithEvents, SkipsUnknownSheets
{
    use Importable, SkipsFailures, SkipsErrors;

    private $sheetName;
    private $personalImport;
    private $administrationImport;
    private $bankImport;
    private $taxImport;
    private $insuranceImport;
    private $licenseImport;
    private $familyImport;
    private $educationImport;
    private $courseImport;
    private $jobExperienceImport;
    private $operableunitImport;
    private $emergencycallImport;
    private $additionaldataImport;
    private $terminationImport;

    // Store rows from personal sheet for other imports to access
    private $pendingPersonalRows = [];

    public function __construct()
    {
        $this->personalImport = new PersonalImport();
        $this->administrationImport = new AdministrationImport();
        $this->bankImport = new BankImport();
        $this->taxImport = new TaxImport();
        $this->insuranceImport = new InsuranceImport();
        $this->licenseImport = new LicenseImport();
        $this->familyImport = new FamilyImport();
        $this->educationImport = new EducationImport();
        $this->courseImport = new CourseImport();
        $this->jobExperienceImport = new JobExperienceImport();
        $this->operableunitImport = new OperableunitImport();
        $this->emergencycallImport = new EmergencycallImport();
        $this->additionaldataImport = new AdditionaldataImport();
        $this->terminationImport = new TerminationImport();

        // Set reference to this parent in each import
        $this->bankImport->setParent($this);
        $this->taxImport->setParent($this);
        $this->insuranceImport->setParent($this);
        $this->licenseImport->setParent($this);
        $this->familyImport->setParent($this);
        $this->educationImport->setParent($this);
        $this->courseImport->setParent($this);
        $this->jobExperienceImport->setParent($this);
        $this->operableunitImport->setParent($this);
        $this->emergencycallImport->setParent($this);
        $this->additionaldataImport->setParent($this);
        $this->administrationImport->setParent($this);
        $this->terminationImport->setParent($this);

        // Have personal import collect rows during validation
        $this->personalImport->setParent($this);
    }

    /**
     * Add a personal sheet row to be tracked during import
     */
    public function addPendingPersonalRow($row)
    {
        $this->pendingPersonalRows[] = $row;
    }

    /**
     * Get all pending personal rows being imported
     */
    public function getPendingPersonalRows()
    {
        return $this->pendingPersonalRows;
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

    /**
     * Handle unknown sheets gracefully
     */
    public function onUnknownSheet($sheetName)
    {
        // Log that a sheet was not found
        info("Sheet '{$sheetName}' was not found in the Excel file and was skipped");

        // Get existing skipped sheets from session
        $skippedSheets = session('skipped_sheets', []);

        // Add the new skipped sheet if not already in the array
        if (!in_array($sheetName, $skippedSheets)) {
            $skippedSheets[] = $sheetName;
            session()->flash('skipped_sheets', $skippedSheets);
        }
    }

    public function sheets(): array
    {
        return [
            'personal' => $this->personalImport,
            'administration' => $this->administrationImport,
            'bank accounts' => $this->bankImport,
            'tax identification no' => $this->taxImport,
            'health insurance' => $this->insuranceImport,
            'license' => $this->licenseImport,
            'family' => $this->familyImport,
            'education' => $this->educationImport,
            'course' => $this->courseImport,
            'job experience' => $this->jobExperienceImport,
            'operable unit' => $this->operableunitImport,
            'emergency call' => $this->emergencycallImport,
            'additional data' => $this->additionaldataImport,
            'termination' => $this->terminationImport,
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
        $this->bankImport->onFailure(...$failures);
        $this->taxImport->onFailure(...$failures);
        $this->insuranceImport->onFailure(...$failures);
        $this->licenseImport->onFailure(...$failures);
        $this->familyImport->onFailure(...$failures);
        $this->educationImport->onFailure(...$failures);
        $this->courseImport->onFailure(...$failures);
        $this->jobExperienceImport->onFailure(...$failures);
        $this->operableunitImport->onFailure(...$failures);
        $this->emergencycallImport->onFailure(...$failures);
        $this->additionaldataImport->onFailure(...$failures);
        $this->terminationImport->onFailure(...$failures);
    }
}
