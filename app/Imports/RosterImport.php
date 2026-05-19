<?php

namespace App\Imports;

use App\Models\Administration;
use App\Models\Roster;
use App\Models\RosterDetail;
use App\Services\RosterCycleDateCalculator;
use App\Support\UserProject;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
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
                        'errors' => $result['errors'],
                    ];
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $this->skippedCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'nik' => $this->getColumnValue($rowArray, 'nik'),
                    'errors' => ['System error: '.$e->getMessage()],
                ];

                Log::error("Roster Import error at row {$rowNumber}: ".$e->getMessage());
            }

            $rowNumber++;
        }

        // Log summary
        Log::info('Roster Import Completed', [
            'total_rows' => $rowNumber - 2,
            'success' => $this->successCount,
            'skipped' => $this->skippedCount,
            'errors' => count($this->errors),
        ]);
    }

    private function processRow($row, $rowNumber)
    {
        $errors = [];

        $nik = $this->getColumnValue($row, 'nik');
        $cycleNo = $this->getColumnValue($row, 'cycle_no');
        $workStart = $this->getColumnValue($row, 'work_start');

        if (empty($nik)) {
            $errors[] = 'NIK is required';
        }

        if (empty($cycleNo)) {
            $errors[] = 'Cycle No is required';
        }

        if (empty($workStart)) {
            $errors[] = 'Work Start is required';
        }

        if (! empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $administration = Administration::with('level')
            ->where('nik', $nik)
            ->where('is_active', 1)
            ->first();

        if (! $administration) {
            return ['success' => false, 'errors' => ['Administration not found for NIK: '.$nik]];
        }

        if (! UserProject::canAccessProjectId((int) $administration->project_id)) {
            return ['success' => false, 'errors' => ['Tidak ada akses ke proyek untuk NIK: '.$nik]];
        }

        if (! $administration->level || ! $administration->level->hasRosterConfig()) {
            return ['success' => false, 'errors' => ['Level does not have roster configuration']];
        }

        try {
            $workStartDate = $this->parseDate($workStart);
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['Invalid Work Start date format: '.$e->getMessage()]];
        }

        $adjustedDaysRaw = $this->getColumnValue($row, 'adjusted_days');
        $adjustedDays = 0;
        if ($adjustedDaysRaw !== null && $adjustedDaysRaw !== '' && $adjustedDaysRaw !== '0') {
            $adjustedDays = (int) $adjustedDaysRaw;
        }

        $workDays = RosterCycleDateCalculator::getWorkDaysFromLevel($administration->level);
        $calculatedDates = RosterCycleDateCalculator::calculate($workStartDate, $workDays, $adjustedDays);

        $workEndDate = $calculatedDates['work_end'];
        $leaveStartDate = $calculatedDates['leave_start'];
        $leaveEndDate = $calculatedDates['leave_end'];

        $remarks = $this->getColumnValue($row, 'remarks') ?? '';

        $roster = $this->getOrCreateRoster($administration);

        $existingDetail = RosterDetail::where('roster_id', $roster->id)
            ->where('cycle_no', $cycleNo)
            ->first();

        $cycleData = [
            'work_start' => $workStartDate,
            'work_end' => $workEndDate,
            'adjusted_days' => $adjustedDays,
            'leave_start' => $leaveStartDate,
            'leave_end' => $leaveEndDate,
            'remarks' => $remarks,
            'status' => 'scheduled',
        ];

        if ($existingDetail) {
            $existingDetail->update($cycleData);
            $existingDetail->updateStatus();
        } else {
            $newDetail = RosterDetail::create(array_merge($cycleData, [
                'roster_id' => $roster->id,
                'cycle_no' => $cycleNo,
            ]));
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

        if (! $roster) {
            $roster = Roster::create([
                'employee_id' => $administration->employee_id,
                'administration_id' => $administration->id,
            ]);
        }

        $this->rostersCache[$cacheKey] = $roster;

        return $roster;
    }

    private function getColumnValue($row, $columnName)
    {
        $variations = [
            strtolower($columnName),
            str_replace('_', ' ', strtolower($columnName)),
            str_replace(' ', '_', strtolower($columnName)),
            ucfirst(str_replace('_', ' ', strtolower($columnName))),
            ucwords(str_replace('_', ' ', strtolower($columnName))),
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
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                $timestamp = ExcelDate::excelToTimestamp($value);

                return Carbon::createFromTimestamp($timestamp);
            } catch (\Exception $e) {
                if (is_numeric($value) && $value > 0 && $value < 2147483647) {
                    return Carbon::createFromTimestamp($value);
                }
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
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
                'Y-m-d H:i',
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
        foreach ($row as $value) {
            if (! empty($value) && (is_string($value) ? trim($value) !== '' : true)) {
                return false;
            }
        }

        return true;
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
