<?php

namespace App\Exports;

use App\Models\Administration;
use App\Models\Roster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class RosterExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    private $projectId;

    public function __construct($projectId = null)
    {
        $this->projectId = $projectId;
    }

    public function collection()
    {
        // Get all administrations in the project (not just those with rosters)
        $query = Administration::with([
            'employee',
            'position',
            'level',
            'project',
            'roster.rosterDetails' => function ($q) {
                $q->orderBy('cycle_no');
            }
        ])
            ->where('is_active', 1);

        // Filter by project if provided
        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        $administrations = $query->orderBy('nik')->get();

        $data = [];

        foreach ($administrations as $administration) {
            $employee = $administration->employee;
            $level = $administration->level;
            $roster = $administration->roster;

            // Base data for employee
            $baseData = [
                'nik' => $administration->nik ?? '',
                'fullname' => $employee->fullname ?? 'N/A',
                'position' => $administration->position->position_name ?? '',
                'level' => $level->name ?? ''
            ];

            // If no roster or no cycles, create one row with empty cycle data (adjusted_days empty)
            if (!$roster || $roster->rosterDetails->count() === 0) {
                $data[] = array_merge($baseData, [
                    'cycle_no' => '',
                    'work_start' => '',
                    'work_end' => '',
                    'adjusted_days' => '', // Empty for employees without cycles
                    'leave_start' => '',
                    'leave_end' => '',
                    'remarks' => '',
                    'status' => ''
                ]);
            } else {
                // Create one row per cycle
                foreach ($roster->rosterDetails as $detail) {
                    // Get adjusted_days from database - always use integer, including 0
                    $adjustedDays = $detail->adjusted_days;
                    if ($adjustedDays === null || $adjustedDays === '') {
                        $adjustedDays = 0;
                    } else {
                        $adjustedDays = (int)$adjustedDays;
                    }

                    $data[] = array_merge($baseData, [
                        'cycle_no' => (int)$detail->cycle_no, // Ensure cycle_no is integer
                        'work_start' => $detail->work_start->format('Y-m-d'),
                        'work_end' => $detail->work_end->format('Y-m-d'),
                        'adjusted_days' => $adjustedDays,
                        'leave_start' => $detail->leave_start ? $detail->leave_start->format('Y-m-d') : '',
                        'leave_end' => $detail->leave_end ? $detail->leave_end->format('Y-m-d') : '',
                        'remarks' => $detail->remarks ?? '',
                        'status' => $detail->status
                    ]);
                }
            }
        }

        // Sort by NIK, then Cycle No
        return collect($data)->sortBy([
            ['nik', 'asc'],
            ['cycle_no', 'asc']
        ])->values();
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Full Name',
            'Position',
            'Level',
            'Cycle No',
            'Work Start',
            'Work End',
            'Adjusted Days',
            'Leave Start',
            'Leave End',
            'Remarks',
            'Status'
        ];
    }

    public function map($row): array
    {
        // Ensure adjusted_days is always 0 if null or empty, but keep actual 0 values
        $adjustedDays = $row['adjusted_days'] ?? 0;
        if ($adjustedDays === null || $adjustedDays === '') {
            $adjustedDays = 0;
        }
        // Convert to int to ensure it's a number, not string
        $adjustedDays = (int)$adjustedDays;

        // Ensure cycle_no is integer if not empty
        $cycleNo = $row['cycle_no'] ?? '';
        if ($cycleNo !== '' && $cycleNo !== null) {
            $cycleNo = (int)$cycleNo;
        }

        return [
            $row['nik'],
            $row['fullname'],
            $row['position'] ?? '',
            $row['level'] ?? '',
            $cycleNo,
            $row['work_start'] ?? '',
            $row['work_end'] ?? '',
            $adjustedDays,
            $row['leave_start'] ?? '',
            $row['leave_end'] ?? '',
            $row['remarks'] ?? '',
            $row['status'] ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row (12 columns: A-L)
        $headerRange = 'A1:L1';
        $sheet->getStyle($headerRange)->applyFromArray([
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

    /**
     * Format columns
     * Column H = Adjusted Days (format: number, always show 0)
     */
    public function columnFormats(): array
    {
        return [
            'H' => '0', // Adjusted Days - format as integer, always show 0
        ];
    }

    /**
     * Register events
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Iterate through all data rows (starting from row 2, row 1 is header)
                for ($row = 2; $row <= $highestRow; $row++) {
                    // Format Cycle No column (E) as integer
                    $cycleNoCell = $sheet->getCell('E' . $row);
                    $cycleNoValue = $cycleNoCell->getValue();
                    if ($cycleNoValue !== null && $cycleNoValue !== '' && trim($cycleNoValue) !== '') {
                        $cycleNoCell->setValueExplicit((int)$cycleNoValue, DataType::TYPE_NUMERIC);
                    }

                    // Format Adjusted Days column (H)
                    $cell = $sheet->getCell('H' . $row);
                    $value = $cell->getValue();

                    // If cycle_no is empty, leave adjusted_days empty (don't set to 0)
                    if ($cycleNoValue === null || $cycleNoValue === '' || trim($cycleNoValue) === '') {
                        // Employee without cycle - leave adjusted_days empty
                        $cell->setValueExplicit('', DataType::TYPE_STRING);
                    } else {
                        // Employee with cycle - ensure adjusted_days is numeric (including 0)
                        if ($value === null || $value === '' || trim($value) === '') {
                            $cell->setValueExplicit(0, DataType::TYPE_NUMERIC);
                        } else {
                            // Convert to integer and set as numeric type
                            $numericValue = (int)$value;
                            $cell->setValueExplicit($numericValue, DataType::TYPE_NUMERIC);
                        }
                    }
                }
            },
        ];
    }
}
