<?php

namespace App\Services;

use App\Models\LetterCategory;
use App\Models\LetterNumber;
use App\Models\RecruitmentRequest;
use Exception;
use Illuminate\Support\Facades\Log;

class RecruitmentLetterNumberService
{
    /**
     * Get or create FPTK letter category
     *
     * @return LetterCategory
     * @throws Exception
     */
    public function getFPTKLetterCategory()
    {
        $fptkCategory = LetterCategory::where('category_code', 'FPTK')
            ->where('is_active', 1)
            ->first();

        if (!$fptkCategory) {
            // Create FPTK category if not exists
            $admin = \App\Models\User::where('email', 'admin@arka.co.id')->first();
            if (!$admin) {
                $admin = \App\Models\User::first();
            }

            $fptkCategory = LetterCategory::create([
                'category_code' => 'FPTK',
                'category_name' => 'Form Permintaan Tenaga Kerja',
                'description' => 'Formulir Permintaan Tenaga Kerja untuk proses recruitment',
                'numbering_behavior' => 'annual_reset',
                'is_active' => 1,
                'user_id' => $admin->id ?? 1,
            ]);

            Log::info('Created FPTK Letter Category', ['category_id' => $fptkCategory->id]);
        }

        return $fptkCategory;
    }

    /**
     * Create letter number for FPTK
     *
     * @param RecruitmentRequest $fptk
     * @return LetterNumber
     * @throws Exception
     */
    public function createLetterNumberForFPTK(RecruitmentRequest $fptk)
    {
        $fptkCategory = $this->getFPTKLetterCategory();

        $letterNumber = new LetterNumber([
            'letter_category_id' => $fptkCategory->id,
            'letter_date' => now(),
            'subject_id' => null, // FPTK tidak memerlukan subject
            'administration_id' => null, // FPTK tidak terkait dengan employee
            'project_id' => $fptk->project_id, // Use project_id for sequence grouping
            'project_code' => $fptk->project?->project_code, // Use project_code from relationship
            'user_id' => $fptk->requested_by,
            'is_active' => 1,
        ]);

        $letterNumber->save();

        Log::info('Created letter number for FPTK', [
            'fptk_id' => $fptk->id,
            'letter_number' => $letterNumber->letter_number,
            'sequence' => $letterNumber->sequence_number,
        ]);

        return $letterNumber;
    }

    /**
     * Assign letter number to FPTK
     *
     * @param RecruitmentRequest $fptk
     * @return bool
     */
    public function assignLetterNumberToFPTK(RecruitmentRequest $fptk)
    {
        try {
            // Check if already has letter number
            if ($fptk->hasLetterNumber()) {
                return true;
            }

            // Create and assign letter number
            $letterNumber = $this->createLetterNumberForFPTK($fptk);

            $success = $fptk->assignLetterNumber($letterNumber->id);

            if ($success) {
                Log::info('Successfully assigned letter number to FPTK', [
                    'fptk_id' => $fptk->id,
                    'letter_number' => $letterNumber->letter_number,
                ]);
            } else {
                Log::error('Failed to assign letter number to FPTK', [
                    'fptk_id' => $fptk->id,
                    'letter_number_id' => $letterNumber->id,
                ]);
            }

            return $success;
        } catch (Exception $e) {
            Log::error('Error assigning letter number to FPTK', [
                'fptk_id' => $fptk->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get letter number statistics for FPTK
     *
     * @param int|null $year
     * @return array
     */
    public function getFPTKLetterNumberStats($year = null)
    {
        $year = $year ?? date('Y');
        $fptkCategory = $this->getFPTKLetterCategory();

        $stats = [
            'category' => $fptkCategory->category_name,
            'year' => $year,
            'total_issued' => 0,
            'total_used' => 0,
            'total_reserved' => 0,
            'last_sequence' => 0,
            'next_sequence' => 1,
        ];

        $letterNumbers = LetterNumber::where('letter_category_id', $fptkCategory->id)
            ->where('year', $year)
            ->get();

        $stats['total_issued'] = $letterNumbers->count();
        $stats['total_used'] = $letterNumbers->where('status', 'used')->count();
        $stats['total_reserved'] = $letterNumbers->where('status', 'reserved')->count();
        $stats['last_sequence'] = $letterNumbers->max('sequence_number') ?? 0;
        $stats['next_sequence'] = $stats['last_sequence'] + 1;

        return $stats;
    }

    /**
     * Get FPTK list with letter number information
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFPTKWithLetterNumbers($filters = [])
    {
        $query = RecruitmentRequest::with(['letterNumber.category', 'department', 'position']);

        // Apply filters
        if (!empty($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['has_letter_number'])) {
            if ($filters['has_letter_number'] === 'yes') {
                $query->whereNotNull('letter_number_id');
            } else {
                $query->whereNull('letter_number_id');
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Generate report for FPTK letter numbers
     *
     * @param int $year
     * @return array
     */
    public function generateFPTKLetterNumberReport($year)
    {
        $fptkCategory = $this->getFPTKLetterCategory();

        $fptks = RecruitmentRequest::with(['letterNumber', 'department', 'position'])
            ->whereYear('created_at', $year)
            ->get();

        $report = [
            'year' => $year,
            'category_info' => [
                'code' => $fptkCategory->category_code,
                'name' => $fptkCategory->category_name,
                'numbering_behavior' => $fptkCategory->numbering_behavior,
            ],
            'summary' => [
                'total_fptk' => $fptks->count(),
                'with_letter_number' => $fptks->whereNotNull('letter_number_id')->count(),
                'without_letter_number' => $fptks->whereNull('letter_number_id')->count(),
            ],
            'by_status' => [
                'draft' => $fptks->where('status', 'draft')->count(),
                'submitted' => $fptks->where('status', 'submitted')->count(),
                'approved' => $fptks->where('status', 'approved')->count(),
                'rejected' => $fptks->where('status', 'rejected')->count(),
            ],
            'by_department' => $fptks->groupBy('department.name')->map(function ($items) {
                return [
                    'total' => $items->count(),
                    'with_letter_number' => $items->whereNotNull('letter_number_id')->count(),
                ];
            }),
            'monthly_distribution' => $fptks->groupBy(function ($item) {
                return $item->created_at->format('Y-m');
            })->map(function ($items) {
                return [
                    'total' => $items->count(),
                    'with_letter_number' => $items->whereNotNull('letter_number_id')->count(),
                ];
            }),
        ];

        return $report;
    }

    /**
     * Validate letter number format for FPTK
     *
     * @param string $letterNumber
     * @return bool
     */
    public function validateFPTKLetterNumber($letterNumber)
    {
        // Format: FPTK####
        return preg_match('/^FPTK\d{4}$/', $letterNumber);
    }

    /**
     * Release letter number from FPTK (for cancellation)
     *
     * @param RecruitmentRequest $fptk
     * @return bool
     */
    public function releaseLetterNumberFromFPTK(RecruitmentRequest $fptk)
    {
        try {
            $success = $fptk->releaseLetterNumber();

            if ($success) {
                Log::info('Successfully released letter number from FPTK', [
                    'fptk_id' => $fptk->id,
                    'letter_number' => $fptk->letter_number,
                ]);
            }

            return $success;
        } catch (Exception $e) {
            Log::error('Error releasing letter number from FPTK', [
                'fptk_id' => $fptk->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Bulk assign letter numbers to FPTKs without letter numbers
     *
     * @param array $fptkIds
     * @return array
     */
    public function bulkAssignLetterNumbers(array $fptkIds)
    {
        $results = [
            'success' => [],
            'failed' => [],
            'already_has' => [],
        ];

        foreach ($fptkIds as $fptkId) {
            $fptk = RecruitmentRequest::find($fptkId);

            if (!$fptk) {
                $results['failed'][] = ['id' => $fptkId, 'reason' => 'FPTK not found'];
                continue;
            }

            if ($fptk->hasLetterNumber()) {
                $results['already_has'][] = [
                    'id' => $fptkId,
                    'letter_number' => $fptk->letter_number,
                ];
                continue;
            }

            $success = $this->assignLetterNumberToFPTK($fptk);

            if ($success) {
                $results['success'][] = [
                    'id' => $fptkId,
                    'letter_number' => $fptk->fresh()->letter_number,
                ];
            } else {
                $results['failed'][] = ['id' => $fptkId, 'reason' => 'Assignment failed'];
            }
        }

        return $results;
    }
}
