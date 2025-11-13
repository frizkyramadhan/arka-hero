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
     * Returns all active employees from the selected project (matching controller logic)
     */
    private function getProjectEmployees($project)
    {
        return Administration::with(['employee', 'position.department', 'level', 'roster'])
            ->where('project_id', $project->id)
            ->where('is_active', 1)
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

            // Normalize row keys to lowercase for case-insensitive matching
            $normalizedRow = [];
            foreach ($row as $key => $value) {
                $normalizedRow[strtolower(trim($key))] = $value;
            }

            // Validate required fields (case-insensitive)
            if (empty($normalizedRow['nik']) || empty($normalizedRow['name'])) {
                $this->errors[] = "Row missing NIK or Name. Available keys: " . implode(', ', array_keys($row));
                Log::warning("Row missing required fields", [
                    'row_keys' => array_keys($row),
                    'normalized_keys' => array_keys($normalizedRow),
                    'row_data' => $row
                ]);
                return null;
            }

            $nik = trim((string) $normalizedRow['nik']); // Convert to string to ensure compatibility
            $employeeName = trim($normalizedRow['name']);

            // Find employee by NIK - reload fresh data
            $administration = $this->employees->get($nik);
            if (!$administration) {
                // Try to reload employees if not found in cache
                $administration = Administration::with(['employee', 'position.department', 'level', 'roster'])
                    ->where('project_id', $this->project->id)
                    ->where('is_active', 1)
                    ->where('nik', $nik)
                    ->first();

                if (!$administration) {
                    $this->errors[] = "Employee with NIK {$nik} not found in project {$this->project->project_code}";
                    return null;
                }
            } else {
                // Reload to get fresh roster data
                $administration = Administration::with(['employee', 'position.department', 'level', 'roster'])
                    ->find($administration->id);
            }

            // Verify employee name matches
            if ($administration->employee->fullname !== $employeeName) {
                $this->errors[] = "Name mismatch for NIK {$nik}: Expected '{$administration->employee->fullname}', Found '{$employeeName}'";
                return null;
            }

            // Verify department if provided in import file (optional validation)
            if (isset($normalizedRow['department']) && !empty(trim($normalizedRow['department']))) {
                $importDepartment = trim($normalizedRow['department']);
                $actualDepartment = $administration->position->department->department_name ?? null;

                if ($actualDepartment && $actualDepartment !== $importDepartment) {
                    $this->errors[] = "Department mismatch for NIK {$nik}: Expected '{$actualDepartment}', Found '{$importDepartment}'";
                    // Don't return null, just log the warning - continue processing
                }
            }

            // Ensure employee has roster - reload to get fresh data
            $administration->load('roster');
            if (!$administration->roster) {
                try {
                    $roster = $this->createRosterForEmployee($administration);
                    if ($roster) {
                        // Reload administration to get the new roster
                        $administration = Administration::with(['employee', 'position.department', 'level', 'roster'])
                            ->find($administration->id);
                    } else {
                        $this->errors[] = "Failed to create roster for NIK {$nik}";
                        return null;
                    }
                } catch (\Exception $e) {
                    Log::error("Cannot create roster for NIK {$nik}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $this->errors[] = "Cannot create roster for NIK {$nik}: {$e->getMessage()}";
                    return null;
                }
            }

            // Verify roster exists before processing
            if (!$administration->roster || !$administration->roster->id) {
                $this->errors[] = "Roster not available for NIK {$nik}";
                return null;
            }

            // Process roster data for each day (use original row for day columns)
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
            // Try different possible column names for the day (case-insensitive)
            $possibleKeys = [
                (string) $day,           // "1", "2", "3", etc.
                "day_{$day}",           // "day_1", "day_2", etc.
                "{$day}",               // "1", "2", "3", etc. (same as above)
            ];

            $statusCode = 'D'; // Default status
            $found = false;

            // Find the status code for this day (try both exact and case-insensitive match)
            foreach ($possibleKeys as $key) {
                // Try exact match first
                if (isset($row[$key]) && !empty(trim($row[$key]))) {
                    $statusCode = trim($row[$key]);
                    $found = true;
                    Log::debug("Found status code for day {$day} (exact match)", [
                        'day' => $day,
                        'key' => $key,
                        'status_code' => $statusCode,
                        'employee_nik' => $administration->nik
                    ]);
                    break;
                }
            }

            // If not found, try case-insensitive match
            if (!$found) {
                foreach ($row as $rowKey => $rowValue) {
                    $normalizedKey = strtolower(trim($rowKey));
                    foreach ($possibleKeys as $key) {
                        if ($normalizedKey === strtolower(trim($key)) && !empty(trim($rowValue))) {
                            $statusCode = trim($rowValue);
                            $found = true;
                            Log::debug("Found status code for day {$day} (case-insensitive)", [
                                'day' => $day,
                                'original_key' => $rowKey,
                                'matched_key' => $key,
                                'status_code' => $statusCode,
                                'employee_nik' => $administration->nik
                            ]);
                            break 2; // Break both loops
                        }
                    }
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

            // Verify roster_id is available
            if (!$administration->roster || !$administration->roster->id) {
                Log::error("Roster ID not available for employee", [
                    'employee_nik' => $administration->nik,
                    'day' => $day
                ]);
                $this->errors[] = "Roster ID not available for NIK {$administration->nik} on day {$day}";
                continue;
            }

            // Update or create roster daily status
            try {
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
                    'roster_id' => $administration->roster->id,
                    'roster_daily_status_id' => $rosterDailyStatus->id,
                    'was_recently_created' => $rosterDailyStatus->wasRecentlyCreated
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to save roster daily status", [
                    'employee_nik' => $administration->nik,
                    'day' => $day,
                    'roster_id' => $administration->roster->id ?? 'null',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->errors[] = "Failed to save status for NIK {$administration->nik} on day {$day}: {$e->getMessage()}";
            }
        }
    }

    /**
     * Auto-create roster for employee if not exists
     * Creates roster for all employees, even if they don't have level with work_days
     */
    private function createRosterForEmployee($administration)
    {
        // Check if roster already exists
        $existingRoster = Roster::where('administration_id', $administration->id)
            ->where('is_active', true)
            ->first();

        if ($existingRoster) {
            return $existingRoster;
        }

        // Create roster for employee (even without level with work_days)
        // This allows import for all employees
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
            'department' => 'nullable|string|max:255',
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
