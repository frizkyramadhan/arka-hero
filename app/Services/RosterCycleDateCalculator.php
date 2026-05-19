<?php

namespace App\Services;

use App\Models\Level;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class RosterCycleDateCalculator
{
    /**
     * Leave period length in days (matches Add/Edit Cycle modal in roster show UI).
     */
    public const LEAVE_PERIOD_DAYS = 15;

    /**
     * Calculate work_end, leave_start, and leave_end from work_start (same rules as UI modal).
     *
     * @return array{work_end: CarbonInterface, leave_start: CarbonInterface, leave_end: CarbonInterface}
     */
    public static function calculate(
        CarbonInterface $workStart,
        int $workDays,
        int $adjustedDays = 0,
        int $leavePeriodDays = self::LEAVE_PERIOD_DAYS
    ): array {
        $workStart = Carbon::parse($workStart)->startOfDay();
        $workEnd = $workStart->copy()->addDays($workDays + $adjustedDays);
        $leaveStart = $workEnd->copy()->addDay();
        $leaveEnd = $leaveStart->copy()->addDays($leavePeriodDays - 1);

        return [
            'work_end' => $workEnd,
            'leave_start' => $leaveStart,
            'leave_end' => $leaveEnd,
        ];
    }

    public static function getWorkDaysFromLevel(?Level $level): int
    {
        return (int) ($level?->work_days ?? 63);
    }
}
