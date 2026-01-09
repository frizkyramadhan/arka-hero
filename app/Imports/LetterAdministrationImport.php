<?php

namespace App\Imports;

use App\Models\Administration;
use App\Models\LetterCategory;
use App\Models\LetterNumber;
use App\Models\Project;
use App\Models\LetterSubject;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class LetterAdministrationImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, SkipsEmptyRows
{
    use Importable, SkipsFailures, SkipsErrors;

    private $rowNumber = 0;
    private $letterCategories;
    private $letterSubjects;

    public function __construct()
    {
        $this->letterCategories = LetterCategory::select('id', 'category_code')->get();
        $this->letterSubjects = LetterSubject::select('id', 'subject_name', 'letter_category_id')->get();
    }

    /**
     * Parse date from various formats (Excel serial, string formats, etc.)
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // If already a Carbon instance
            if ($value instanceof Carbon) {
                return $value->format('Y-m-d');
            }

            // If it's a numeric value (Excel date serial number)
            if (is_numeric($value)) {
                // Check if it's a valid Excel date serial
                if ($value > 0 && $value < 2958466) { // Excel date range
                    $date = ExcelDate::excelToDateTimeObject($value);
                    return Carbon::instance($date)->format('Y-m-d');
                }
            }

            // Try to parse as string with various formats
            $formats = [
                'Y-m-d',           // 2024-01-09
                'd/m/Y',           // 09/01/2024
                'd-m-Y',           // 09-01-2024
                'd F Y',           // 09 January 2024
                'd M Y',           // 09 Jan 2024
                'd/m/y',           // 09/01/24
                'd-m-y',           // 09-01-24
                'm/d/Y',           // 01/09/2024 (US format)
                'm-d-Y',           // 01-09-2024 (US format)
                'Y/m/d',           // 2024/01/09
                'd.m.Y',           // 09.01.2024
                'Y.m.d',           // 2024.01.09
            ];

            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $value);
                    if ($date && $date->year > 1900 && $date->year < 2100) {
                        return $date->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Last resort: let Carbon try to parse it automatically
            $date = Carbon::parse($value);
            if ($date && $date->year > 1900 && $date->year < 2100) {
                return $date->format('Y-m-d');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        $this->rowNumber++;

        if (empty($row['category_code'])) {
            return null; // Skip row if category code is missing
        }

        try {
            $category = $this->letterCategories->where('category_code', $row['category_code'])->first();
            // NIK lookup - if not found, administration_id will be null (allowing import without NIK)
            $administration = null;
            if (!empty($row['nik'])) {
                $administration = Administration::where('nik', $row['nik'])->first();
            }
            // Lookup project_id from project_code or use project_id directly
            $project = null;
            if (!empty($row['project_code'])) {
                $project = Project::where('project_code', $row['project_code'])->first();
            } elseif (!empty($row['project_id'])) {
                $project = Project::find($row['project_id']);
            }
            $projectId = $project?->id;
            $projectCode = $row['project_code'] ?? $project?->project_code;
            $subject = null;
            if ($category && !empty($row['subject_master'])) {
                $subject = $this->letterSubjects->where('subject_name', $row['subject_master'])
                    ->where('letter_category_id', $category->id)
                    ->first();
            }

            $letterData = [
                'letter_category_id' => $category?->id,
                'subject_id' => $subject?->id,
                'letter_date' => $this->parseDate($row['letter_date']),
                'destination' => $row['destination'],
                'remarks' => $row['remarks'],
                'custom_subject' => $row['subject_custom'],
                'administration_id' => $administration?->id,
                'project_id' => $projectId,
                'project_code' => $projectCode,
                'duration' => $row['duration'],
                'start_date' => $this->parseDate($row['start_date']),
                'end_date' => $this->parseDate($row['end_date']),
                'classification' => $row['classification'],
                'pkwt_type' => $row['pkwt_type'],
                'par_type' => $row['par_type'],
                'ticket_classification' => $row['ticket_classification'],
                'user_id' => auth()->id(),
            ];

            // Handle year: use from import if provided, otherwise derive from letter_date
            if (!empty($row['year'])) {
                $year = $row['year'];
            } else {
                $year = isset($letterData['letter_date']) ? date('Y', strtotime($letterData['letter_date'])) : date('Y');
            }
            $letterData['year'] = $year;

            // Use sequence_number from import if provided, otherwise auto-generate
            if (!empty($row['sequence_number'])) {
                $letterData['sequence_number'] = (int) $row['sequence_number'];
            } else {
                $letterData['sequence_number'] = LetterNumber::getNextSequenceNumberSafe($category->id, $year, $projectId);
            }

            // Use status from import if provided, otherwise default to 'used'
            if (!empty($row['status'])) {
                $letterData['status'] = $row['status'];
            } else {
                $letterData['status'] = 'used';
            }

            // Use createOrUpdate method for better handling
            if (!empty($row['id'])) {
                $letterData['id'] = $row['id'];
            }

            if (!empty($row['letter_number'])) {
                $letterData['letter_number'] = $row['letter_number'];
            }

            // Set default values
            $letterData['reserved_by'] = auth()->id() ?? 1;

            // Use createOrUpdate method
            return LetterNumber::createOrUpdate($letterData);
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $rows = $validator->getData();

            foreach ($rows as $rowIndex => $row) {
                if (empty($row['category_code'])) {
                    continue;
                }

                // Validate ID if provided
                if (!empty($row['id'])) {
                    $existingRecord = LetterNumber::find($row['id']);
                    if (!$existingRecord) {
                        $validator->errors()->add($rowIndex . '.id', 'The ID does not exist in the system.');
                    }
                }

                // Custom date validation using parseDate method
                if (!empty($row['letter_date'])) {
                    $parsedDate = $this->parseDate($row['letter_date']);
                    if ($parsedDate === null) {
                        $validator->errors()->add($rowIndex . '.letter_date', 'The Letter Date must be a valid date. Excel serial numbers, Y-m-d, d/m/Y, d-m-Y formats are supported.');
                    }
                }

                if (!empty($row['start_date'])) {
                    $parsedDate = $this->parseDate($row['start_date']);
                    if ($parsedDate === null) {
                        $validator->errors()->add($rowIndex . '.start_date', 'The Start Date must be a valid date.');
                    }
                }

                if (!empty($row['end_date'])) {
                    $parsedDate = $this->parseDate($row['end_date']);
                    if ($parsedDate === null) {
                        $validator->errors()->add($rowIndex . '.end_date', 'The End Date must be a valid date.');
                    }
                }

                $categoryCode = $row['category_code'];

                // Conditional Required Field Validation
                $this->validateConditionalRequiredFields($validator, $rowIndex, $row, $categoryCode);

                // PKWT Date Validation
                if ($categoryCode === 'PKWT' && !empty($row['start_date']) && !empty($row['end_date'])) {
                    try {
                        $startDate = $this->parseDate($row['start_date']);
                        $endDate = $this->parseDate($row['end_date']);

                        if ($startDate && $endDate) {
                            $startCarbon = Carbon::parse($startDate);
                            $endCarbon = Carbon::parse($endDate);

                            if ($endCarbon->lessThanOrEqualTo($startCarbon)) {
                                $validator->errors()->add($rowIndex . '.end_date', 'The end date must be after the start date.');
                            }
                        }
                    } catch (\Exception $e) {
                        // Let the main date validation rule handle parsing errors.
                    }
                }

                // Subject Master Validation
                if (!empty($row['subject_master'])) {
                    $category = $this->letterCategories->where('category_code', $categoryCode)->first();
                    if ($category) {
                        $subjectExists = $this->letterSubjects
                            ->where('letter_category_id', $category->id)
                            ->where('subject_name', $row['subject_master'])
                            ->isNotEmpty();

                        if (!$subjectExists) {
                            $validSubjectsCollection = $this->letterSubjects
                                ->where('letter_category_id', $category->id)
                                ->pluck('subject_name');

                            $errorMessage = "The subject '{$row['subject_master']}' is not valid for category '{$categoryCode}'.";

                            if ($validSubjectsCollection->isNotEmpty()) {
                                $subjectsList = $validSubjectsCollection->map(function ($subject) {
                                    return '• ' . $subject;
                                })->implode("\n");
                                $errorMessage .= " Valid options are:\n" . $subjectsList;
                            } else {
                                $errorMessage .= " This category has no master subjects defined.";
                            }

                            $validator->errors()->add(
                                $rowIndex . '.subject_master',
                                $errorMessage
                            );
                        }
                    }
                }
            }
        });
    }

    private function validateConditionalRequiredFields($validator, $rowIndex, $row, $categoryCode)
    {
        // NIK validation removed for all categories - allowing import even when NIK not yet in administrations table

        // Removed FPTK project_code required validation

        // External letters (A) require classification
        if ($categoryCode === 'A') {
            if (empty($row['classification'])) {
                $validator->errors()->add($rowIndex . '.classification', 'Classification is required for category A.');
            }
        }

        // PKWT-specific required fields - Removed duration requirement
        if ($categoryCode === 'PKWT') {
            if (empty($row['pkwt_type'])) {
                $validator->errors()->add($rowIndex . '.pkwt_type', 'PKWT Type is required for category PKWT.');
            }
            if (empty($row['start_date'])) {
                $validator->errors()->add($rowIndex . '.start_date', 'Start Date is required for category PKWT.');
            }
            if (empty($row['end_date'])) {
                $validator->errors()->add($rowIndex . '.end_date', 'End Date is required for category PKWT.');
            }
        }

        // PAR-specific required fields
        if ($categoryCode === 'PAR') {
            if (empty($row['par_type'])) {
                $validator->errors()->add($rowIndex . '.par_type', 'PAR Type is required for category PAR.');
            }
        }

        // Travel request (FR) specific required fields
        if ($categoryCode === 'FR') {
            if (empty($row['ticket_classification'])) {
                $validator->errors()->add($rowIndex . '.ticket_classification', 'Ticket Classification is required for category FR.');
            }
        }
    }

    public function rules(): array
    {
        return [
            // ID field for update existing records
            'id' => 'nullable|exists:letter_numbers,id',
            // Year field - can be imported or derived from letter_date
            'year' => 'nullable|integer',
            // Basic required fields
            'category_code' => 'required|exists:letter_categories,category_code',
            'letter_date' => 'required', // Will be validated with custom parseDate method
            'subject_master' => 'nullable|string',
            'destination' => 'nullable|string',
            'remarks' => 'nullable|string',
            'subject_custom' => 'nullable|string',
            // NIK field - no longer required for any categories, allowing import without NIK validation
            'nik' => 'nullable',
            // Sequence number field - can be imported or auto-generated
            'sequence_number' => 'nullable|integer|min:1',
            // Status field - can be imported or default to 'used'
            'status' => 'nullable|in:reserved,used,cancelled',

            // Project-related categories - project_code as free text string
            'project_code' => 'nullable|string|max:50',
            'project_id' => 'nullable|exists:projects,id',

            // External letters (A) require classification
            'classification' => [
                'nullable',
                Rule::in(['Umum', 'Lembaga Pendidikan', 'Pemerintah']),
            ],

            // PKWT-specific fields
            'pkwt_type' => [
                'nullable',
                Rule::in(['PKWT', 'PKWTT']),
            ],
            'duration' => 'nullable',
            'start_date' => 'nullable', // Will be validated with custom parseDate method
            'end_date' => 'nullable', // Will be validated with custom parseDate method

            // PAR-specific fields
            'par_type' => [
                'nullable',
                Rule::in(['new hire', 'promosi', 'mutasi', 'demosi']),
            ],

            // Travel request (FR) specific fields
            'ticket_classification' => [
                'nullable',
                Rule::in(['Pesawat', 'Kereta Api', 'Bus']),
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            // ID validation messages
            'id.exists' => 'The ID does not exist in the system.',

            // Year validation messages
            'year.integer' => 'The Year must be an integer.',

            // Basic validation messages
            'category_code.required' => 'Category Code is required.',
            'category_code.exists' => 'The selected Category Code is invalid.',
            'letter_date.required' => 'Letter Date is required.',
            'subject_master.string' => 'The Master Subject must be a string.',
            'destination.string' => 'The Destination must be a string.',
            'remarks.string' => 'The Remarks must be a string.',
            'subject_custom.string' => 'The Custom Subject must be a string.',

            // Employee-related validation
            // NIK validation removed - allowing import even when NIK not yet in administrations table

            // Sequence number validation
            'sequence_number.integer' => 'The Sequence Number must be an integer.',
            'sequence_number.min' => 'The Sequence Number must be at least 1.',

            // Status validation
            'status.in' => 'The Status must be one of: reserved, used, cancelled.',

            // Project-related validation
            'project_code.string' => 'The Project Code must be a string.',
            'project_code.max' => 'The Project Code may not be greater than 50 characters.',

            // Classification validation
            'classification.in' => 'The selected Classification is invalid. Valid options: Umum, Lembaga Pendidikan, Pemerintah.',

            // PKWT validation
            'pkwt_type.in' => 'The selected PKWT Type is invalid. Valid options: PKWT, PKWTT.',

            // PAR validation
            'par_type.in' => 'The selected PAR Type is invalid. Valid options: new hire, promosi, mutasi, demosi.',

            // Travel request validation
            'ticket_classification.in' => 'The selected Ticket Classification is invalid. Valid options: Pesawat, Kereta Api, Bus.',
        ];
    }
}
