<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdministrationYearsOfServiceCalculator
{
    public function calculateYears(object $administration, Collection $administrations): ?int
    {
        $period = $this->getServicePeriodForAdministration($administration, $administrations);

        if ($period === null) {
            return null;
        }

        return Carbon::parse($period['start_doh'])->diffInYears($period['end_date']);
    }

    /**
     * @return array{start_doh: mixed, end_date: Carbon}|null
     */
    public function getServicePeriodForAdministration(object $administration, Collection $administrations): ?array
    {
        if (! $administration->doh) {
            return null;
        }

        $periodMap = $this->buildServicePeriodMap($administrations);

        return $periodMap[$administration->id] ?? null;
    }

    public function getServiceStartDoh(object $administration, Collection $administrations): ?Carbon
    {
        $period = $this->getServicePeriodForAdministration($administration, $administrations);

        if ($period === null) {
            return null;
        }

        return Carbon::parse($period['start_doh'])->startOfDay();
    }

    /**
     * LSL entitlement window: each cycle spans eligible_after_years, starting when
     * continuous service (from start DOH) reaches eligible_after_years.
     *
     * @return array{start: Carbon, end: Carbon}|null
     */
    public function calculateLSLPeriodDates(
        object $administration,
        Collection $administrations,
        int $eligibleAfterYears,
        ?Carbon $referenceDate = null
    ): ?array {
        if ($eligibleAfterYears <= 0) {
            return null;
        }

        $servicePeriod = $this->getServicePeriodForAdministration($administration, $administrations);

        if ($servicePeriod === null) {
            return null;
        }

        $referenceDate = ($referenceDate ?? Carbon::now())->copy()->startOfDay();
        $startDoh = Carbon::parse($servicePeriod['start_doh'])->startOfDay();
        $firstPeriodStart = $startDoh->copy()->addYears($eligibleAfterYears);

        if ($referenceDate->lt($firstPeriodStart)) {
            return null;
        }

        $periodStart = $firstPeriodStart->copy();
        $periodEnd = $periodStart->copy()->addYears($eligibleAfterYears)->subDay();

        while ($referenceDate->gt($periodEnd)) {
            $periodStart->addYears($eligibleAfterYears);
            $periodEnd = $periodStart->copy()->addYears($eligibleAfterYears)->subDay();
        }

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }

    /**
     * Group administrations into continuous service periods.
     * EOC rehires stay in the same period until a non-EOC termination closes it.
     * All rows in a period share the first DOH and the closing termination date (or today if still open).
     *
     * @return array<int, array{start_doh: mixed, end_date: Carbon}>
     */
    private function buildServicePeriodMap(Collection $administrations): array
    {
        $chronological = $administrations
            ->filter(fn ($admin) => $admin->doh)
            ->sortBy(fn ($admin) => Carbon::parse($admin->doh)->timestamp)
            ->values();

        $periodMap = [];
        $periodStartDoh = null;
        $periodAdminIds = [];

        foreach ($chronological as $administration) {
            if ($periodStartDoh === null) {
                $periodStartDoh = $administration->doh;
            }

            $periodAdminIds[] = $administration->id;

            $closesPeriod = $administration->termination_date
                && $administration->termination_reason
                && ! $this->isEndOfContract($administration->termination_reason);

            if ($closesPeriod) {
                $this->assignPeriodToMap(
                    $periodMap,
                    $periodStartDoh,
                    Carbon::parse($administration->termination_date),
                    $periodAdminIds,
                );

                $periodStartDoh = null;
                $periodAdminIds = [];
            }
        }

        if ($periodStartDoh !== null) {
            $this->assignPeriodToMap(
                $periodMap,
                $periodStartDoh,
                $this->resolveOpenPeriodEndDate($chronological, $periodAdminIds),
                $periodAdminIds,
            );
        }

        return $periodMap;
    }

    /**
     * @param  array<int>  $periodAdminIds
     */
    private function resolveOpenPeriodEndDate(Collection $chronological, array $periodAdminIds): Carbon
    {
        $lastAdministrationId = end($periodAdminIds);
        $lastAdministration = $chronological->first(fn ($admin) => $admin->id === $lastAdministrationId);

        if (! $lastAdministration) {
            return Carbon::now();
        }

        $lastDoh = Carbon::parse($lastAdministration->doh);

        $hasSubsequentAdministration = $chronological->contains(
            fn ($admin) => $admin->doh && Carbon::parse($admin->doh)->gt($lastDoh),
        );

        if (
            ! $hasSubsequentAdministration
            && $lastAdministration->termination_date
            && $this->isEndOfContract($lastAdministration->termination_reason)
        ) {
            return Carbon::parse($lastAdministration->termination_date);
        }

        return Carbon::now();
    }

    /**
     * @param  array<int>  $adminIds
     */
    private function assignPeriodToMap(array &$periodMap, mixed $startDoh, Carbon $endDate, array $adminIds): void
    {
        $period = [
            'start_doh' => $startDoh,
            'end_date' => $endDate,
        ];

        foreach ($adminIds as $adminId) {
            $periodMap[$adminId] = $period;
        }
    }

    private function isEndOfContract(?string $terminationReason): bool
    {
        return strtolower(trim((string) $terminationReason)) === 'end of contract';
    }
}
