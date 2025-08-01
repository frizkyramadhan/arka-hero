<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait BaseImportTrait
{
    /**
     * Delete old data for a specific employee
     * This method should be overridden in each import class
     */
    protected function deleteOldData($employeeId)
    {
        // This method will be overridden in each import class
        // Example: License::where('employee_id', $employeeId)->delete();
    }

    /**
     * Create new data from row
     * This method should be overridden in each import class
     */
    protected function createNewData($row, $employee)
    {
        // This method will be overridden in each import class
        // Example: return License::create($licenseData);
    }

    /**
     * Execute delete-then-create operation with transaction
     */
    protected function executeDeleteThenCreate($row, $employee)
    {
        try {
            // Start database transaction
            DB::beginTransaction();

            // Delete old data for this employee
            $this->deleteOldData($employee->id);

            // Create new data
            $result = $this->createNewData($row, $employee);

            // Commit transaction
            DB::commit();

            return $result;
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Log the error
            Log::error('Import error in ' . get_class($this) . ': ' . $e->getMessage(), [
                'employee_id' => $employee->id,
                'row_data' => $row,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Validate essential fields for import
     */
    protected function validateEssentialFields($row, $requiredFields = [])
    {
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Find employee by name and identity card
     */
    protected function findEmployee($fullName, $identityCard)
    {
        // First check in cached employees
        $employee = $this->employees->where('fullname', $fullName)
            ->where('identity_card', $identityCard)
            ->first();

        // If not found in cache, query database
        if (!$employee) {
            $employee = \App\Models\Employee::where('fullname', $fullName)
                ->where('identity_card', $identityCard)
                ->first();
        }

        return $employee;
    }

    /**
     * Check if employee exists in pending personal rows
     */
    protected function employeeExistsInPending($fullName, $identityCard)
    {
        if (!$this->parent) {
            return false;
        }

        $pendingPersonalRows = $this->parent->getPendingPersonalRows();

        return collect($pendingPersonalRows)->first(function ($pendingRow) use ($fullName, $identityCard) {
            return ($pendingRow['full_name'] ?? null) === $fullName &&
                ($pendingRow['identity_card_no'] ?? null) === $identityCard;
        });
    }
}
