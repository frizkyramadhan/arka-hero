<?php

namespace App\Services;

use App\Models\EmployeeMutation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EmployeeMutationLeaveAnchor
{
    public function latestMutation(object $employee): ?EmployeeMutation
    {
        if (method_exists($employee, 'relationLoaded') && $employee->relationLoaded('mutations')) {
            return $this->pickLatest($employee->mutations);
        }

        if (! isset($employee->id)) {
            return null;
        }

        return EmployeeMutation::query()
            ->with('project')
            ->where('employee_id', $employee->id)
            ->where('status', EmployeeMutation::STATUS_ACTIVE)
            ->orderByDesc('mutated_at')
            ->orderByDesc('id')
            ->first();
    }

    public function leaveProject(object $employee, mixed $fallbackProject): mixed
    {
        $mutation = $this->latestMutation($employee);

        if ($mutation === null) {
            return $fallbackProject;
        }

        if (method_exists($mutation, 'loadMissing')) {
            $mutation->loadMissing('project');
        }

        return $mutation->project ?? $fallbackProject;
    }

    public function annualLeaveAnchorDate(object $employee, mixed $serviceStartDoh, mixed $administrationDoh): ?Carbon
    {
        $mutation = $this->latestMutation($employee);

        if ($mutation && $mutation->mutated_at) {
            return Carbon::parse($mutation->mutated_at)->startOfDay();
        }

        if ($serviceStartDoh) {
            return Carbon::parse($serviceStartDoh)->startOfDay();
        }

        if ($administrationDoh) {
            return Carbon::parse($administrationDoh)->startOfDay();
        }

        return null;
    }

    private function pickLatest(mixed $mutations): ?EmployeeMutation
    {
        $collection = $mutations instanceof Collection ? $mutations : collect($mutations);

        return $collection
            ->filter(function ($mutation) {
                $status = $mutation->status ?? EmployeeMutation::STATUS_ACTIVE;

                return $status === EmployeeMutation::STATUS_ACTIVE;
            })
            ->sortByDesc(function ($mutation) {
                $date = Carbon::parse($mutation->mutated_at)->format('Y-m-d');
                $id = (int) ($mutation->id ?? 0);

                return sprintf('%s-%020d', $date, $id);
            })
            ->first();
    }
}
