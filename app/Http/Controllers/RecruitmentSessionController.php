<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentSession;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentAssessment;
use App\Models\Department;
use App\Models\Position;
use App\Services\RecruitmentSessionService;
use App\Services\RecruitmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RecruitmentSessionController extends Controller
{
    protected $sessionService;
    protected $workflowService;

    public function __construct(
        RecruitmentSessionService $sessionService,
        RecruitmentWorkflowService $workflowService
    ) {
        $this->sessionService = $sessionService;
        $this->workflowService = $workflowService;
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

            // Check if FPTK has available positions
            $currentSessions = RecruitmentSession::where('fptk_id', $request->fptk_id)->count();
            if ($currentSessions >= $fptk->required_qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'FPTK has reached maximum number of candidates'
                ], 400);
            }

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
            'sessions.assessments'
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
            'sessions.assessments',
            'sessions.offers',
            'sessions.documents'
        ])->findOrFail($id);
        $subtitle = 'FPTK Details: ' . $fptk->request_number;

        // Get all sessions for this FPTK
        $sessions = $fptk->sessions()->with([
            'candidate',
            'assessments'
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
            'assessments',
            'offers',
            'documents'
        ])->findOrFail($id);
        $subtitle = 'Session Details: ' . $session->session_number;

        $timeline = $this->sessionService->getSessionTimeline($id);
        $progressPercentage = $this->sessionService->getProgressPercentage($session);

        return view('recruitment.sessions.show-session', compact('session', 'timeline', 'progressPercentage', 'title', 'subtitle'));
    }

    /**
     * Advance session to next stage
     */
    public function advanceStage(Request $request, $id)
    {
        $session = RecruitmentSession::findOrFail($id);

        // Debug logging
        Log::info("Advance stage request received", [
            'session_id' => $session->id,
            'current_stage' => $session->current_stage,
            'stage_status' => $session->stage_status,
            'session_status' => $session->status,
            'request_data' => $request->all()
        ]);

        if ($session->status !== 'in_process') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session cannot be advanced because it is already completed.'
                ], 400);
            }
            return redirect()->back()
                ->with('toast_error', 'Session cannot be advanced because it is already completed.');
        }

        // Validate that previous stages are completed
        if (isset($request->assessment_data) && !empty($request->assessment_data)) {
            $assessmentData = $request->assessment_data;
            if (is_string($assessmentData)) {
                $assessmentData = json_decode($assessmentData, true);
            }

            $stage = $assessmentData['stage'] ?? null;
            if ($stage && !$this->validatePreviousStages($session, $stage)) {
                $errorMessage = $this->getPreviousStageValidationMessage($session, $stage);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                return redirect()->back()->with('toast_error', $errorMessage);
            }
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'assessment_data' => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            // Parse assessment_data if it's a JSON string
            $assessmentData = $request->assessment_data;
            if (is_string($assessmentData)) {
                $assessmentData = json_decode($assessmentData, true);
            }

            $result = $this->sessionService->advanceToNextStage($session, [
                'notes' => $request->notes,
                'assessment_data' => $assessmentData,
                'advanced_by' => Auth::id(),
                'advanced_at' => now(),
            ]);

            if ($result['success']) {
                DB::commit();

                // Handle AJAX requests
                if ($request->ajax()) {
                    $response = [
                        'success' => true,
                        'message' => $result['message'],
                    ];

                    // If session was ended (rejected), redirect to show page
                    if (isset($result['session_ended']) && $result['session_ended']) {
                        $response['session_ended'] = true;
                        $response['redirect'] = route('recruitment.sessions.show-session', $session->id);
                    } elseif (isset($result['auto_advanced']) && $result['auto_advanced']) {
                        $response['auto_advanced'] = true;
                        $response['next_stage'] = $result['next_stage'];
                        $response['redirect'] = route('recruitment.sessions.show-session', $session->id);
                    } elseif (isset($result['session_completed']) && $result['session_completed']) {
                        $response['session_completed'] = true;
                        $response['redirect'] = route('recruitment.sessions.show-session', $session->id);
                    } else {
                        $response['redirect'] = route('recruitment.sessions.show-session', $session->id);
                    }

                    return response()->json($response);
                }

                return redirect()->route('recruitment.sessions.show-session', $session->id)
                    ->with('toast_success', $result['message']);
            } else {
                DB::rollback();

                Log::error("Failed to advance session", [
                    'session_id' => $session->id,
                    'error_message' => $result['message']
                ]);

                // Handle AJAX requests
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['message']
                    ], 400);
                }

                return redirect()->back()
                    ->with('toast_error', $result['message']);
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error advancing session: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while advancing the session. Please try again.'
                ], 500);
            }

            return redirect()->back()
                ->with('toast_error', 'An error occurred while advancing the session. Please try again.');
        }
    }

    /**
     * Reject session
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $session = RecruitmentSession::findOrFail($id);

        if ($session->status !== 'in_process') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session cannot be rejected because it is already completed.'
                ], 400);
            }
            return redirect()->back()
                ->with('toast_error', 'Session cannot be rejected because it is already completed.');
        }

        try {
            DB::beginTransaction();

            $success = $this->sessionService->rejectSession(
                $session,
                $request->rejection_reason,
                Auth::id()
            );

            if ($success) {
                DB::commit();

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Session successfully rejected.'
                    ]);
                }

                return redirect()->route('recruitment.sessions.show', $session->id)
                    ->with('toast_success', 'Session successfully rejected.');
            } else {
                DB::rollback();

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to reject session. Please try again.'
                    ], 400);
                }

                return redirect()->back()
                    ->with('toast_error', 'Failed to reject session. Please try again.');
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error rejecting session: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while rejecting the session. Please try again.'
                ], 500);
            }

            return redirect()->back()
                ->with('toast_error', 'An error occurred while rejecting the session. Please try again.');
        }
    }

    /**
     * Complete session (hire candidate)
     */
    public function complete(Request $request, $id)
    {
        $session = RecruitmentSession::findOrFail($id);

        if ($session->status !== 'in_process') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session cannot be completed because it is already finished.'
                ], 400);
            }
            return redirect()->back()
                ->with('toast_error', 'Session cannot be completed because it is already finished.');
        }

        if ($session->current_stage !== 'onboarding') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session can only be completed at the onboarding stage.'
                ], 400);
            }
            return redirect()->back()
                ->with('toast_error', 'Session can only be completed at the onboarding stage.');
        }

        $request->validate([
            'hire_date' => 'required|date',
            'employee_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $success = $this->sessionService->completeSession($session, [
                'hire_date' => $request->hire_date,
                'employee_id' => $request->employee_id,
                'notes' => $request->notes,
                'completed_by' => Auth::id(),
                'completed_at' => now(),
            ]);

            if ($success) {
                DB::commit();

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Session successfully completed. Candidate has been hired.'
                    ]);
                }

                return redirect()->route('recruitment.sessions.show', $session->id)
                    ->with('toast_success', 'Session successfully completed. Candidate has been hired.');
            } else {
                DB::rollback();

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to complete session. Please try again.'
                    ], 400);
                }

                return redirect()->back()
                    ->with('toast_error', 'Failed to complete session. Please try again.');
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error completing session: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while completing the session. Please try again.'
                ], 500);
            }

            return redirect()->back()
                ->with('toast_error', 'An error occurred while completing the session. Please try again.');
        }
    }

    /**
     * Cancel session
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:1000'
        ]);

        $session = RecruitmentSession::findOrFail($id);

        if ($session->status !== 'in_process') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session cannot be cancelled because it is already completed.'
                ], 400);
            }
            return redirect()->back()
                ->with('toast_error', 'Session cannot be cancelled because it is already completed.');
        }

        try {
            DB::beginTransaction();

            $session->update([
                'status' => 'cancelled',
                'final_decision_date' => now(),
                'final_decision_by' => Auth::id(),
                'final_decision_notes' => $request->cancel_reason,
            ]);

            // Update candidate status back to available
            $candidate = $session->candidate;
            $activeSessionsCount = $candidate->sessions()
                ->where('status', 'in_process')
                ->where('id', '!=', $session->id)
                ->count();

            if ($activeSessionsCount === 0) {
                $candidate->update(['global_status' => 'available']);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Session successfully cancelled.'
                ]);
            }

            return redirect()->route('recruitment.sessions.show', $session->id)
                ->with('toast_success', 'Session successfully cancelled.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error cancelling session: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while cancelling the session. Please try again.'
                ], 500);
            }

            return redirect()->back()
                ->with('toast_error', 'An error occurred while cancelling the session. Please try again.');
        }
    }

    /**
     * Withdraw application (candidate initiated)
     */
    public function withdraw(Request $request, $id)
    {
        $request->validate([
            'withdraw_reason' => 'required|string|max:1000'
        ]);

        $session = RecruitmentSession::findOrFail($id);

        if ($session->status !== 'in_process') {
            return redirect()->back()
                ->with('toast_error', 'Session cannot be withdrawn because it is already completed.');
        }

        try {
            DB::beginTransaction();

            $session->update([
                'status' => 'withdrawn',
                'final_decision_date' => now(),
                'final_decision_by' => Auth::id(),
                'final_decision_notes' => $request->withdraw_reason,
            ]);

            // Update candidate status back to available
            $candidate = $session->candidate;
            $activeSessionsCount = $candidate->sessions()
                ->where('status', 'in_process')
                ->where('id', '!=', $session->id)
                ->count();

            if ($activeSessionsCount === 0) {
                $candidate->update(['global_status' => 'available']);
            }

            DB::commit();

            return redirect()->route('recruitment.sessions.show', $session->id)
                ->with('toast_success', 'Application successfully withdrawn.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error withdrawing session: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'An error occurred while withdrawing the application. Please try again.');
        }
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
            'assessments',
            'offers'
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
                'assessments' => $session->assessments->map(function ($assessment) {
                    return [
                        'id' => $assessment->id,
                        'assessment_type' => $assessment->assessment_type,
                        'status' => $assessment->status,
                        'overall_score' => $assessment->overall_score,
                        'recommendation' => $assessment->recommendation,
                        'completed_at' => $assessment->completed_at,
                    ];
                }),
                'timeline' => $timeline,
            ]
        ]);
    }

    /**
     * Get sessions by FPTK
     */
    public function getSessionsByFPTK($fptkId)
    {
        $sessions = RecruitmentSession::with(['candidate', 'assessments'])
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
        $sessions = RecruitmentSession::with(['fptk.department', 'fptk.position', 'assessments'])
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

    /**
     * Dashboard analytics
     */
    public function dashboard()
    {
        $title = 'Recruitment Dashboard';
        $subtitle = 'Recruitment Analytics and Overview';

        $stats = [
            'total_sessions' => RecruitmentSession::count(),
            'active_sessions' => RecruitmentSession::where('status', 'in_process')->count(),
            'completed_sessions' => RecruitmentSession::where('status', 'hired')->count(),
            'rejected_sessions' => RecruitmentSession::where('status', 'rejected')->count(),
        ];

        // Sessions by stage
        $sessionsByStage = RecruitmentSession::where('status', 'in_process')
            ->selectRaw('current_stage, COUNT(*) as count')
            ->groupBy('current_stage')
            ->get();

        // Recent sessions
        $recentSessions = RecruitmentSession::with(['fptk.position', 'candidate'])
            ->latest()
            ->limit(10)
            ->get();

        return view('recruitment.sessions.dashboard', compact('title', 'subtitle', 'stats', 'sessionsByStage', 'recentSessions'));
    }

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
            $session->assessments()->delete();
            $session->offers()->delete();
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

    /**
     * Validate that previous stages are completed before allowing current stage
     *
     * @param RecruitmentSession $session
     * @param string $currentStage
     * @return bool
     */
    private function validatePreviousStages(RecruitmentSession $session, string $currentStage): bool
    {
        $stageOrder = [
            'cv_review' => 1,
            'psikotes' => 2,
            'tes_teori' => 3,
            'interview_hr' => 4,
            'interview_user' => 5,
            'offering' => 6,
            'mcu' => 7,
            'hire' => 8,
            'onboarding' => 9
        ];

        $currentStageOrder = $stageOrder[$currentStage] ?? 0;

        // Check if current stage is the expected next stage
        $expectedCurrentStage = $this->getExpectedCurrentStage($session);

        return $currentStage === $expectedCurrentStage;
    }

    /**
     * Get the expected current stage based on completed stages
     *
     * @param RecruitmentSession $session
     * @return string
     */
    private function getExpectedCurrentStage(RecruitmentSession $session): string
    {
        $stageOrder = [
            'cv_review' => 1,
            'psikotes' => 2,
            'tes_teori' => 3,
            'interview_hr' => 4,
            'interview_user' => 5,
            'offering' => 6,
            'mcu' => 7,
            'hire' => 8,
            'onboarding' => 9
        ];

        // Check completed assessments
        $completedAssessments = $session->assessments()
            ->where('status', 'completed')
            ->where('overall_score', '>=', 70) // Assuming 70 is passing score
            ->pluck('assessment_type')
            ->toArray();

        // Find the highest completed stage
        $highestCompletedOrder = 0;
        foreach ($completedAssessments as $assessmentType) {
            $order = $stageOrder[$assessmentType] ?? 0;
            if ($order > $highestCompletedOrder) {
                $highestCompletedOrder = $order;
            }
        }

        // Return the next stage
        foreach ($stageOrder as $stage => $order) {
            if ($order === $highestCompletedOrder + 1) {
                return $stage;
            }
        }

        // If all stages completed, return onboarding
        return 'onboarding';
    }

    /**
     * Get validation message for previous stage requirement
     *
     * @param RecruitmentSession $session
     * @param string $currentStage
     * @return string
     */
    private function getPreviousStageValidationMessage(RecruitmentSession $session, string $currentStage): string
    {
        $stageNames = [
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

        $expectedStage = $this->getExpectedCurrentStage($session);
        $expectedStageName = $stageNames[$expectedStage] ?? $expectedStage;
        $currentStageName = $stageNames[$currentStage] ?? $currentStage;

        return "Cannot submit {$currentStageName} assessment. Please complete {$expectedStageName} stage first.";
    }
}
