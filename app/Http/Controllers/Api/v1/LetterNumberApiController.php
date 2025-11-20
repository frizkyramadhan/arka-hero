<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\LetterNumber;
use App\Models\LetterSubject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LetterNumberApiController extends Controller
{
    /**
     * Request nomor surat baru dari sistem lain
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function requestNumber(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'letter_category_id' => 'required|exists:letter_categories,id',
                'letter_date' => 'required|date',
                'custom_subject' => 'nullable|string|max:200',
                'administration_id' => 'nullable|exists:administrations,id',
                'project_code' => 'nullable|string|max:50',
                'destination' => 'nullable|string|max:200',
                'remarks' => 'nullable|string',
            ]);

            $letterNumber = LetterNumber::createWithRetry([
                'letter_category_id' => $request->letter_category_id,
                'letter_date' => $request->letter_date,
                'custom_subject' => $request->custom_subject,
                'administration_id' => $request->administration_id,
                'project_code' => $request->project_code,
                'destination' => $request->destination,
                'remarks' => $request->remarks,
                'user_id' => auth()->id(),
            ]);

            $letterNumber->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Letter number created successfully',
                'data' => [
                    'id' => $letterNumber->id,
                    'letter_number' => $letterNumber->letter_number,
                    'category_code' => $letterNumber->category->category_code,
                    'sequence_number' => $letterNumber->sequence_number,
                    'year' => $letterNumber->year,
                    'status' => $letterNumber->status,
                    'letter_date' => $letterNumber->letter_date->format('Y-m-d'),
                    'subject' => $letterNumber->custom_subject,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create letter number: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark nomor surat sebagai used
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function markAsUsed(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'document_type' => 'required|string|max:50',
                'document_id' => 'required|integer',
            ]);

            $letterNumber = LetterNumber::findOrFail($id);

            if ($letterNumber->status !== 'reserved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Letter number is not in reserved status',
                    'current_status' => $letterNumber->status
                ], 400);
            }

            $letterNumber->markAsUsed(
                $request->document_type,
                $request->document_id,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Letter number marked as used successfully',
                'data' => [
                    'id' => $letterNumber->id,
                    'letter_number' => $letterNumber->letter_number,
                    'status' => $letterNumber->status,
                    'related_document_type' => $letterNumber->related_document_type,
                    'related_document_id' => $letterNumber->related_document_id,
                    'used_at' => $letterNumber->used_at?->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Letter number not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark letter number as used: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available letter numbers untuk dropdown
     *
     * @param Request $request
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getAvailableNumbers(Request $request, int $categoryId): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            $limit = min($limit, 100); // Maximum 100 records

            $numbers = LetterNumber::with(['category', 'subject'])
                ->where('letter_category_id', $categoryId)
                ->where('status', 'reserved')
                ->orderBy('sequence_number', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'letter_number' => $item->letter_number,
                        'subject' => $item->subject->subject_name ?? $item->custom_subject,
                        'letter_date' => $item->letter_date->format('Y-m-d'),
                        'category_name' => $item->category->category_name ?? null,
                        'sequence_number' => $item->sequence_number,
                        'year' => $item->year,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Available letter numbers retrieved successfully',
                'data' => $numbers,
                'meta' => [
                    'category_id' => $categoryId,
                    'count' => $numbers->count(),
                    'limit' => $limit,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available numbers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel reserved letter number
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancelNumber(int $id): JsonResponse
    {
        try {
            $letterNumber = LetterNumber::findOrFail($id);

            if ($letterNumber->status !== 'reserved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Letter number is not in reserved status',
                    'current_status' => $letterNumber->status
                ], 400);
            }

            $letterNumber->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Letter number cancelled successfully',
                'data' => [
                    'id' => $letterNumber->id,
                    'letter_number' => $letterNumber->letter_number,
                    'status' => $letterNumber->status,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Letter number not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel letter number: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get letter subjects by category
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getSubjectsByCategory(int $categoryId): JsonResponse
    {
        try {
            $subjects = LetterSubject::where('letter_category_id', $categoryId)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'subject_name' => $item->subject_name,
                        'letter_category_id' => $item->letter_category_id,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Letter subjects retrieved successfully',
                'data' => $subjects,
                'meta' => [
                    'category_id' => $categoryId,
                    'count' => $subjects->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve letter subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get letter number details
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getLetterNumber(int $id): JsonResponse
    {
        try {
            $letterNumber = LetterNumber::with([
                'category',
                'subject',
                'administration.employee',
                'administration.project',
                'project',
                'reservedBy',
                'usedBy'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Letter number details retrieved successfully',
                'data' => [
                    'id' => $letterNumber->id,
                    'letter_number' => $letterNumber->letter_number,
                    'category_code' => $letterNumber->category->category_code,
                    'category_name' => $letterNumber->category->category_name ?? null,
                    'sequence_number' => $letterNumber->sequence_number,
                    'year' => $letterNumber->year,
                    'subject' => $letterNumber->subject->subject_name ?? $letterNumber->custom_subject,
                    'letter_date' => $letterNumber->letter_date->format('Y-m-d'),
                    'destination' => $letterNumber->destination,
                    'remarks' => $letterNumber->remarks,
                    'status' => $letterNumber->status,
                    'employee_name' => $letterNumber->employee_name,
                    'nik' => $letterNumber->nik,
                    'project_code' => $letterNumber->administration && $letterNumber->administration->project
                        ? $letterNumber->administration->project->project_code
                        : $letterNumber->project_code,
                    'related_document_type' => $letterNumber->related_document_type,
                    'related_document_id' => $letterNumber->related_document_id,
                    'reserved_by' => $letterNumber->reservedBy->name ?? null,
                    'used_by' => $letterNumber->usedBy->name ?? null,
                    'used_at' => $letterNumber->used_at?->format('Y-m-d H:i:s'),
                    'created_at' => $letterNumber->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $letterNumber->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Letter number not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve letter number details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check letter number availability
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'letter_category_id' => 'required|exists:letter_categories,id',
            ]);

            $categoryId = $request->letter_category_id;
            $currentYear = now()->year;

            $stats = [
                'category_id' => $categoryId,
                'year' => $currentYear,
                'total_numbers' => LetterNumber::where('letter_category_id', $categoryId)
                    ->where('year', $currentYear)
                    ->count(),
                'reserved_numbers' => LetterNumber::where('letter_category_id', $categoryId)
                    ->where('year', $currentYear)
                    ->where('status', 'reserved')
                    ->count(),
                'used_numbers' => LetterNumber::where('letter_category_id', $categoryId)
                    ->where('year', $currentYear)
                    ->where('status', 'used')
                    ->count(),
                'cancelled_numbers' => LetterNumber::where('letter_category_id', $categoryId)
                    ->where('year', $currentYear)
                    ->where('status', 'cancelled')
                    ->count(),
                'next_sequence' => LetterNumber::where('letter_category_id', $categoryId)
                    ->where('year', $currentYear)
                    ->max('sequence_number') + 1,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Letter number availability checked successfully',
                'data' => $stats
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability: ' . $e->getMessage()
            ], 500);
        }
    }
}
