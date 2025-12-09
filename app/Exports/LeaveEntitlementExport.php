<?php

namespace App\Exports;

use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\Administration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LeaveEntitlementExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private $leaveTypes;
    private $includeData;

    public function __construct($includeData = true)
    {
        // Get all active leave types ordered by code for consistent column order
        $this->leaveTypes = LeaveType::where('is_active', true)
            ->orderBy('code')
            ->get();
        $this->includeData = $includeData;
    }

    /**
     * Determine if level is considered staff
     */
    private function isStaffLevel($levelName)
    {
        if (empty($levelName)) {
            return false;
        }

        $staffLevels = [
            'Director',
            'Manager',
            'Superintendent',
            'Supervisor',
            'Foreman/Officer',
            'Project Manager',
            'SPT',
            'SPV',
            'FM'
        ];

        return in_array($levelName, $staffLevels);
    }

    /**
     * Get color for leave type based on category
     */
    private function getCategoryColor($category)
    {
        $colors = [
            'annual' => '70AD47',    // Green
            'paid' => 'FFC000',      // Orange/Yellow
            'unpaid' => 'FF6B6B',    // Red
            'lsl' => '5B9BD5',       // Blue
            'periodic' => '70C5E8',  // Cyan/Light Blue
        ];

        return $colors[strtolower($category)] ?? 'D3D3D3'; // Default gray
    }

    public function collection()
    {
        if (!$this->includeData) {
            // Return empty collection for template
            return collect([]);
        }

        // Get all active administrations with employee, project, position, and level
        // Only from active projects
        $administrations = Administration::where('is_active', 1)
            ->whereHas('project', function ($q) {
                $q->where('project_status', 1); // Only active projects
            })
            ->with(['employee', 'project', 'position', 'level'])
            ->get();

        // Get all entitlements grouped by employee and period for quick lookup
        $entitlementsByEmployeePeriod = LeaveEntitlement::with(['leaveType'])
            ->get()
            ->groupBy(function ($entitlement) {
                return $entitlement->employee_id . '_' .
                    $entitlement->period_start->format('Y-m-d') . '_' .
                    $entitlement->period_end->format('Y-m-d');
            });

        $data = [];

        foreach ($administrations as $administration) {
            $employee = $administration->employee;

            // Skip if no employee found
            if (!$employee) {
                continue;
            }

            // Get all entitlements for this employee (grouped by period)
            $employeeEntitlements = $entitlementsByEmployeePeriod->filter(function ($groupedEntitlements, $key) use ($employee) {
                return strpos($key, $employee->id . '_') === 0;
            });

            // If employee has entitlements, create one row per period
            if ($employeeEntitlements->count() > 0) {
                foreach ($employeeEntitlements as $groupKey => $groupedEntitlements) {
                    $firstEntitlement = $groupedEntitlements->first();

                    // Determine staff/non-staff based on level
                    $levelName = $administration->level ? $administration->level->name : '';
                    $staffType = $this->isStaffLevel($levelName) ? 'Staff' : 'Non-Staff';

                    $row = [
                        'employee_id' => $employee->id,
                        'nik' => $administration->nik ?? '',
                        'nama' => $employee->fullname,
                        'position' => $administration->position->position_name ?? '',
                        'staff_type' => $staffType,
                        'project' => $administration->project->project_code ?? '',
                        'doh' => $administration->doh ? $administration->doh->format('Y-m-d') : '',
                        'period_start' => $firstEntitlement->period_start->format('Y-m-d'),
                        'period_end' => $firstEntitlement->period_end->format('Y-m-d'),
                        'deposit_days' => 0,
                    ];

                    // Map entitlements by leave type name (matching heading - use exact name)
                    // Display actual entitlement (remaining_days = entitled_days - taken_days)
                    foreach ($this->leaveTypes as $leaveType) {
                        $entitlement = $groupedEntitlements->firstWhere('leave_type_id', $leaveType->id);

                        if ($entitlement) {
                            // Calculate actual entitlement (remaining days)
                            $remainingDays = $entitlement->entitled_days - $entitlement->taken_days;
                            $row[$leaveType->name] = max(0, $remainingDays); // Ensure non-negative
                        } else {
                            $row[$leaveType->name] = 0;
                        }

                        // Set deposit_days if this is LSL with deposit
                        if ($leaveType->category === 'lsl' && $entitlement && $entitlement->deposit_days > 0) {
                            $row['deposit_days'] = $entitlement->deposit_days;
                        }
                    }

                    $data[] = $row;
                }
            } else {
                // Employee has no entitlements - create row with empty entitlement data
                // Determine staff/non-staff based on level
                $levelName = $administration->level ? $administration->level->name : '';
                $staffType = $this->isStaffLevel($levelName) ? 'Staff' : 'Non-Staff';

                $row = [
                    'employee_id' => $employee->id,
                    'nik' => $administration->nik ?? '',
                    'nama' => $employee->fullname,
                    'position' => $administration->position->position_name ?? '',
                    'staff_type' => $staffType,
                    'project' => $administration->project->project_code ?? '',
                    'doh' => $administration->doh ? $administration->doh->format('Y-m-d') : '',
                    'period_start' => '', // Empty if no entitlement
                    'period_end' => '', // Empty if no entitlement
                    'deposit_days' => 0,
                ];

                // Set all leave types to 0 or empty
                foreach ($this->leaveTypes as $leaveType) {
                    $row[$leaveType->name] = 0;
                }

                $data[] = $row;
            }
        }

        // Sort by Project ASC, then NIK ASC
        return collect($data)
            ->sortBy([
                ['project', 'asc'],
                ['nik', 'asc']
            ])
            ->values();
    }

    public function headings(): array
    {
        $headings = [
            'NIK',
            'Nama',
            'Position',
            'Staff Type',
            'Project',
            'DOH',
            'Start Period',
            'End Period',
        ];

        // Add all leave types as columns (ordered by code)
        foreach ($this->leaveTypes as $leaveType) {
            $headings[] = $leaveType->name;
        }

        // Add deposit days column
        $headings[] = 'Deposit Days';

        return $headings;
    }

    public function map($row): array
    {
        $mapped = [
            $row['nik'],
            $row['nama'],
            $row['position'] ?? '',
            $row['staff_type'] ?? 'Non-Staff',
            $row['project'],
            $row['doh'] ?? '',
            $row['period_start'] ?? '',
            $row['period_end'] ?? '',
        ];

        // Map leave type values (using leave type name as key - exact match with heading)
        foreach ($this->leaveTypes as $leaveType) {
            $key = $leaveType->name; // Use exact name to match heading
            $mapped[] = $row[$key] ?? 0;
        }

        // Add deposit days
        $mapped[] = $row['deposit_days'] ?? 0;

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        // Apply default style to first 8 columns (NIK, Nama, Position, Staff Type, Project, DOH, Start Period, End Period)
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $defaultCol) {
            $sheet->getStyle($defaultCol . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Set individual colors for leave type columns based on category
        // Start from column I (after H = End Period)
        $leaveTypeCol = 'I';
        foreach ($this->leaveTypes as $leaveType) {
            $color = $this->getCategoryColor($leaveType->category);
            $cell = $leaveTypeCol . '1';
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $color]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Move to next column (G -> H -> I, etc.)
            $leaveTypeCol++;
        }

        // Apply default style to Deposit Days column (last column)
        $sheet->getStyle($leaveTypeCol . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        return [];
    }
}
