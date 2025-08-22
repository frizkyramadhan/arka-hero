<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Position;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class PositionImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading,
    WithEvents
{
    use Importable, SkipsErrors, SkipsFailures;

    private $departments;
    private $sheetName;
    private $rowNumber = 0;

    public function __construct()
    {
        $this->departments = Department::select('id', 'department_name')->get();
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->sheetName = $event->getSheet()->getTitle();
            }
        ];
    }

    public function getSheetName()
    {
        return $this->sheetName;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
    {
        $this->rowNumber++;

        // Skip empty rows
        if (empty($row['id']) && empty($row['position_name'])) {
            return null;
        }

        try {
            $department = null;
            if (isset($row['department_name']) && $row['department_name']) {
                $department = $this->departments->where('department_name', $row['department_name'])->first();
            }

            // Convert status text to boolean
            $positionStatus = 1; // default to active
            if (isset($row['position_status'])) {
                $statusText = strtolower(trim($row['position_status']));
                if (in_array($statusText, ['inactive', '0', 'false', 'no'])) {
                    $positionStatus = 0;
                }
            }

            $positionData = [
                'position_name' => $row['position_name'] ?? null,
                'department_id' => $department ? $department->id : null,
                'position_status' => $positionStatus,
            ];

            // Use updateOrCreate with ID as key
            if (!empty($row['id'])) {
                $position = Position::updateOrCreate(
                    ['id' => $row['id']], // The attribute(s) to search by
                    $positionData // The attributes to update or create with
                );
            } else {
                // If no ID provided, create new position
                $position = Position::create($positionData);
            }

            return $position;
        } catch (\Illuminate\Database\QueryException $e) {
            $attribute = 'system_error';
            $errorMessage = 'Database error: ' . $e->getMessage();

            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $attribute = 'position_name';
                $errorMessage = "Duplicate Position Name '{$row['position_name']}' found.";
            }

            $this->onFailure(new Failure(
                $this->rowNumber,
                $attribute,
                [$errorMessage],
                $row
            ));

            return null;
        } catch (\Exception $e) {
            $this->onFailure(new Failure(
                $this->rowNumber,
                'system_error',
                ['Error: ' . $e->getMessage()],
                $row
            ));

            return null;
        }
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'position_name' => ['required', 'string', 'max:255'],
            'department_name' => ['required', 'exists:departments,department_name'],
            'position_status' => ['nullable', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'position_name.required' => 'Position Name is required',
            'position_name.max' => 'Position Name cannot exceed 255 characters',
            'department_name.required' => 'Department Name is required',
            'department_name.exists' => 'Selected Department does not exist in our database',
        ];
    }

    public function getRowNumber()
    {
        return $this->rowNumber;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
