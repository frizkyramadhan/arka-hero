<?php

namespace App\Exports;

use App\Models\Administration;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LeaveEntitlementExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithMapping
{
    private ?LeaveType $annualLeaveType;

    private ?LeaveType $lslStaffType;

    private ?LeaveType $lslNonStaffType;

    private bool $includeData;

    private ?int $projectId;

    public function __construct($includeData = true, $projectId = null)
    {
        $leaveTypes = LeaveType::where('is_active', true)
            ->where(function ($query) {
                $query->where('name', '!=', 'Cuti Periodik Site')
                    ->where('name', 'NOT LIKE', '%Periodik Site%');
            })
            ->orderBy('code')
            ->get();

        $this->annualLeaveType = $leaveTypes->firstWhere('category', 'annual');
        $this->lslStaffType = $leaveTypes->first(
            fn (LeaveType $type) => str_contains($type->name, 'Staff') && ! str_contains($type->name, 'Non Staff')
        );
        $this->lslNonStaffType = $leaveTypes->first(
            fn (LeaveType $type) => str_contains($type->name, 'Non Staff')
        );
        $this->includeData = $includeData;
        $this->projectId = $projectId;
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function collection()
    {
        if (! $this->includeData) {
            return collect([]);
        }

        $administrationsQuery = Administration::where('is_active', 1)
            ->whereHas('project', fn ($q) => $q->where('project_status', 1));

        if ($this->projectId && $this->projectId !== 'all') {
            $administrationsQuery->where('project_id', $this->projectId);
        }

        $administrations = $administrationsQuery
            ->with(['employee', 'project', 'position', 'level'])
            ->get();

        $employeeIds = $administrations->pluck('employee_id')->filter()->unique();

        $entitlementsByEmployeePeriod = LeaveEntitlement::with(['leaveType'])
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->groupBy(fn (LeaveEntitlement $entitlement) => $entitlement->employee_id.'|'
                .$entitlement->period_start->format('Y-m-d').'|'
                .$entitlement->period_end->format('Y-m-d'));

        $data = [];

        foreach ($administrations as $administration) {
            $employee = $administration->employee;
            if (! $employee) {
                continue;
            }

            $data[] = $this->buildEmployeeRow($administration, $entitlementsByEmployeePeriod);
        }

        return collect($data)->sortBy([
            ['project', 'asc'],
            ['nik', 'asc'],
        ])->values();
    }

    public function map($row): array
    {
        return [
            $row['nama'],
            $row['nik'],
            $row['level'] ?? '',
            $row['position'] ?? '',
            $row['doh'] ?? '',
            $row['project'],
            $row['annual_start'] ?? '',
            $row['annual_end'] ?? '',
            $row['cuti_tahunan'] ?? '',
            $row['lsl_start'] ?? '',
            $row['lsl_end'] ?? '',
            $row['lsl_staff'] ?? '',
            $row['lsl_non_staff'] ?? '',
            $row['deposit_days'] ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->applyHeaderLayout($event->sheet->getDelegate());
            },
        ];
    }

    private function applyHeaderLayout(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $employeeColumns = [
            'A' => 'Nama',
            'B' => 'NIK',
            'C' => 'Level',
            'D' => 'Position',
            'E' => 'DOH',
            'F' => 'Project',
        ];

        foreach ($employeeColumns as $column => $label) {
            $sheet->mergeCells("{$column}1:{$column}2");
            $sheet->setCellValue("{$column}1", $label);
            $sheet->getStyle("{$column}1:{$column}2")->applyFromArray($this->headerStyle('4472C4'));
        }

        $sheet->setCellValue('G1', 'Periode Tahunan');
        $sheet->mergeCells('G1:I1');
        $sheet->setCellValue('J1', 'Periode Cuti Panjang');
        $sheet->mergeCells('J1:M1');
        $sheet->setCellValue('N1', 'Deposit Days');
        $sheet->mergeCells('N1:N2');

        $periodHeaders = [
            'G2' => 'Start Period',
            'H2' => 'End Period',
            'I2' => 'Cuti Tahunan',
            'J2' => 'Start Period',
            'K2' => 'End Period',
            'L2' => 'Cuti Panjang - Staff',
            'M2' => 'Cuti Panjang - Non Staff',
        ];

        foreach ($periodHeaders as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $sheet->getStyle('G1:I1')->applyFromArray($this->headerStyle('70AD47'));
        $sheet->getStyle('J1:M1')->applyFromArray($this->headerStyle('5B9BD5'));
        $sheet->getStyle('N1:N2')->applyFromArray($this->headerStyle('2F5496'));
        $sheet->getStyle('G2:I2')->applyFromArray($this->headerStyle('70AD47'));
        $sheet->getStyle('J2:M2')->applyFromArray($this->headerStyle('5B9BD5'));

        $sheet->getStyle('A1:N2')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ]);

        $sheet->freezePane('A3');
    }

    /**
     * @param  Collection<string, Collection<int, LeaveEntitlement>>  $entitlementsByEmployeePeriod
     * @return array<string, mixed>
     */
    private function buildEmployeeRow(Administration $administration, Collection $entitlementsByEmployeePeriod): array
    {
        $employee = $administration->employee;
        [$annualGroup, $lslGroup] = $this->resolvePeriodGroups($employee->id, $entitlementsByEmployeePeriod);

        $annualEntitlement = $annualGroup && $this->annualLeaveType
            ? $annualGroup->firstWhere('leave_type_id', $this->annualLeaveType->id)
            : null;

        $lslStaffEntitlement = $lslGroup && $this->lslStaffType
            ? $lslGroup->firstWhere('leave_type_id', $this->lslStaffType->id)
            : null;

        $lslNonStaffEntitlement = $lslGroup && $this->lslNonStaffType
            ? $lslGroup->firstWhere('leave_type_id', $this->lslNonStaffType->id)
            : null;

        $lslEntitlementForDeposit = $lslStaffEntitlement ?? $lslNonStaffEntitlement;

        return [
            'nama' => $employee->fullname,
            'nik' => $administration->nik ?? '',
            'level' => $administration->level->name ?? '',
            'position' => $administration->position->position_name ?? '',
            'project' => $administration->project->project_code ?? '',
            'doh' => $administration->doh ? $this->formatDisplayDate($administration->doh) : '',
            'annual_start' => $annualEntitlement ? $this->formatDisplayDate($annualEntitlement->period_start) : '',
            'annual_end' => $annualEntitlement ? $this->formatDisplayDate($annualEntitlement->period_end) : '',
            'cuti_tahunan' => $annualEntitlement ? (int) $annualEntitlement->entitled_days : '',
            'lsl_start' => $lslStaffEntitlement || $lslNonStaffEntitlement
                ? $this->formatDisplayDate(($lslStaffEntitlement ?? $lslNonStaffEntitlement)->period_start)
                : '',
            'lsl_end' => $lslStaffEntitlement || $lslNonStaffEntitlement
                ? $this->formatDisplayDate(($lslStaffEntitlement ?? $lslNonStaffEntitlement)->period_end)
                : '',
            'lsl_staff' => $lslStaffEntitlement ? (int) $lslStaffEntitlement->entitled_days : '',
            'lsl_non_staff' => $lslNonStaffEntitlement ? (int) $lslNonStaffEntitlement->entitled_days : '',
            'deposit_days' => $lslEntitlementForDeposit && $lslEntitlementForDeposit->deposit_days > 0
                ? (int) $lslEntitlementForDeposit->deposit_days
                : '',
        ];
    }

    /**
     * @param  Collection<string, Collection<int, LeaveEntitlement>>  $entitlementsByEmployeePeriod
     * @return array{0: ?Collection, 1: ?Collection}
     */
    private function resolvePeriodGroups(string $employeeId, Collection $entitlementsByEmployeePeriod): array
    {
        $annualGroup = null;
        $lslGroup = null;

        $employeePeriods = $entitlementsByEmployeePeriod->filter(
            fn ($_, $key) => str_starts_with($key, $employeeId.'|')
        );

        foreach ($employeePeriods as $groupedEntitlements) {
            $first = $groupedEntitlements->first();
            if (! $first?->period_start || ! $first?->period_end) {
                continue;
            }

            $isLsl = $groupedEntitlements->contains(
                fn (LeaveEntitlement $entitlement) => ($entitlement->leaveType->category ?? null) === 'lsl'
            );

            if ($isLsl) {
                if ($lslGroup === null || $this->isLaterPeriodGroup($groupedEntitlements, $lslGroup)) {
                    $lslGroup = $groupedEntitlements;
                }
            } elseif ($annualGroup === null || $this->isLaterPeriodGroup($groupedEntitlements, $annualGroup)) {
                $annualGroup = $groupedEntitlements;
            }
        }

        return [$annualGroup, $lslGroup];
    }

    /**
     * @param  Collection<int, LeaveEntitlement>  $candidate
     * @param  Collection<int, LeaveEntitlement>  $current
     */
    private function isLaterPeriodGroup(Collection $candidate, Collection $current): bool
    {
        $candidateFirst = $candidate->first();
        $currentFirst = $current->first();

        if ($candidateFirst->period_start->gt($currentFirst->period_start)) {
            return true;
        }

        if ($candidateFirst->period_start->lt($currentFirst->period_start)) {
            return false;
        }

        return $candidateFirst->period_end->gt($currentFirst->period_end);
    }

    private function formatDisplayDate(mixed $date): string
    {
        return Carbon::parse($date)->format('d M Y');
    }

    private function headerStyle(string $rgb): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $rgb],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
    }
}
