<?php

namespace App\Services;

use App\Models\Administration;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Services\LeaveEntitlementCarryOverService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveEntitlementLSLPeriodNormalizer
{
    public function __construct(
        private readonly AdministrationYearsOfServiceCalculator $yearsOfServiceCalculator = new AdministrationYearsOfServiceCalculator,
        private readonly LeaveEntitlementCarryOverService $carryOverService = new LeaveEntitlementCarryOverService
    ) {}

    /**
     * @return array{updated: int, merged: int, skipped: int, failed: int}
     */
    public function normalize(): array
    {
        $stats = [
            'updated' => 0,
            'merged' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        $lslLeaveTypeIds = LeaveType::query()
            ->where('category', 'lsl')
            ->pluck('id');

        if ($lslLeaveTypeIds->isEmpty()) {
            return $stats;
        }

        $entitlements = LeaveEntitlement::query()
            ->with([
                'employee.administrations',
                'leaveType',
            ])
            ->whereIn('leave_type_id', $lslLeaveTypeIds)
            ->orderBy('employee_id')
            ->orderBy('leave_type_id')
            ->orderBy('period_start')
            ->get();

        $grouped = $entitlements->groupBy(
            fn (LeaveEntitlement $entitlement) => $entitlement->employee_id.'|'.$entitlement->leave_type_id
        );

        DB::transaction(function () use ($grouped, &$stats) {
            foreach ($grouped as $groupEntitlements) {
                $this->normalizeGroup($groupEntitlements, $stats);
            }
        });

        return $stats;
    }

    /**
     * @param  array{updated: int, merged: int, skipped: int, failed: int}  $stats
     */
    private function normalizeGroup(Collection $groupEntitlements, array &$stats): void
    {
        /** @var LeaveEntitlement $firstEntitlement */
        $firstEntitlement = $groupEntitlements->first();
        $employee = $firstEntitlement->employee;
        $leaveType = $firstEntitlement->leaveType;

        if (! $employee || ! $leaveType) {
            $stats['failed'] += $groupEntitlements->count();

            return;
        }

        $administration = $this->resolveAdministration($employee->administrations);

        if ($administration === null) {
            Log::warning('LSL period normalization skipped: no administration with DOH', [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
            ]);
            $stats['failed'] += $groupEntitlements->count();

            return;
        }

        $eligibleAfterYears = (int) $leaveType->eligible_after_years;

        if ($eligibleAfterYears <= 0) {
            $stats['failed'] += $groupEntitlements->count();

            return;
        }

        /** @var array<string, array{start: string, end: string, entitlement_ids: array<int>, taken_days: int, entitled_days: int, deposit_days: int}> $buckets */
        $buckets = [];

        foreach ($groupEntitlements as $entitlement) {
            $targetPeriod = $this->resolveTargetPeriod(
                $entitlement,
                $administration,
                $employee->administrations,
                $eligibleAfterYears
            );

            if ($targetPeriod === null) {
                Log::warning('LSL period normalization skipped: unable to resolve target period', [
                    'entitlement_id' => $entitlement->id,
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'period_start' => $entitlement->period_start?->toDateString(),
                    'period_end' => $entitlement->period_end?->toDateString(),
                ]);
                $stats['failed']++;

                continue;
            }

            $targetKey = $targetPeriod['start'].'|'.$targetPeriod['end'];

            if (! isset($buckets[$targetKey])) {
                $buckets[$targetKey] = [
                    'start' => $targetPeriod['start'],
                    'end' => $targetPeriod['end'],
                    'entitlement_ids' => [],
                    'taken_days' => 0,
                    'entitled_days' => (int) ($leaveType->default_days ?? 50),
                    'deposit_days' => (int) ($leaveType->deposit_days_first ?? 0),
                ];
            }

            $buckets[$targetKey]['entitlement_ids'][] = $entitlement->id;
            $buckets[$targetKey]['taken_days'] += (int) $entitlement->taken_days;
            $buckets[$targetKey]['entitled_days'] = max(
                $buckets[$targetKey]['entitled_days'],
                (int) $entitlement->entitled_days
            );
        }

        foreach ($buckets as $bucket) {
            $this->applyBucket($bucket, $stats);
        }

        if ($leaveType->canCarryOver()) {
            $this->carryOverService->recalculateGroup($employee->id, $leaveType->id);
        }
    }

    /**
     * @return array{start: string, end: string}|null
     */
    private function resolveTargetPeriod(
        LeaveEntitlement $entitlement,
        Administration $administration,
        Collection $administrations,
        int $eligibleAfterYears
    ): ?array {
        $periodStart = Carbon::parse($entitlement->period_start)->startOfDay();
        $periodEnd = Carbon::parse($entitlement->period_end)->startOfDay();
        $referenceDate = $periodStart->copy()->addDays((int) floor($periodStart->diffInDays($periodEnd) / 2));

        $targetPeriod = $this->yearsOfServiceCalculator->calculateLSLPeriodDates(
            $administration,
            $administrations,
            $eligibleAfterYears,
            $referenceDate
        );

        if ($targetPeriod === null) {
            return null;
        }

        return [
            'start' => $targetPeriod['start']->toDateString(),
            'end' => $targetPeriod['end']->toDateString(),
        ];
    }

    /**
     * @param  array{start: string, end: string, entitlement_ids: array<int>, taken_days: int, entitled_days: int, deposit_days: int}  $bucket
     * @param  array{updated: int, merged: int, skipped: int, failed: int}  $stats
     */
    private function applyBucket(array $bucket, array &$stats): void
    {
        $records = LeaveEntitlement::query()
            ->whereIn('id', $bucket['entitlement_ids'])
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        $targetStart = $bucket['start'];
        $targetEnd = $bucket['end'];

        $keeper = $records->first(
            fn (LeaveEntitlement $record) => $record->period_start->toDateString() === $targetStart
                && $record->period_end->toDateString() === $targetEnd
        ) ?? $records->sortBy('id')->first();

        $alreadyNormalized = $records->count() === 1
            && $keeper->period_start->toDateString() === $targetStart
            && $keeper->period_end->toDateString() === $targetEnd
            && (int) $keeper->taken_days === $bucket['taken_days'];

        if ($alreadyNormalized) {
            $stats['skipped']++;

            return;
        }

        $deletedCount = LeaveEntitlement::query()
            ->whereIn('id', $bucket['entitlement_ids'])
            ->where('id', '!=', $keeper->id)
            ->delete();

        $keeper->update([
            'period_start' => $targetStart,
            'period_end' => $targetEnd,
            'taken_days' => $bucket['taken_days'],
            'entitled_days' => $bucket['entitled_days'],
            'deposit_days' => $bucket['deposit_days'],
        ]);

        if ($deletedCount > 0) {
            $stats['merged'] += $deletedCount;
        }

        $stats['updated']++;
    }

    private function resolveAdministration(Collection $administrations): ?Administration
    {
        $activeWithDoh = $administrations
            ->where('is_active', 1)
            ->first(fn (Administration $administration) => $administration->doh !== null);

        if ($activeWithDoh) {
            return $activeWithDoh;
        }

        return $administrations
            ->sortBy(fn (Administration $administration) => $administration->doh ?? '9999-12-31')
            ->first(fn (Administration $administration) => $administration->doh !== null);
    }
}
