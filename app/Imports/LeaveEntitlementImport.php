<?php

namespace App\Imports;

use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\Administration;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LeaveEntitlementImport implements ToCollection, WithHeadingRow
{
    private $leaveTypes;
    private $errors = [];
    private $successCount = 0;
    private $skippedCount = 0;

    public function __construct()
    {
        // Get leave types ordered by code (same as export)
        $this->leaveTypes = LeaveType::where('is_active', true)
            ->orderBy('code')
            ->get();

        // Log leave types for debugging
        Log::info('Leave Types loaded for import', [
            'types' => $this->leaveTypes->map(function ($lt) {
                return [
                    'id' => $lt->id,
                    'name' => $lt->name,
                    'normalized' => $this->normalizeColumnName($lt->name),
                    'category' => $lt->category,
                ];
            })->toArray()
        ]);
    }

    public function collection(Collection $rows)
    {
        $rowNumber = 2; // Start from 2 (row 1 is header)

        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            // Skip completely empty rows
            if ($this->isEmptyRow($rowArray)) {
                $rowNumber++;
                continue;
            }

            // Process row with transaction
            DB::beginTransaction();
            try {
                $result = $this->processRow($rowArray, $rowNumber);

                if ($result['success']) {
                    DB::commit();
                    $this->successCount++;
                } else {
                    DB::rollBack();
                    $this->skippedCount++;
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'nik' => $this->getColumnValue($rowArray, 'nik'),
                        'errors' => $result['errors']
                    ];
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $this->skippedCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'nik' => $this->getColumnValue($rowArray, 'nik'),
                    'errors' => ['System error: ' . $e->getMessage()]
                ];

                Log::error("Import error at row {$rowNumber}: " . $e->getMessage());
            }

            $rowNumber++;
        }

        // Log summary
        Log::info('Leave Entitlement Import Completed', [
            'total_rows' => $rowNumber - 2,
            'success' => $this->successCount,
            'skipped' => $this->skippedCount,
            'errors' => count($this->errors)
        ]);
    }

    private function processRow($row, $rowNumber)
    {
        $errors = [];

        // 1. Validate NIK (REQUIRED)
        $nik = $this->getColumnValue($row, 'nik');
        if (empty($nik)) {
            return ['success' => false, 'errors' => ['NIK is required']];
        }

        // 2. Find employee by NIK
        $administration = Administration::where('nik', $nik)
            ->where('is_active', 1)
            ->first();

        if (!$administration || !$administration->employee) {
            return ['success' => false, 'errors' => ["NIK '{$nik}' not found or not active"]];
        }

        $employee = $administration->employee;

        // 3. Validate period dates (REQUIRED)
        // Try both 'start_period' and 'Start Period' (with space)
        $startPeriod = $this->getColumnValue($row, 'start_period') 
            ?: $this->getColumnValue($row, 'start period')
            ?: $this->getColumnValue($row, 'Start Period');
        $endPeriod = $this->getColumnValue($row, 'end_period')
            ?: $this->getColumnValue($row, 'end period')
            ?: $this->getColumnValue($row, 'End Period');

        if (empty($startPeriod) || empty($endPeriod)) {
            return ['success' => false, 'errors' => ['Start Period and End Period are required']];
        }

        // 4. Parse dates
        $periodStart = $this->parseDate($startPeriod);
        $periodEnd = $this->parseDate($endPeriod);

        if (!$periodStart || !$periodEnd) {
            return ['success' => false, 'errors' => ['Invalid date format. Use YYYY-MM-DD or Excel date format']];
        }

        if ($periodStart >= $periodEnd) {
            return ['success' => false, 'errors' => ['Start Period must be before End Period']];
        }

        // 5. Get deposit days
        $depositDays = (int) ($this->getColumnValue($row, 'deposit_days') ?? 0);
        if ($depositDays < 0) {
            return ['success' => false, 'errors' => ['Deposit Days cannot be negative']];
        }

        // 6. Process each leave type
        foreach ($this->leaveTypes as $leaveType) {
            // Get remaining days from Excel (export shows actual entitlement = remaining_days)
            // Excel contains remaining_days (entitled_days - taken_days)
            $remainingDays = $this->getLeaveTypeValue($row, $leaveType->name);

            // Default to 0 if not found or empty
            $remainingDays = is_numeric($remainingDays) ? max(0, (int) $remainingDays) : 0;

            // Check if this is a "Cuti Panjang" leave type
            $isCutiPanjang = stripos($leaveType->name, 'Cuti Panjang') !== false;

            if ($isCutiPanjang) {
                // Determine employee level (Staff/Non Staff) - same logic as export
                $level = $administration->level;
                $levelName = $level ? $level->name : '';
                $isStaff = $this->isStaffLevel($levelName);

                // Check if leave type matches employee level
                $isCutiPanjangStaff = stripos($leaveType->name, 'Staff') !== false &&
                    stripos($leaveType->name, 'Non') === false;
                $isCutiPanjangNonStaff = stripos($leaveType->name, 'Non Staff') !== false;

                // Skip if level doesn't match
                if ($isStaff && !$isCutiPanjangStaff) {
                    Log::info("Skipping Cuti Panjang mismatch for Staff", [
                        'nik' => $nik,
                        'leave_type' => $leaveType->name,
                        'employee_level' => $level ? $level->name : 'N/A'
                    ]);
                    continue; // Skip "Cuti Panjang - Non Staff" for Staff employees
                }
                if (!$isStaff && !$isCutiPanjangNonStaff) {
                    Log::info("Skipping Cuti Panjang mismatch for Non Staff", [
                        'nik' => $nik,
                        'leave_type' => $leaveType->name,
                        'employee_level' => $level ? $level->name : 'N/A'
                    ]);
                    continue; // Skip "Cuti Panjang - Staff" for Non Staff employees
                }
            }

            // Skip if 0 and no existing record (don't create unnecessary records)
            $existing = LeaveEntitlement::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('period_start', $periodStart->format('Y-m-d'))
                ->where('period_end', $periodEnd->format('Y-m-d'))
                ->first();

            // Preserve taken_days from existing record
            $takenDays = $existing ? $existing->taken_days : 0;

            // Calculate entitled_days from remaining_days (Excel contains remaining_days)
            // Formula: entitled_days = remaining_days + taken_days
            // This ensures that when we import, the actual entitlement (remaining) matches Excel
            $entitledDays = $remainingDays + $takenDays;

            // Skip if remaining_days is 0 and no existing record
            // But process if: remaining_days > 0 OR existing record exists OR is paid/unpaid leave
            $isPaidOrUnpaid = in_array($leaveType->category, ['paid', 'unpaid']);
            if ($remainingDays == 0 && !$existing && !$isPaidOrUnpaid) {
                continue;
            }

            // Set deposit_days only for LSL category
            $finalDepositDays = ($leaveType->category === 'lsl') ? $depositDays : 0;

            // Log for debugging
            Log::info("Processing leave entitlement for NIK {$nik}", [
                'leave_type' => $leaveType->name,
                'remaining_days_from_excel' => $remainingDays,
                'taken_days_preserved' => $takenDays,
                'calculated_entitled_days' => $entitledDays,
                'deposit_days' => $finalDepositDays,
                'existing_id' => $existing ? $existing->id : null,
                'employee_level' => $administration->level ? $administration->level->name : 'N/A'
            ]);

            // Create or update entitlement
            LeaveEntitlement::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'period_start' => $periodStart->format('Y-m-d'),
                    'period_end' => $periodEnd->format('Y-m-d'),
                ],
                [
                    'entitled_days' => $entitledDays,
                    'taken_days' => $takenDays, // Preserve existing taken_days
                    'deposit_days' => $finalDepositDays,
                ]
            );
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * Check if row is completely empty
     */
    private function isEmptyRow($row)
    {
        foreach ($row as $value) {
            if (!empty($value) && trim($value) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Get column value with multiple possible key formats
     */
    private function getColumnValue($row, $columnName)
    {
        $normalized = $this->normalizeColumnName($columnName);

        // Try normalized key first
        if (isset($row[$normalized])) {
            return $row[$normalized];
        }

        // Try original key
        if (isset($row[$columnName])) {
            return $row[$columnName];
        }

        // Try other common variations
        $variations = [
            strtolower($columnName),
            str_replace('_', ' ', strtolower($columnName)),
            ucwords(str_replace('_', ' ', $columnName)),
        ];

        foreach ($variations as $variation) {
            if (isset($row[$variation])) {
                return $row[$variation];
            }
        }

        return null;
    }

    /**
     * Get leave type value from row with comprehensive column matching
     * Handles special characters like / and -
     * Uses Laravel Excel's actual WithHeadingRow behavior
     */
    private function getLeaveTypeValue($row, $leaveTypeName)
    {
        // Log available columns in first row for debugging
        static $loggedColumns = false;
        if (!$loggedColumns) {
            Log::info('Excel columns detected', [
                'columns' => array_keys($row)
            ]);
            $loggedColumns = true;
        }

        // APPROACH 1: Try all possible variations (includes Laravel Excel actual behavior)
        // Variation #3 in generateColumnVariations() matches Laravel Excel's actual behavior:
        // - Removes slashes "/" entirely
        // - Replaces spaces with underscores "_"
        // - Replaces dashes "-" with underscores "_"
        $variations = $this->generateColumnVariations($leaveTypeName);
        foreach ($variations as $variation) {
            if (isset($row[$variation])) {
                Log::debug("Column match found using variation", [
                    'leave_type' => $leaveTypeName,
                    'matched_column' => $variation,
                    'value' => $row[$variation]
                ]);
                return $row[$variation];
            }
        }

        // APPROACH 2: Case-insensitive partial match (LAST RESORT)
        // Remove all non-alphanumeric characters and compare
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $leaveTypeName));
        foreach (array_keys($row) as $column) {
            $cleanColumn = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $column));
            if ($cleanColumn === $cleanName) {
                Log::debug("Column match found using alphanumeric comparison", [
                    'leave_type' => $leaveTypeName,
                    'matched_column' => $column,
                    'value' => $row[$column]
                ]);
                return $row[$column];
            }
        }

        // Log if not found
        Log::warning("Column not found for leave type", [
            'leave_type' => $leaveTypeName,
            'tried_variations' => $variations,
            'available_columns' => array_keys($row)
        ]);

        return null;
    }

    /**
     * Generate all possible column name variations for matching
     * Handles special characters like /, -, and spaces
     *
     * Laravel Excel's actual WithHeadingRow behavior:
     * - Removes slashes "/" entirely (not replaced)
     * - Replaces spaces with underscores "_"
     * - Keeps dashes "-" as-is or converts to underscores
     */
    private function generateColumnVariations($name)
    {
        $variations = [];

        // 1. Original name (as-is from database)
        $variations[] = $name;

        // 2. Lowercase version
        $variations[] = strtolower($name);

        // 3. Laravel Excel ACTUAL behavior - Remove slashes, spaces to underscores
        // "Melahirkan/cuti hamil" → "melahirkancuti_hamil" (slash removed, space to underscore)
        // "Naik Haji/Ziarah Agama" → "naik_hajiziarah_agama" (slash removed, spaces to underscores)
        $laravelExcelActual = str_replace('/', '', $name); // Remove slashes first
        $laravelExcelActual = str_replace(' ', '_', $laravelExcelActual); // Spaces to underscores
        $laravelExcelActual = str_replace('-', '_', $laravelExcelActual); // Dashes to underscores
        $laravelExcelActual = preg_replace('/_+/', '_', strtolower($laravelExcelActual)); // Collapse multiple underscores
        $laravelExcelActual = trim($laravelExcelActual, '_');
        $variations[] = $laravelExcelActual;

        // 4. Laravel Excel Str::slug() behavior (fallback)
        // "Melahirkan/cuti hamil" → "melahirkan-cuti-hamil"
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');
        $variations[] = $slug;

        // 5. Same as slug but with underscores
        // "Melahirkan/cuti hamil" → "melahirkan_cuti_hamil"
        $underscore = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
        $underscore = trim($underscore, '_');
        $variations[] = $underscore;

        // 6. Replace only /, -, and spaces with underscore (our normalize method)
        $normalized = $this->normalizeColumnName($name);
        $variations[] = $normalized;

        // 7. Replace only /, -, and spaces with dash
        $dashNormalized = str_replace([' ', '/', '-'], '-', $name);
        $dashNormalized = preg_replace('/-+/', '-', strtolower($dashNormalized));
        $dashNormalized = trim($dashNormalized, '-');
        $variations[] = $dashNormalized;

        // 8. Remove all special chars, just alphanumeric
        // "Melahirkan/cuti hamil" → "melahirkancutihamil"
        $alphanumeric = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $variations[] = $alphanumeric;

        // 9. Spaces to underscores only, keep other chars
        // "Cuti Panjang - Staff" → "cuti_panjang_-_staff"
        $spaceToUnderscore = str_replace(' ', '_', strtolower($name));
        $variations[] = $spaceToUnderscore;

        // 10. All special chars to single underscore then clean up
        // "Cuti Panjang - Staff" → "cuti_panjang_staff"
        $allToUnderscore = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
        $allToUnderscore = preg_replace('/_+/', '_', strtolower($allToUnderscore));
        $allToUnderscore = trim($allToUnderscore, '_');
        $variations[] = $allToUnderscore;

        // 11. All special chars to single dash then clean up
        // "Cuti Panjang - Staff" → "cuti-panjang-staff"
        $allToDash = preg_replace('/[^a-zA-Z0-9]+/', '-', $name);
        $allToDash = preg_replace('/-+/', '-', strtolower($allToDash));
        $allToDash = trim($allToDash, '-');
        $variations[] = $allToDash;

        // Remove duplicates, empty strings, and return
        $variations = array_filter(array_unique($variations), function ($v) {
            return !empty($v);
        });

        return array_values($variations);
    }

    /**
     * Determine if level is considered staff (same logic as export)
     */
    private function isStaffLevel($levelName)
    {
        if (empty($levelName)) {
            return false;
        }

        $staffLevels = [
            'Director',
            'Manager',
            'Superintendent',
            'Supervisor',
            'Foreman/Officer',
            'Project Manager',
            'SPT',
            'SPV',
            'FM'
        ];

        return in_array($levelName, $staffLevels);
    }

    /**
     * Normalize column name for consistent matching
     * Matches WithHeadingRow behavior
     */
    private function normalizeColumnName($name)
    {
        // Replace spaces, slashes, and dashes with underscore
        $normalized = str_replace([' ', '/', '-'], '_', $name);

        // Replace multiple consecutive underscores with single underscore
        $normalized = preg_replace('/_+/', '_', $normalized);

        // Convert to lowercase
        $normalized = strtolower($normalized);

        // Trim underscores from start and end
        $normalized = trim($normalized, '_');

        return $normalized;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        // Handle Excel date serial number
        if (is_numeric($dateValue)) {
            try {
                $dateTime = Date::excelToDateTimeObject((float) $dateValue);
                return Carbon::instance($dateTime)->startOfDay();
            } catch (\Exception $e) {
                return null;
            }
        }

        // Try standard formats
        try {
            $date = Carbon::parse($dateValue);
            return $date->startOfDay();
        } catch (\Exception $e) {
            // Try specific formats
            $formats = [
                'Y-m-d',
                'd/m/Y',
                'm/d/Y',
                'd-m-Y',
                'm-d-Y',
                'Y/m/d',
                'd.m.Y',
                'Ymd'
            ];

            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, trim($dateValue));
                    if ($date) {
                        return $date->startOfDay();
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}
