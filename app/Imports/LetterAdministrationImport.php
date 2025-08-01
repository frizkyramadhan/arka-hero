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
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Validators\Failure;

class LetterAdministrationImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithChunkReading, WithBatchInserts
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

    public function model(array $row)
    {
        $this->rowNumber++;

        if (empty($row['category_code'])) {
            return null; // Skip row if category code is missing
        }

        try {
            $category = $this->letterCategories->where('category_code', $row['category_code'])->first();
            $administration = isset($row['nik']) ? Administration::where('nik', $row['nik'])->first() : null;
            $project = isset($row['project_code']) ? Project::where('project_code', $row['project_code'])->first() : null;
            $subject = null;
            if ($category && !empty($row['subject_master'])) {
                $subject = $this->letterSubjects->where('subject_name', $row['subject_master'])
                    ->where('letter_category_id', $category->id)
                    ->first();
            }

            $letterData = [
                'letter_category_id' => $category?->id,
                'subject_id' => $subject?->id,
                'letter_date' => $row['letter_date'] ? Carbon::parse($row['letter_date'])->format('Y-m-d') : null,
                'destination' => $row['destination'],
                'remarks' => $row['remarks'],
                'custom_subject' => $row['subject_custom'],
                'administration_id' => $administration?->id,
                'project_id' => $project?->id,
                'duration' => $row['duration'],
                'start_date' => $row['start_date'] ? Carbon::parse($row['start_date'])->format('Y-m-d') : null,
                'end_date' => $row['end_date'] ? Carbon::parse($row['end_date'])->format('Y-m-d') : null,
                'classification' => $row['classification'],
                'pkwt_type' => $row['pkwt_type'],
                'par_type' => $row['par_type'],
                'ticket_classification' => $row['ticket_classification'],
                'user_id' => auth()->id(),
                'status' => 'used',
            ];

            if (!empty($row['letter_number'])) {
                // If letter_number is provided, update existing record or create a new one with this number.
                return LetterNumber::updateOrCreate(
                    ['letter_number' => $row['letter_number']],
                    $letterData
                );
            } else {
                // If letter_number is not provided, create a new record.
                // The 'creating' event on the LetterNumber model will generate the number.
                return LetterNumber::createWithRetry($letterData);
            }
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

                $categoryCode = $row['category_code'];

                // Conditional Required Field Validation
                $this->validateConditionalRequiredFields($validator, $rowIndex, $row, $categoryCode);

                // PKWT Date Validation
                if ($categoryCode === 'PKWT' && !empty($row['start_date']) && !empty($row['end_date'])) {
                    try {
                        $startDate = Carbon::parse($row['start_date']);
                        $endDate = Carbon::parse($row['end_date']);

                        if ($endDate->lessThanOrEqualTo($startDate)) {
                            $validator->errors()->add($rowIndex . '.end_date', 'The end date must be after the start date.');
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
        // Employee-related categories (PKWT, CRTE, SKPK) require NIK
        if (in_array($categoryCode, ['PKWT', 'CRTE', 'SKPK'])) {
            if (empty($row['nik'])) {
                $validator->errors()->add($rowIndex . '.nik', 'NIK is required for category ' . $categoryCode . '.');
            }
        }

        // Project-related categories (FPTK) require project
        if ($categoryCode === 'FPTK') {
            if (empty($row['project_code'])) {
                $validator->errors()->add($rowIndex . '.project_code', 'Project Code is required for category FPTK.');
            }
        }

        // External letters (A) require classification
        if ($categoryCode === 'A') {
            if (empty($row['classification'])) {
                $validator->errors()->add($rowIndex . '.classification', 'Classification is required for category A.');
            }
        }

        // PKWT-specific required fields
        if ($categoryCode === 'PKWT') {
            if (empty($row['pkwt_type'])) {
                $validator->errors()->add($rowIndex . '.pkwt_type', 'PKWT Type is required for category PKWT.');
            }
            if (empty($row['duration'])) {
                $validator->errors()->add($rowIndex . '.duration', 'Duration is required for category PKWT.');
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
            // Basic required fields
            'category_code' => 'required|exists:letter_categories,category_code',
            'letter_date' => 'required|date',
            'subject_master' => 'nullable|string',
            'destination' => 'nullable|string',
            'remarks' => 'nullable|string',
            'subject_custom' => 'nullable|string',
            // Employee-related categories (PKWT, CRTE, SKPK) require NIK
            'nik' => 'nullable|exists:administrations,nik',

            // Project-related categories (FPTK) require project
            'project_code' => 'nullable|exists:projects,project_code',

            // External letters (A) require classification
            'classification' => [
                'nullable',
                Rule::in(['Umum', 'Lembaga Pendidikan', 'Pemerintah']),
            ],

            // PKWT-specific fields
            'pkwt_type' => [
                'nullable',
                Rule::in(['PKWT I', 'PKWT II', 'PKWT III']),
            ],
            'duration' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',

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
            // Basic validation messages
            'category_code.required' => 'Category Code is required.',
            'category_code.exists' => 'The selected Category Code is invalid.',
            'letter_date.required' => 'Letter Date is required.',
            'letter_date.date' => 'The Letter Date must be a valid date.',
            'subject_master.string' => 'The Master Subject must be a string.',
            'destination.string' => 'The Destination must be a string.',
            'remarks.string' => 'The Remarks must be a string.',
            'subject_custom.string' => 'The Custom Subject must be a string.',

            // Employee-related validation
            'nik.exists' => 'The selected NIK does not exist in administrations.',

            // Project-related validation
            'project_code.exists' => 'The selected Project Code does not exist.',

            // Classification validation
            'classification.in' => 'The selected Classification is invalid. Valid options: Umum, Lembaga Pendidikan, Pemerintah.',

            // PKWT validation
            'pkwt_type.in' => 'The selected PKWT Type is invalid. Valid options: PKWT I, PKWT II, PKWT III.',
            'duration.string' => 'The Duration must be a string.',
            'start_date.date' => 'The Start Date must be a valid date.',
            'end_date.date' => 'The End Date must be a valid date.',

            // PAR validation
            'par_type.in' => 'The selected PAR Type is invalid. Valid options: new hire, promosi, mutasi, demosi.',

            // Travel request validation
            'ticket_classification.in' => 'The selected Ticket Classification is invalid. Valid options: Pesawat, Kereta Api, Bus.',
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
