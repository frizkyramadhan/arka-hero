<?php

namespace App\Services;

use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use Carbon\Carbon;

class LeaveEntitlementCarryOverService
{
    /** @var list<string> */
    private const ANNUAL_CARRY_OVER_LEVELS = ['Manager', 'Director'];

    public function supportsCarryOver(LeaveType $leaveType, ?string $levelName): bool
    {
        if ($leaveType->category === 'lsl') {
            return $leaveType->canCarryOver();
        }

        if ($leaveType->category === 'annual') {
            return $this->isManagerOrDirectorLevel($levelName);
        }

        return false;
    }

    /**
     * @return array{base_days: int, carried_over: int, entitled_days: int}
     */
    public function calculate(
        string $employeeId,
        LeaveType $leaveType,
        Carbon $periodStart,
        ?string $levelName = null
    ): array {
        $previous = $this->findPreviousPeriodEntitlement(
            $employeeId,
            (int) $leaveType->id,
            $periodStart
        );

        return $this->calculateFromPrevious(
            $leaveType,
            $previous,
            $this->supportsCarryOver($leaveType, $levelName)
        );
    }

    /**
     * @return array{base_days: int, carried_over: int, entitled_days: int}
     */
    public function calculateFromPrevious(
        LeaveType $leaveType,
        ?LeaveEntitlement $previous,
        bool $allowCarryOver
    ): array {
        $baseDays = (int) ($leaveType->default_days ?? ($leaveType->category === 'lsl' ? 50 : 12));
        $carriedOver = 0;

        if ($allowCarryOver && $previous !== null) {
            $carriedOver = max(0, $previous->entitled_days - $previous->taken_days);
        }

        return [
            'base_days' => $baseDays,
            'carried_over' => $carriedOver,
            'entitled_days' => $baseDays + $carriedOver,
        ];
    }

    public function findPreviousPeriodEntitlement(
        string $employeeId,
        int $leaveTypeId,
        Carbon $periodStart
    ): ?LeaveEntitlement {
        return LeaveEntitlement::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->whereDate('period_end', $periodStart->copy()->subDay()->toDateString())
            ->first();
    }

    /**
     * @return array{entitled_days: int, deposit_days: int, taken_days: int}
     */
    public function buildCreateAttributes(
        string $employeeId,
        LeaveType $leaveType,
        Carbon $periodStart,
        Carbon $periodEnd,
        int $takenDays = 0,
        ?string $levelName = null
    ): array {
        $entitledDays = (int) ($leaveType->default_days ?? 0);

        if ($this->supportsCarryOver($leaveType, $levelName)) {
            $entitledDays = $this->calculate($employeeId, $leaveType, $periodStart, $levelName)['entitled_days'];
        }

        return [
            'entitled_days' => $entitledDays,
            'deposit_days' => (int) $leaveType->getDepositDays(),
            'taken_days' => $takenDays,
        ];
    }

    /**
     * Recalculate carry-over chain for one employee + leave type (LSL only).
     */
    public function recalculateGroup(string $employeeId, int $leaveTypeId): void
    {
        $leaveType = LeaveType::find($leaveTypeId);

        if (! $leaveType || $leaveType->category !== 'lsl' || ! $leaveType->canCarryOver()) {
            return;
        }

        $previous = null;

        LeaveEntitlement::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->orderBy('period_start')
            ->get()
            ->each(function (LeaveEntitlement $entitlement) use ($leaveType, &$previous) {
                $calculation = $this->calculateFromPrevious($leaveType, $previous, true);

                $entitlement->update([
                    'entitled_days' => $calculation['entitled_days'],
                ]);

                $previous = $entitlement->fresh();
            });
    }

    private function isManagerOrDirectorLevel(?string $levelName): bool
    {
        return in_array($levelName, self::ANNUAL_CARRY_OVER_LEVELS, true);
    }
}
