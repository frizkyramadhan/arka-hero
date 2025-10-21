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

    // ✅ OPTIMASI: Hanya simpan instance yang sudah dibuat
    private $importInstances = [];

    // Store rows from personal sheet for other imports to access
    private $pendingPersonalRows = [];

    public function __construct()
    {
        // ✅ OPTIMASI: Tidak memuat semua class sekaligus
        // Class akan dibuat hanya saat dibutuhkan (lazy loading)
    }

    /**
     * ✅ OPTIMASI: Lazy loading untuk import instances
     */
    private function getImportInstance($sheetName)
    {
        if (!isset($this->importInstances[$sheetName])) {
            switch ($sheetName) {
                case 'personal':
                    $this->importInstances[$sheetName] = new PersonalImport();
                    break;
                case 'administration':
                    $this->importInstances[$sheetName] = new AdministrationImport();
                    break;
                case 'bank accounts':
                    $this->importInstances[$sheetName] = new BankImport();
                    break;
                case 'tax identification no':
                    $this->importInstances[$sheetName] = new TaxImport();
                    break;
                case 'health insurance':
                    $this->importInstances[$sheetName] = new InsuranceImport();
                    break;
                case 'license':
                    $this->importInstances[$sheetName] = new LicenseImport();
                    break;
                case 'family':
                    $this->importInstances[$sheetName] = new FamilyImport();
                    break;
                case 'education':
                    $this->importInstances[$sheetName] = new EducationImport();
                    break;
                case 'course':
                    $this->importInstances[$sheetName] = new CourseImport();
                    break;
                case 'job experience':
                    $this->importInstances[$sheetName] = new JobExperienceImport();
                    break;
                case 'operable unit':
                    $this->importInstances[$sheetName] = new OperableunitImport();
                    break;
                case 'emergency call':
                    $this->importInstances[$sheetName] = new EmergencycallImport();
                    break;
                case 'additional data':
                    $this->importInstances[$sheetName] = new AdditionaldataImport();
                    break;
                case 'termination':
                    $this->importInstances[$sheetName] = new TerminationImport();
                    break;
                default:
                    return null;
            }

            // Set parent reference hanya untuk instance yang dibuat
            if (method_exists($this->importInstances[$sheetName], 'setParent')) {
                $this->importInstances[$sheetName]->setParent($this);
            }
        }

        return $this->importInstances[$sheetName];
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
        // ✅ OPTIMASI: Menggunakan lazy loading
        // Sheet hanya dibuat saat Laravel Excel membutuhkannya
        return [
            'personal' => $this->getImportInstance('personal'),
            'administration' => $this->getImportInstance('administration'),
            'bank accounts' => $this->getImportInstance('bank accounts'),
            'tax identification no' => $this->getImportInstance('tax identification no'),
            'health insurance' => $this->getImportInstance('health insurance'),
            'license' => $this->getImportInstance('license'),
            'family' => $this->getImportInstance('family'),
            'education' => $this->getImportInstance('education'),
            'course' => $this->getImportInstance('course'),
            'job experience' => $this->getImportInstance('job experience'),
            'operable unit' => $this->getImportInstance('operable unit'),
            'emergency call' => $this->getImportInstance('emergency call'),
            'additional data' => $this->getImportInstance('additional data'),
            'termination' => $this->getImportInstance('termination'),
        ];
    }

    public function rules(): array
    {
        return [];
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        // ✅ OPTIMASI: Forward failures hanya ke instance yang sudah dibuat
        foreach ($this->importInstances as $instance) {
            if (method_exists($instance, 'onFailure')) {
                $instance->onFailure(...$failures);
            }
        }
    }
}
