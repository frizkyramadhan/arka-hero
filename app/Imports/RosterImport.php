<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Roster;
use App\Models\Project;
use App\Models\Administration;
use App\Models\RosterDailyStatus;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RosterImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $project;
    protected $year;
    protected $month;
    protected $employees;
    protected $importedCount = 0;
    protected $errors = [];

    public function __construct(Project $project, int $year, int $month)
    {
        $this->project = $project;
        $this->year = $year;
        $this->month = $month;
        $this->employees = $this->getProjectEmployees($project);

        Log::info('=== ROSTER IMPORT INITIALIZED ===', [
            'project_code' => $project->project_code,
            'project_id' => $project->id,
            'year' => $year,
            'month' => $month,
            'employees_count' => $this->employees->count(),
            'employees_nik' => $this->employees->keys()->toArray()
        ]);
    }

    /**
     * Get employees for roster project
     */
    private function getProjectEmployees($project)
    {
        return Administration::with(['employee', 'position', 'level', 'roster'])
            ->where('project_id', $project->id)
            ->where('is_active', 1)
            ->whereHas('level', function ($query) {
                $query->whereNotNull('work_days');
            })
            ->orderBy('nik')
            ->get()
            ->keyBy('nik'); // Key by NIK for easy lookup
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Enhanced debug logging for import data
            Log::info('=== ROSTER IMPORT DEBUG ===', [
                'project' => $this->project->project_code,
                'year' => $this->year,
                'month' => $this->month,
                'row_number' => $this->importedCount + 1,
                'row_keys' => array_keys($row),
                'row_data' => $row,
                'available_employees' => $this->employees->keys()->toArray()
            ]);

            // Validate required fields
            if (empty($row['nik']) || empty($row['name'])) {
                $this->errors[] = "Row missing NIK or Name: " . json_encode($row);
                return null;
            }

            $nik = trim((string) $row['nik']); // Convert to string to ensure compatibility
            $employeeName = trim($row['name']);

            // Find employee by NIK
            $administration = $this->employees->get($nik);
            if (!$administration) {
                $this->errors[] = "Employee with NIK {$nik} not found in project {$this->project->project_code}";
                return null;
            }

            // Verify employee name matches
            if ($administration->employee->fullname !== $employeeName) {
                $this->errors[] = "Name mismatch for NIK {$nik}: Expected '{$administration->employee->fullname}', Found '{$employeeName}'";
                return null;
            }

            // Ensure employee has roster
            if (!$administration->roster) {
                $administration->roster = $this->createRosterForEmployee($administration);
            }

            // Process roster data for each day
            $this->processRosterData($administration, $row);

            $this->importedCount++;
            return null; // We're not creating a model, just processing data

        } catch (\Exception $e) {
            Log::error('Roster Import Error: ' . $e->getMessage(), [
                'row' => $row,
                'project' => $this->project->project_code,
                'year' => $this->year,
                'month' => $this->month
            ]);

            $this->errors[] = "Error processing row: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Process roster data for each day in the month
     */
    private function processRosterData($administration, $row)
    {
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;

        Log::info('Processing roster data for employee', [
            'employee_nik' => $administration->nik,
            'employee_name' => $administration->employee->fullname,
            'roster_id' => $administration->roster->id,
            'days_in_month' => $daysInMonth,
            'row_data_sample' => array_slice($row, 0, 10) // First 10 columns for debugging
        ]);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            // Try different possible column names for the day
            $possibleKeys = [
                (string) $day,           // "1", "2", "3", etc.
                "day_{$day}",           // "day_1", "day_2", etc.
                "{$day}",               // "1", "2", "3", etc. (same as above)
            ];

            $statusCode = 'D'; // Default status

            // Find the status code for this day
            foreach ($possibleKeys as $key) {
                if (isset($row[$key]) && !empty(trim($row[$key]))) {
                    $statusCode = trim($row[$key]);
                    Log::debug("Found status code for day {$day}", [
                        'day' => $day,
                        'key' => $key,
                        'status_code' => $statusCode,
                        'employee_nik' => $administration->nik
                    ]);
                    break;
                }
            }

            // Validate status code
            if (!in_array($statusCode, ['D', 'N', 'OFF', 'S', 'I', 'A', 'C'])) {
                Log::warning("Invalid status code found", [
                    'day' => $day,
                    'status_code' => $statusCode,
                    'employee_nik' => $administration->nik,
                    'possible_keys' => $possibleKeys,
                    'row_data' => $row
                ]);
                $this->errors[] = "Invalid status code '{$statusCode}' for NIK {$administration->nik} on day {$day}";
                continue;
            }

            $currentDate = Carbon::create($this->year, $this->month, $day);

            // Update or create roster daily status
            $rosterDailyStatus = RosterDailyStatus::updateOrCreate(
                [
                    'roster_id' => $administration->roster->id,
                    'date' => $currentDate->format('Y-m-d')
                ],
                [
                    'status_code' => $statusCode,
                    'notes' => null // Notes can be added separately if needed
                ]
            );

            Log::debug("Roster daily status saved", [
                'day' => $day,
                'date' => $currentDate->format('Y-m-d'),
                'status_code' => $statusCode,
                'employee_nik' => $administration->nik,
                'roster_daily_status_id' => $rosterDailyStatus->id,
                'was_recently_created' => $rosterDailyStatus->wasRecentlyCreated
            ]);
        }
    }

    /**
     * Auto-create roster for employee if not exists
     */
    private function createRosterForEmployee($administration)
    {
        // Check if level has roster configuration
        if (!$administration->level || !$administration->level->hasRosterConfig()) {
            throw new \Exception("Employee {$administration->nik} level does not have roster configuration");
        }

        return Roster::create([
            'employee_id' => $administration->employee_id,
            'administration_id' => $administration->id,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(3)->endOfMonth(),
            'cycle_no' => 1,
            'adjusted_days' => 0,
            'is_active' => true
        ]);
    }

    /**
     * @return int
     */
    public function headingRow(): int
    {
        return 1; // First row contains headers
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
        $rules = [
            'no' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'nik' => 'required|max:20', // Accept both string and integer
            'position' => 'nullable|string|max:255'
        ];

        // Add validation rules for each day
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $rules[(string) $day] = 'nullable|in:D,N,OFF,S,I,A,C';
        }

        return $rules;
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
        $messages = [
            'name.required' => 'Name is required',
            'nik.required' => 'NIK is required',
            'nik.max' => 'NIK cannot exceed 20 characters',
            'name.max' => 'Name cannot exceed 255 characters',
            'position.max' => 'Position cannot exceed 255 characters'
        ];

        // Add validation messages for each day
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $messages["{$day}.in"] = "Day {$day} status must be one of: D, N, OFF, S, I, A, C";
        }

        return $messages;
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Get import results
     */
    public function getImportResults()
    {
        $results = [
            'imported_count' => $this->importedCount,
            'errors' => $this->errors,
            'has_errors' => !empty($this->errors),
            'project_code' => $this->project->project_code,
            'year' => $this->year,
            'month' => $this->month,
            'total_employees' => $this->employees->count(),
            'available_employees' => $this->employees->keys()->toArray()
        ];

        Log::info('=== IMPORT RESULTS SUMMARY ===', $results);

        return $results;
    }

    /**
     * Clear existing roster data before import
     */
    public function clearExistingData()
    {
        $rosters = Roster::whereHas('administration', function ($query) {
            $query->where('project_id', $this->project->id);
        })->get();

        $deletedCount = 0;

        foreach ($rosters as $roster) {
            $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
            $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

            $deleted = RosterDailyStatus::where('roster_id', $roster->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->delete();

            $deletedCount += $deleted;
        }

        return $deletedCount;
    }

    /**
     * Handle import errors
     */
    public function onError(\Throwable $e)
    {
        Log::error('Roster Import Error: ' . $e->getMessage(), [
            'project' => $this->project->project_code,
            'year' => $this->year,
            'month' => $this->month
        ]);

        $this->errors[] = "Import error: " . $e->getMessage();
    }

    /**
     * Handle import failures
     */
    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Roster Import Failure: ' . $failure->errors()[0], [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'project' => $this->project->project_code,
                'year' => $this->year,
                'month' => $this->month
            ]);

            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }
}
