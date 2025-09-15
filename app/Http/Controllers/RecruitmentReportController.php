<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\RecruitmentCvReview;
use App\Models\RecruitmentInterview;
use App\Models\RecruitmentPsikotes;
use App\Models\RecruitmentTesTeori;
use App\Models\RecruitmentMcu;
use App\Models\RecruitmentOffering;
use App\Models\RecruitmentHiring;
use App\Models\RecruitmentOnboarding;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentSession;
use App\Models\Department;
use App\Models\Position;
use App\Models\Project;

class RecruitmentReportController extends Controller
{
    public function index()
    {
        return view('recruitment.reports.index', [
            'title' => 'Recruitment Reports',
            'subtitle' => 'HR Analytics & Reports',
        ]);
    }

    public function funnel(Request $request)
    {
        $date1 = $request->get('date1');
        $date2 = $request->get('date2');
        $department = $request->get('department');
        $position = $request->get('position');
        $project = $request->get('project');

        $data = $this->buildFunnelData($date1, $date2, $department, $position, $project);

        return view('recruitment.reports.funnel', [
            'title' => 'Recruitment Reports',
            'subtitle' => 'Funnel by Stage',
            'date1' => $date1,
            'date2' => $date2,
            'department' => $department,
            'position' => $position,
            'project' => $project,
            'rows' => $data,
        ]);
    }

    public function exportFunnel(Request $request)
    {
        $date1 = $request->get('date1');
        $date2 = $request->get('date2');
        $department = $request->get('department');
        $position = $request->get('position');
        $project = $request->get('project');
        $rows = collect($this->buildFunnelData($date1, $date2, $department, $position, $project));

        return Excel::download(new class($rows) implements FromCollection, WithHeadings, WithMapping {
            private $rows;
            public function __construct($rows)
            {
                $this->rows = $rows;
            }
            public function collection()
            {
                return $this->rows;
            }
            public function headings(): array
            {
                return [
                    'Stage',
                    'Total Candidates',
                    'Previous Stage Count',
                    'Conversion Rate (%)',
                    'Avg Days in Stage',
                    'Date From',
                    'Date To',
                ];
            }
            public function map($row): array
            {
                return [
                    $row['stage'],
                    $row['total_candidates'],
                    $row['previous_stage_count'],
                    $row['conversion_rate'],
                    $row['avg_days_in_stage'],
                    $row['date1'] ?: '-',
                    $row['date2'] ?: '-',
                ];
            }
        }, 'recruitment_funnel_' . date('YmdHis') . '.xlsx');
    }

    private function buildFunnelData(?string $date1, ?string $date2, ?string $department, ?string $position, ?string $project): array
    {
        // Get recruitment sessions with filters
        $sessionsQuery = \App\Models\RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'candidate'
        ]);

        // Apply FPTK-based filters
        if (!empty($department) || !empty($position) || !empty($project)) {
            $sessionsQuery->whereHas('fptk', function ($q) use ($department, $position, $project) {
                if (!empty($department)) {
                    $q->where('department_id', $department);
                }
                if (!empty($position)) {
                    $q->where('position_id', $position);
                }
                if (!empty($project)) {
                    $q->where('project_id', $project);
                }
            });
        }

        // Apply date filter
        if (!empty($date1) && !empty($date2)) {
            $sessionsQuery->whereBetween('created_at', [$date1, $date2]);
        }

        $sessions = $sessionsQuery->get();

        // Separate sessions by employment type
        $sessionsRegular = $sessions->filter(function ($session) {
            return $session->fptk && !in_array($session->fptk->employment_type, ['magang', 'harian']);
        });

        $sessionsMagangHarian = $sessions->filter(function ($session) {
            return $session->fptk && in_array($session->fptk->employment_type, ['magang', 'harian']);
        });

        // Separate sessions by theory test requirement
        $sessionsWithTheory = $sessions->filter(function ($session) {
            return $session->fptk && $session->fptk->requires_theory_test;
        });

        $sessionsWithoutTheory = $sessions->filter(function ($session) {
            return $session->fptk && !$session->fptk->requires_theory_test;
        });

        // Define stage progression models
        $stageModels = [
            'CV Review' => RecruitmentCvReview::class,
            'Psikotes' => RecruitmentPsikotes::class,
            'Tes Teori' => RecruitmentTesTeori::class,
            'Interview' => RecruitmentInterview::class,
            'Offering' => RecruitmentOffering::class,
            'MCU' => RecruitmentMcu::class,
            'Hiring' => RecruitmentHiring::class,
            'Onboarding' => RecruitmentOnboarding::class,
        ];

        $rows = [];

        // Build standard stages (CV Review, Psikotes) - REGULAR ONLY
        $previousStageCount = 0;

        foreach (['CV Review', 'Psikotes'] as $stageName) {
            $modelClass = $stageModels[$stageName];
            $stageQuery = $modelClass::whereIn('session_id', $sessionsRegular->pluck('id'));

            if (!empty($date1) && !empty($date2)) {
                $stageQuery->whereBetween('created_at', [$date1, $date2]);
            }

            $stageRecords = $stageQuery->get();
            $totalCandidates = $stageRecords->count();

            // Calculate conversion rate
            $conversionRate = 0;
            if ($stageName === 'CV Review') {
                $conversionRate = $totalCandidates > 0 ? 100 : 0;
            } elseif ($previousStageCount > 0) {
                $conversionRate = round(($totalCandidates / $previousStageCount) * 100, 2);
            }

            // Calculate average days in stage
            $avgDaysInStage = $this->calculateAvgDays($stageRecords, $totalCandidates);

            $rows[] = [
                'stage' => $stageName,
                'total_candidates' => $totalCandidates,
                'previous_stage_count' => $previousStageCount,
                'conversion_rate' => $conversionRate,
                'avg_days_in_stage' => $avgDaysInStage,
                'flow_type' => 'standard',
                'date1' => $date1,
                'date2' => $date2,
            ];

            $previousStageCount = $totalCandidates;
        }

        // Handle Tes Teori stage (conditional) - REGULAR ONLY
        $sessionsRegularWithTheory = $sessionsRegular->filter(function ($session) {
            return $session->fptk && $session->fptk->requires_theory_test;
        });

        $tesTeoriQuery = $stageModels['Tes Teori']::whereIn('session_id', $sessionsRegularWithTheory->pluck('id'));
        if (!empty($date1) && !empty($date2)) {
            $tesTeoriQuery->whereBetween('created_at', [$date1, $date2]);
        }
        $tesTeoriRecords = $tesTeoriQuery->get();
        $tesTeoriCandidates = $tesTeoriRecords->count();

        // Calculate Psikotes candidates for positions requiring theory test - REGULAR ONLY
        $psikotesWithTheoryQuery = $stageModels['Psikotes']::whereIn('session_id', $sessionsRegularWithTheory->pluck('id'));
        if (!empty($date1) && !empty($date2)) {
            $psikotesWithTheoryQuery->whereBetween('created_at', [$date1, $date2]);
        }
        $psikotesWithTheoryCandidates = $psikotesWithTheoryQuery->count();

        $tesTeoriConversionRate = $psikotesWithTheoryCandidates > 0 ? round(($tesTeoriCandidates / $psikotesWithTheoryCandidates) * 100, 2) : 0;
        $tesTeoriAvgDays = $this->calculateAvgDays($tesTeoriRecords, $tesTeoriCandidates);

        $rows[] = [
            'stage' => 'Tes Teori',
            'stage_display' => 'Tes Teori (Technical Positions Only)',
            'total_candidates' => $tesTeoriCandidates,
            'previous_stage_count' => $psikotesWithTheoryCandidates,
            'conversion_rate' => $tesTeoriConversionRate,
            'avg_days_in_stage' => $tesTeoriAvgDays,
            'flow_type' => 'technical_only',
            'eligible_candidates' => $psikotesWithTheoryCandidates,
            'date1' => $date1,
            'date2' => $date2,
        ];

        // Handle Interview stage - REGULAR ONLY
        $interviewQuery = $stageModels['Interview']::whereIn('session_id', $sessionsRegular->pluck('id'));
        if (!empty($date1) && !empty($date2)) {
            $interviewQuery->whereBetween('created_at', [$date1, $date2]);
        }
        $interviewRecords = $interviewQuery->get();
        $interviewCandidates = $interviewRecords->pluck('session_id')->unique()->count();

        // Calculate previous stage count for interview - REGULAR ONLY
        $interviewPreviousCount = $tesTeoriCandidates; // From technical positions
        $sessionsRegularWithoutTheory = $sessionsRegular->filter(function ($session) {
            return $session->fptk && !$session->fptk->requires_theory_test;
        });
        $psikotesWithoutTheoryQuery = $stageModels['Psikotes']::whereIn('session_id', $sessionsRegularWithoutTheory->pluck('id'));
        if (!empty($date1) && !empty($date2)) {
            $psikotesWithoutTheoryQuery->whereBetween('created_at', [$date1, $date2]);
        }
        $psikotesWithoutTheoryCandidates = $psikotesWithoutTheoryQuery->count();
        $interviewPreviousCount += $psikotesWithoutTheoryCandidates; // From non-technical positions

        $interviewConversionRate = $interviewPreviousCount > 0 ? round(($interviewCandidates / $interviewPreviousCount) * 100, 2) : 0;
        $interviewAvgDays = $this->calculateAvgDays($interviewRecords, $interviewCandidates);

        $rows[] = [
            'stage' => 'Interview',
            'stage_display' => 'Interview (Combined Flows)',
            'total_candidates' => $interviewCandidates,
            'previous_stage_count' => $interviewPreviousCount,
            'conversion_rate' => $interviewConversionRate,
            'avg_days_in_stage' => $interviewAvgDays,
            'flow_type' => 'combined',
            'from_technical' => $tesTeoriCandidates,
            'from_non_technical' => $psikotesWithoutTheoryCandidates,
            'date1' => $date1,
            'date2' => $date2,
        ];

        // Continue with remaining stages (Offering, MCU, Hiring) - REGULAR ONLY
        $previousStageCount = $interviewCandidates;

        foreach (['Offering', 'MCU', 'Hiring'] as $stageName) {
            $modelClass = $stageModels[$stageName];
            $stageQuery = $modelClass::whereIn('session_id', $sessionsRegular->pluck('id'));

            if (!empty($date1) && !empty($date2)) {
                $stageQuery->whereBetween('created_at', [$date1, $date2]);
            }

            $stageRecords = $stageQuery->get();
            $totalCandidates = $stageRecords->count();

            $conversionRate = $previousStageCount > 0 ? round(($totalCandidates / $previousStageCount) * 100, 2) : 0;
            $avgDaysInStage = $this->calculateAvgDays($stageRecords, $totalCandidates);

            $rows[] = [
                'stage' => $stageName,
                'total_candidates' => $totalCandidates,
                'previous_stage_count' => $previousStageCount,
                'conversion_rate' => $conversionRate,
                'avg_days_in_stage' => $avgDaysInStage,
                'flow_type' => 'standard',
                'date1' => $date1,
                'date2' => $date2,
            ];

            $previousStageCount = $totalCandidates;
        }

        // Add Magang/Harian specific flow data
        if ($sessionsMagangHarian->count() > 0) {
            $rows[] = [
                'stage' => 'Employment Type Breakdown',
                'stage_display' => '--- Magang/Harian Flow (MCU → Hiring) ---',
                'total_candidates' => 0,
                'previous_stage_count' => 0,
                'conversion_rate' => 0,
                'avg_days_in_stage' => 0,
                'flow_type' => 'separator',
                'date1' => $date1,
                'date2' => $date2,
            ];

            // Magang/Harian MCU (first stage - no CV Review)
            $magangMcuQuery = $stageModels['MCU']::whereIn('session_id', $sessionsMagangHarian->pluck('id'));
            if (!empty($date1) && !empty($date2)) {
                $magangMcuQuery->whereBetween('created_at', [$date1, $date2]);
            }
            $magangMcuRecords = $magangMcuQuery->get();
            $magangMcuCount = $magangMcuRecords->count();

            $rows[] = [
                'stage' => 'MCU (Magang/Harian)',
                'total_candidates' => $magangMcuCount,
                'previous_stage_count' => 0,
                'conversion_rate' => $magangMcuCount > 0 ? 100 : 0,
                'avg_days_in_stage' => $this->calculateAvgDays($magangMcuRecords, $magangMcuCount),
                'flow_type' => 'magang_harian',
                'date1' => $date1,
                'date2' => $date2,
            ];

            // Magang/Harian Hiring (final stage)
            $magangHiringQuery = $stageModels['Hiring']::whereIn('session_id', $sessionsMagangHarian->pluck('id'));
            if (!empty($date1) && !empty($date2)) {
                $magangHiringQuery->whereBetween('created_at', [$date1, $date2]);
            }
            $magangHiringRecords = $magangHiringQuery->get();
            $magangHiringCount = $magangHiringRecords->count();

            $rows[] = [
                'stage' => 'Hiring (Magang/Harian)',
                'total_candidates' => $magangHiringCount,
                'previous_stage_count' => $magangMcuCount,
                'conversion_rate' => $magangMcuCount > 0 ? round(($magangHiringCount / $magangMcuCount) * 100, 2) : 0,
                'avg_days_in_stage' => $this->calculateAvgDays($magangHiringRecords, $magangHiringCount),
                'flow_type' => 'magang_harian',
                'date1' => $date1,
                'date2' => $date2,
            ];
        }

        return $rows;
    }

    private function calculateAvgDays($stageRecords, $totalCandidates): float
    {
        if ($totalCandidates <= 0) return 0;

        $totalDays = $stageRecords->sum(function ($record) {
            if ($record->updated_at && $record->created_at) {
                return $record->updated_at->diffInDays($record->created_at);
            } elseif ($record->created_at) {
                return now()->diffInDays($record->created_at);
            }
            return 1;
        });

        return $totalDays > 0 ? round($totalDays / $totalCandidates, 1) : 1;
    }

    public function stageDetail(Request $request, $stage)
    {
        $date1 = $request->get('date1');
        $date2 = $request->get('date2');
        $department = $request->get('department');
        $position = $request->get('position');
        $project = $request->get('project');

        $data = $this->buildStageDetailData($stage, $date1, $date2, $department, $position, $project);

        return view('recruitment.reports.stage-detail', [
            'title' => 'Recruitment Reports',
            'subtitle' => 'Stage Detail: ' . ucwords(str_replace('_', ' ', $stage)),
            'stage' => $stage,
            'date1' => $date1,
            'date2' => $date2,
            'department' => $department,
            'position' => $position,
            'project' => $project,
            'rows' => $data,
        ]);
    }

    private function buildStageDetailData($stage, ?string $date1, ?string $date2, ?string $department, ?string $position, ?string $project): array
    {
        // Map stage names to model classes - FIXED ORDER
        $stageModels = [
            'cv_review' => RecruitmentCvReview::class,
            'psikotes' => RecruitmentPsikotes::class,
            'tes_teori' => RecruitmentTesTeori::class,
            'interview' => RecruitmentInterview::class,
            'offering' => RecruitmentOffering::class,
            'mcu' => RecruitmentMcu::class,
            'mcu_magang_harian' => RecruitmentMcu::class, // Special case for magang/harian MCU
            'hiring' => RecruitmentHiring::class,
            'hiring_magang_harian' => RecruitmentHiring::class, // Special case for magang/harian Hiring
            'onboarding' => RecruitmentOnboarding::class,
        ];

        // Handle special stage naming
        $actualStage = in_array($stage, ['mcu_magang_harian', 'hiring_magang_harian']) ?
            str_replace('_magang_harian', '', $stage) : $stage;

        if (!isset($stageModels[$stage])) {
            return [];
        }

        $modelClass = $stageModels[$stage];

        // Get recruitment sessions with filters
        $sessionsQuery = \App\Models\RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'candidate'
        ]);

        // Apply FPTK-based filters
        if (!empty($department) || !empty($position) || !empty($project)) {
            $sessionsQuery->whereHas('fptk', function ($q) use ($department, $position, $project) {
                if (!empty($department)) {
                    $q->where('department_id', $department);
                }
                if (!empty($position)) {
                    $q->where('position_id', $position);
                }
                if (!empty($project)) {
                    $q->where('project_id', $project);
                }
            });
        }

        // Apply date filter
        if (!empty($date1) && !empty($date2)) {
            $sessionsQuery->whereBetween('created_at', [$date1, $date2]);
        }

        $sessions = $sessionsQuery->get();

        // Filter sessions based on employment type and stage compatibility
        $filteredSessions = $sessions->filter(function ($session) use ($stage) {
            if (!$session->fptk) return false;

            $employmentType = $session->fptk->employment_type ?? 'regular';

            // Handle special magang/harian stage naming
            if (in_array($stage, ['mcu_magang_harian', 'hiring_magang_harian'])) {
                // Only magang and harian employment types
                return in_array($employmentType, ['magang', 'harian']);
            }

            $expectedStages = $this->getExpectedStagesForEmploymentType($employmentType);

            // Convert stage name to match expected stages format
            $stageMapping = [
                'cv_review' => 'CV Review',
                'psikotes' => 'Psikotes',
                'tes_teori' => 'Tes Teori',
                'interview' => 'Interview',
                'offering' => 'Offering',
                'mcu' => 'MCU',
                'hiring' => 'Hiring',
                'onboarding' => 'Onboarding'
            ];

            $stageName = $stageMapping[$stage] ?? ucfirst(str_replace('_', ' ', $stage));

            // For regular MCU and Hiring, exclude magang/harian
            if (in_array($stage, ['mcu', 'hiring'])) {
                $isRegularEmployment = !in_array($employmentType, ['magang', 'harian']);
                $stageAllowed = in_array($stageName, $expectedStages);
                return $isRegularEmployment && $stageAllowed;
            }

            // Only include sessions where this stage is expected for the employment type
            return in_array($stageName, $expectedStages);
        });

        // Get stage records only for filtered sessions
        $stageQuery = $modelClass::with(['session.fptk.department', 'session.fptk.position', 'session.fptk.project', 'session.candidate'])
            ->whereIn('session_id', $filteredSessions->pluck('id'));

        if (!empty($date1) && !empty($date2)) {
            $stageQuery->whereBetween('created_at', [$date1, $date2]);
        }

        $stageRecords = $stageQuery->orderBy('created_at', 'desc')->get();

        $rows = [];
        foreach ($stageRecords as $record) {
            $session = $record->session;
            if (!$session || !$session->fptk || !$session->candidate) {
                continue;
            }

            $fptk = $session->fptk;
            $candidate = $session->candidate;

            // Calculate days in stage
            $daysInStage = 0;
            if ($record->updated_at && $record->created_at) {
                $daysInStage = $record->updated_at->diffInDays($record->created_at);
            } elseif ($record->created_at) {
                $daysInStage = now()->diffInDays($record->created_at);
            }

            // Get result/status from record
            $result = 'Pending';
            if (isset($record->result)) {
                $result = ucfirst($record->result);
            } elseif (isset($record->status)) {
                $result = ucfirst($record->status);
            } elseif (isset($record->decision)) {
                $result = ucfirst($record->decision);
            }

            // Special handling for hiring and onboarding stages
            if ($stage === 'hiring') {
                if ($result === 'Pending' || $result === 'In Progress') {
                    $result = 'Hired';
                }
            } elseif ($stage === 'onboarding') {
                if ($result === 'Pending' || $result === 'In Progress') {
                    $result = 'Complete';
                }
            }

            // Get interview type for interview stage
            $interviewType = null;
            if ($stage === 'interview' && isset($record->type)) {
                $interviewType = $record->type === 'hr' ? 'HR' : 'User';
            }

            // Build detailed remarks based on stage type
            $detailedRemarks = $this->buildStageRemarks($stage, $record);

            // Get employment type information
            $employmentType = $fptk->employment_type ?? 'regular';
            $expectedStages = $this->getExpectedStagesForEmploymentType($employmentType);

            $rows[] = [
                'session_id' => $session->id,
                'fptk_number' => $fptk->request_number,
                'department' => $fptk->department ? $fptk->department->department_name : '-',
                'position' => $fptk->position ? $fptk->position->position_name : '-',
                'project' => $fptk->project ? $fptk->project->project_name : '-',
                'candidate_name' => $candidate->fullname,
                'candidate_number' => $candidate->candidate_number,
                'session_number' => $session->session_number,
                'stage_date' => $record->created_at->format('d/m/Y H:i'),
                'days_in_stage' => $daysInStage,
                'result' => $result,
                'interview_type' => $interviewType,
                'remarks' => $detailedRemarks,
                'employment_type' => ucfirst($employmentType),
                'expected_stages' => implode(' → ', $expectedStages),
                'is_magang_harian' => in_array($employmentType, ['magang', 'harian']),
            ];
        }

        return $rows;
    }

    private function buildStageRemarks($stage, $record): string
    {
        $remarks = [];

        switch ($stage) {
            case 'cv_review':
                if (isset($record->decision)) {
                    $remarks[] = "Decision: " . ucfirst($record->decision);
                }
                if (!empty($record->notes)) {
                    $remarks[] = "Notes: " . $record->notes;
                }
                break;

            case 'interview':
                if (isset($record->result)) {
                    $remarks[] = "Result: " . ucfirst($record->result);
                }
                if (isset($record->type)) {
                    $remarks[] = "Type: " . ($record->type === 'hr' ? 'HR Interview' : 'User Interview');
                }
                if (!empty($record->notes)) {
                    $remarks[] = "Notes: " . $record->notes;
                }
                break;

            case 'psikotes':
                $scoreDetails = [];
                if (isset($record->online_score) && $record->online_score !== null) {
                    $scoreDetails[] = "Online: " . number_format($record->online_score, 1);
                }
                if (isset($record->offline_score) && $record->offline_score !== null) {
                    $scoreDetails[] = "Offline: " . number_format($record->offline_score, 1);
                }
                if (!empty($scoreDetails)) {
                    $remarks[] = "Score (" . implode(', ', $scoreDetails) . ")";
                }
                if (isset($record->result)) {
                    $remarks[] = "Result: " . ucfirst($record->result);
                }
                if (!empty($record->notes)) {
                    $remarks[] = "Notes: " . $record->notes;
                }
                break;

            case 'tes_teori':
                if (isset($record->score) && $record->score !== null) {
                    $remarks[] = "Score: " . number_format($record->score, 1);
                }
                if (isset($record->result)) {
                    $remarks[] = "Result: " . ucfirst($record->result);
                }
                if (!empty($record->notes)) {
                    $remarks[] = "Notes: " . $record->notes;
                }
                break;

            case 'mcu':
                if (isset($record->result)) {
                    $remarks[] = "Result: " . ucfirst(str_replace('_', ' ', $record->result));
                }
                if (!empty($record->notes)) {
                    $remarks[] = "Notes: " . $record->notes;
                }
                break;

            case 'offering':
                if (!empty($record->offering_letter_number)) {
                    $remarks[] = "Letter No: " . $record->offering_letter_number;
                }
                if (isset($record->result)) {
                    $remarks[] = "Response: " . ucfirst($record->result);
                }
                if (!empty($record->notes)) {
                    $remarks[] = "Notes: " . $record->notes;
                }
                break;

            case 'hiring':
                if (isset($record->agreement_type)) {
                    $remarks[] = "Agreement: " . strtoupper($record->agreement_type);
                }
                if (!empty($record->letter_number)) {
                    $remarks[] = "Letter No: " . $record->letter_number;
                }
                if (!empty($record->notes)) {
                    $remarks[] = "Notes: " . $record->notes;
                }
                break;

            case 'onboarding':
                if (isset($record->onboarding_date) && $record->onboarding_date) {
                    $remarks[] = "Date: " . $record->onboarding_date->format('d/m/Y');
                }
                if (!empty($record->notes)) {
                    $remarks[] = "Notes: " . $record->notes;
                }
                break;

            default:
                if (!empty($record->notes)) {
                    $remarks[] = $record->notes;
                }
                if (isset($record->remarks) && !empty($record->remarks)) {
                    $remarks[] = $record->remarks;
                }
                break;
        }

        return !empty($remarks) ? implode(' | ', $remarks) : '-';
    }

    public function aging(Request $request)
    {
        $date1 = $request->get('date1');
        $date2 = $request->get('date2');
        $department = $request->get('department');
        $project = $request->get('project');
        $status = $request->get('status');

        return view('recruitment.reports.aging', [
            'title' => 'Recruitment Reports',
            'subtitle' => 'Request Aging & SLA',
            'date1' => $date1,
            'date2' => $date2,
            'department' => $department,
            'project' => $project,
            'status' => $status,
        ]);
    }

    public function exportAging(Request $request)
    {
        $date1 = $request->get('date1');
        $date2 = $request->get('date2');
        $department = $request->get('department');
        $project = $request->get('project');
        $status = $request->get('status');

        $rows = collect($this->buildAgingData($date1, $date2, $department, $project, $status));

        return Excel::download(new class($rows) implements FromCollection, WithHeadings, WithMapping {
            private $rows;

            public function __construct($rows)
            {
                $this->rows = $rows;
            }

            public function collection()
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return [
                    'Request No',
                    'Department',
                    'Position',
                    'Project',
                    'Requested By',
                    'Requested At',
                    'Status',
                    'Days Open',
                    'Latest Approval',
                    'Approved At',
                    'Days to Approve',
                    'SLA Target (Days)',
                    'SLA Status',
                    'SLA Days Remaining',
                    'Approval Remarks',
                    'Request Reason',
                    'Approval Flow Type',
                    'Expected Approvers',
                    'Approval SLA Target',
                    'Actual Approvers Count',
                    'Expected Approvers Count',
                    'Approval Efficiency',
                ];
            }

            public function map($row): array
            {
                return [
                    $row['request_no'],
                    $row['department'],
                    $row['position'],
                    $row['project'],
                    $row['requested_by'],
                    $row['requested_at'],
                    $row['status'],
                    $row['days_open'],
                    $row['latest_approval'],
                    $row['approved_at'],
                    // Fix Excel display issue with integer 0
                    ($row['days_to_approve'] === 0 || $row['days_to_approve'] === '0')
                        ? '0'
                        : (is_numeric($row['days_to_approve']) ? (string)$row['days_to_approve'] : $row['days_to_approve']),
                    $row['sla_target'],
                    $row['sla_status'],
                    $row['sla_days_remaining'] !== null ? $row['sla_days_remaining'] : '-',
                    $row['remarks'],
                    $row['request_reason'],
                    $row['approval_flow_type'],
                    $row['expected_approvers'],
                    $row['approval_sla_target'],
                    $row['actual_approvers_count'],
                    $row['expected_approvers_count'],
                    $row['approval_efficiency'],
                ];
            }
        }, 'recruitment_aging_' . date('YmdHis') . '.xlsx');
    }

    /**
     * Calculate days to approve from requested_at to approved_at
     */
    private function calculateDaysToApprove($requestedAt, $approvedAt)
    {
        try {
            // Check if both dates are valid and approved_at is not empty
            if (!$requestedAt || !$approvedAt || $approvedAt === '-') {
                return '-';
            }

            // Parse dates with error handling
            $requestDate = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $requestedAt);
            $approvalDate = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $approvedAt);

            // Calculate difference in days
            $days = $requestDate->diffInDays($approvalDate);

            return $days;
        } catch (\Exception $e) {
            // Log error for debugging
            \Illuminate\Support\Facades\Log::error('Error calculating days to approve', [
                'requested_at' => $requestedAt,
                'approved_at' => $approvedAt,
                'error' => $e->getMessage()
            ]);

            return '-';
        }
    }

    /**
     * Get project type based on project name
     */
    private function getProjectType($projectId)
    {
        if (!$projectId) return 'unknown';

        $project = \App\Models\Project::find($projectId);
        if (!$project) return 'unknown';

        $projectName = strtoupper($project->project_name ?? $project->project_code ?? '');

        if (str_contains($projectName, 'HO')) {
            return 'HO';
        } elseif (str_contains($projectName, 'BO')) {
            return 'BO';
        } elseif (str_contains($projectName, 'APS')) {
            return 'APS';
        } else {
            return 'ALL_PROJECT';
        }
    }

    /**
     * Get expected stages based on employment type
     */
    private function getExpectedStagesForEmploymentType($employmentType)
    {
        if (in_array($employmentType, ['magang', 'harian'])) {
            return ['MCU', 'Hiring'];
        }
        return ['CV Review', 'Psikotes', 'Tes Teori', 'Interview', 'Offering', 'MCU', 'Hiring'];
    }

    /**
     * Calculate conditional approval metrics based on request reason
     */
    private function calculateConditionalApprovalMetrics($recruitmentRequest)
    {
        $requestReason = $recruitmentRequest->request_reason ?? 'legacy';
        $projectType = $this->getProjectType($recruitmentRequest->project_id);

        switch ($requestReason) {
            case 'replacement':
                return [
                    'flow_type' => 'Single Approval',
                    'expected_approvers' => 'HCS Division Manager',
                    'sla_target' => 30, // days
                    'approval_stages' => 1
                ];
            case 'additional':
                if (in_array($projectType, ['HO', 'BO', 'APS'])) {
                    return [
                        'flow_type' => 'Two Stage - HO/BO/APS',
                        'expected_approvers' => 'HCS DM → HCL Director',
                        'sla_target' => 60, // days
                        'approval_stages' => 2
                    ];
                } else {
                    return [
                        'flow_type' => 'Two Stage - All Project',
                        'expected_approvers' => 'Operational GM → HCS DM',
                        'sla_target' => 45, // days
                        'approval_stages' => 2
                    ];
                }
            default:
                return [
                    'flow_type' => 'Legacy Flow',
                    'expected_approvers' => 'Multiple Approvers',
                    'sla_target' => 90, // days
                    'approval_stages' => 3
                ];
        }
    }

    /**
     * Calculate SLA metrics for a recruitment request
     */
    private function calculateSLAMetrics($recruitmentRequest, $latestApproval, $daysToApprove): array
    {
        $slaTarget = 180; // Target: 6 months (180 days) from approval completion
        $slaStatus = '-';
        $slaClass = '';
        $slaDaysRemaining = null;

        if ($daysToApprove !== null) {
            // Calculate days from approval completion to 6 months target
            $approvalCompletionDate = $latestApproval->updated_at;
            $slaDeadline = $approvalCompletionDate->addDays($slaTarget);
            $currentDate = now();

            if ($currentDate <= $slaDeadline) {
                $slaStatus = 'Active';
                $slaClass = 'badge-success';
                $slaDaysRemaining = $currentDate->diffInDays($slaDeadline);
            } else {
                $slaStatus = 'Overdue';
                $slaClass = 'badge-danger';
                $slaDaysRemaining = $currentDate->diffInDays($slaDeadline);
            }
        } elseif ($recruitmentRequest->status === 'submitted') {
            $slaStatus = 'Pending Approval';
            $slaClass = 'badge-warning';
            $slaDaysRemaining = null;
        } else {
            // For other statuses (draft, rejected, closed)
            $slaStatus = '-';
            $slaClass = '';
            $slaDaysRemaining = null;
        }

        return [
            'sla_target' => $slaTarget,
            'sla_status' => $slaStatus,
            'sla_class' => $slaClass,
            'sla_days_remaining' => $slaDaysRemaining
        ];
    }

    private function buildAgingData(?string $date1, ?string $date2, ?string $department, ?string $project, ?string $status): array
    {
        $query = \App\Models\RecruitmentRequest::with([
            'department',
            'position',
            'project',
            'createdBy',
            'approval_plans.approver'
        ]);

        // Apply filters
        if (!empty($date1) && !empty($date2)) {
            $query->whereBetween('created_at', [$date1, $date2]);
        }
        if (!empty($department)) {
            $query->where('department_id', $department);
        }
        if (!empty($project)) {
            $query->where('project_id', $project);
        }
        if (!empty($status)) {
            $query->where('status', $status);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        $rows = [];
        foreach ($requests as $recruitmentRequest) {
            // Calculate days open
            $daysOpen = now()->diffInDays($recruitmentRequest->created_at);

            // Get latest approval info
            $latestApproval = null;
            $approvedAt = null;
            $daysToApprove = null;
            $latestApprovalName = '-';
            $approvalRemarks = '-';

            if ($recruitmentRequest->approval_plans && $recruitmentRequest->approval_plans->count() > 0) {
                $approvedPlans = $recruitmentRequest->approval_plans->where('status', 1);
                if ($approvedPlans->count() > 0) {
                    // Get the latest approval by approval_order (step) instead of updated_at
                    $latestApproval = $approvedPlans->sortByDesc('approval_order')->first();
                    $approvedAt = $latestApproval->updated_at ? $latestApproval->updated_at->format('d/m/Y H:i') : '-';

                    // Calculate days to approve: from request creation to the LAST approval step completion
                    $daysToApprove = $latestApproval->updated_at ? $recruitmentRequest->created_at->diffInDays($latestApproval->updated_at) : null;

                    $latestApprovalName = $latestApproval->approver ? $latestApproval->approver->name : '-';
                    $approvalRemarks = $latestApproval->remarks ?: '-';
                }
            }

            // Calculate SLA metrics - 6 months from approval completion
            $slaTarget = 180; // Target: 6 months (180 days) from approval completion
            $slaStatus = '-';
            $slaClass = '';
            $slaDaysRemaining = null;

            if ($daysToApprove !== null) {
                // Calculate days from approval completion to 6 months target
                $approvalCompletionDate = $latestApproval->updated_at;
                $slaDeadline = $approvalCompletionDate->addDays($slaTarget);
                $currentDate = now();

                if ($currentDate <= $slaDeadline) {
                    $slaStatus = 'Active';
                    $slaClass = 'badge-success';
                    $slaDaysRemaining = $currentDate->diffInDays($slaDeadline);
                } else {
                    $slaStatus = 'Overdue';
                    $slaClass = 'badge-danger';
                    $slaDaysRemaining = $currentDate->diffInDays($slaDeadline);
                }
            } elseif ($recruitmentRequest->status === 'submitted') {
                $slaStatus = 'Pending Approval';
                $slaClass = 'badge-warning';
            }

            // Get conditional approval metrics
            $approvalMetrics = $this->calculateConditionalApprovalMetrics($recruitmentRequest);

            // Calculate approval efficiency
            $actualApprovers = $recruitmentRequest->approval_plans ? $recruitmentRequest->approval_plans->where('status', 1)->count() : 0;
            $approvalEfficiency = $approvalMetrics['approval_stages'] > 0 ?
                round(($actualApprovers / $approvalMetrics['approval_stages']) * 100, 1) : 0;

            $rows[] = [
                'request_id' => $recruitmentRequest->id,
                'request_no' => $recruitmentRequest->request_number,
                'department' => $recruitmentRequest->department ? $recruitmentRequest->department->department_name : '-',
                'position' => $recruitmentRequest->position ? $recruitmentRequest->position->position_name : '-',
                'project' => $recruitmentRequest->project ? $recruitmentRequest->project->project_name : '-',
                'requested_by' => $recruitmentRequest->createdBy ? $recruitmentRequest->createdBy->name : '-',
                'requested_at' => $recruitmentRequest->created_at->format('d/m/Y H:i'),
                'status' => ucfirst($recruitmentRequest->status),
                'days_open' => $daysOpen,
                'latest_approval' => $latestApprovalName,
                'approved_at' => $approvedAt,
                'days_to_approve' => $daysToApprove !== null ? $daysToApprove : '-',
                'sla_target' => $slaTarget,
                'sla_status' => $slaStatus,
                'sla_class' => $slaClass,
                'sla_days_remaining' => $slaDaysRemaining,
                'remarks' => $approvalRemarks,
                // New fields for conditional approval
                'request_reason' => ucfirst($recruitmentRequest->request_reason ?? 'Legacy'),
                'approval_flow_type' => $approvalMetrics['flow_type'],
                'expected_approvers' => $approvalMetrics['expected_approvers'],
                'approval_sla_target' => $approvalMetrics['sla_target'],
                'actual_approvers_count' => $actualApprovers,
                'expected_approvers_count' => $approvalMetrics['approval_stages'],
                'approval_efficiency' => $approvalEfficiency . '%',
            ];
        }

        return $rows;
    }

    public function timeToHire()
    {
        $title = 'Recruitment Reports';
        $subtitle = 'Time to Hire & Time to Fill Analytics';
        $date1 = request('date1', '');
        $date2 = request('date2', '');
        $department = request('department');
        $position = request('position');
        $project = request('project');
        $metric_type = request('metric_type', 'candidate'); // 'candidate' or 'fptk'

        $departments = Department::orderBy('department_name')->get();
        $positions = Position::orderBy('position_name')->get();
        $projects = Project::orderBy('project_code')->get();

        return view('recruitment.reports.time-to-hire', compact(
            'title',
            'subtitle',
            'date1',
            'date2',
            'department',
            'position',
            'project',
            'metric_type',
            'departments',
            'positions',
            'projects'
        ));
    }

    public function exportTimeToHire()
    {
        $date1 = request('date1', '');
        $date2 = request('date2', '');
        $department = request('department');
        $position = request('position');
        $project = request('project');
        $metricType = request('metric_type', 'candidate');

        if ($metricType === 'fptk') {
            return $this->exportTimeToFillData($date1, $date2, $department, $position, $project);
        } else {
            return $this->exportTimeToHirePerCandidateData($date1, $date2, $department, $position, $project);
        }
    }

    /**
     * Export Time to Hire per Candidate data
     */
    private function exportTimeToHirePerCandidateData($date1, $date2, $department, $position, $project)
    {
        $query = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'fptk.approval_plans.approver',
            'hiring',
            'candidate'
        ])
            ->whereIn('status', ['in_process', 'hired'])
            ->whereNotNull('fptk_id')
            ->whereNotNull('candidate_id');

        if ($date1 && $date2) {
            $query->whereBetween('created_at', [$date1 . ' 00:00:00', $date2 . ' 23:59:59']);
        }

        if ($department) {
            $query->whereHas('fptk', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($position) {
            $query->whereHas('fptk', function ($q) use ($position) {
                $q->where('position_id', $position);
            });
        }
        if ($project) {
            $query->whereHas('fptk', function ($q) use ($project) {
                $q->where('project_id', $project);
            });
        }

        $sessions = $query->get();
        $rows = $this->buildTimeToHirePerCandidateExportData($sessions);

        return Excel::download(new class($rows) implements FromCollection, WithHeadings, WithMapping {
            private $rows;

            public function __construct($rows)
            {
                $this->rows = $rows;
            }

            public function collection()
            {
                return collect($this->rows);
            }

            public function headings(): array
            {
                return [
                    'Candidate Name',
                    'Candidate Number',
                    'Request No',
                    'Department',
                    'Position',
                    'Project',
                    'Session Created At',
                    'Hiring Date',
                    'Time to Hire (Days)',
                    'Approval Days',
                    'Recruitment Days',
                    'Employment Type',
                    'Status',
                    'Latest Approval',
                    'Approval Remarks'
                ];
            }

            public function map($row): array
            {
                return [
                    $row['candidate_name'],
                    $row['candidate_number'],
                    $row['request_no'],
                    $row['department'],
                    $row['position'],
                    $row['project'],
                    $row['session_created_at'],
                    $row['hiring_date'],
                    $row['time_to_hire_days'],
                    $row['approval_days'],
                    $row['recruitment_days'],
                    $row['employment_type'],
                    $row['status'],
                    $row['latest_approval'],
                    $row['remarks']
                ];
            }
        }, 'time_to_hire_per_candidate_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Time to Fill per FPTK data
     */
    private function exportTimeToFillData($date1, $date2, $department, $position, $project)
    {
        $query = RecruitmentRequest::with([
            'department',
            'position',
            'project',
            'createdBy',
            'approval_plans.approver',
            'sessions' => function ($q) {
                $q->whereIn('status', ['in_process', 'hired'])
                    ->whereNotNull('candidate_id');
            },
            'sessions.hiring',
            'sessions.candidate'
        ])
            ->whereHas('sessions', function ($q) {
                $q->whereIn('status', ['in_process', 'hired'])
                    ->whereNotNull('candidate_id');
            });

        if ($date1 && $date2) {
            $query->whereBetween('created_at', [$date1 . ' 00:00:00', $date2 . ' 23:59:59']);
        }

        if ($department) {
            $query->where('department_id', $department);
        }
        if ($position) {
            $query->where('position_id', $position);
        }
        if ($project) {
            $query->where('project_id', $project);
        }

        $fptks = $query->get();
        $rows = $this->buildTimeToFillExportData($fptks);

        return Excel::download(new class($rows) implements FromCollection, WithHeadings, WithMapping {
            private $rows;

            public function __construct($rows)
            {
                $this->rows = $rows;
            }

            public function collection()
            {
                return collect($this->rows);
            }

            public function headings(): array
            {
                return [
                    'Request No',
                    'Department',
                    'Position',
                    'Project',
                    'FPTK Created At',
                    'First Hiring Date',
                    'Time to Fill (Days)',
                    'Approval Days',
                    'Recruitment Days',
                    'Hired Count',
                    'Required Qty',
                    'Fill Rate (%)',
                    'Employment Type',
                    'Status',
                    'Latest Approval',
                    'Approval Remarks'
                ];
            }

            public function map($row): array
            {
                return [
                    $row['request_no'],
                    $row['department'],
                    $row['position'],
                    $row['project'],
                    $row['fptk_created_at'],
                    $row['first_hiring_date'],
                    $row['time_to_fill_days'],
                    $row['approval_days'],
                    $row['recruitment_days'],
                    $row['hired_count'],
                    $row['required_qty'],
                    $row['fill_rate'],
                    $row['employment_type'],
                    $row['status'],
                    $row['latest_approval'],
                    $row['remarks']
                ];
            }
        }, 'time_to_fill_per_fptk_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Build Time to Hire per Candidate export data
     */
    private function buildTimeToHirePerCandidateExportData($sessions)
    {
        $rows = [];
        foreach ($sessions as $session) {
            if (!$session->candidate || !$session->fptk) {
                continue;
            }

            // Time to Hire per Candidate: Session created to current status
            $timeToHireDays = 0;
            $hiringDate = null;

            if ($session->status === 'hired' && $session->hiring) {
                // If hired, calculate from session created to hiring date
                $hiringDate = $session->hiring->created_at;
                $timeToHireDays = $hiringDate->diffInDays($session->created_at);
            } else {
                // If in_process, calculate from session created to now
                $timeToHireDays = now()->diffInDays($session->created_at);
            }

            // Approval days: FPTK created to approval completion
            $approvalDays = 0;
            $latestApproval = null;
            if ($session->fptk && $session->fptk->approval_plans && $session->fptk->approval_plans->count() > 0) {
                $approvedPlans = $session->fptk->approval_plans->where('status', 1);
                if ($approvedPlans->count() > 0) {
                    $latestApproval = $approvedPlans->sortByDesc('updated_at')->first();
                    if ($latestApproval->updated_at) {
                        $approvalDays = $latestApproval->updated_at->diffInDays($session->fptk->created_at);
                    }
                }
            }

            // Recruitment days: Approval completion to current status
            $recruitmentDays = 0;
            if ($latestApproval && $latestApproval->updated_at) {
                if ($hiringDate) {
                    $recruitmentDays = $hiringDate->diffInDays($latestApproval->updated_at);
                } else {
                    $recruitmentDays = now()->diffInDays($latestApproval->updated_at);
                }
            }

            $rows[] = [
                'candidate_name' => $session->candidate->fullname,
                'candidate_number' => $session->candidate->candidate_number,
                'request_no' => $session->fptk ? $session->fptk->request_number : '-',
                'department' => $session->fptk && $session->fptk->department ? $session->fptk->department->department_name : '-',
                'position' => $session->fptk && $session->fptk->position ? $session->fptk->position->position_name : '-',
                'project' => $session->fptk && $session->fptk->project ? $session->fptk->project->project_name : '-',
                'session_created_at' => $session->created_at->format('Y-m-d H:i:s'),
                'hiring_date' => $hiringDate ? $hiringDate->format('Y-m-d') : ($session->status === 'in_process' ? 'In Progress' : '-'),
                'time_to_hire_days' => $timeToHireDays,
                'approval_days' => $approvalDays,
                'recruitment_days' => $recruitmentDays,
                'employment_type' => $session->fptk ? ucfirst($session->fptk->employment_type ?? 'regular') : 'Regular',
                'status' => ucfirst($session->status),
                'latest_approval' => $latestApproval && $latestApproval->approver ? $latestApproval->approver->name : '-',
                'remarks' => $latestApproval ? ($latestApproval->remarks ?: '-') : '-',
            ];
        }
        return $rows;
    }

    /**
     * Build Time to Fill per FPTK export data
     */
    private function buildTimeToFillExportData($fptks)
    {
        $rows = [];
        foreach ($fptks as $fptk) {
            // Get sessions with valid status
            $validSessions = $fptk->sessions->whereIn('status', ['in_process', 'hired'])
                ->whereNotNull('candidate_id');

            if ($validSessions->isEmpty()) {
                continue;
            }

            // Get first hiring date from hired sessions
            $hiredSessions = $validSessions->where('status', 'hired')->where('hiring', '!=', null);
            $firstHiringDate = null;
            $timeToFillDays = 0;

            if ($hiredSessions->isNotEmpty()) {
                $firstHiringDate = $hiredSessions->min(function ($session) {
                    return $session->hiring->created_at;
                });
                $timeToFillDays = $firstHiringDate->diffInDays($fptk->created_at);
            } else {
                // If no hired sessions, calculate from FPTK created to now
                $timeToFillDays = now()->diffInDays($fptk->created_at);
            }

            // Approval days: FPTK created to approval completion
            $approvalDays = 0;
            $latestApproval = null;
            if ($fptk->approval_plans && $fptk->approval_plans->count() > 0) {
                $approvedPlans = $fptk->approval_plans->where('status', 1);
                if ($approvedPlans->count() > 0) {
                    $latestApproval = $approvedPlans->sortByDesc('updated_at')->first();
                    if ($latestApproval->updated_at) {
                        $approvalDays = $latestApproval->updated_at->diffInDays($fptk->created_at);
                    }
                }
            }

            // Recruitment days: Approval completion to current status
            $recruitmentDays = 0;
            if ($latestApproval && $latestApproval->updated_at) {
                if ($firstHiringDate) {
                    $recruitmentDays = $firstHiringDate->diffInDays($latestApproval->updated_at);
                } else {
                    $recruitmentDays = now()->diffInDays($latestApproval->updated_at);
                }
            }

            // Calculate fill rate
            $hiredCount = $hiredSessions->count();
            $inProcessCount = $validSessions->where('status', 'in_process')->count();
            $totalSessions = $validSessions->count();
            $requiredQty = $fptk->required_qty ?? 1;
            $fillRate = $requiredQty > 0 ? round(($hiredCount / $requiredQty) * 100, 1) : 0;

            $rows[] = [
                'request_no' => $fptk->request_number,
                'department' => $fptk->department ? $fptk->department->department_name : '-',
                'position' => $fptk->position ? $fptk->position->position_name : '-',
                'project' => $fptk->project ? $fptk->project->project_name : '-',
                'fptk_created_at' => $fptk->created_at->format('Y-m-d H:i:s'),
                'first_hiring_date' => $firstHiringDate ? $firstHiringDate->format('Y-m-d') : 'In Progress',
                'time_to_fill_days' => $timeToFillDays,
                'approval_days' => $approvalDays,
                'recruitment_days' => $recruitmentDays,
                'hired_count' => $hiredCount,
                'required_qty' => $requiredQty,
                'fill_rate' => $fillRate,
                'employment_type' => ucfirst($fptk->employment_type ?? 'regular'),
                'status' => ucfirst($fptk->status),
                'latest_approval' => $latestApproval && $latestApproval->approver ? $latestApproval->approver->name : '-',
                'remarks' => $latestApproval ? ($latestApproval->remarks ?: '-') : '-',
            ];
        }
        return $rows;
    }

    public function offerAcceptanceRate(Request $request)
    {
        $title = 'Recruitment Reports';
        $subtitle = 'Offer Acceptance Rate';
        $date1 = request('date1', '');
        $date2 = request('date2', '');
        $department = request('department');
        $position = request('position');
        $project = request('project');

        $departments = Department::orderBy('department_name')->get();
        $positions = Position::orderBy('position_name')->get();
        $projects = Project::orderBy('project_code')->get();

        return view('recruitment.reports.offer-acceptance-rate', compact(
            'title',
            'subtitle',
            'date1',
            'date2',
            'department',
            'position',
            'project',
            'departments',
            'positions',
            'projects'
        ));
    }

    public function exportOfferAcceptanceRate()
    {
        $date1 = request('date1', '');
        $date2 = request('date2', '');
        $department = request('department');
        $position = request('position');
        $project = request('project');

        $query = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'offering'
        ])
            ->whereHas('offering');

        if ($date1 && $date2) {
            $query->whereBetween('created_at', [$date1 . ' 00:00:00', $date2 . ' 23:59:59']);
        }

        if ($department) {
            $query->whereHas('fptk', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($position) {
            $query->whereHas('fptk', function ($q) use ($position) {
                $q->where('position_id', $position);
            });
        }
        if ($project) {
            $query->whereHas('fptk', function ($q) use ($project) {
                $q->where('project_id', $project);
            });
        }

        $sessions = $query->get();
        $rows = $this->buildOfferAcceptanceData($sessions);

        return Excel::download(new class($rows) implements FromCollection, WithHeadings, WithMapping {
            private $rows;

            public function __construct($rows)
            {
                $this->rows = $rows;
            }

            public function collection()
            {
                return collect($this->rows);
            }

            public function headings(): array
            {
                return [
                    'Request No',
                    'Department',
                    'Position',
                    'Project',
                    'Candidate Name',
                    'Offering Date',
                    'Response Date',
                    'Response Time (Days)',
                    'Response',
                    'Offering Letter No',
                    'Notes'
                ];
            }

            public function map($row): array
            {
                return [
                    $row['request_no'],
                    $row['department'],
                    $row['position'],
                    $row['project'],
                    $row['candidate_name'],
                    $row['offering_date'],
                    $row['response_date'],
                    $row['response_time'],
                    $row['response'],
                    $row['offering_letter_no'],
                    $row['notes']
                ];
            }
        }, 'offer_acceptance_rate_' . date('Y-m-d') . '.xlsx');
    }

    private function buildTimeToHireData($sessions)
    {
        $rows = [];
        foreach ($sessions as $session) {
            // Get hiring record for this session
            if (!$session->hiring) {
                continue; // Skip if no hiring record found
            }

            $hiringDate = $session->hiring->created_at->format('Y-m-d');

            // Calculate total days from session creation to hiring
            $totalDays = $session->hiring->created_at->diffInDays($session->created_at);

            // Calculate approval days (from FPTK creation to approval)
            $approvalDays = 0;
            $latestApproval = null;
            if ($session->fptk && $session->fptk->approval_plans && $session->fptk->approval_plans->count() > 0) {
                $approvedPlans = $session->fptk->approval_plans->where('status', 1);
                if ($approvedPlans->count() > 0) {
                    $latestApproval = $approvedPlans->sortByDesc('updated_at')->first();
                    if ($latestApproval->updated_at) {
                        $approvalDays = $latestApproval->updated_at->diffInDays($session->fptk->created_at);
                    }
                }
            }

            // Calculate recruitment days (from approval to hiring)
            $recruitmentDays = 0;
            if ($latestApproval && $latestApproval->updated_at) {
                $recruitmentDays = $session->hiring->created_at->diffInDays($latestApproval->updated_at);
            }

            // Get employment type and expected stages
            $employmentType = $session->fptk->employment_type ?? 'regular';
            $expectedStages = $this->getExpectedStagesForEmploymentType($employmentType);

            // Get conditional approval metrics
            $approvalMetrics = $session->fptk ? $this->calculateConditionalApprovalMetrics($session->fptk) : [
                'flow_type' => 'Unknown',
                'expected_approvers' => 'Unknown',
                'sla_target' => 0,
                'approval_stages' => 0
            ];

            // Calculate efficiency metrics
            $actualApprovers = $session->fptk && $session->fptk->approval_plans ?
                $session->fptk->approval_plans->where('status', 1)->count() : 0;
            $approvalEfficiency = $approvalMetrics['approval_stages'] > 0 ?
                round(($actualApprovers / $approvalMetrics['approval_stages']) * 100, 1) : 0;

            $rows[] = [
                'session_id' => $session->id,
                'request_id' => $session->fptk ? $session->fptk->id : 0,
                'request_no' => $session->fptk ? $session->fptk->request_number : '-',
                'department' => $session->fptk && $session->fptk->department ? $session->fptk->department->department_name : '-',
                'position' => $session->fptk && $session->fptk->position ? $session->fptk->position->position_name : '-',
                'project' => $session->fptk && $session->fptk->project ? $session->fptk->project->project_name : '-',
                'requested_at' => $session->fptk ? $session->fptk->created_at->format('Y-m-d H:i:s') : '-',
                'hiring_date' => $hiringDate,
                'total_days' => $totalDays,
                'approval_days' => $approvalDays,
                'recruitment_days' => $recruitmentDays,
                'status' => $session->fptk ? ucfirst($session->fptk->status) : '-',
                'latest_approval' => $latestApproval && $latestApproval->approver ? $latestApproval->approver->name : '-',
                'remarks' => $latestApproval ? ($latestApproval->remarks ?: '-') : '-',
                // New fields for employment type and conditional approval
                'employment_type' => ucfirst($employmentType),
                'expected_stages' => implode(' → ', $expectedStages),
                'request_reason' => ucfirst($session->fptk->request_reason ?? 'Legacy'),
                'approval_flow_type' => $approvalMetrics['flow_type'],
                'expected_approvers' => $approvalMetrics['expected_approvers'],
                'approval_sla_target' => $approvalMetrics['sla_target'],
                'actual_approvers_count' => $actualApprovers,
                'expected_approvers_count' => $approvalMetrics['approval_stages'],
                'approval_efficiency' => $approvalEfficiency . '%',
                'approval_vs_sla' => $approvalDays <= $approvalMetrics['sla_target'] ? 'On Time' : 'Delayed',
            ];
        }
        return $rows;
    }

    private function buildOfferAcceptanceData($sessions)
    {
        $rows = [];
        foreach ($sessions as $session) {
            if (!$session->offering) {
                continue;
            }

            $offering = $session->offering;
            $fptk = $session->fptk;
            $candidate = $session->candidate;

            if (!$fptk || !$candidate) {
                continue;
            }

            // Calculate response time
            $responseTime = '-';
            if ($offering->response_date && $offering->offering_date) {
                $responseTime = $offering->response_date->diffInDays($offering->offering_date);
            } elseif ($offering->offering_date) {
                $responseTime = now()->diffInDays($offering->offering_date);
            }

            $rows[] = [
                'session_id' => $session->id,
                'request_id' => $fptk->id,
                'request_no' => $fptk->request_number,
                'department' => $fptk->department ? $fptk->department->department_name : '-',
                'position' => $fptk->position ? $fptk->position->position_name : '-',
                'project' => $fptk->project ? $fptk->project->project_name : '-',
                'candidate_name' => $candidate->fullname,
                'candidate_number' => $candidate->candidate_number,
                'offering_date' => $offering->offering_date ? $offering->offering_date->format('d/m/Y') : '-',
                'offering_date_sort' => $offering->offering_date ? $offering->offering_date->format('Y-m-d') : '1900-01-01',
                'response_date' => $offering->response_date ? $offering->response_date->format('d/m/Y') : '-',
                'response_date_sort' => $offering->response_date ? $offering->response_date->format('Y-m-d') : '1900-01-01',
                'response_time' => $responseTime,
                'response' => ucfirst($offering->result ?? 'pending'),
                'offering_letter_no' => $offering->offering_letter_number ?? '-',
                'notes' => $offering->notes ?? '-',
            ];
        }
        return $rows;
    }

    public function interviewAssessmentAnalytics(Request $request)
    {
        $title = 'Recruitment Reports';
        $subtitle = 'Interview & Assessment Analytics';
        $date1 = request('date1', '');
        $date2 = request('date2', '');
        $department = request('department');
        $position = request('position');
        $project = request('project');

        $departments = Department::orderBy('department_name')->get();
        $positions = Position::orderBy('position_name')->get();
        $projects = Project::orderBy('project_code')->get();

        return view('recruitment.reports.interview-assessment-analytics', compact(
            'title',
            'subtitle',
            'date1',
            'date2',
            'department',
            'position',
            'project',
            'departments',
            'positions',
            'projects'
        ));
    }

    public function exportInterviewAssessmentAnalytics()
    {
        $date1 = request('date1', '');
        $date2 = request('date2', '');
        $department = request('department');
        $position = request('position');
        $project = request('project');

        $query = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'cvReview',
            'psikotes',
            'tesTeori',
            'interviews'
        ])
            ->whereHas('cvReview', function ($q) {
                $q->whereNotIn('decision', ['fail', 'rejected', 'not fit', 'declined']);
            });

        if ($date1 && $date2) {
            $query->whereBetween('created_at', [$date1 . ' 00:00:00', $date2 . ' 23:59:59']);
        }

        if ($department) {
            $query->whereHas('fptk', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($position) {
            $query->whereHas('fptk', function ($q) use ($position) {
                $q->where('position_id', $position);
            });
        }
        if ($project) {
            $query->whereHas('fptk', function ($q) use ($project) {
                $q->where('project_id', $project);
            });
        }

        $sessions = $query->get();
        $rows = $this->buildInterviewAssessmentData($sessions);

        return Excel::download(new class($rows) implements FromCollection, WithHeadings, WithMapping {
            private $rows;

            public function __construct($rows)
            {
                $this->rows = $rows;
            }

            public function collection()
            {
                return collect($this->rows);
            }

            public function headings(): array
            {
                return [
                    'Request No',
                    'Department',
                    'Position',
                    'Project',
                    'Candidate Name',
                    'Psikotes Result',
                    'Psikotes Score',
                    'Tes Teori Result',
                    'Tes Teori Score',
                    'Interview Type',
                    'Interview Result',
                    'Overall Assessment',
                    'Notes'
                ];
            }

            public function map($row): array
            {
                return [
                    $row['request_no'],
                    $row['department'],
                    $row['position'],
                    $row['project'],
                    $row['candidate_name'],
                    $row['psikotes_result'],
                    $row['psikotes_score'],
                    $row['tes_teori_result'],
                    $row['tes_teori_score'],
                    $row['interview_type'],
                    $row['interview_result'],
                    $row['overall_assessment'],
                    $row['notes']
                ];
            }
        }, 'interview_assessment_analytics_' . date('Y-m-d') . '.xlsx');
    }

    private function buildInterviewAssessmentData($sessions)
    {
        $rows = [];
        foreach ($sessions as $session) {
            $fptk = $session->fptk;
            $candidate = $session->candidate;

            if (!$fptk || !$candidate) {
                continue;
            }

            // Skip candidates who didn't pass CV Review
            if (!$session->cvReview || strtolower($session->cvReview->decision ?? '') === 'fail' || strtolower($session->cvReview->decision ?? '') === 'rejected' || strtolower($session->cvReview->decision ?? '') === 'not fit' || strtolower($session->cvReview->decision ?? '') === 'declined') {
                continue;
            }

            // Psikotes data
            $psikotesResult = '-';
            $psikotesScore = '-';
            if ($session->psikotes) {
                $psikotesResult = ucfirst($session->psikotes->result ?? 'pending');
                $scoreDetails = [];
                if (isset($session->psikotes->online_score) && $session->psikotes->online_score !== null) {
                    $scoreDetails[] = 'Online: ' . number_format($session->psikotes->online_score, 1);
                }
                if (isset($session->psikotes->offline_score) && $session->psikotes->offline_score !== null) {
                    $scoreDetails[] = 'Offline: ' . number_format($session->psikotes->offline_score, 1);
                }
                $psikotesScore = !empty($scoreDetails) ? implode(', ', $scoreDetails) : '-';
            }

            // Tes Teori data
            $tesTeoriResult = '-';
            $tesTeoriScore = '-';
            if ($session->tesTeori) {
                $tesTeoriResult = ucfirst($session->tesTeori->result ?? 'pending');
                $tesTeoriScore = isset($session->tesTeori->score) ? number_format($session->tesTeori->score, 1) : '-';
            }

            // Interview data - separate by type (HR, User, Trainer)
            $interviewHr = '-';
            $interviewUser = '-';
            $interviewTrainer = '-';
            $interviewResult = '-'; // Combined for backward compatibility

            if ($session->interviews && is_object($session->interviews) && method_exists($session->interviews, 'count') && $session->interviews->count() > 0) {
                $interviewTypes = [];
                $interviewResults = [];

                foreach ($session->interviews as $interview) {
                    if (isset($interview->type) && isset($interview->result)) {
                        $result = ucfirst($interview->result);

                        switch ($interview->type) {
                            case 'hr':
                                $interviewHr = $result;
                                break;
                            case 'user':
                                $interviewUser = $result;
                                break;
                            case 'trainer':
                                $interviewTrainer = $result;
                                break;
                        }

                        $interviewTypes[] = ucfirst($interview->type);
                        $interviewResults[] = $result;
                    }
                }

                // Build combined result for backward compatibility
                $type = !empty($interviewTypes) ? implode(', ', array_unique($interviewTypes)) : '';
                $result = !empty($interviewResults) ? implode(', ', array_unique($interviewResults)) : '';

                if ($type && $result) {
                    $interviewResult = $type . ' - ' . $result;
                } elseif ($type) {
                    $interviewResult = $type;
                } elseif ($result) {
                    $interviewResult = $result;
                }
            }

            // Calculate overall assessment (excluding CV Review since all candidates passed)
            $overallAssessment = $this->calculateOverallAssessment($psikotesResult, $tesTeoriResult, $interviewResult);

            $rows[] = [
                'session_id' => $session->id,
                'request_id' => $fptk->id,
                'request_no' => $fptk->request_number,
                'department' => $fptk->department ? $fptk->department->department_name : '-',
                'position' => $fptk->position ? $fptk->position->position_name : '-',
                'project' => $fptk->project ? $fptk->project->project_name : '-',
                'candidate_name' => $candidate->fullname,
                'candidate_number' => $candidate->candidate_number,
                'psikotes_result' => $psikotesResult,
                'psikotes_score' => $psikotesScore,
                'tes_teori_result' => $tesTeoriResult,
                'tes_teori_score' => $tesTeoriScore,
                'interview_hr' => $interviewHr,
                'interview_user' => $interviewUser,
                'interview_trainer' => $interviewTrainer,
                'interview_type' => $interviewResult, // Backward compatibility
                'interview_result' => $interviewResult, // Backward compatibility
                'overall_assessment' => $overallAssessment,
                'notes' => $this->buildAssessmentNotes($session)
            ];
        }
        return $rows;
    }

    private function calculateOverallAssessment($psikotes, $tesTeori, $interviewResult)
    {
        // Extract result part from combined data (remove score/type info in parentheses)
        $psikotesResult = preg_replace('/\s*\([^)]*\)/', '', $psikotes);
        $tesTeoriResult = preg_replace('/\s*\([^)]*\)/', '', $tesTeori);

        // Extract interview type and result from combined string
        $interviewType = '';
        $interviewResultOnly = '';

        if (strpos($interviewResult, ' - ') !== false) {
            list($interviewType, $interviewResultOnly) = explode(' - ', $interviewResult, 2);
        } elseif (strpos($interviewResult, 'HR') !== false || strpos($interviewResult, 'User') !== false || strpos($interviewResult, 'Trainer') !== false) {
            $interviewType = $interviewResult;
            $interviewResultOnly = '';
        } else {
            $interviewResultOnly = $interviewResult;
        }

        // Convert results to numerical scores
        $psikotesScore = $this->convertResultToScore($psikotesResult);
        $teoriScore = $this->convertResultToScore($tesTeoriResult);

        // Parse interview results for HR, User, and Trainer
        $hrScore = 0;
        $userScore = 0;
        $trainerScore = 0;

        if ($interviewType && $interviewResultOnly) {
            // Handle format: "HR, User, Trainer - Pass, Pass, Pass"
            $types = array_map('trim', explode(',', $interviewType));
            $results = array_map('trim', explode(',', $interviewResultOnly));

            for ($i = 0; $i < count($types) && $i < count($results); $i++) {
                $type = $types[$i];
                $result = $results[$i];

                if (strpos($type, 'HR') !== false || strpos($type, 'hr') !== false || strpos($type, 'Hr') !== false) {
                    $hrScore = $this->convertResultToScore($result);
                }
                if (strpos($type, 'User') !== false || strpos($type, 'user') !== false) {
                    $userScore = $this->convertResultToScore($result);
                }
                if (strpos($type, 'Trainer') !== false || strpos($type, 'trainer') !== false) {
                    $trainerScore = $this->convertResultToScore($result);
                }
            }
        } elseif ($interviewResultOnly && !$interviewType) {
            // If no type specified, assume it's a general interview result
            $hrScore = $this->convertResultToScore($interviewResultOnly);
        }

        // Calculate total score
        $totalScore = $psikotesScore + $teoriScore + $hrScore + $userScore + $trainerScore;

        // Apply scoring rules based on the correct assessment logic
        if ($psikotesScore === 0) {
            return 'Poor'; // Psikotes fail = Poor (process stops)
        } elseif ($psikotesScore === 1) {
            return 'Average'; // Psikotes pending = all pending = Average
        } elseif ($psikotesScore === 2) {
            // Psikotes pass - check Tes Teori status
            if ($teoriScore === 0) {
                return 'Poor'; // Tes Teori fail = process stops = Poor
            } elseif ($teoriScore === 1) {
                return 'Average'; // Tes Teori pending = all interviews pending = Average
            } elseif ($teoriScore === 2) {
                // Tes Teori pass - all interviews can be 2/1/0 independently
                // But ensure no more than 1 interview can fail (0) for Tes Teori = 2
                $failCount = 0;
                if ($hrScore === 0) $failCount++;
                if ($userScore === 0) $failCount++;
                if ($trainerScore === 0) $failCount++;

                if ($failCount > 1) {
                    // If more than 1 interview fails, adjust to pending (1) to follow the rule
                    if ($hrScore === 0 && $failCount > 1) $hrScore = 1;
                    if ($userScore === 0 && $failCount > 1) $userScore = 1;
                    if ($trainerScore === 0 && $failCount > 1) $trainerScore = 1;

                    // Recalculate total score
                    $totalScore = $psikotesScore + $teoriScore + $hrScore + $userScore + $trainerScore;
                }

                if ($totalScore >= 9) {
                    return 'Excellent';
                } elseif ($totalScore >= 7) {
                    return 'Very Good';
                } elseif ($totalScore >= 5) {
                    return 'Good';
                } elseif ($totalScore >= 4) {
                    return 'Average';
                } else {
                    return 'Poor';
                }
            } else {
                // Tes Teori NA (-) - only HR & User required, Trainer = NA
                // Ensure no more than 1 interview can fail (0) for Tes Teori = NA
                $failCount = 0;
                if ($hrScore === 0) $failCount++;
                if ($userScore === 0) $failCount++;

                if ($failCount > 1) {
                    // If more than 1 interview fails, adjust to pending (1) to follow the rule
                    if ($hrScore === 0 && $failCount > 1) $hrScore = 1;
                    if ($userScore === 0 && $failCount > 1) $userScore = 1;
                }

                $hrUserScore = $hrScore + $userScore;
                if ($hrUserScore >= 6) {
                    return 'Good';
                } elseif ($hrUserScore >= 4) {
                    return 'Good';
                } elseif ($hrUserScore >= 2) {
                    return 'Average';
                } else {
                    return 'Poor';
                }
            }
        } else {
            return 'Poor'; // Default fallback
        }
    }

    private function convertResultToScore($result)
    {
        if ($result === '-' || $result === 'NA' || $result === 'Not Applicable') {
            return -1; // Not applicable (special case)
        }

        if ($result === 'Pending') {
            return 1; // Pending
        }

        $resultLower = strtolower($result);

        if (in_array($resultLower, ['pass', 'accepted', 'approved', 'fit', 'recommended'])) {
            return 2; // Pass/Recommended
        } elseif (in_array($resultLower, ['pending', 'in progress', 'average'])) {
            return 1; // Pending/Average
        } else {
            return 0; // Fail/Not recommended
        }
    }



    private function buildAssessmentNotes($session)
    {
        $notes = [];

        if ($session->psikotes && !empty($session->psikotes->notes)) {
            $notes[] = 'Psikotes: ' . $session->psikotes->notes;
        }
        if ($session->tesTeori && !empty($session->tesTeori->notes)) {
            $notes[] = 'Teori: ' . $session->tesTeori->notes;
        }
        if ($session->interviews && $session->interviews->count() > 0) {
            foreach ($session->interviews as $interview) {
                if (!empty($interview->notes)) {
                    $notes[] = 'Interview: ' . $interview->notes;
                }
            }
        }

        return !empty($notes) ? implode(' | ', $notes) : '-';
    }

    public function staleCandidates(Request $request)
    {
        $title = 'Stale Candidates';

        // Get filter options
        $departments = Department::orderBy('department_name')->get();
        $positions = Position::orderBy('position_name')->get();
        $projects = Project::orderBy('project_name')->get();

        return view('recruitment.reports.stale-candidates', compact('departments', 'positions', 'projects', 'title'));
    }

    public function staleCandidatesData(Request $request)
    {
        $query = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'fptk.approval_plans.approver',
            'cvReview',
            'psikotes',
            'tesTeori',
            'interviews',
            'offering',
            'mcu',
            'hiring',
            'onboarding',
            'candidate'
        ])
            ->where('status', 'in_process');

        // Apply filters
        if ($request->filled('date1') && $request->filled('date2')) {
            $query->whereBetween('created_at', [$request->date1, $request->date2]);
        }

        if ($request->filled('department')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        if ($request->filled('position')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('position_id', $request->position);
            });
        }

        if ($request->filled('project')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('project_id', $request->project);
            });
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Apply search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('fptk', function ($fptkQuery) use ($searchValue) {
                    $fptkQuery->where('request_number', 'like', "%{$searchValue}%")
                        ->orWhereHas('department', function ($deptQuery) use ($searchValue) {
                            $deptQuery->where('department_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('position', function ($posQuery) use ($searchValue) {
                            $posQuery->where('position_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('project', function ($projQuery) use ($searchValue) {
                            $projQuery->where('project_name', 'like', "%{$searchValue}%");
                        });
                })
                    ->orWhereHas('candidate', function ($candidateQuery) use ($searchValue) {
                        $candidateQuery->where('fullname', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = [
            'request_no',
            'department',
            'position',
            'project',
            'candidate_name',
            'current_stage',
            'last_activity_date',
            'days_since_last_activity',
            'days_in_current_stage',
            'status',
            'notes'
        ];

        if (isset($columns[$orderColumn])) {
            $column = $columns[$orderColumn];
            if ($column === 'days_since_last_activity') {
                $query->orderBy('created_at', $orderDir);
            } else {
                $query->orderBy($column, $orderDir);
            }
        }

        // Get filtered count
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $sessions = $query->skip($start)->take($length)->get();

        // Build data
        $data = [];
        foreach ($sessions as $session) {
            $currentStage = $this->getCurrentStage($session);
            $lastActivity = $this->getLastActivity($session);
            $daysSinceLastActivity = $lastActivity ? now()->diffInDays($lastActivity) : 0;
            $daysInCurrentStage = $this->getDaysInCurrentStage($session, $currentStage);

            if ($this->isCandidateCompletedOrFailed($session)) {
                continue;
            }

            $isStale = $daysSinceLastActivity > 7;

            $data[] = [
                'session_id' => $session->id,
                'request_id' => $session->fptk ? $session->fptk->id : 0,
                'request_no' => $session->fptk ? $session->fptk->request_number : '-',
                'department' => $session->fptk && $session->fptk->department ? $session->fptk->department->department_name : '-',
                'position' => $session->fptk && $session->fptk->position ? $session->fptk->position->position_name : '-',
                'project' => $session->fptk && $session->fptk->project ? $session->fptk->project->project_name : '-',
                'candidate_name' => $session->candidate ? $session->candidate->fullname : '-',
                'current_stage' => $currentStage,
                'last_activity_date' => $lastActivity ? $lastActivity->format('d/m/Y') : '-',
                'days_since_last_activity' => $daysSinceLastActivity,
                'days_in_current_stage' => $daysInCurrentStage,
                'status' => $isStale ? 'Stale' : 'Active',
                'notes' => $this->buildStaleCandidatesNotes($session, $currentStage)
            ];
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function agingData(Request $request)
    {
        $query = \App\Models\RecruitmentRequest::with([
            'department',
            'position',
            'project',
            'createdBy',
            'approval_plans.approver'
        ]);

        // Apply filters
        if ($request->filled('date1') && $request->filled('date2')) {
            $query->whereBetween('created_at', [$request->date1, $request->date2]);
        }
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }
        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Apply search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('request_number', 'like', "%{$searchValue}%")
                    ->orWhereHas('department', function ($deptQuery) use ($searchValue) {
                        $deptQuery->where('department_name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('position', function ($posQuery) use ($searchValue) {
                        $posQuery->where('position_name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('project', function ($projQuery) use ($searchValue) {
                        $projQuery->where('project_name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('createdBy', function ($userQuery) use ($searchValue) {
                        $userQuery->where('name', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = [
            'request_number',
            'department',
            'position',
            'project',
            'requested_by',
            'requested_at',
            'status',
            'days_open',
            'latest_approval',
            'approved_at',
            'days_to_approve',
            'remarks'
        ];

        if (isset($columns[$orderColumn])) {
            $column = $columns[$orderColumn];
            if ($column === 'requested_at') {
                $query->orderBy('created_at', $orderDir);
            } elseif ($column === 'approved_at') {
                // For approved_at, we need to join with approval_plans
                $query->leftJoin('approval_plans', function ($join) {
                    $join->on('recruitment_requests.id', '=', 'approval_plans.document_id')
                        ->where('approval_plans.document_type', '=', 'recruitment_request')
                        ->where('approval_plans.status', '=', 1);
                });
                $query->orderBy('approval_plans.updated_at', $orderDir);
            } elseif ($column === 'request_number') {
                $query->orderBy('request_number', $orderDir);
            } else {
                $query->orderBy($column, $orderDir);
            }
        }

        // Get filtered count
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $requests = $query->skip($start)->take($length)->get();

        // Build data
        $data = [];
        foreach ($requests as $recruitmentRequest) {
            $daysOpen = now()->diffInDays($recruitmentRequest->created_at);
            $latestApproval = null;
            $approvedAt = null;
            $daysToApprove = null;
            $latestApprovalName = '-';
            $approvalRemarks = '-';

            if ($recruitmentRequest->approval_plans && $recruitmentRequest->approval_plans->count() > 0) {
                $approvedPlans = $recruitmentRequest->approval_plans->where('status', 1);
                if ($approvedPlans->count() > 0) {
                    // Get the latest approval by approval_order (step) instead of updated_at
                    $latestApproval = $approvedPlans->sortByDesc('approval_order')->first();
                    $approvedAt = $latestApproval->updated_at ? $latestApproval->updated_at->format('d/m/Y H:i') : '-';

                    // Calculate days to approve: from request creation to the LAST approval step completion
                    $daysToApprove = $latestApproval->updated_at ? $recruitmentRequest->created_at->diffInDays($latestApproval->updated_at) : null;

                    $latestApprovalName = $latestApproval->approver ? $latestApproval->approver->name : '-';
                    $approvalRemarks = $latestApproval->remarks ?: '-';
                }
            }

            // Get conditional approval metrics
            $approvalMetrics = $this->calculateConditionalApprovalMetrics($recruitmentRequest);
            $slaTarget = $approvalMetrics['sla_target'];
            $slaStatus = '-';
            $slaClass = '';
            $slaDaysRemaining = null;

            if ($daysToApprove !== null) {
                // Calculate days from approval completion to SLA target
                $approvalCompletionDate = $latestApproval->updated_at;
                $slaDeadline = $approvalCompletionDate->addDays($slaTarget);
                $currentDate = now();

                if ($currentDate <= $slaDeadline) {
                    $slaStatus = 'Active';
                    $slaClass = 'badge-success';
                    $slaDaysRemaining = $currentDate->diffInDays($slaDeadline);
                } else {
                    $slaStatus = 'Overdue';
                    $slaClass = 'badge-danger';
                    $slaDaysRemaining = -$slaDeadline->diffInDays($currentDate); // Negative value for overdue
                }
            } elseif ($recruitmentRequest->status === 'submitted') {
                $slaStatus = 'Pending Approval';
                $slaClass = 'badge-warning';
            } elseif (in_array($recruitmentRequest->status, ['approved', 'rejected'])) {
                // For legacy FPTK without approval plans, use request creation date
                $slaDeadline = $recruitmentRequest->created_at->addDays($slaTarget);
                $currentDate = now();

                if ($currentDate <= $slaDeadline) {
                    $slaStatus = 'Active';
                    $slaClass = 'badge-success';
                    $slaDaysRemaining = $currentDate->diffInDays($slaDeadline);
                } else {
                    $slaStatus = 'Overdue';
                    $slaClass = 'badge-danger';
                    $slaDaysRemaining = -$slaDeadline->diffInDays($currentDate); // Negative value for overdue
                }
            }


            $data[] = [
                'request_id' => $recruitmentRequest->id,
                'request_no' => $recruitmentRequest->request_number,
                'department' => $recruitmentRequest->department ? $recruitmentRequest->department->department_name : '-',
                'position' => $recruitmentRequest->position ? $recruitmentRequest->position->position_name : '-',
                'project' => $recruitmentRequest->project ? $recruitmentRequest->project->project_name : '-',
                'requested_by' => $recruitmentRequest->createdBy ? $recruitmentRequest->createdBy->name : '-',
                'requested_at' => $recruitmentRequest->created_at->format('d/m/Y H:i'),
                'status' => ucfirst($recruitmentRequest->status),
                'days_open' => $daysOpen,
                'latest_approval' => $latestApprovalName,
                'approved_at' => $approvedAt,
                'days_to_approve' => $daysToApprove !== null ? $daysToApprove : '-',
                'sla_target' => $slaTarget,
                'sla_status' => $slaStatus,
                'sla_class' => $slaClass,
                'sla_days_remaining' => $slaDaysRemaining,
                'remarks' => $approvalRemarks,
            ];
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function timeToHireData(Request $request)
    {
        $metricType = $request->get('metric_type', 'candidate');

        if ($metricType === 'fptk') {
            return $this->getTimeToFillData($request);
        } else {
            return $this->getTimeToHirePerCandidateData($request);
        }
    }

    /**
     * Time to Hire per Candidate - Waktu dari kandidat masuk proses hingga di-hire
     */
    private function getTimeToHirePerCandidateData(Request $request)
    {
        $query = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'fptk.approval_plans.approver',
            'hiring',
            'candidate'
        ])
            ->whereIn('status', ['in_process', 'hired'])
            ->whereNotNull('fptk_id')
            ->whereNotNull('candidate_id');

        // Apply filters
        if ($request->filled('date1') && $request->filled('date2')) {
            $query->whereBetween('created_at', [$request->date1 . ' 00:00:00', $request->date2 . ' 23:59:59']);
        }

        if ($request->filled('department')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }
        if ($request->filled('position')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('position_id', $request->position);
            });
        }
        if ($request->filled('project')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('project_id', $request->project);
            });
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Apply search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('fptk', function ($fptkQuery) use ($searchValue) {
                    $fptkQuery->where('request_number', 'like', "%{$searchValue}%")
                        ->orWhereHas('department', function ($deptQuery) use ($searchValue) {
                            $deptQuery->where('department_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('position', function ($posQuery) use ($searchValue) {
                            $posQuery->where('position_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('project', function ($projQuery) use ($searchValue) {
                            $projQuery->where('project_name', 'like', "%{$searchValue}%");
                        });
                })
                    ->orWhereHas('candidate', function ($candidateQuery) use ($searchValue) {
                        $candidateQuery->where('fullname', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = [
            'candidate_name',
            'request_no',
            'department',
            'position',
            'project',
            'session_created_at',
            'hiring_date',
            'time_to_hire_days',
            'approval_days',
            'recruitment_days',
            'employment_type',
            'status'
        ];

        if (isset($columns[$orderColumn])) {
            $column = $columns[$orderColumn];
            if ($column === 'session_created_at') {
                $query->orderBy('created_at', $orderDir);
            } elseif ($column === 'candidate_name') {
                // Order by candidate name through relationship - use subquery
                $query->orderBy(DB::raw('(SELECT fullname FROM recruitment_candidates WHERE recruitment_candidates.id = recruitment_sessions.candidate_id)'), $orderDir);
            } elseif ($column === 'hiring_date') {
                // Order by hiring date through relationship - use subquery
                $query->orderBy(DB::raw('(SELECT created_at FROM recruitment_hiring WHERE recruitment_hiring.session_id = recruitment_sessions.id)'), $orderDir);
            } elseif ($column === 'request_no') {
                // Order by request number through relationship - use subquery
                $query->orderBy(DB::raw('(SELECT request_number FROM recruitment_requests WHERE recruitment_requests.id = recruitment_sessions.fptk_id)'), $orderDir);
            } elseif ($column === 'department') {
                // Order by department through relationship - use subquery
                $query->orderBy(DB::raw('(SELECT d.department_name FROM recruitment_requests r JOIN departments d ON r.department_id = d.id WHERE r.id = recruitment_sessions.fptk_id)'), $orderDir);
            } elseif ($column === 'position') {
                // Order by position through relationship - use subquery
                $query->orderBy(DB::raw('(SELECT p.position_name FROM recruitment_requests r JOIN positions p ON r.position_id = p.id WHERE r.id = recruitment_sessions.fptk_id)'), $orderDir);
            } elseif ($column === 'project') {
                // Order by project through relationship - use subquery
                $query->orderBy(DB::raw('(SELECT pr.project_name FROM recruitment_requests r JOIN projects pr ON r.project_id = pr.id WHERE r.id = recruitment_sessions.fptk_id)'), $orderDir);
            } else {
                $query->orderBy($column, $orderDir);
            }
        }

        // Get filtered count
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $sessions = $query->skip($start)->take($length)->get();

        // Build data
        $data = [];
        foreach ($sessions as $session) {
            if (!$session->candidate || !$session->fptk) {
                continue;
            }

            // Time to Hire per Candidate: Session created to current status
            $timeToHireDays = 0;
            $hiringDate = null;

            if ($session->status === 'hired' && $session->hiring) {
                // If hired, calculate from session created to hiring date
                $hiringDate = $session->hiring->created_at;
                $timeToHireDays = $hiringDate->diffInDays($session->created_at);
            } else {
                // If in_process, calculate from session created to now
                $timeToHireDays = now()->diffInDays($session->created_at);
            }

            // Approval days: FPTK created to approval completion
            $approvalDays = 0;
            $latestApproval = null;
            if ($session->fptk && $session->fptk->approval_plans && $session->fptk->approval_plans->count() > 0) {
                $approvedPlans = $session->fptk->approval_plans->where('status', 1);
                if ($approvedPlans->count() > 0) {
                    $latestApproval = $approvedPlans->sortByDesc('updated_at')->first();
                    if ($latestApproval->updated_at) {
                        $approvalDays = $latestApproval->updated_at->diffInDays($session->fptk->created_at);
                    }
                }
            }

            // Recruitment days: Approval completion to current status
            $recruitmentDays = 0;
            if ($latestApproval && $latestApproval->updated_at) {
                if ($hiringDate) {
                    $recruitmentDays = $hiringDate->diffInDays($latestApproval->updated_at);
                } else {
                    $recruitmentDays = now()->diffInDays($latestApproval->updated_at);
                }
            }

            $data[] = [
                'session_id' => $session->id,
                'request_id' => $session->fptk ? $session->fptk->id : 0,
                'candidate_name' => $session->candidate->fullname,
                'candidate_number' => $session->candidate->candidate_number,
                'request_no' => $session->fptk ? $session->fptk->request_number : '-',
                'department' => $session->fptk && $session->fptk->department ? $session->fptk->department->department_name : '-',
                'position' => $session->fptk && $session->fptk->position ? $session->fptk->position->position_name : '-',
                'project' => $session->fptk && $session->fptk->project ? $session->fptk->project->project_name : '-',
                'session_created_at' => $session->created_at->format('Y-m-d H:i:s'),
                'hiring_date' => $hiringDate ? $hiringDate->format('Y-m-d') : ($session->status === 'in_process' ? 'In Progress' : '-'),
                'time_to_hire_days' => $timeToHireDays,
                'approval_days' => $approvalDays,
                'recruitment_days' => $recruitmentDays,
                'employment_type' => $session->fptk ? ucfirst($session->fptk->employment_type ?? 'regular') : 'Regular',
                'status' => ucfirst($session->status),
                'latest_approval' => $latestApproval && $latestApproval->approver ? $latestApproval->approver->name : '-',
                'remarks' => $latestApproval ? ($latestApproval->remarks ?: '-') : '-',
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Time to Fill per FPTK - Waktu dari FPTK dibuat hingga posisi terisi
     */
    private function getTimeToFillData(Request $request)
    {
        $query = RecruitmentRequest::with([
            'department',
            'position',
            'project',
            'createdBy',
            'approval_plans.approver',
            'sessions' => function ($q) {
                $q->whereIn('status', ['in_process', 'hired'])
                    ->whereNotNull('candidate_id');
            },
            'sessions.hiring',
            'sessions.candidate'
        ])
            ->whereHas('sessions', function ($q) {
                $q->whereIn('status', ['in_process', 'hired'])
                    ->whereNotNull('candidate_id');
            });

        // Apply filters
        if ($request->filled('date1') && $request->filled('date2')) {
            $query->whereBetween('created_at', [$request->date1 . ' 00:00:00', $request->date2 . ' 23:59:59']);
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }
        if ($request->filled('position')) {
            $query->where('position_id', $request->position);
        }
        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Apply search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('request_number', 'like', "%{$searchValue}%")
                    ->orWhereHas('department', function ($deptQuery) use ($searchValue) {
                        $deptQuery->where('department_name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('position', function ($posQuery) use ($searchValue) {
                        $posQuery->where('position_name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('project', function ($projQuery) use ($searchValue) {
                        $projQuery->where('project_name', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = [
            'request_number',
            'department',
            'position',
            'project',
            'fptk_created_at',
            'first_hiring_date',
            'time_to_fill_days',
            'approval_days',
            'recruitment_days',
            'hired_count',
            'required_qty',
            'fill_rate',
            'employment_type',
            'status'
        ];

        if (isset($columns[$orderColumn])) {
            $column = $columns[$orderColumn];
            if ($column === 'fptk_created_at') {
                $query->orderBy('created_at', $orderDir);
            } elseif ($column === 'request_number') {
                $query->orderBy('request_number', $orderDir);
            } elseif ($column === 'department') {
                $query->orderBy('department_id', $orderDir);
            } elseif ($column === 'position') {
                $query->orderBy('position_id', $orderDir);
            } elseif ($column === 'project') {
                $query->orderBy('project_id', $orderDir);
            } elseif ($column === 'employment_type') {
                $query->orderBy('employment_type', $orderDir);
            } elseif ($column === 'status') {
                $query->orderBy('status', $orderDir);
            } else {
                // For calculated fields, order by created_at as fallback
                $query->orderBy('created_at', $orderDir);
            }
        }

        // Get filtered count
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $fptks = $query->skip($start)->take($length)->get();

        // Build data
        $data = [];
        foreach ($fptks as $fptk) {
            // Get sessions with valid status
            $validSessions = $fptk->sessions->whereIn('status', ['in_process', 'hired'])
                ->whereNotNull('candidate_id');

            if ($validSessions->isEmpty()) {
                continue;
            }

            // Get first hiring date from hired sessions
            $hiredSessions = $validSessions->where('status', 'hired')->where('hiring', '!=', null);
            $firstHiringDate = null;
            $timeToFillDays = 0;

            if ($hiredSessions->isNotEmpty()) {
                $firstHiringDate = $hiredSessions->min(function ($session) {
                    return $session->hiring->created_at;
                });
                $timeToFillDays = $firstHiringDate->diffInDays($fptk->created_at);
            } else {
                // If no hired sessions, calculate from FPTK created to now
                $timeToFillDays = now()->diffInDays($fptk->created_at);
            }

            // Approval days: FPTK created to approval completion
            $approvalDays = 0;
            $latestApproval = null;
            if ($fptk->approval_plans && $fptk->approval_plans->count() > 0) {
                $approvedPlans = $fptk->approval_plans->where('status', 1);
                if ($approvedPlans->count() > 0) {
                    $latestApproval = $approvedPlans->sortByDesc('updated_at')->first();
                    if ($latestApproval->updated_at) {
                        $approvalDays = $latestApproval->updated_at->diffInDays($fptk->created_at);
                    }
                }
            }

            // Recruitment days: Approval completion to current status
            $recruitmentDays = 0;
            if ($latestApproval && $latestApproval->updated_at) {
                if ($firstHiringDate) {
                    $recruitmentDays = $firstHiringDate->diffInDays($latestApproval->updated_at);
                } else {
                    $recruitmentDays = now()->diffInDays($latestApproval->updated_at);
                }
            }

            // Calculate fill rate
            $hiredCount = $hiredSessions->count();
            $inProcessCount = $validSessions->where('status', 'in_process')->count();
            $totalSessions = $validSessions->count();
            $requiredQty = $fptk->required_qty ?? 1;
            $fillRate = $requiredQty > 0 ? round(($hiredCount / $requiredQty) * 100, 1) : 0;

            $data[] = [
                'fptk_id' => $fptk->id,
                'request_no' => $fptk->request_number,
                'department' => $fptk->department ? $fptk->department->department_name : '-',
                'position' => $fptk->position ? $fptk->position->position_name : '-',
                'project' => $fptk->project ? $fptk->project->project_name : '-',
                'fptk_created_at' => $fptk->created_at->format('Y-m-d H:i:s'),
                'first_hiring_date' => $firstHiringDate ? $firstHiringDate->format('Y-m-d') : 'In Progress',
                'time_to_fill_days' => $timeToFillDays,
                'approval_days' => $approvalDays,
                'recruitment_days' => $recruitmentDays,
                'hired_count' => $hiredCount,
                'required_qty' => $requiredQty,
                'fill_rate' => $fillRate . '%',
                'employment_type' => ucfirst($fptk->employment_type ?? 'regular'),
                'status' => ucfirst($fptk->status),
                'latest_approval' => $latestApproval && $latestApproval->approver ? $latestApproval->approver->name : '-',
                'remarks' => $latestApproval ? ($latestApproval->remarks ?: '-') : '-',
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function offerAcceptanceRateData(Request $request)
    {
        $query = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'offering'
        ])
            ->whereHas('offering');

        if ($request->filled('date1') && $request->filled('date2')) {
            $query->whereBetween('created_at', [$request->date1 . ' 00:00:00', $request->date2 . ' 23:59:59']);
        }

        if ($request->filled('department')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }
        if ($request->filled('position')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('position_id', $request->position);
            });
        }
        if ($request->filled('project')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('project_id', $request->project);
            });
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Apply search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('fptk', function ($fptkQuery) use ($searchValue) {
                    $fptkQuery->where('request_number', 'like', "%{$searchValue}%")
                        ->orWhereHas('department', function ($deptQuery) use ($searchValue) {
                            $deptQuery->where('department_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('position', function ($posQuery) use ($searchValue) {
                            $posQuery->where('position_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('project', function ($projQuery) use ($searchValue) {
                            $projQuery->where('project_name', 'like', "%{$searchValue}%");
                        });
                })
                    ->orWhereHas('candidate', function ($candidateQuery) use ($searchValue) {
                        $candidateQuery->where('fullname', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = [
            'request_no',
            'department',
            'position',
            'project',
            'candidate_name',
            'offering_date',
            'response_date',
            'response_time',
            'response',
            'offering_letter_no',
            'notes'
        ];

        if (isset($columns[$orderColumn])) {
            $column = $columns[$orderColumn];
            if ($column === 'offering_date') {
                $query->orderBy('created_at', $orderDir);
            } else {
                $query->orderBy($column, $orderDir);
            }
        }

        // Get filtered count
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $sessions = $query->skip($start)->take($length)->get();

        // Build data
        $data = [];
        foreach ($sessions as $session) {
            if (!$session->offering) {
                continue;
            }

            $offering = $session->offering;
            $fptk = $session->fptk;
            $candidate = $session->candidate;

            if (!$fptk || !$candidate) {
                continue;
            }

            $responseTime = '-';
            if ($offering->response_date && $offering->offering_date) {
                $responseTime = $offering->response_date->diffInDays($offering->offering_date);
            } elseif ($offering->offering_date) {
                $responseTime = now()->diffInDays($offering->offering_date);
            }

            $data[] = [
                'session_id' => $session->id,
                'request_id' => $fptk->id,
                'request_no' => $fptk->request_number,
                'department' => $fptk->department ? $fptk->department->department_name : '-',
                'position' => $fptk->position ? $fptk->position->position_name : '-',
                'project' => $fptk->project ? $fptk->project->project_name : '-',
                'candidate_name' => $candidate->fullname,
                'offering_date' => $offering->offering_date ? $offering->offering_date->format('d/m/Y') : '-',
                'response_date' => $offering->response_date ? $offering->response_date->format('d/m/Y') : '-',
                'response_time' => $responseTime,
                'response' => ucfirst($offering->result ?? 'pending'),
                'offering_letter_no' => $offering->offering_letter_number ?? '-',
                'notes' => $offering->notes ?? '-',
            ];
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function interviewAssessmentAnalyticsData(Request $request)
    {
        $query = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'cvReview',
            'psikotes',
            'tesTeori',
            'interviews'
        ])
            ->whereHas('cvReview', function ($q) {
                $q->whereNotIn('decision', ['fail', 'rejected', 'not fit', 'declined']);
            });

        if ($request->filled('date1') && $request->filled('date2')) {
            $query->whereBetween('created_at', [$request->date1 . ' 00:00:00', $request->date2 . ' 23:59:59']);
        }

        if ($request->filled('department')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }
        if ($request->filled('position')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('position_id', $request->position);
            });
        }
        if ($request->filled('project')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('project_id', $request->project);
            });
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Apply search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('fptk', function ($fptkQuery) use ($searchValue) {
                    $fptkQuery->where('request_number', 'like', "%{$searchValue}%")
                        ->orWhereHas('department', function ($deptQuery) use ($searchValue) {
                            $deptQuery->where('department_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('position', function ($posQuery) use ($searchValue) {
                            $posQuery->where('position_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('project', function ($projQuery) use ($searchValue) {
                            $projQuery->where('project_name', 'like', "%{$searchValue}%");
                        });
                })
                    ->orWhereHas('candidate', function ($candidateQuery) use ($searchValue) {
                        $candidateQuery->where('fullname', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');

        $columns = [
            'request_no',
            'department',
            'position',
            'project',
            'candidate_name',
            'psikotes_result',
            'tes_teori_result',
            'interview_result',
            'overall_assessment',
            'notes'
        ];

        if (isset($columns[$orderColumn])) {
            $column = $columns[$orderColumn];
            if ($column === 'request_no') {
                $query->orderBy('created_at', $orderDir);
            } else {
                $query->orderBy($column, $orderDir);
            }
        }

        // Get filtered count
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $sessions = $query->skip($start)->take($length)->get();

        // Build data
        $data = [];
        foreach ($sessions as $session) {
            $fptk = $session->fptk;
            $candidate = $session->candidate;

            if (!$fptk || !$candidate) {
                continue;
            }

            if (!$session->cvReview || strtolower($session->cvReview->decision ?? '') === 'fail' || strtolower($session->cvReview->decision ?? '') === 'rejected' || strtolower($session->cvReview->decision ?? '') === 'not fit' || strtolower($session->cvReview->decision ?? '') === 'declined') {
                continue;
            }

            // Psikotes data - combine result and score
            $psikotesResult = '-';
            if ($session->psikotes) {
                $result = ucfirst($session->psikotes->result ?? 'pending');
                $scoreDetails = [];
                if (isset($session->psikotes->online_score) && $session->psikotes->online_score !== null) {
                    $scoreDetails[] = 'Online: ' . number_format($session->psikotes->online_score, 1);
                }
                if (isset($session->psikotes->offline_score) && $session->psikotes->offline_score !== null) {
                    $scoreDetails[] = 'Offline: ' . number_format($session->psikotes->offline_score, 1);
                }
                $psikotesResult = $result;
                if (!empty($scoreDetails)) {
                    $psikotesResult .= ' (' . implode(', ', $scoreDetails) . ')';
                }
            }

            // Tes Teori data - combine result and score
            $tesTeoriResult = '-';
            if ($session->tesTeori) {
                $result = ucfirst($session->tesTeori->result ?? 'pending');
                $score = isset($session->tesTeori->score) ? number_format($session->tesTeori->score, 1) : null;
                $tesTeoriResult = $result;
                if ($score) {
                    $tesTeoriResult .= ' (' . $score . ')';
                }
            }

            // Interview data - combined with color coding
            $interviewResult = '-';
            $interviewDisplay = '-';

            if ($session->interviews && is_object($session->interviews) && method_exists($session->interviews, 'count') && $session->interviews->count() > 0) {
                // Group interviews by type
                $interviewData = [];
                foreach ($session->interviews as $interview) {
                    if (isset($interview->type) && isset($interview->result)) {
                        $type = ucfirst($interview->type);
                        $result = ucfirst($interview->result);

                        // Handle different type formats
                        if (strtolower($type) === 'hr') {
                            $interviewData['HR'] = $result;
                        } elseif (strtolower($type) === 'user') {
                            $interviewData['User'] = $result;
                        } elseif (strtolower($type) === 'trainer') {
                            $interviewData['Trainer'] = $result;
                        }
                    }
                }

                // Build the combined interview result string for calculation
                if (!empty($interviewData)) {
                    $types = [];
                    $results = [];
                    $displayItems = [];

                    // Always include HR and User if they exist
                    if (isset($interviewData['HR'])) {
                        $types[] = 'HR';
                        $results[] = $interviewData['HR'];
                        $displayItems[] = 'HR: ' . $interviewData['HR'];
                    }
                    if (isset($interviewData['User'])) {
                        $types[] = 'User';
                        $results[] = $interviewData['User'];
                        $displayItems[] = 'User: ' . $interviewData['User'];
                    }
                    if (isset($interviewData['Trainer'])) {
                        $types[] = 'Trainer';
                        $results[] = $interviewData['Trainer'];
                        $displayItems[] = 'Trainer: ' . $interviewData['Trainer'];
                    }

                    if (!empty($types) && !empty($results)) {
                        $interviewResult = implode(', ', $types) . ' - ' . implode(', ', $results);
                        $interviewDisplay = implode(' | ', $displayItems);
                    }
                }
            }

            // Calculate overall assessment
            $overallAssessment = $this->calculateOverallAssessment($psikotesResult, $tesTeoriResult, $interviewResult);

            $data[] = [
                'session_id' => $session->id,
                'request_id' => $fptk->id,
                'request_no' => $fptk->request_number,
                'department' => $fptk->department ? $fptk->department->department_name : '-',
                'position' => $fptk->position ? $fptk->position->position_name : '-',
                'project' => $fptk->project ? $fptk->project->project_name : '-',
                'candidate_name' => $candidate->fullname,
                'psikotes_result' => $psikotesResult,
                'tes_teori_result' => $tesTeoriResult,
                'interview_result' => $interviewDisplay,
                'overall_assessment' => $overallAssessment,
                'notes' => $this->buildAssessmentNotes($session)
            ];
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function stageDetailData(Request $request, $stage)
    {
        // Map stage names to model classes
        $stageModels = [
            'cv_review' => RecruitmentCvReview::class,
            'psikotes' => RecruitmentPsikotes::class,
            'tes_teori' => RecruitmentTesTeori::class,
            'interview' => RecruitmentInterview::class,
            'offering' => RecruitmentOffering::class,
            'mcu' => RecruitmentMcu::class,
            'mcu_magang_harian' => RecruitmentMcu::class, // Special case for magang/harian MCU
            'hiring' => RecruitmentHiring::class,
            'hiring_magang_harian' => RecruitmentHiring::class, // Special case for magang/harian Hiring
            'onboarding' => RecruitmentOnboarding::class,
        ];

        if (!isset($stageModels[$stage])) {
            return response()->json(['error' => 'Invalid stage'], 400);
        }

        $modelClass = $stageModels[$stage];

        // Get recruitment sessions with filters
        $sessionsQuery = \App\Models\RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'candidate'
        ]);

        // Apply FPTK-based filters
        if ($request->filled('department') || $request->filled('position') || $request->filled('project')) {
            $sessionsQuery->whereHas('fptk', function ($q) use ($request) {
                if ($request->filled('department')) {
                    $q->where('department_id', $request->department);
                }
                if ($request->filled('position')) {
                    $q->where('position_id', $request->position);
                }
                if ($request->filled('project')) {
                    $q->where('project_id', $request->project);
                }
            });
        }

        // Apply date filter
        if ($request->filled('date1') && $request->filled('date2')) {
            $sessionsQuery->whereBetween('created_at', [$request->date1, $request->date2]);
        }

        $sessions = $sessionsQuery->get();

        // Filter sessions based on employment type and stage compatibility
        $filteredSessions = $sessions->filter(function ($session) use ($stage) {
            if (!$session->fptk) return false;

            $employmentType = $session->fptk->employment_type ?? 'regular';

            // Handle special magang/harian stage naming
            if (in_array($stage, ['mcu_magang_harian', 'hiring_magang_harian'])) {
                // Only magang and harian employment types
                return in_array($employmentType, ['magang', 'harian']);
            }

            $expectedStages = $this->getExpectedStagesForEmploymentType($employmentType);

            // Convert stage name to match expected stages format
            $stageMapping = [
                'cv_review' => 'CV Review',
                'psikotes' => 'Psikotes',
                'tes_teori' => 'Tes Teori',
                'interview' => 'Interview',
                'offering' => 'Offering',
                'mcu' => 'MCU',
                'hiring' => 'Hiring',
                'onboarding' => 'Onboarding'
            ];

            $stageName = $stageMapping[$stage] ?? ucfirst(str_replace('_', ' ', $stage));

            // For regular MCU and Hiring, exclude magang/harian
            if (in_array($stage, ['mcu', 'hiring'])) {
                $isRegularEmployment = !in_array($employmentType, ['magang', 'harian']);
                $stageAllowed = in_array($stageName, $expectedStages);
                return $isRegularEmployment && $stageAllowed;
            }

            // Only include sessions where this stage is expected for the employment type
            return in_array($stageName, $expectedStages);
        });

        // Get stage records only for filtered sessions
        $stageQuery = $modelClass::with(['session.fptk.department', 'session.fptk.position', 'session.fptk.project', 'session.candidate'])
            ->whereIn('session_id', $filteredSessions->pluck('id'));

        if ($request->filled('date1') && $request->filled('date2')) {
            $stageQuery->whereBetween('created_at', [$request->date1, $request->date2]);
        }

        // Get total count before pagination
        $totalRecords = $stageQuery->count();

        // Apply search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $stageQuery->where(function ($q) use ($searchValue) {
                $q->whereHas('session.fptk', function ($fptkQuery) use ($searchValue) {
                    $fptkQuery->where('request_number', 'like', "%{$searchValue}%")
                        ->orWhereHas('department', function ($deptQuery) use ($searchValue) {
                            $deptQuery->where('department_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('position', function ($posQuery) use ($searchValue) {
                            $posQuery->where('position_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('project', function ($projQuery) use ($searchValue) {
                            $projQuery->where('project_name', 'like', "%{$searchValue}%");
                        });
                })
                    ->orWhereHas('session.candidate', function ($candidateQuery) use ($searchValue) {
                        $candidateQuery->where('fullname', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = [
            'fptk_number',
            'department',
            'position',
            'project',
            'candidate_name',
            'session_number',
            'stage_date',
            'days_in_stage',
            'result',
            'remarks'
        ];

        if (isset($columns[$orderColumn])) {
            $column = $columns[$orderColumn];
            if ($column === 'stage_date') {
                $stageQuery->orderBy('created_at', $orderDir);
            } elseif ($column === 'fptk_number') {
                // Order by FPTK request_number through relationship
                $stageQuery->orderBy('session.fptk.request_number', $orderDir);
            } elseif ($column === 'department') {
                // Order by department name through relationship
                $stageQuery->orderBy('session.fptk.department.department_name', $orderDir);
            } elseif ($column === 'position') {
                // Order by position name through relationship
                $stageQuery->orderBy('session.fptk.position.position_name', $orderDir);
            } elseif ($column === 'project') {
                // Order by project name through relationship
                $stageQuery->orderBy('session.fptk.project.project_name', $orderDir);
            } elseif ($column === 'candidate_name') {
                // Order by candidate name through relationship
                $stageQuery->orderBy('recruitment_candidates.fullname', $orderDir);
            } else {
                $stageQuery->orderBy($column, $orderDir);
            }
        }

        // Get filtered count
        $filteredRecords = $stageQuery->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $stageRecords = $stageQuery->skip($start)->take($length)->get();

        // Build data
        $data = [];
        foreach ($stageRecords as $record) {
            $session = $record->session;
            if (!$session || !$session->fptk || !$session->candidate) {
                continue;
            }

            $fptk = $session->fptk;
            $candidate = $session->candidate;

            // Calculate days in stage
            $daysInStage = 0;
            if ($record->updated_at && $record->created_at) {
                $daysInStage = $record->updated_at->diffInDays($record->created_at);
            } elseif ($record->created_at) {
                $daysInStage = now()->diffInDays($record->created_at);
            }

            // Get result/status from record
            $result = 'Pending';
            if (isset($record->result)) {
                $result = ucfirst($record->result);
            } elseif (isset($record->status)) {
                $result = ucfirst($record->status);
            } elseif (isset($record->decision)) {
                $result = ucfirst($record->decision);
            }

            // Special handling for hiring and onboarding stages
            if ($stage === 'hiring') {
                if ($result === 'Pending' || $result === 'In Progress') {
                    $result = 'Hired';
                }
            } elseif ($stage === 'onboarding') {
                if ($result === 'Pending' || $result === 'In Progress') {
                    $result = 'Complete';
                }
            }

            // Get interview type for interview stage
            $interviewType = null;
            if ($stage === 'interview' && isset($record->type)) {
                $interviewType = $record->type === 'hr' ? 'HR' : 'User';
            }

            // Build detailed remarks based on stage type
            $detailedRemarks = $this->buildStageRemarks($stage, $record);

            // Get employment type information
            $employmentType = $fptk->employment_type ?? 'regular';
            $expectedStages = $this->getExpectedStagesForEmploymentType($employmentType);

            $data[] = [
                'session_id' => $session->id,
                'fptk_id' => $fptk->id,
                'fptk_number' => $fptk->request_number,
                'department' => $fptk->department ? $fptk->department->department_name : '-',
                'position' => $fptk->position ? $fptk->position->position_name : '-',
                'project' => $fptk->project ? $fptk->project->project_name : '-',
                'candidate_name' => $candidate->fullname,
                'candidate_number' => $candidate->candidate_number,
                'session_number' => $session->session_number,
                'stage_date' => $record->created_at->format('d/m/Y H:i'),
                'days_in_stage' => $daysInStage,
                'result' => $result,
                'interview_type' => $interviewType,
                'remarks' => $detailedRemarks,
                'employment_type' => ucfirst($employmentType),
                'expected_stages' => implode(' → ', $expectedStages),
                'is_magang_harian' => in_array($employmentType, ['magang', 'harian']),
            ];
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    private function getCurrentStage($session)
    {
        // Normalize helper
        $normalize = function ($value) {
            return strtolower(trim((string) $value));
        };

        // Treat these as PASS
        $isPass = function ($value) use ($normalize) {
            return in_array($normalize($value), ['pass', 'fit', 'approved', 'accepted', 'recommended']);
        };

        // 1) TRUST session.current_stage when present
        if (!empty($session->current_stage)) {
            $stage = $session->current_stage; // raw value like 'tes_teori'
            switch ($stage) {
                case 'cv_review':
                    if ($session->cvReview && !$isPass($session->cvReview->decision)) {
                        return 'CV Review';
                    }
                    break;
                case 'psikotes':
                    if ($session->psikotes && !$isPass($session->psikotes->result)) {
                        return 'Psikotes';
                    }
                    break;
                case 'tes_teori':
                    if ($session->tesTeori && !$isPass($session->tesTeori->result)) {
                        return 'Tes Teori';
                    }
                    break;
                case 'interview':
                    if ($session->interviews && is_object($session->interviews) && method_exists($session->interviews, 'count') && $session->interviews->count() > 0) {
                        $hasPendingOrFail = $session->interviews->contains(function ($i) use ($isPass) {
                            return !$isPass($i->result);
                        });
                        if ($hasPendingOrFail) {
                            return 'Interview';
                        }
                    }
                    break;
                case 'offering':
                    if ($session->offering && $normalize($session->offering->response) !== 'accepted') {
                        return 'Offering';
                    }
                    break;
                case 'mcu':
                    if ($session->mcu && !$isPass($session->mcu->result)) {
                        return 'MCU';
                    }
                    break;
                case 'hire':
                    if ($session->hiring && $normalize($session->hiring->result) !== 'hired') {
                        return 'Hiring';
                    }
                    break;
                case 'onboarding':
                    if ($session->onboarding && $normalize($session->onboarding->result) !== 'complete') {
                        return 'Onboarding';
                    }
                    break;
            }
            // If we reach here, fall back to displaying the declared current_stage text
            return ucfirst(str_replace('_', ' ', $stage));
        }

        // 2) Fallback: detect from highest to lowest stage
        if ($session->onboarding && $normalize($session->onboarding->result) !== 'complete') return 'Onboarding';
        if ($session->hiring && $normalize($session->hiring->result) !== 'hired') return 'Hiring';
        if ($session->mcu && !$isPass($session->mcu->result)) return 'MCU';
        if ($session->offering && $normalize($session->offering->response) !== 'accepted') return 'Offering';
        if ($session->interviews && is_object($session->interviews) && method_exists($session->interviews, 'count') && $session->interviews->count() > 0) {
            $hasPendingOrFail = $session->interviews->contains(function ($i) use ($isPass) {
                return !$isPass($i->result);
            });
            if ($hasPendingOrFail) return 'Interview';
        }
        if ($session->tesTeori && !$isPass($session->tesTeori->result)) return 'Tes Teori';
        if ($session->psikotes && !$isPass($session->psikotes->result)) return 'Psikotes';
        if ($session->cvReview && !$isPass($session->cvReview->decision)) return 'CV Review';

        return 'Unknown';
    }

    private function getLastActivity($session)
    {
        $dates = [];

        if ($session->onboarding && $session->onboarding->updated_at) {
            $dates[] = $session->onboarding->updated_at;
        }
        if ($session->hiring && $session->hiring->updated_at) {
            $dates[] = $session->hiring->updated_at;
        }
        if ($session->mcu && $session->mcu->updated_at) {
            $dates[] = $session->mcu->updated_at;
        }
        if ($session->offering && $session->offering->updated_at) {
            $dates[] = $session->offering->updated_at;
        }
        if ($session->interviews && is_object($session->interviews) && method_exists($session->interviews, 'count') && $session->interviews->count() > 0) {
            foreach ($session->interviews as $interview) {
                if ($interview->updated_at) {
                    $dates[] = $interview->updated_at;
                }
            }
        }
        if ($session->tesTeori && $session->tesTeori->updated_at) {
            $dates[] = $session->tesTeori->updated_at;
        }
        if ($session->psikotes && $session->psikotes->updated_at) {
            $dates[] = $session->psikotes->updated_at;
        }
        if ($session->cvReview && $session->cvReview->updated_at) {
            $dates[] = $session->cvReview->updated_at;
        }

        return !empty($dates) ? max($dates) : $session->updated_at;
    }

    private function getDaysInCurrentStage($session, $currentStage)
    {
        $stageDate = null;

        switch ($currentStage) {
            case 'Onboarding':
                $stageDate = $session->onboarding ? $session->onboarding->created_at : null;
                break;
            case 'Hiring':
                $stageDate = $session->hiring ? $session->hiring->created_at : null;
                break;
            case 'MCU':
                $stageDate = $session->mcu ? $session->mcu->created_at : null;
                break;
            case 'Offering':
                $stageDate = $session->offering ? $session->offering->created_at : null;
                break;
            case 'Interview':
                if ($session->interviews && is_object($session->interviews) && method_exists($session->interviews, 'count') && $session->interviews->count() > 0) {
                    $stageDate = $session->interviews->first()->created_at;
                }
                break;
            case 'Tes Teori':
                $stageDate = $session->tesTeori ? $session->tesTeori->created_at : null;
                break;
            case 'Psikotes':
                $stageDate = $session->psikotes ? $session->psikotes->created_at : null;
                break;
            case 'CV Review':
                $stageDate = $session->cvReview ? $session->cvReview->created_at : null;
                break;
        }

        return $stageDate ? now()->diffInDays($stageDate) : 0;
    }

    private function buildStaleCandidatesNotes($session, $currentStage)
    {
        $notes = [];

        switch ($currentStage) {
            case 'CV Review':
                if ($session->cvReview) {
                    $notes[] = "CV Review: " . ($session->cvReview->decision ?? 'No decision');
                }
                break;
            case 'Psikotes':
                if ($session->psikotes) {
                    $notes[] = "Psikotes: " . ($session->psikotes->result ?? 'No result');
                }
                break;
            case 'Tes Teori':
                if ($session->tesTeori) {
                    $notes[] = "Tes Teori: " . ($session->tesTeori->result ?? 'No result');
                }
                break;
            case 'Interview':
                if ($session->interviews && is_object($session->interviews) && method_exists($session->interviews, 'count') && $session->interviews->count() > 0) {
                    foreach ($session->interviews as $interview) {
                        $notes[] = "Interview " . ucfirst($interview->type) . ": " . ($interview->result ?? 'No result');
                    }
                }
                break;
            case 'Offering':
                if ($session->offering) {
                    $notes[] = "Offering: " . ($session->offering->response ?? 'No response');
                }
                break;
            case 'MCU':
                if ($session->mcu) {
                    $notes[] = "MCU: " . ($session->mcu->result ?? 'No result');
                }
                break;
            case 'Hiring':
                if ($session->hiring) {
                    $notes[] = "Hiring: " . ($session->hiring->result ?? 'No result');
                }
                break;
        }

        return implode(' | ', $notes);
    }

    private function isCandidateCompletedOrFailed($session)
    {
        // Check if candidate has completed onboarding
        if ($session->onboarding && $session->onboarding->result === 'complete') {
            return true;
        }

        // Check if candidate has been hired
        if ($session->hiring && $session->hiring->result === 'hired') {
            return true;
        }

        // Check if candidate failed at any stage and was rejected
        if ($session->cvReview && $session->cvReview->decision === 'fail') {
            return true;
        }
        if ($session->psikotes && $session->psikotes->result === 'fail') {
            return true;
        }
        if ($session->tesTeori && $session->tesTeori->result === 'fail') {
            return true;
        }
        if ($session->interviews && $session->interviews->contains(function ($interview) {
            return $interview->result === 'fail';
        })) {
            return true;
        }
        if ($session->offering && $session->offering->response === 'rejected') {
            return true;
        }
        if ($session->mcu && $session->mcu->result === 'fail') {
            return true;
        }

        // Check if session status indicates completion or failure
        if (in_array($session->status, ['hired', 'rejected', 'withdrawn', 'cancelled'])) {
            return true;
        }

        return false;
    }

    public function exportStaleCandidates(Request $request)
    {
        $query = \App\Models\RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'cvReview',
            'psikotes',
            'tesTeori',
            'interviews',
            'offering',
            'mcu',
            'hiring',
            'onboarding',
            'candidate'
        ])->where('status', 'in_process');

        if ($request->filled('date1') && $request->filled('date2')) {
            $query->whereBetween('created_at', [$request->date1, $request->date2]);
        }
        if ($request->filled('department')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }
        if ($request->filled('position')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('position_id', $request->position);
            });
        }
        if ($request->filled('project')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('project_id', $request->project);
            });
        }

        $sessions = $query->get();

        $rows = [];
        foreach ($sessions as $session) {
            // Skip completed/failed per stale logic
            if ($this->isCandidateCompletedOrFailed($session)) {
                continue;
            }
            $currentStage = $this->getCurrentStage($session);
            $lastActivity = $this->getLastActivity($session);
            $daysSinceLastActivity = $lastActivity ? now()->diffInDays($lastActivity) : 0;
            $daysInCurrentStage = $this->getDaysInCurrentStage($session, $currentStage);

            $rows[] = [
                'request_no' => $session->fptk ? $session->fptk->request_number : '-',
                'department' => $session->fptk && $session->fptk->department ? $session->fptk->department->department_name : '-',
                'position' => $session->fptk && $session->fptk->position ? $session->fptk->position->position_name : '-',
                'project' => $session->fptk && $session->fptk->project ? $session->fptk->project->project_name : '-',
                'candidate_name' => $session->candidate ? $session->candidate->fullname : '-',
                'current_stage' => $currentStage,
                'last_activity_date' => $lastActivity ? $lastActivity->format('d/m/Y') : '-',
                'days_since_last_activity' => $daysSinceLastActivity,
                'days_in_current_stage' => $daysInCurrentStage,
                'status' => $daysSinceLastActivity > 7 ? 'Stale' : 'Active',
                'notes' => $this->buildStaleCandidatesNotes($session, $currentStage),
            ];
        }

        return Excel::download(new class(collect($rows)) implements FromCollection, WithHeadings, WithMapping {
            private $rows;
            public function __construct($rows)
            {
                $this->rows = $rows;
            }
            public function collection()
            {
                return $this->rows;
            }
            public function headings(): array
            {
                return [
                    'Request No',
                    'Department',
                    'Position',
                    'Project',
                    'Candidate Name',
                    'Current Stage',
                    'Last Activity Date',
                    'Days Since Last Activity',
                    'Days in Current Stage',
                    'Status',
                    'Notes'
                ];
            }
            public function map($row): array
            {
                return [
                    $row['request_no'],
                    $row['department'],
                    $row['position'],
                    $row['project'],
                    $row['candidate_name'],
                    $row['current_stage'],
                    $row['last_activity_date'],
                    $row['days_since_last_activity'],
                    $row['days_in_current_stage'],
                    $row['status'],
                    $row['notes'],
                ];
            }
        }, 'recruitment_stale_candidates_' . date('YmdHis') . '.xlsx');
    }
}
