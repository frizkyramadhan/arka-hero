<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Roster;
use App\Models\Project;
use App\Models\Administration;
use App\Models\RosterDailyStatus;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RosterExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $project;
    protected $year;
    protected $month;
    protected $search;
    protected $employees;
    protected $rosterData;

    public function __construct(Project $project, int $year, int $month, string $search = '')
    {
        $this->project = $project;
        $this->year = $year;
        $this->month = $month;
        $this->search = $search;
        $this->employees = $this->getProjectEmployees($project, $search);
        $this->rosterData = $this->getRosterData($this->employees, $year, $month);
    }

    /**
     * Get employees for roster project
     * Returns all active employees from the selected project (matching controller logic)
     */
    private function getProjectEmployees($project, $search = '')
    {
        $query = Administration::with(['employee', 'position.department', 'level', 'roster'])
            ->where('project_id', $project->id)
            ->where('is_active', 1);

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('fullname', 'like', "%{$search}%");
                    })
                    ->orWhereHas('position', function ($positionQuery) use ($search) {
                        $positionQuery->where('position_name', 'like', "%{$search}%")
                            ->orWhereHas('department', function ($departmentQuery) use ($search) {
                                $departmentQuery->where('department_name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query->orderBy('nik')->get();
    }

    /**
     * Get roster data for employees for specific month/year
     * Handles employees with or without roster
     */
    private function getRosterData($employees, $year, $month)
    {
        $rosterData = [];

        foreach ($employees as $admin) {
            $rosterData[$admin->employee_id] = [];
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = Carbon::create($year, $month, $day);

                // If employee has roster, get status from database
                if ($admin->roster) {
                    $dayStatus = RosterDailyStatus::where('roster_id', $admin->roster->id)
                        ->where('date', $currentDate->format('Y-m-d'))
                        ->first();

                    $rosterData[$admin->employee_id][$day] = [
                        'status' => $dayStatus ? $dayStatus->status_code : 'D',
                        'notes' => $dayStatus ? $dayStatus->notes : null,
                        'date' => $currentDate->format('Y-m-d')
                    ];
                } else {
                    // If no roster, default to 'D' (Day Shift)
                    $rosterData[$admin->employee_id][$day] = [
                        'status' => 'D',
                        'notes' => null,
                        'date' => $currentDate->format('Y-m-d')
                    ];
                }
            }
        }

        return $rosterData;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->employees;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
        $headers = ['NO', 'Name', 'NIK', 'Department', 'Position'];

        // Add day columns
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $headers[] = $day;
        }

        return $headers;
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        static $index = 0;
        $index++;

        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
        $mappedRow = [
            $index,
            $row->employee->fullname ?? 'Unknown',
            $row->nik ?? 'Unknown',
            $row->position->department->department_name ?? 'N/A',
            $row->position->position_name ?? 'Unknown'
        ];

        // Add roster status for each day
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayData = $this->rosterData[$row->employee_id][$day] ?? ['status' => 'D'];
            $mappedRow[] = $dayData['status'];
        }

        return $mappedRow;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
        $lastColumn = 5 + $daysInMonth; // NO, Name, NIK, Department, Position + days

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E6E6FA']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            // Data rows styling
            'A2:' . $this->getColumnLetter($lastColumn) . ($this->employees->count() + 1) => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
        $widths = [
            'A' => 8,  // NO
            'B' => 20, // Name (reduced)
            'C' => 10, // NIK (reduced)
            'D' => 15, // Department (reduced)
            'E' => 20  // Position
        ];

        // Set width for day columns
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $column = $this->getColumnLetter(5 + $i);
            $widths[$column] = 8;
        }

        return $widths;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
                $lastColumn = 5 + $daysInMonth;
                $lastRow = $this->employees->count() + 1;

                // Apply conditional formatting for roster statuses
                $this->applyConditionalFormatting($event, $daysInMonth, $lastRow);

                // Apply column formatting and alignment
                $this->applyColumnFormatting($event, $lastRow);

                // Set sheet name to Month Year format
                $this->setSheetName($event);

                // Freeze first row and columns A-E (NO, Name, NIK, Department, Position)
                $event->sheet->freezePane('F2');

                // Add project info as comment
                $event->sheet->getComment('A1')
                    ->getText()
                    ->createTextRun("Project: {$this->project->project_code}\nPeriod: {$this->year}/{$this->month}")
                    ->getFont()
                    ->setSize(10);
            }
        ];
    }

    /**
     * Apply conditional formatting based on roster status codes
     */
    private function applyConditionalFormatting($event, $daysInMonth, $lastRow)
    {
        $statusColors = [
            'D' => 'FFFFFF',   // Day Shift - White
            'N' => 'ADD8E6',   // Night Shift - Light Blue
            'OFF' => 'FFB6C1', // Off Work - Light Pink
            'C' => '90EE90',   // Periodic Leave - Light Green
            'S' => 'FFE4B5',   // Sick Leave - Light Yellow
            'I' => 'E6E6FA',   // Permission - Light Purple
            'A' => 'FF6B6B'    // Absent - Red
        ];

        // Apply direct cell formatting instead of conditional formatting
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $column = $this->getColumnLetter(5 + $day);

            // Apply formatting to each cell based on its value
            for ($row = 2; $row <= $lastRow; $row++) {
                $cellCoordinate = $column . $row;
                $cellValue = $event->sheet->getCell($cellCoordinate)->getValue();

                if (isset($statusColors[$cellValue])) {
                    $event->sheet->getStyle($cellCoordinate)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB($statusColors[$cellValue]);
                }
            }
        }
    }

    /**
     * Apply column formatting, alignment, and width
     */
    private function applyColumnFormatting($event, $lastRow)
    {
        // Set column widths
        $event->sheet->getColumnDimension('A')->setWidth(5);  // NO column
        $event->sheet->getColumnDimension('B')->setWidth(20);  // Name column (reduced)
        $event->sheet->getColumnDimension('C')->setWidth(10);  // NIK column (reduced)
        $event->sheet->getColumnDimension('D')->setWidth(15);  // Department column (reduced)
        $event->sheet->getColumnDimension('E')->setWidth(35);  // Position column (wider)

        // Set alignment for Name column (left aligned)
        $event->sheet->getStyle('B2:B' . $lastRow)
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Set alignment for Department column (left aligned)
        $event->sheet->getStyle('D2:D' . $lastRow)
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Set alignment for Position column (left aligned)
        $event->sheet->getStyle('E2:E' . $lastRow)
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Set alignment for NO and NIK columns (center aligned)
        $event->sheet->getStyle('A2:A' . $lastRow)
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $event->sheet->getStyle('C2:C' . $lastRow)
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set header alignment (center)
        $event->sheet->getStyle('A1:E1')
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply borders to all cells
        $event->sheet->getStyle('A1:' . $this->getColumnLetter(5 + Carbon::create($this->year, $this->month)->daysInMonth) . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    /**
     * Set sheet name to Month Year format
     */
    private function setSheetName($event)
    {
        $monthName = Carbon::create($this->year, $this->month)->locale('id')->isoFormat('MMMM YYYY');
        $event->sheet->setTitle($monthName);
    }

    /**
     * Get column letter from number
     */
    private function getColumnLetter($number)
    {
        $letter = '';
        while ($number > 0) {
            $number--;
            $letter = chr(65 + ($number % 26)) . $letter;
            $number = intval($number / 26);
        }
        return $letter;
    }
}
