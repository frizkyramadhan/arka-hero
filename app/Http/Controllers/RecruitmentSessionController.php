<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentSession;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentCandidate;
use App\Models\Employee;
use App\Models\Administration;
use App\Models\Department;
use App\Models\Position;
use App\Models\RecruitmentCvReview;
use App\Models\RecruitmentPsikotes;
use App\Models\RecruitmentTesTeori;
use App\Models\RecruitmentInterview;
use App\Services\RecruitmentSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RecruitmentSessionController extends Controller
{
    protected $sessionService;


    public function __construct(
        RecruitmentSessionService $sessionService
    ) {
        $this->sessionService = $sessionService;
        $this->middleware('role_or_permission:recruitment-sessions.show')->only('index', 'show');
        $this->middleware('role_or_permission:recruitment-sessions.create')->only('create');
        $this->middleware('role_or_permission:recruitment-sessions.edit')->only('edit', 'advanceStage', 'reject', 'complete', 'cancel', 'withdraw');
        $this->middleware('role_or_permission:recruitment-sessions.delete')->only('destroy');
    }

    /**
     * Store a new recruitment session (add candidate to FPTK)
     */
    public function store(Request $request)
    {
        try {
            Log::info('Store method called', ['data' => $request->all()]);

            $request->validate([
                'candidate_id' => 'required|exists:recruitment_candidates,id',
                'fptk_id' => 'required|exists:recruitment_requests,id',
            ]);

            // Double check that candidate and FPTK exist
            $candidate = RecruitmentCandidate::find($request->candidate_id);
            $fptk = RecruitmentRequest::find($request->fptk_id);

            if (!$candidate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate not found'
                ], 404);
            }

            if (!$fptk) {
                return response()->json([
                    'success' => false,
                    'message' => 'FPTK not found'
                ], 404);
            }

            // Check if candidate is already in this FPTK
            $existingSession = RecruitmentSession::where('candidate_id', $request->candidate_id)
                ->where('fptk_id', $request->fptk_id)
                ->first();

            if ($existingSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate is already in this FPTK'
                ], 400);
            }

            // Check if FPTK is approved
            if ($fptk->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'FPTK must be approved to add candidates'
                ], 400);
            }

            // Business rule update: allow adding candidates regardless of required_qty

            // Create new session
            $sessionData = [
                'candidate_id' => $request->candidate_id,
                'fptk_id' => $request->fptk_id,
                'session_number' => $this->generateSessionNumber(),
                'current_stage' => 'cv_review',
                'stage_status' => 'pending',
                'status' => 'in_process',
                'applied_date' => now(),
                'source' => 'manual_add',
            ];

            Log::info('Creating session with data', $sessionData);

            try {
                $session = RecruitmentSession::create($sessionData);
                Log::info('Session created successfully', ['session_id' => $session->id]);
            } catch (\Exception $createException) {
                Log::error('Failed to create session', [
                    'error' => $createException->getMessage(),
                    'data' => $sessionData,
                    'trace' => $createException->getTraceAsString()
                ]);
                throw $createException;
            }

            // Update candidate global status
            $candidate->update(['global_status' => 'in_process']);

            Log::info('Candidate status updated', ['candidate_id' => $request->candidate_id]);

            return response()->json([
                'success' => true,
                'message' => 'Candidate added to FPTK successfully',
                'session_id' => $session->id
            ]);
        } catch (Exception $e) {
            Log::error('Error creating recruitment session: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error adding candidate to FPTK: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique session number with format RSN/2025/08/0001
     */
    private function generateSessionNumber()
    {
        $year = date('Y');
        $month = str_pad(date('m'), 2, '0', STR_PAD_LEFT);

        $maxRetries = 10;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                // Use database transaction to ensure uniqueness
                $sessionNumber = DB::transaction(function () use ($year, $month) {
                    // Get the count of existing sessions for this year/month
                    $count = RecruitmentSession::where('session_number', 'LIKE', "RSN/{$year}/{$month}/%")
                        ->lockForUpdate()
                        ->count();

                    // Generate next number
                    $newNumber = $count + 1;
                    $sessionNumber = "RSN/{$year}/{$month}/" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

                    // Check if this number already exists
                    while (RecruitmentSession::where('session_number', $sessionNumber)->exists()) {
                        $newNumber++;
                        $sessionNumber = "RSN/{$year}/{$month}/" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                    }

                    return $sessionNumber;
                });

                Log::info('Generated session number', [
                    'session_number' => $sessionNumber,
                    'attempt' => $attempt + 1
                ]);

                return $sessionNumber;
            } catch (\Exception $e) {
                $attempt++;
                Log::warning('Failed to generate session number, retrying', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);

                if ($attempt >= $maxRetries) {
                    // Fallback: use timestamp to ensure uniqueness
                    $timestamp = now()->format('YmdHis');
                    $sessionNumber = "RSN/{$year}/{$month}/" . substr($timestamp, -4);
                    Log::error('Using timestamp fallback for session number', [
                        'session_number' => $sessionNumber
                    ]);
                    return $sessionNumber;
                }

                // Wait a bit before retry
                usleep(100000); // 100ms
            }
        }
    }

    /**
     * Display a listing of recruitment sessions
     */
    public function index(Request $request)
    {
        $title = 'Recruitment Sessions';
        $subtitle = 'List of Recruitment Sessions';

        // Data for filters
        $departments = Department::where('department_status', '1')->orderBy('department_name', 'asc')->get();
        $positions = Position::where('position_status', '1')->orderBy('position_name', 'asc')->get();
        $stages = [
            'cv_review' => 'CV Review',
            'psikotes' => 'Psikotes',
            'tes_teori' => 'Tes Teori',
            'interview_hr' => 'Interview HR',
            'interview_user' => 'Interview User',
            'offering' => 'Offering',
            'mcu' => 'MCU',
            'hire' => 'Hire',
            'onboarding' => 'Onboarding'
        ];

        return view('recruitment.sessions.index', compact('title', 'subtitle', 'departments', 'positions', 'stages'));
    }

    /**
     * Get FPTK-based sessions data for DataTables
     */
    public function getSessions(Request $request)
    {
        // Group sessions by FPTK and get aggregated data - only approved FPTKs
        $query = RecruitmentRequest::with([
            'department',
            'position',
            'project',
            'level',
            'sessions.candidate',
            'sessions.cvReview',
            'sessions.psikotes',
            'sessions.tesTeori',
            'sessions.interviews',
            'sessions.offering',
            'sessions.mcu',
            'sessions.hiring',
            'sessions.onboarding'
        ])->where('status', 'approved');

        // Apply filters
        if ($request->filled('fptk_number')) {
            $query->where('request_number', 'LIKE', '%' . $request->fptk_number . '%');
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->filled('required_date_from')) {
            $query->whereDate('required_date', '>=', $request->required_date_from);
        }

        if ($request->filled('required_date_to')) {
            $query->whereDate('required_date', '<=', $request->required_date_to);
        }

        // Order by sessions count descending (most sessions first)
        $query->withCount('sessions')->orderBy('sessions_count', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('fptk_number', function ($fptk) {
                return $fptk->request_number ?? '-';
            })
            ->addColumn('position_name', function ($fptk) {
                return $fptk->position->position_name ?? '-';
            })
            ->addColumn('candidate_count', function ($fptk) {
                return $fptk->sessions->count();
            })
            ->addColumn('overall_progress', function ($fptk) {
                if ($fptk->sessions->count() === 0) {
                    return '<div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%;"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            0%
                        </div>
                    </div>';
                }

                $totalProgress = 0;
                $activeSessions = 0;

                foreach ($fptk->sessions as $session) {
                    if ($session->status === 'in_process' || $session->status === 'hired') {
                        $progress = $this->sessionService->getProgressPercentage($session);
                        $totalProgress += $progress;
                        $activeSessions++;
                    }
                }

                $averageProgress = $activeSessions > 0 ? round($totalProgress / $activeSessions, 1) : 0;

                return '<div class="progress" style="height: 20px;">
                    <div class="progress-bar" role="progressbar" style="width: ' . $averageProgress . '%;"
                         aria-valuenow="' . $averageProgress . '" aria-valuemin="0" aria-valuemax="100">
                        ' . $averageProgress . '%
                    </div>
                </div>';
            })
            ->addColumn('final_status', function ($fptk) {
                $hiredCount = $fptk->sessions->where('status', 'hired')->count();
                $rejectedCount = $fptk->sessions->where('status', 'rejected')->count();
                $inProcessCount = $fptk->sessions->where('status', 'in_process')->count();
                $totalSessions = $fptk->sessions->count();

                if ($totalSessions === 0) {
                    return '<span class="badge badge-secondary">No Applications</span>';
                }

                if ($hiredCount > 0) {
                    return '<span class="badge badge-success">Hired (' . $hiredCount . ')</span>';
                } elseif ($rejectedCount === $totalSessions) {
                    return '<span class="badge badge-danger">All Rejected</span>';
                } elseif ($inProcessCount > 0) {
                    return '<span class="badge badge-primary">In Process (' . $inProcessCount . ')</span>';
                } else {
                    return '<span class="badge badge-warning">Mixed Status</span>';
                }
            })
            ->addColumn('required_date', function ($fptk) {
                return $fptk->required_date ? $fptk->required_date->format('d/m/Y') : '-';
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $search = $request->get('search');
                    $instance->where(function ($q) use ($search) {
                        $q->where('request_number', 'LIKE', "%{$search}%")
                            ->orWhere('letter_number', 'LIKE', "%{$search}%")
                            ->orWhereHas('position', function ($q) use ($search) {
                                $q->where('position_name', 'LIKE', "%{$search}%");
                            })
                            ->orWhereHas('department', function ($q) use ($search) {
                                $q->where('department_name', 'LIKE', "%{$search}%");
                            });
                    });
                }
            })
            ->addColumn('action', function ($fptk) {
                return view('recruitment.sessions.action', compact('fptk'))->render();
            })
            ->rawColumns(['overall_progress', 'final_status', 'action'])
            ->toJson();
    }

    /**
     * Display the specified FPTK with all its sessions
     */
    public function show($id)
    {
        $title = 'FPTK Recruitment Sessions';
        $fptk = RecruitmentRequest::with([
            'department',
            'position',
            'project',
            'level',
            'createdBy',
            'sessions.candidate',
            'sessions.cvReview',
            'sessions.psikotes',
            'sessions.tesTeori',
            'sessions.interviews',
            'sessions.offering',
            'sessions.mcu',
            'sessions.hiring',
            'sessions.onboarding',
            'sessions.documents'
        ])->findOrFail($id);
        $subtitle = 'FPTK Details: ' . $fptk->request_number;

        // Get all sessions for this FPTK
        $sessions = $fptk->sessions()->with([
            'candidate',
            'cvReview',
            'psikotes',
            'tesTeori',
            'interviews',
            'offering',
            'mcu',
            'hiring',
            'onboarding'
        ])->get();

        return view('recruitment.sessions.show', compact('fptk', 'sessions', 'title', 'subtitle'));
    }

    /**
     * Display the specified individual session
     */
    public function showSession($id)
    {
        $title = 'Recruitment Session';
        $session = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'fptk.level',
            'fptk.createdBy',
            'candidate',
            'cvReview',
            'psikotes',
            'tesTeori',
            'interviews',
            'offering',
            'mcu',
            'hiring',
            'onboarding',
            'documents'
        ])->findOrFail($id);
        $subtitle = 'Session Details: ' . $session->session_number;

        $timeline = $this->sessionService->getSessionTimeline($id);
        $progressPercentage = $this->sessionService->getProgressPercentage($session);

        return view('recruitment.sessions.show-session', compact('session', 'timeline', 'progressPercentage', 'title', 'subtitle'));
    }

    /**
     * Get session data for AJAX
     */
    public function getSessionData($id)
    {
        $session = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'candidate',
            'cvReview',
            'psikotes',
            'tesTeori',
            'interviews',
            'offering',
            'mcu',
            'hiring',
            'onboarding'
        ])->findOrFail($id);

        $timeline = $this->sessionService->getSessionTimeline($id);
        $progressPercentage = $this->sessionService->getProgressPercentage($session);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'session_number' => $session->session_number,
                'current_stage' => $session->current_stage,
                'stage_status' => $session->stage_status,
                'status' => $session->status,
                'progress_percentage' => $progressPercentage,
                'applied_date' => $session->applied_date,
                'fptk' => [
                    'id' => $session->fptk->id,
                    'request_number' => $session->fptk->request_number,
                    'letter_number' => $session->fptk->getFPTKLetterNumber(),
                    'department' => $session->fptk->department->name,
                    'position' => $session->fptk->position->name,
                    'project' => $session->fptk->project->name,
                ],
                'candidate' => [
                    'id' => $session->candidate->id,
                    'candidate_number' => $session->candidate->candidate_number,
                    'fullname' => $session->candidate->fullname,
                    'email' => $session->candidate->email,
                    'phone' => $session->candidate->phone,
                    'global_status' => $session->candidate->global_status,
                ],
                'assessments' => $session->getAllAssessments(),
                'timeline' => $timeline,
            ]
        ]);
    }

    /**
     * Get sessions by FPTK
     */
    public function getSessionsByFPTK($fptkId)
    {
        $sessions = RecruitmentSession::with(['candidate', 'cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring', 'onboarding'])
            ->where('fptk_id', $fptkId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sessions->map(function (RecruitmentSession $session) {
                return [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'current_stage' => $session->current_stage,
                    'stage_status' => $session->stage_status,
                    'status' => $session->status,
                    'progress_percentage' => $this->sessionService->getProgressPercentage($session),
                    'candidate' => [
                        'fullname' => $session->candidate->fullname,
                        'email' => $session->candidate->email,
                        'phone' => $session->candidate->phone,
                    ],
                    'applied_date' => $session->applied_date,
                ];
            })
        ]);
    }

    /**
     * Get sessions by candidate
     */
    public function getSessionsByCandidate($candidateId)
    {
        $sessions = RecruitmentSession::with(['fptk.department', 'fptk.position', 'cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring', 'onboarding'])
            ->where('candidate_id', $candidateId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sessions->map(function (RecruitmentSession $session) {
                return [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'current_stage' => $session->current_stage,
                    'stage_status' => $session->stage_status,
                    'status' => $session->status,
                    'progress_percentage' => $this->sessionService->getProgressPercentage($session),
                    'fptk' => [
                        'request_number' => $session->fptk->request_number,
                        'department' => $session->fptk->department->name,
                        'position' => $session->fptk->position->name,
                    ],
                    'applied_date' => $session->applied_date,
                ];
            })
        ]);
    }

    // dashboard() moved to DashboardController@recruitment

    /**
     * Remove candidate from session
     */
    public function destroy($id)
    {
        try {
            $session = RecruitmentSession::with(['candidate', 'fptk'])->findOrFail($id);

            // Check if user has permission to delete
            if (!auth()->user()->can('recruitment-sessions.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to remove candidates from sessions.'
                ], 403);
            }

            // Check if session can be deleted (not in final stages)
            if (in_array($session->status, ['hired', 'onboarding'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove candidate from session that is already hired or in onboarding stage.'
                ], 400);
            }

            $candidateName = $session->candidate->fullname ?? 'Unknown Candidate';
            $fptkNumber = $session->fptk->request_number ?? 'Unknown FPTK';

            // Log the deletion
            Log::info('Removing candidate from session', [
                'session_id' => $session->id,
                'session_number' => $session->session_number,
                'candidate_name' => $candidateName,
                'fptk_number' => $fptkNumber,
                'deleted_by' => auth()->id(),
                'deleted_at' => now()
            ]);

            // Delete related data first
            $session->cvReview()->delete();
            $session->psikotes()->delete();
            $session->tesTeori()->delete();
            $session->interviews()->delete();
            $session->offering()->delete();
            $session->mcu()->delete();
            $session->hiring()->delete();
            $session->onboarding()->delete();
            $session->documents()->delete();

            // Delete the session
            $session->delete();

            return response()->json([
                'success' => true,
                'message' => "Candidate {$candidateName} has been successfully removed from FPTK {$fptkNumber}."
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing candidate from session', [
                'session_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error removing candidate from session. Please try again.'
            ], 500);
        }
    }

    public function updateCvReview(Request $request, $sessionId)
    {
        try {
            DB::beginTransaction();

            // Find the session
            $session = RecruitmentSession::findOrFail($sessionId);

            // Validate request
            $request->validate([
                'decision' => 'required|in:recommended,not_recommended',
                'notes' => 'required|string',
                'reviewed_at' => 'required|date',
            ]);

            // Check if session is in CV review stage
            if ($session->current_stage !== 'cv_review') {
                return back()->with('toast_error', 'Session is not in CV review stage.');
            }

            // Check if CV review already exists
            $cvReview = $session->cvReview;
            if (!$cvReview) {
                // Create new CV review
                $cvReview = new RecruitmentCvReview([
                    'session_id' => $sessionId,
                    'decision' => $request->decision,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]);
                $cvReview->save();
            } else {
                // Update existing CV review
                $cvReview->update([
                    'decision' => $request->decision,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]);
            }

            // Update session stage based on decision
            if ($request->decision === 'not_recommended') {
                // Reject the session
                $session->update([
                    'stage_status' => 'failed',
                    'stage_completed_at' => now(),
                    'status' => 'rejected',
                    'final_decision_date' => now(),
                    'final_decision_by' => auth()->id(),
                    'final_decision_notes' => 'CV Review: Not Recommended - ' . $request->notes,
                ]);

                // Update candidate global status
                $session->candidate->updateGlobalStatus();

                DB::commit();

                return back()->with('toast_success', 'CV review completed. Candidate rejected due to not recommended CV review.');
            } else {
                // Complete CV review stage and advance to next stage
                $session->update([
                    'stage_status' => 'completed',
                    'stage_completed_at' => now(),
                ]);

                // Automatically advance to next stage (psikotes)
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => RecruitmentSession::STAGE_PROGRESS[$nextStage],
                    ]);

                    DB::commit();

                    return back()->with('toast_success', 'CV review completed successfully. Candidate recommended and advanced to ' . ucfirst($nextStage) . ' stage.');
                } else {
                    DB::commit();
                    return back()->with('toast_success', 'CV review completed successfully. Candidate recommended.');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update CV review', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('toast_error', 'Failed to update CV review. Please try again.');
        }
    }

    public function updatePsikotes(Request $request, $sessionId)
    {
        try {
            DB::beginTransaction();

            // Find the session
            $session = RecruitmentSession::findOrFail($sessionId);

            // Validate request
            $request->validate([
                'online_score' => 'nullable|numeric|min:0|max:100',
                'offline_score' => 'nullable|numeric|min:0|max:10',
                'notes' => 'nullable|string',
                'reviewed_at' => 'required|date',
            ]);

            // Check if session is in psikotes stage
            if ($session->current_stage !== 'psikotes') {
                return back()->with('toast_error', 'Session is not in psikotes stage.');
            }

            // Validate that at least one score is provided
            $onlineScore = $request->online_score;
            $offlineScore = $request->offline_score;

            if (is_null($onlineScore) && is_null($offlineScore)) {
                return back()->with('toast_error', 'At least one score (online or offline) must be provided.');
            }

            // Calculate overall result based on criteria
            $overallResult = 'pass';
            $resultDetails = [];

            if (!is_null($onlineScore)) {
                if ($onlineScore >= 40) {
                    $resultDetails[] = 'Online: Pass (≥40)';
                } else {
                    $resultDetails[] = 'Online: Fail (<40)';
                    $overallResult = 'fail';
                }
            }

            if (!is_null($offlineScore)) {
                if ($offlineScore >= 8) {
                    $resultDetails[] = 'Offline: Pass (≥8)';
                } else {
                    $resultDetails[] = 'Offline: Fail (<8)';
                    $overallResult = 'fail';
                }
            }

            // Check if psikotes already exists
            $psikotes = $session->psikotes;
            if (!$psikotes) {
                // Create new psikotes assessment
                $psikotes = new RecruitmentPsikotes([
                    'session_id' => $sessionId,
                    'online_score' => $onlineScore,
                    'offline_score' => $offlineScore,
                    'result' => $overallResult,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]);
                $psikotes->save();
            } else {
                // Update existing psikotes assessment
                $psikotes->update([
                    'online_score' => $onlineScore,
                    'offline_score' => $offlineScore,
                    'result' => $overallResult,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]);
            }

            // Update session stage based on result
            if ($overallResult === 'fail') {
                // Reject the session
                $session->update([
                    'stage_status' => 'failed',
                    'stage_completed_at' => now(),
                    'status' => 'rejected',
                    'final_decision_date' => now(),
                    'final_decision_by' => auth()->id(),
                    'final_decision_notes' => 'Psikotes: Failed - ' . implode(', ', $resultDetails) . ($request->notes ? ' - ' . $request->notes : ''),
                ]);

                // Update candidate global status
                $session->candidate->updateGlobalStatus();

                DB::commit();

                return back()->with('toast_success', 'Psikotes assessment completed. Candidate rejected due to failed psikotes.');
            } else {
                // Complete psikotes stage and advance to next stage
                $session->update([
                    'stage_status' => 'completed',
                    'stage_completed_at' => now(),
                ]);

                // Automatically advance to next stage (tes teori)
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => RecruitmentSession::STAGE_PROGRESS[$nextStage],
                    ]);

                    DB::commit();

                    return back()->with('toast_success', 'Psikotes assessment completed successfully. Candidate passed and advanced to ' . ucfirst($nextStage) . ' stage.');
                } else {
                    DB::commit();
                    return back()->with('toast_success', 'Psikotes assessment completed successfully. Candidate passed.');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update psikotes assessment', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('toast_error', 'Failed to update psikotes assessment. Please try again.');
        }
    }

    public function updateTesTeori(Request $request, $sessionId)
    {
        try {
            DB::beginTransaction();

            // Find the session
            $session = RecruitmentSession::findOrFail($sessionId);

            // Validate request
            $request->validate([
                'score' => 'required|numeric|min:0|max:100',
                'notes' => 'nullable|string',
                'reviewed_at' => 'required|date',
            ]);

            // Check if session is in tes teori stage
            if ($session->current_stage !== 'tes_teori') {
                return back()->with('toast_error', 'Session is not in tes teori stage.');
            }

            $score = $request->score;

            // Determine category based on score
            $category = '';
            if ($score >= 76) {
                $category = 'Mechanic Senior';
            } elseif ($score >= 61) {
                $category = 'Mechanic Advance';
            } elseif ($score >= 46) {
                $category = 'Mechanic';
            } elseif ($score >= 21) {
                $category = 'Helper Mechanic';
            } else {
                $category = 'Belum Kompeten';
            }

            // Calculate result based on category (only "Belum Kompeten" fails)
            $result = ($category === 'Belum Kompeten') ? 'fail' : 'pass';

            // Prepare notes - if notes already contain category, use as is, otherwise add category
            $notes = $request->notes;
            if (!$notes || !str_contains($notes, 'Kategori:')) {
                $notes = 'Kategori: ' . $category;
                if ($request->notes) {
                    $notes .= "\n\n" . $request->notes;
                }
            }

            // Check if tes teori already exists
            $tesTeori = $session->tesTeori;
            if (!$tesTeori) {
                // Create new tes teori assessment
                $tesTeori = new RecruitmentTesTeori([
                    'session_id' => $sessionId,
                    'score' => $score,
                    'result' => $result,
                    'notes' => $notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]);
                $tesTeori->save();
            } else {
                // Update existing tes teori assessment
                $tesTeori->update([
                    'score' => $score,
                    'result' => $result,
                    'notes' => $notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]);
            }

            // Update session stage based on result
            if ($result === 'fail') {
                // Reject the session
                $session->update([
                    'stage_status' => 'failed',
                    'stage_completed_at' => now(),
                    'status' => 'rejected',
                    'final_decision_date' => now(),
                    'final_decision_by' => auth()->id(),
                    'final_decision_notes' => 'Tes Teori: Failed - Score: ' . $score . ' - Kategori: ' . $category . ' (Belum Kompeten)' . ($request->notes ? ' - Catatan: ' . $request->notes : ''),
                ]);

                // Update candidate global status
                $session->candidate->updateGlobalStatus();

                DB::commit();

                return back()->with('toast_success', 'Tes Teori assessment completed. Candidate rejected due to failed tes teori.');
            } else {
                // Complete tes teori stage and advance to next stage
                $session->update([
                    'stage_status' => 'completed',
                    'stage_completed_at' => now(),
                ]);

                // Automatically advance to next stage (interview HR)
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => RecruitmentSession::STAGE_PROGRESS[$nextStage],
                    ]);

                    DB::commit();

                    return back()->with('toast_success', 'Tes Teori assessment completed successfully. Candidate passed and advanced to ' . ucfirst($nextStage) . ' stage.');
                } else {
                    DB::commit();
                    return back()->with('toast_success', 'Tes Teori assessment completed successfully. Candidate passed.');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update tes teori assessment', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('toast_error', 'Failed to update tes teori assessment. Please try again.');
        }
    }

    public function updateInterview(Request $request, $sessionId)
    {
        try {
            DB::beginTransaction();

            // Find the session
            $session = RecruitmentSession::findOrFail($sessionId);

            // Validate request
            $request->validate([
                'type' => 'required|in:hr,user',
                'result' => 'required|in:recommended,not_recommended',
                'notes' => 'required|string',
                'reviewed_at' => 'required|date',
            ]);

            $type = $request->type;
            $result = $request->result;

            // Check if session is in interview stage
            if ($session->current_stage !== 'interview') {
                return back()->with('toast_error', 'Session is not in interview stage.');
            }

            // Check if interview already exists
            $interview = $session->interviews()->where('type', $type)->first();
            if (!$interview) {
                // Create new interview assessment
                $interview = new RecruitmentInterview([
                    'session_id' => $sessionId,
                    'type' => $type,
                    'result' => $result,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]);
                $interview->save();
            } else {
                // Update existing interview assessment
                $interview->update([
                    'result' => $result,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]);
            }

            // Update session stage based on result
            if ($result === 'not_recommended') {
                // Reject the session
                $session->update([
                    'stage_status' => 'failed',
                    'stage_completed_at' => now(),
                    'status' => 'rejected',
                    'final_decision_date' => now(),
                    'final_decision_by' => auth()->id(),
                    'final_decision_notes' => ucfirst($type) . ' Interview: Not Recommended' . ($request->notes ? ' - ' . $request->notes : ''),
                ]);

                // Update candidate global status
                $session->candidate->updateGlobalStatus();

                DB::commit();

                return back()->with('toast_success', ucfirst($type) . ' interview completed. Candidate rejected due to not recommended interview.');
            } else {
                // Check if both interviews are completed
                $hrInterview = $session->interviews()->where('type', 'hr')->first();
                $userInterview = $session->interviews()->where('type', 'user')->first();

                if ($hrInterview && $userInterview && $hrInterview->result === 'recommended' && $userInterview->result === 'recommended') {
                    // Both interviews passed, advance to next stage (offering)
                    $nextStage = $session->getNextStageAttribute();
                    if ($nextStage) {
                        $session->update([
                            'current_stage' => $nextStage,
                            'stage_status' => 'pending',
                            'stage_started_at' => now(),
                            'overall_progress' => RecruitmentSession::STAGE_PROGRESS[$nextStage],
                        ]);

                        DB::commit();

                        return back()->with('toast_success', ucfirst($type) . ' interview completed successfully. Both interviews passed. Candidate advanced to ' . ucfirst($nextStage) . ' stage.');
                    } else {
                        DB::commit();
                        return back()->with('toast_success', ucfirst($type) . ' interview completed successfully. Both interviews passed.');
                    }
                } else {
                    // Current interview passed, but waiting for other interview
                    // Stay in interview stage, just update status
                    $session->update([
                        'stage_status' => 'in_progress',
                    ]);

                    DB::commit();
                    return back()->with('toast_success', ucfirst($type) . ' interview completed successfully. Waiting for other interview to complete.');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update interview assessment', [
                'session_id' => $sessionId,
                'type' => $request->type ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('toast_error', 'Failed to update interview assessment. Please try again.');
        }
    }

    public function updateOffering(Request $request, $sessionId)
    {
        try {
            DB::beginTransaction();

            $session = RecruitmentSession::with(['candidate', 'offering'])->findOrFail($sessionId);

            // Validate request
            $request->validate([
                'offering_letter_number_id' => 'required|exists:letter_numbers,id',
                'result' => 'required|in:accepted,rejected',
                'notes' => 'nullable|string',
                'reviewed_at' => 'required|date',
            ]);

            // Check if session is in offering stage
            if ($session->current_stage !== 'offering') {
                return back()->with('toast_error', 'Session is not in offering stage.');
            }

            // Get letter number details
            $letterNumber = \App\Models\LetterNumber::findOrFail($request->offering_letter_number_id);

            // Build full formatted offering letter number for storage (matches UI)
            $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $now = now();
            $romanMonth = $romanMonths[((int)$now->format('n')) - 1];
            $year = $now->format('Y');
            $fullOfferingLetterNumber = $letterNumber->letter_number . '/ARKA-HCS/' . $romanMonth . '/' . $year;

            // Create or update offering record with full letter number
            $offering = $session->offering()->updateOrCreate(
                ['session_id' => $sessionId],
                [
                    'offering_letter_number' => $fullOfferingLetterNumber,
                    'result' => $request->result,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]
            );

            // Mark the letter number as used for this offering (only once)
            try {
                $letterNumber->markAsUsed('recruitment_offering', $session->id, auth()->id());
            } catch (\Throwable $e) {
                Log::warning('Failed to mark letter number as used for offering', [
                    'letter_number_id' => $letterNumber->id,
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($request->result === 'rejected') {
                // Reject session if offering failed
                $session->update([
                    'stage_status' => 'failed',
                    'stage_completed_at' => now(),
                    'status' => 'rejected',
                    'final_decision_date' => now(),
                    'final_decision_by' => auth()->id(),
                    'final_decision_notes' => 'Offering: Rejected by candidate',
                ]);

                // Update candidate global status
                $session->candidate->updateGlobalStatus();

                DB::commit();
                return back()->with('toast_success', 'Offering completed. Session rejected.');
            } elseif ($request->result === 'accepted') {
                // Accept offering, advance to MCU stage
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => RecruitmentSession::STAGE_PROGRESS[$nextStage],
                    ]);

                    DB::commit();
                    return back()->with('toast_success', 'Offering accepted. Advanced to ' . ucfirst($nextStage) . ' stage.');
                } else {
                    DB::commit();
                    return back()->with('toast_success', 'Offering accepted.');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update offering assessment', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('toast_error', 'Failed to update offering assessment. Please try again.');
        }
    }

    public function updateMcu(Request $request, $sessionId)
    {
        $request->validate([
            'overall_health' => 'required|in:fit,unfit,follow_up',
            'notes' => 'nullable|string',
            'reviewed_at' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $session = RecruitmentSession::with(['candidate', 'mcu'])->findOrFail($sessionId);

            // Ensure we are in MCU stage
            if ($session->current_stage !== 'mcu') {
                return back()->with('toast_error', 'Session is not in MCU stage.');
            }

            $overallHealth = $request->overall_health; // fit | unfit | follow_up

            // Create or update MCU record
            $session->mcu()->updateOrCreate(
                ['session_id' => $sessionId],
                [
                    'result' => $overallHealth,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]
            );

            if ($overallHealth === 'unfit') {
                // Session rejected if unfit
                $session->update([
                    'stage_status' => 'failed',
                    'stage_completed_at' => now(),
                    'status' => 'rejected',
                    'final_decision_date' => now(),
                    'final_decision_by' => auth()->id(),
                    'final_decision_notes' => 'MCU: Unfit',
                ]);

                // Update candidate global status
                $session->candidate->updateGlobalStatus();

                DB::commit();
                return back()->with('toast_success', 'MCU completed. Session rejected (Unfit).');
            }

            if ($overallHealth === 'fit') {
                // Advance to Hire stage
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => \App\Models\RecruitmentSession::STAGE_PROGRESS[$nextStage],
                    ]);

                    DB::commit();
                    return back()->with('toast_success', 'MCU passed (Fit). Advanced to ' . ucfirst($nextStage) . ' stage.');
                }

                DB::commit();
                return back()->with('toast_success', 'MCU passed (Fit).');
            }

            // follow_up: keep in MCU stage and mark in progress
            $session->update([
                'stage_status' => 'in_progress',
            ]);

            DB::commit();
            return back()->with('toast_success', 'MCU requires follow up. Stage remains In Progress.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update MCU assessment', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('toast_error', 'Failed to update MCU assessment. Please try again.');
        }
    }

    public function updateHiring(Request $request, $sessionId)
    {
        $request->validate([
            'employee' => 'required|array',
            'employee.fullname' => 'required|string',
            'employee.identity_card' => 'required|string',
            'employee.emp_pob' => 'required|string',
            'employee.emp_dob' => 'required|date',
            'employee.religion_id' => 'required|exists:religions,id',
            'employee.gender' => 'nullable|in:male,female',
            'administration' => 'required|array',
            'administration.department_id' => 'required|exists:departments,id',
            'administration.position_id' => 'required|exists:positions,id',
            'administration.project_id' => 'required|exists:projects,id',
            'administration.level_id' => 'required|exists:levels,id',
            'administration.foc' => 'required_if:agreement_type,pkwt|nullable|date',
            'hiring_letter_number_id' => 'required|exists:letter_numbers,id',
            'agreement_type' => 'required|in:pkwt,pkwtt',
            'notes' => 'nullable|string',
            'reviewed_at' => 'required|date',
        ], [
            'employee.identity_card.unique' => 'Identity Card No already exists',
            'administration.nik.unique' => 'NIK already exists',
        ]);

        try {
            DB::beginTransaction();

            $session = RecruitmentSession::with(['candidate', 'hiring'])->findOrFail($sessionId);

            // Ensure we are in Hire stage
            if ($session->current_stage !== 'hire') {
                return back()->with('toast_error', 'Session is not in Hire stage.');
            }

            // Fetch letter number (PKWT)
            $letterNumber = \App\Models\LetterNumber::findOrFail($request->hiring_letter_number_id);

            // Build full formatted letter number for PKWT hiring display and storage
            $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $now = now();
            $romanMonth = $romanMonths[((int)$now->format('n')) - 1];
            $year = $now->format('Y');
            // Remove any leading alpha prefix (e.g., PKWT0001 -> 0001) to keep base as numeric for storage format
            $base = preg_replace('/^[A-Za-z]+/', '', $letterNumber->letter_number);
            $fullLetterNumber = $base . '/ARKA-HO/PKWT-I/' . $romanMonth . '/' . $year;

            // Create or update Hiring record with full formatted letter number
            $session->hiring()->updateOrCreate(
                ['session_id' => $sessionId],
                [
                    'agreement_type' => $request->agreement_type,
                    'letter_number' => $fullLetterNumber,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]
            );

            // Mark letter number as used for recruitment_hiring
            try {
                $letterNumber->markAsUsed('recruitment_hiring', $session->id, auth()->id());
            } catch (\Throwable $e) {
                Log::warning('Failed to mark letter number as used for hiring', [
                    'letter_number_id' => $letterNumber->id,
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Create or update Employee and Administration based on submitted data
            $employeeData = $request->input('employee', []);
            $adminData = $request->input('administration', []);

            // Prefill from candidate and FPTK if not provided
            $candidate = $session->candidate;
            $fptk = $session->fptk()->with(['project', 'position', 'level'])->first();
            if (!$employeeData) {
                $employeeData = [];
            }
            if (!$adminData) {
                $adminData = [];
            }

            $employeePayload = [
                'fullname' => $employeeData['fullname'] ?? ($candidate->fullname ?? ''),
                'emp_pob' => $employeeData['emp_pob'] ?? '-',
                'emp_dob' => $employeeData['emp_dob'] ?? now()->toDateString(),
                'blood_type' => $employeeData['blood_type'] ?? null,
                'religion_id' => $employeeData['religion_id'] ?? null,
                'nationality' => $employeeData['nationality'] ?? null,
                'gender' => $employeeData['gender'] ?? null,
                'marital' => $employeeData['marital'] ?? null,
                'address' => $employeeData['address'] ?? $candidate->address ?? null,
                'village' => $employeeData['village'] ?? null,
                'ward' => $employeeData['ward'] ?? null,
                'district' => $employeeData['district'] ?? null,
                'city' => $employeeData['city'] ?? null,
                'phone' => $employeeData['phone'] ?? ($candidate->phone ?? null),
                'email' => $employeeData['email'] ?? ($candidate->email ?? null),
                'identity_card' => $employeeData['identity_card'],
                'user_id' => auth()->id(),
            ];

            // Reuse existing Employee by identity_card if exists; otherwise create
            $employee = Employee::where('identity_card', $employeePayload['identity_card'])->first();
            if ($employee) {
                $employee->update($employeePayload);
            } else {
                $employee = Employee::create($employeePayload);
            }

            // Deactivate previous active administration if exists
            Administration::where('employee_id', $employee->id)->where('is_active', 1)->update(['is_active' => 0]);

            // Map administration payload
            $administrationPayload = [
                'employee_id' => $employee->id,
                'project_id' => $adminData['project_id'] ?? ($fptk->project_id ?? null),
                'position_id' => $adminData['position_id'] ?? ($fptk->position_id ?? null),
                'grade_id' => $adminData['grade_id'] ?? null,
                'level_id' => $adminData['level_id'] ?? null,
                'nik' => $adminData['nik'],
                'class' => $adminData['class'],
                'doh' => $adminData['doh'],
                'poh' => $adminData['poh'],
                'foc' => $request->agreement_type === 'pkwt' ? ($adminData['foc'] ?? null) : null,
                'agreement' => $adminData['agreement'] ?? strtoupper($request->agreement_type),
                'no_fptk' => $adminData['no_fptk'] ?? ($fptk->request_number ?? null),
                'is_active' => 1,
                'user_id' => auth()->id(),
            ];

            Administration::create($administrationPayload);

            // Update candidate global status to hired at Hiring stage
            // Business rule: once Hiring is saved (agreement issued), candidate becomes globally hired
            if ($session->candidate && $session->candidate->global_status !== 'hired') {
                $session->candidate->update(['global_status' => 'hired']);
            }

            // Advance to next stage (onboarding) but keep progress at 'hire' until onboarding completed
            $nextStage = $session->getNextStageAttribute();
            if ($nextStage) {
                $session->update([
                    'current_stage' => $nextStage,
                    'stage_status' => 'pending',
                    'stage_started_at' => now(),
                    'overall_progress' => \App\Models\RecruitmentSession::STAGE_PROGRESS['hire'],
                ]);
            }

            DB::commit();
            return back()->with('toast_success', 'Hiring saved. Advanced to ' . ucfirst($nextStage ?: 'next') . ' stage.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update hiring', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('toast_error', 'Failed to update hiring. Please try again.');
        }
    }

    public function updateOnboarding(Request $request, $sessionId)
    {
        $request->validate([
            'onboarding_date' => 'required|date',
            'notes' => 'nullable|string',
            'reviewed_at' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $session = RecruitmentSession::with(['candidate', 'onboarding'])->findOrFail($sessionId);

            // Ensure we are in Onboarding stage
            if ($session->current_stage !== 'onboarding') {
                return back()->with('toast_error', 'Session is not in Onboarding stage.');
            }

            // Create or update Onboarding record
            $session->onboarding()->updateOrCreate(
                ['session_id' => $sessionId],
                [
                    'onboarding_date' => $request->onboarding_date,
                    'notes' => $request->notes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => $request->reviewed_at,
                ]
            );

            // Complete the session as hired (final 100%)
            $session->update([
                'stage_status' => 'completed',
                'stage_completed_at' => now(),
                'status' => 'hired',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => $request->notes,
                'overall_progress' => 100.0,
            ]);

            DB::commit();
            return back()->with('toast_success', 'Onboarding saved. Session marked as Hired.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update onboarding', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('toast_error', 'Failed to update onboarding. Please try again.');
        }
    }

    public function closeRequest(Request $request, $sessionOrFptkId)
    {
        try {
            DB::beginTransaction();

            // Accept either session ID (UUID) or FPTK ID (UUID) to improve robustness
            $fptk = null;
            if (strlen($sessionOrFptkId) === 36) {
                // Try resolve as Session first
                $session = RecruitmentSession::with('fptk')->find($sessionOrFptkId);
                if ($session) {
                    if (!in_array($session->status, ['hired', 'rejected', 'withdrawn', 'cancelled'])) {
                        return back()->with('toast_error', 'Recruitment request can be closed only after the session is finished.');
                    }
                    $fptk = $session->fptk;
                }
            }

            // If not resolved via session, treat as FPTK ID directly
            if (!$fptk) {
                $fptk = \App\Models\RecruitmentRequest::findOrFail($sessionOrFptkId);
            }

            // Close FPTK
            $fptk->update(['status' => \App\Models\RecruitmentRequest::STATUS_CLOSED]);

            DB::commit();
            return redirect()->route('recruitment.sessions.index')->with('toast_success', 'Recruitment request has been closed.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to close recruitment request', [
                'input_id' => $sessionOrFptkId,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('recruitment.sessions.index')->with('toast_error', 'Failed to close recruitment request.');
        }
    }
}
