<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Define stage progression - FIXED ORDER
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
        $previousStageCount = 0;

        foreach ($stageModels as $stageName => $modelClass) {
            // Get stage data with session filtering
            $stageQuery = $modelClass::whereIn('session_id', $sessions->pluck('id'));

            if (!empty($date1) && !empty($date2)) {
                $stageQuery->whereBetween('created_at', [$date1, $date2]);
            }

            $stageRecords = $stageQuery->get();

            // For interview stage, count unique sessions (not individual interviews)
            if ($stageName === 'Interview') {
                $totalCandidates = $stageRecords->pluck('session_id')->unique()->count();
            } else {
                $totalCandidates = $stageRecords->count();
            }

            // Calculate conversion rate (from previous stage)
            $conversionRate = 0;
            if ($stageName === 'CV Review') {
                // First stage: conversion rate is meaningless, show as 100% only if there are candidates
                $conversionRate = $totalCandidates > 0 ? 100 : 0;
            } elseif ($previousStageCount > 0) {
                $conversionRate = round(($totalCandidates / $previousStageCount) * 100, 2);
            } else {
                // No previous stage data, can't calculate conversion
                $conversionRate = 0;
            }

            // Calculate average days in stage
            $avgDaysInStage = 0;
            if ($totalCandidates > 0) {
                $totalDays = $stageRecords->sum(function ($record) {
                    if ($record->updated_at && $record->created_at) {
                        return $record->updated_at->diffInDays($record->created_at);
                    } elseif ($record->created_at) {
                        // If no updated_at, calculate from created_at to now
                        return now()->diffInDays($record->created_at);
                    }
                    return 1; // Default to 1 day if no dates available
                });
                $avgDaysInStage = $totalDays > 0 ? round($totalDays / $totalCandidates, 1) : 1;
            }

            $rows[] = [
                'stage' => $stageName,
                'total_candidates' => $totalCandidates,
                'previous_stage_count' => $previousStageCount,
                'conversion_rate' => $conversionRate,
                'avg_days_in_stage' => $avgDaysInStage,
                'date1' => $date1,
                'date2' => $date2,
            ];

            $previousStageCount = $totalCandidates;
        }

        return $rows;
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
            'hiring' => RecruitmentHiring::class,
            'onboarding' => RecruitmentOnboarding::class,
        ];

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

        // Get stage records
        $stageQuery = $modelClass::with(['session.fptk.department', 'session.fptk.position', 'session.fptk.project', 'session.candidate'])
            ->whereIn('session_id', $sessions->pluck('id'));

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

        $data = $this->buildAgingData($date1, $date2, $department, $project, $status);

        return view('recruitment.reports.aging', [
            'title' => 'Recruitment Reports',
            'subtitle' => 'Request Aging & SLA',
            'date1' => $date1,
            'date2' => $date2,
            'department' => $department,
            'project' => $project,
            'status' => $status,
            'rows' => $data,
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
                    'Approval Remarks',
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
                    $row['days_to_approve'],
                    $row['remarks'],
                ];
            }
        }, 'recruitment_aging_' . date('YmdHis') . '.xlsx');
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
        foreach ($requests as $request) {
            // Calculate days open
            $daysOpen = now()->diffInDays($request->created_at);

            // Get latest approval info
            $latestApproval = null;
            $approvedAt = null;
            $daysToApprove = null;
            $latestApprovalName = '-';
            $approvalRemarks = '-';

            if ($request->approval_plans && $request->approval_plans->count() > 0) {
                $approvedPlans = $request->approval_plans->where('status', 1);
                if ($approvedPlans->count() > 0) {
                    $latestApproval = $approvedPlans->sortByDesc('updated_at')->first();
                    $approvedAt = $latestApproval->updated_at ? $latestApproval->updated_at->format('d/m/Y H:i') : '-';
                    $approvedAtSort = $latestApproval->updated_at ? $latestApproval->updated_at->format('Y-m-d H:i:s') : '';
                    $daysToApprove = $latestApproval->updated_at ? now()->diffInDays($request->created_at, $latestApproval->updated_at) : null;
                    $latestApprovalName = $latestApproval->approver ? $latestApproval->approver->name : '-';
                    $approvalRemarks = $latestApproval->remarks ?: '-';
                }
            }

            $rows[] = [
                'request_id' => $request->id,
                'request_no' => $request->request_number,
                'department' => $request->department ? $request->department->department_name : '-',
                'position' => $request->position ? $request->position->position_name : '-',
                'project' => $request->project ? $request->project->project_name : '-',
                'requested_by' => $request->createdBy ? $request->createdBy->name : '-',
                'requested_at' => $request->created_at->format('d/m/Y H:i'),
                'requested_at_sort' => $request->created_at->format('Y-m-d H:i:s'),
                'status' => ucfirst($request->status),
                'days_open' => $daysOpen,
                'latest_approval' => $latestApprovalName,
                'approved_at' => $approvedAt,
                'approved_at_sort' => $approvedAtSort ?? '',
                'days_to_approve' => $daysToApprove ?: '-',
                'remarks' => $approvalRemarks,
            ];
        }

        return $rows;
    }

    public function timeToHire()
    {
        $title = 'Recruitment Reports';
        $subtitle = 'Time to Hire';
        $date1 = request('date1', '');
        $date2 = request('date2', '');
        $department = request('department');
        $position = request('position');
        $project = request('project');

        $query = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'fptk.approval_plans.approver',
            'hiring'
        ])
            ->whereHas('hiring');

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
        $rows = $this->buildTimeToHireData($sessions);

        $departments = Department::orderBy('department_name')->get();
        $positions = Position::orderBy('position_name')->get();
        $projects = Project::orderBy('project_name')->get();

        return view('recruitment.reports.time-to-hire', compact(
            'title',
            'subtitle',
            'rows',
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

    public function exportTimeToHire()
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
            'fptk.approval_plans.approver',
            'hiring'
        ])
            ->whereHas('hiring');

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
        $rows = $this->buildTimeToHireData($sessions);

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
                    'Requested At',
                    'Hiring Date',
                    'Total Days',
                    'Approval Days',
                    'Recruitment Days',
                    'Status',
                    'Latest Approval',
                    'Approval Remarks',
                ];
            }

            public function map($row): array
            {
                return [
                    $row['request_no'],
                    $row['department'],
                    $row['position'],
                    $row['project'],
                    $row['requested_at'],
                    $row['hiring_date'],
                    $row['total_days'],
                    $row['approval_days'],
                    $row['recruitment_days'],
                    $row['status'],
                    $row['latest_approval'],
                    $row['remarks'],
                ];
            }
        }, 'recruitment_time_to_hire_' . date('Y-m-d') . '.xlsx');
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

        // Debug: Check if we have any data
        $totalOfferings = \App\Models\RecruitmentOffering::count();
        $totalSessions = RecruitmentSession::count();
        $sessionsWithOfferings = RecruitmentSession::whereHas('offering')->count();

        $rows = $this->buildOfferAcceptanceData($sessions);

        $departments = Department::orderBy('department_name')->get();
        $positions = Position::orderBy('position_name')->get();
        $projects = Project::orderBy('project_name')->get();

        return view('recruitment.reports.offer-acceptance-rate', compact(
            'title',
            'subtitle',
            'rows',
            'date1',
            'date2',
            'department',
            'position',
            'project',
            'departments',
            'positions',
            'projects',
            'totalOfferings',
            'totalSessions',
            'sessionsWithOfferings'
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
}
