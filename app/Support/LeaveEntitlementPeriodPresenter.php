<?php

namespace App\Support;

use Carbon\Carbon;

class LeaveEntitlementPeriodPresenter
{
    public function __construct(
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly bool $isLsl,
        public readonly int $durationDays,
        public readonly int $durationYears,
        public readonly string $typeLabel,
        public readonly string $typeBadge,
        public readonly string $labelShort,
        public readonly string $labelLong,
        public readonly string $helpText,
        public readonly bool $isActive,
        public readonly bool $isExpired,
        public readonly bool $isExpiringSoon,
    ) {}

    public static function make($start, $end, ?string $category = null): self
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->startOfDay();
        $durationDays = $startDate->diffInDays($endDate) + 1;
        $isLsl = $category === 'lsl' || $durationDays > 400;
        $durationYears = max(1, (int) round($durationDays / 365.25));

        $now = now()->startOfDay();
        $isActive = $now->between($startDate, $endDate);
        $isExpired = $now->gt($endDate);
        $expiringThresholdDays = $isLsl ? 90 : 30;
        $isExpiringSoon = ! $isExpired && $now->copy()->addDays($expiringThresholdDays)->gte($endDate);

        if ($isLsl) {
            $typeLabel = "Cuti Panjang ({$durationYears} Tahun)";
            $helpText = "Saldo cuti panjang berlaku untuk satu siklus {$durationYears} tahun, dihitung dari tanggal mulai kerja (DOH) dan masa kerja karyawan.";
        } else {
            $typeLabel = 'Periode Tahunan (1 Tahun)';
            $helpText = 'Saldo cuti tahunan, cuti berbayar, dan izin lainnya berlaku untuk periode 1 tahun.';
        }

        return new self(
            start: $startDate,
            end: $endDate,
            isLsl: $isLsl,
            durationDays: $durationDays,
            durationYears: $durationYears,
            typeLabel: $typeLabel,
            typeBadge: $isLsl ? 'warning' : 'info',
            labelShort: $startDate->format('Y').' – '.$endDate->format('Y'),
            labelLong: $startDate->format('d M Y').' – '.$endDate->format('d M Y'),
            helpText: $helpText,
            isActive: $isActive,
            isExpired: $isExpired,
            isExpiringSoon: $isExpiringSoon,
        );
    }

    /**
     * @param  iterable<int, mixed>  $entitlements
     */
    public static function categoryFromEntitlements(iterable $entitlements): ?string
    {
        foreach ($entitlements as $entitlement) {
            $category = $entitlement->leaveType->category ?? null;
            if ($category === 'lsl') {
                return 'lsl';
            }
        }

        return null;
    }
}
