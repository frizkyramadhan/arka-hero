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
    private $projectId;

    public function __construct($includeData = true, $projectId = null)
    {
        // Get all active leave types ordered by code for consistent column order
        // Exclude "Cuti Periodik Site" from export
        $this->leaveTypes = LeaveType::where('is_active', true)
            ->where(function ($query) {
                $query->where('name', '!=', 'Cuti Periodik Site')
                    ->where('name', 'NOT LIKE', '%Periodik Site%');
            })
            ->orderBy('code')
            ->get();
        $this->includeData = $includeData;
        $this->projectId = $projectId;
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
        $administrationsQuery = Administration::where('is_active', 1)
            ->whereHas('project', function ($q) {
                $q->where('project_status', 1); // Only active projects
            });

        // Filter by project if specified
        if ($this->projectId && $this->projectId !== 'all') {
            $administrationsQuery->where('project_id', $this->projectId);
        }

        $administrations = $administrationsQuery->with(['employee', 'project', 'position', 'level'])
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

            // If employee has entitlements, get only the latest period (most recent period_end)
            if ($employeeEntitlements->count() > 0) {
                // Find the latest period by comparing period_end dates
                $latestPeriod = null;
                $latestPeriodEnd = null;

                foreach ($employeeEntitlements as $groupKey => $groupedEntitlements) {
                    $firstEntitlement = $groupedEntitlements->first();
                    
                    // Ensure period_end exists and is valid
                    if (!$firstEntitlement || !$firstEntitlement->period_end) {
                        continue;
                    }
                    
                    $periodEnd = $firstEntitlement->period_end;

                    // If this is the first period or this period_end is more recent, update latest
                    if ($latestPeriodEnd === null || $periodEnd->gt($latestPeriodEnd)) {
                        $latestPeriodEnd = $periodEnd;
                        $latestPeriod = $groupedEntitlements;
                    }
                }

                // Process only the latest period
                if ($latestPeriod) {
                    $firstEntitlement = $latestPeriod->first();
                    
                    // Ensure we have valid period dates
                    if (!$firstEntitlement || !$firstEntitlement->period_start || !$firstEntitlement->period_end) {
                        continue;
                    }

                    $row = [
                        'employee_id' => $employee->id,
                        'nik' => $administration->nik ?? '',
                        'nama' => $employee->fullname,
                        'position' => $administration->position->position_name ?? '',
                        'project' => $administration->project->project_code ?? '',
                        'doh' => $administration->doh ? $administration->doh->format('d-m-Y') : '',
                        'period_start' => $firstEntitlement->period_start->format('d-m-Y'),
                        'period_end' => $firstEntitlement->period_end->format('d-m-Y'),
                        'deposit_days' => 0,
                    ];

                    // Map entitlements by leave type name (matching heading - use exact name)
                    // Export entitled_days (source of truth) - remaining_days is calculated automatically
                    foreach ($this->leaveTypes as $leaveType) {
                        $entitlement = $latestPeriod->firstWhere('leave_type_id', $leaveType->id);

                        if ($entitlement) {
                            // Export entitled_days (the actual entitlement value stored in database)
                            $row[$leaveType->name] = $entitlement->entitled_days;
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
                $row = [
                    'employee_id' => $employee->id,
                    'nik' => $administration->nik ?? '',
                    'nama' => $employee->fullname,
                    'position' => $administration->position->position_name ?? '',
                    'project' => $administration->project->project_code ?? '',
                    'doh' => $administration->doh ? $administration->doh->format('d-m-Y') : '',
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
            'Nama',
            'NIK',
            'Position',
            'DOH',
            'Project',
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
            $row['nama'],
            $row['nik'],
            $row['position'] ?? '',
            $row['doh'] ?? '',
            $row['project'],
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
        // Apply default style to first 7 columns (Nama, NIK, Position, DOH, Project, Start Period, End Period)
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $defaultCol) {
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
        // Start from column H (after G = End Period)
        $leaveTypeCol = 'H';
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

            // Move to next column
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
