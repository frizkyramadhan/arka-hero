<?php

namespace App\Imports;

use App\Models\Roster;
use App\Models\RosterDetail;
use App\Models\Administration;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class RosterImport implements ToCollection, WithHeadingRow
{
    private $errors = [];
    private $successCount = 0;
    private $skippedCount = 0;
    private $rostersCache = [];

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

                Log::error("Roster Import error at row {$rowNumber}: " . $e->getMessage());
            }

            $rowNumber++;
        }

        // Log summary
        Log::info('Roster Import Completed', [
            'total_rows' => $rowNumber - 2,
            'success' => $this->successCount,
            'skipped' => $this->skippedCount,
            'errors' => count($this->errors)
        ]);
    }

    private function processRow($row, $rowNumber)
    {
        $errors = [];

        // Get required fields (support both old and new format)
        $nik = $this->getColumnValue($row, 'nik');
        $cycleNo = $this->getColumnValue($row, 'cycle_no');
        $workStart = $this->getColumnValue($row, 'work_start');
        $workEnd = $this->getColumnValue($row, 'work_end');

        // Optional fields (for backward compatibility, Position, Level, Pattern are informational only)
        $position = $this->getColumnValue($row, 'position');
        $level = $this->getColumnValue($row, 'level');
        $pattern = $this->getColumnValue($row, 'pattern');

        // Validate required fields
        if (empty($nik)) {
            $errors[] = 'NIK is required';
        }

        if (empty($cycleNo)) {
            $errors[] = 'Cycle No is required';
        }

        if (empty($workStart)) {
            $errors[] = 'Work Start is required';
        }

        if (empty($workEnd)) {
            $errors[] = 'Work End is required';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Find administration by NIK
        $administration = Administration::where('nik', $nik)
            ->where('is_active', 1)
            ->first();

        if (!$administration) {
            return ['success' => false, 'errors' => ['Administration not found for NIK: ' . $nik]];
        }

        // Check if level has roster config
        if (!$administration->level || !$administration->level->hasRosterConfig()) {
            return ['success' => false, 'errors' => ['Level does not have roster configuration']];
        }

        // Get or create roster
        $roster = $this->getOrCreateRoster($administration);

        // Validate dates - handle various formats including Excel serial dates
        try {
            $workStartDate = $this->parseDate($workStart);
            $workEndDate = $this->parseDate($workEnd);
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['Invalid date format: ' . $e->getMessage()]];
        }

        if ($workEndDate->lt($workStartDate)) {
            return ['success' => false, 'errors' => ['Work End must be after Work Start']];
        }

        // Parse optional dates
        $leaveStartDate = null;
        $leaveEndDate = null;
        $leaveStart = $this->getColumnValue($row, 'leave_start');
        $leaveEnd = $this->getColumnValue($row, 'leave_end');

        if (!empty($leaveStart)) {
            try {
                $leaveStartDate = $this->parseDate($leaveStart);
            } catch (\Exception $e) {
                return ['success' => false, 'errors' => ['Invalid Leave Start date format']];
            }
        }

        if (!empty($leaveEnd)) {
            try {
                $leaveEndDate = $this->parseDate($leaveEnd);
            } catch (\Exception $e) {
                return ['success' => false, 'errors' => ['Invalid Leave End date format']];
            }
        }

        if ($leaveStartDate && $leaveEndDate && $leaveEndDate->lt($leaveStartDate)) {
            return ['success' => false, 'errors' => ['Leave End must be after Leave Start']];
        }

        if ($leaveStartDate && $workEndDate && $leaveStartDate->lt($workEndDate)) {
            return ['success' => false, 'errors' => ['Leave Start must be after or equal to Work End']];
        }

        // Get adjusted days - ensure it's always 0 if null or empty
        $adjustedDaysRaw = $this->getColumnValue($row, 'adjusted_days');
        $adjustedDays = 0;
        if ($adjustedDaysRaw !== null && $adjustedDaysRaw !== '' && $adjustedDaysRaw !== '0') {
            $adjustedDays = (int)$adjustedDaysRaw;
        }
        $remarks = $this->getColumnValue($row, 'remarks') ?? '';
        $status = $this->getColumnValue($row, 'status') ?? 'scheduled';

        // Validate status
        $validStatuses = ['scheduled', 'active', 'on_leave', 'completed'];
        if (!in_array($status, $validStatuses)) {
            $status = 'scheduled';
        }

        // Check if cycle already exists
        $existingDetail = RosterDetail::where('roster_id', $roster->id)
            ->where('cycle_no', $cycleNo)
            ->first();

        if ($existingDetail) {
            // Update existing cycle
            $existingDetail->update([
                'work_start' => $workStartDate,
                'work_end' => $workEndDate,
                'adjusted_days' => $adjustedDays,
                'leave_start' => $leaveStartDate,
                'leave_end' => $leaveEndDate,
                'remarks' => $remarks,
                'status' => $status
            ]);

            // Auto-update status based on dates
            $existingDetail->updateStatus();
        } else {
            // Create new cycle
            $newDetail = RosterDetail::create([
                'roster_id' => $roster->id,
                'cycle_no' => $cycleNo,
                'work_start' => $workStartDate,
                'work_end' => $workEndDate,
                'adjusted_days' => $adjustedDays,
                'leave_start' => $leaveStartDate,
                'leave_end' => $leaveEndDate,
                'remarks' => $remarks,
                'status' => $status
            ]);

            // Auto-update status based on dates
            $newDetail->updateStatus();
        }

        return ['success' => true];
    }

    private function getOrCreateRoster($administration)
    {
        $cacheKey = $administration->id;

        if (isset($this->rostersCache[$cacheKey])) {
            return $this->rostersCache[$cacheKey];
        }

        $roster = Roster::where('administration_id', $administration->id)
            ->where('employee_id', $administration->employee_id)
            ->first();

        if (!$roster) {
            $roster = Roster::create([
                'employee_id' => $administration->employee_id,
                'administration_id' => $administration->id
            ]);
        }

        $this->rostersCache[$cacheKey] = $roster;
        return $roster;
    }

    private function getColumnValue($row, $columnName)
    {
        // Try different column name variations
        $variations = [
            strtolower($columnName),
            str_replace('_', ' ', strtolower($columnName)),
            str_replace(' ', '_', strtolower($columnName)),
            ucfirst(str_replace('_', ' ', strtolower($columnName))),
            ucwords(str_replace('_', ' ', strtolower($columnName)))
        ];

        foreach ($variations as $variation) {
            if (isset($row[$variation])) {
                $value = $row[$variation];
                return is_string($value) ? trim($value) : $value;
            }
        }

        return null;
    }

    /**
     * Parse date from various formats including Excel serial dates
     * Supports: Y-m-d, d/m/Y, d-m-Y, d.m.Y, Excel serial number, etc.
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // Handle Excel serial date number (numeric value)
        if (is_numeric($value)) {
            try {
                $timestamp = ExcelDate::excelToTimestamp($value);
                return Carbon::createFromTimestamp($timestamp);
            } catch (\Exception $e) {
                // If not Excel date, try as regular number (Unix timestamp)
                if (is_numeric($value) && $value > 0 && $value < 2147483647) {
                    return Carbon::createFromTimestamp($value);
                }
            }
        }

        // Try Carbon parse (handles most date formats)
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            // Try common date formats manually
            $formats = [
                'Y-m-d',
                'd/m/Y',
                'd-m-Y',
                'd.m.Y',
                'Y/m/d',
                'm/d/Y',
                'd M Y',
                'd F Y',
                'Y-m-d H:i:s',
                'Y-m-d H:i'
            ];

            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $value);
                    if ($date) {
                        return $date;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            throw new \Exception("Unable to parse date: {$value}");
        }
    }

    private function isEmptyRow($row)
    {
        $requiredFields = ['nik', 'cycle_no', 'work_start', 'work_end'];
        $hasAnyValue = false;

        foreach ($row as $key => $value) {
            if (!empty($value) && trim($value) !== '') {
                $hasAnyValue = true;
                break;
            }
        }

        return !$hasAnyValue;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
