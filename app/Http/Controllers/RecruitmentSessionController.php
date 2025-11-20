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
use App\Models\ManPowerPlan;
use App\Models\ManPowerPlanDetail;
use App\Services\RecruitmentSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Exception;

class RecruitmentSessionController extends Controller
{
    protected $sessionService;


    public function __construct(
        RecruitmentSessionService $sessionService
    ) {
        $this->sessionService = $sessionService;
        $this->middleware('permission:recruitment-sessions.show')->only('index', 'show', 'showSession', 'getSessions', 'getSessionData', 'getSessionsByFPTK', 'getSessionsByCandidate');
        $this->middleware('permission:recruitment-sessions.create')->only('store');
        $this->middleware('permission:recruitment-sessions.edit-stages')->only('transitionStage');
        $this->middleware('permission:recruitment-sessions.delete')->only('destroy');
    }

    /**
     * Store a new recruitment session (add candidate to FPTK or MPP)
     */
    public function store(Request $request)
    {
        try {
            Log::info('Store method called', ['data' => $request->all()]);

            // Validate that either fptk_id or mpp_detail_id is provided
            $request->validate([
                'candidate_id' => 'required|exists:recruitment_candidates,id',
                'fptk_id' => 'nullable|exists:recruitment_requests,id',
                'mpp_detail_id' => 'nullable|exists:man_power_plan_details,id',
            ]);

            // Ensure at least one source is provided
            if (!$request->fptk_id && !$request->mpp_detail_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either FPTK or MPP Detail must be provided'
                ], 400);
            }

            // Ensure only one source is provided
            if ($request->fptk_id && $request->mpp_detail_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot specify both FPTK and MPP Detail'
                ], 400);
            }

            // Get candidate
            $candidate = RecruitmentCandidate::find($request->candidate_id);
            if (!$candidate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate not found'
                ], 404);
            }

            // Initialize FPTK variable for later use
            $fptk = null;

            // Handle FPTK source
            if ($request->fptk_id) {
                $fptk = RecruitmentRequest::find($request->fptk_id);

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

                // Check if FPTK status allows adding candidates
                // Allow both 'approved' and 'draft' status
                if (!in_array($fptk->status, ['approved', 'draft'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'FPTK must be approved or draft to add candidates'
                    ], 400);
                }

                // Determine initial stage based on employment type
                $initialStage = in_array($fptk->employment_type, ['magang', 'harian']) ? 'mcu' : 'cv_review';
            }
            // Handle MPP Detail source
            else {
                $mppDetail = \App\Models\ManPowerPlanDetail::find($request->mpp_detail_id);

                if (!$mppDetail) {
                    return response()->json([
                        'success' => false,
                        'message' => 'MPP Detail not found'
                    ], 404);
                }

                // Check if candidate is already in this MPP Detail
                $existingSession = RecruitmentSession::where('candidate_id', $request->candidate_id)
                    ->where('mpp_detail_id', $request->mpp_detail_id)
                    ->first();

                if ($existingSession) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Candidate is already in this MPP Detail'
                    ], 400);
                }

                // Check if MPP is active
                if (!$mppDetail->canReceiveApplications()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'MPP must be active and not fulfilled to add candidates'
                    ], 400);
                }

                // MPP always uses standard recruitment flow (cv_review)
                $initialStage = 'cv_review';
            }

            // Create new session
            $sessionData = [
                'candidate_id' => $request->candidate_id,
                'fptk_id' => $request->fptk_id ?? null,
                'mpp_detail_id' => $request->mpp_detail_id ?? null,
                'session_number' => $this->generateSessionNumber(),
                'current_stage' => $initialStage,
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

            // Determine message based on source
            $message = $request->fptk_id
                ? 'Candidate added to FPTK successfully'
                : 'Candidate added to MPP Detail successfully';

            // Add note if FPTK is still draft
            if ($request->fptk_id && $fptk && $fptk->status === 'draft') {
                $message .= ' (Note: FPTK is still in draft status)';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
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

        ];

        return view('recruitment.sessions.index', compact('title', 'subtitle', 'departments', 'positions', 'stages'));
    }

    /**
     * Get FPTK and MPP-based sessions data for DataTables
     */
    public function getSessions(Request $request)
    {
        // Get FPTK-based sessions - approved or draft FPTKs
        $fptkQuery = RecruitmentRequest::with([
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
            'sessions.hiring'
        ])->whereIn('status', ['approved', 'draft']);

        // Apply FPTK filters
        if ($request->filled('fptk_number')) {
            $fptkQuery->where('request_number', 'LIKE', '%' . $request->fptk_number . '%');
        }

        if ($request->filled('department_id')) {
            $fptkQuery->where('department_id', $request->department_id);
        }

        if ($request->filled('position_id')) {
            $fptkQuery->where('position_id', $request->position_id);
        }

        if ($request->filled('required_date_from')) {
            $fptkQuery->whereDate('required_date', '>=', $request->required_date_from);
        }

        if ($request->filled('required_date_to')) {
            $fptkQuery->whereDate('required_date', '<=', $request->required_date_to);
        }

        $fptkQuery->withCount('sessions');
        $fptks = $fptkQuery->get();

        // Get MPP-based sessions - only active MPPs
        $mppQuery = ManPowerPlan::with([
            'project',
            'details.sessions.candidate',
            'details.sessions.cvReview',
            'details.sessions.psikotes',
            'details.sessions.tesTeori',
            'details.sessions.interviews',
            'details.sessions.offering',
            'details.sessions.mcu',
            'details.sessions.hiring',
            'details.position.department'
        ])->where('status', 'active');

        // Apply MPP filters
        if ($request->filled('fptk_number')) {
            $mppQuery->where('mpp_number', 'LIKE', '%' . $request->fptk_number . '%');
        }

        if ($request->filled('position_id')) {
            $mppQuery->whereHas('details', function ($q) use ($request) {
                $q->where('position_id', $request->position_id);
            });
        }

        $mpps = $mppQuery->get();

        // Combine FPTK and MPP data into unified collection
        $combinedData = collect();

        // Add FPTK data
        foreach ($fptks as $fptk) {
            $combinedData->push([
                'id' => $fptk->id,
                'type' => 'fptk',
                'source_number' => $fptk->request_number,
                'position_name' => $fptk->position->position_name ?? '-',
                'project_name' => $fptk->project->project_name ?? '-',
                'department_name' => $fptk->department->department_name ?? '-',
                'required_date' => $fptk->required_date,
                'sessions' => $fptk->sessions,
                'sessions_count' => $fptk->sessions_count,
                'fptk' => $fptk,
                'mpp' => null,
                'mpp_detail' => null,
            ]);
        }

        // Add MPP data (group by MPP Detail)
        foreach ($mpps as $mpp) {
            foreach ($mpp->details()->with('position')->get() as $detail) {
                if ($detail->sessions->count() > 0) {
                    $combinedData->push([
                        'id' => $mpp->id . '_' . $detail->id,
                        'type' => 'mpp',
                        'source_number' => $mpp->mpp_number,
                        'position_name' => $detail->position->position_name ?? 'N/A',
                        'project_name' => $mpp->project->project_name ?? '-',
                        'department_name' => '-',
                        'required_date' => $mpp->created_at,
                        'sessions' => $detail->sessions,
                        'sessions_count' => $detail->sessions->count(),
                        'fptk' => null,
                        'mpp' => $mpp,
                        'mpp_detail' => $detail,
                    ]);
                }
            }
        }

        // Get total records before any filtering
        $totalRecords = $combinedData->count();

        // Get DataTables parameters for pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');

        // Apply search filter if provided
        if (!empty($search)) {
            $combinedData = $combinedData->filter(function ($item) use ($search) {
                return stripos($item['source_number'], $search) !== false ||
                    stripos($item['position_name'], $search) !== false ||
                    stripos($item['project_name'], $search) !== false ||
                    stripos($item['department_name'], $search) !== false;
            });
        }

        // Get filtered records count after search
        $filteredRecords = $combinedData->count();

        // Sort by sessions count descending
        $combinedData = $combinedData->sortByDesc('sessions_count')->values();

        // Apply pagination
        $paginatedData = $combinedData->slice($start, $length)->values();

        return datatables()->of($paginatedData)
            ->with([
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
            ])
            ->addIndexColumn()
            ->addColumn('source_type', function ($item) {
                $badgeClass = $item['type'] === 'fptk' ? 'badge-info' : 'badge-success';
                $label = $item['type'] === 'fptk' ? 'FPTK' : 'MPP';
                return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
            })
            ->addColumn('fptk_number', function ($item) {
                return $item['source_number'] ?? '-';
            })
            ->addColumn('position_name', function ($item) {
                return $item['position_name'] ?? '-';
            })
            ->addColumn('candidate_count', function ($item) {
                return $item['sessions_count'] ?? 0;
            })
            ->addColumn('overall_progress', function ($item) {
                $sessions = $item['sessions'] ?? collect();

                if ($sessions->count() === 0) {
                    return '<div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%;"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            0%
                        </div>
                    </div>';
                }

                $totalProgress = 0;
                $activeSessions = 0;

                foreach ($sessions as $session) {
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
            ->addColumn('final_status', function ($item) {
                $sessions = $item['sessions'] ?? collect();
                $hiredCount = $sessions->where('status', 'hired')->count();
                $rejectedCount = $sessions->where('status', 'rejected')->count();
                $inProcessCount = $sessions->where('status', 'in_process')->count();
                $totalSessions = $sessions->count();

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
            ->addColumn('required_date', function ($item) {
                $date = $item['required_date'] ?? null;
                if ($date) {
                    return is_string($date) ? date('d/m/Y', strtotime($date)) : $date->format('d/m/Y');
                }
                return '-';
            })
            ->addColumn('action', function ($item) {
                return view('recruitment.sessions.action', [
                    'item' => $item,
                    'fptk' => $item['fptk'],
                    'mpp' => $item['mpp'],
                    'mpp_detail' => $item['mpp_detail']
                ])->render();
            })
            ->rawColumns(['source_type', 'overall_progress', 'final_status', 'action'])
            ->make(true);
    }

    /**
     * Display the specified FPTK or MPP Detail with all its sessions
     */
    public function show($id)
    {
        // Try to find as MPP Detail first (UUID format)
        $mppDetail = ManPowerPlanDetail::with([
            'mpp.project',
            'mpp.creator',
            'position.department',
            'sessions.candidate',
            'sessions.cvReview',
            'sessions.psikotes',
            'sessions.tesTeori',
            'sessions.interviews',
            'sessions.offering',
            'sessions.mcu',
            'sessions.hiring',
            'sessions.documents'
        ])->find($id);

        if ($mppDetail) {
            // This is an MPP Detail
            $mpp = $mppDetail->mpp;
            $title = 'MPP Recruitment Sessions';
            $subtitle = 'MPP Detail: ' . ($mppDetail->position->position_name ?? 'N/A') . ' - ' . $mpp->mpp_number;

            // Get all sessions for this MPP Detail
            $sessions = $mppDetail->sessions()->with([
                'candidate',
                'cvReview',
                'psikotes',
                'tesTeori',
                'interviews',
                'offering',
                'mcu',
                'hiring'
            ])->get();

            return view('recruitment.sessions.show', compact('mpp', 'mppDetail', 'sessions', 'title', 'subtitle'));
        }

        // Try to find as FPTK
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
            'sessions.documents'
        ])->find($id);

        if ($fptk) {
            // This is an FPTK
            $title = 'FPTK Recruitment Sessions';
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
                'hiring'
            ])->get();
            return view('recruitment.sessions.show', compact('fptk', 'sessions', 'title', 'subtitle'));
        }

        // Not found in either table
        abort(404, 'FPTK or MPP Detail not found');
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
            'mppDetail.position.department',
            'mppDetail.mpp.project',
            'mppDetail.mpp.creator',
            'candidate',
            'cvReview',
            'psikotes',
            'tesTeori',
            'interviews',
            'offering',
            'mcu',
            'hiring',
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
            'hiring'
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
        $sessions = RecruitmentSession::with(['candidate', 'cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring'])
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
        $sessions = RecruitmentSession::with(['fptk.department', 'fptk.position', 'cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring'])
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
            DB::beginTransaction();

            // Log the incoming ID for debugging
            Log::info('Attempting to delete session', [
                'session_id' => $id,
                'id_type' => gettype($id),
                'id_length' => strlen($id)
            ]);

            // Eager load both FPTK and MPP relationships
            // Try to find session - handle both UUID string and integer ID
            $session = RecruitmentSession::with(['candidate', 'fptk', 'mppDetail.mpp'])->find($id);

            if (!$session) {
                DB::rollBack();
                Log::error('Session not found', [
                    'session_id' => $id,
                    'available_sessions' => RecruitmentSession::pluck('id')->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Session not found. It may have been already deleted.'
                ], 404);
            }

            // Check if session can be deleted (not in final stages)
            if (in_array($session->status, ['hired'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove candidate from session that is already hired.'
                ], 400);
            }

            $candidateName = $session->candidate->fullname ?? 'Unknown Candidate';

            // Determine source (FPTK or MPP)
            $sourceType = 'Unknown';
            $sourceNumber = 'Unknown';
            if ($session->fptk_id && $session->fptk) {
                $sourceType = 'FPTK';
                $sourceNumber = $session->fptk->request_number ?? 'Unknown';
            } elseif ($session->mpp_detail_id && $session->mppDetail && $session->mppDetail->mpp) {
                $sourceType = 'MPP';
                $sourceNumber = $session->mppDetail->mpp->mpp_number ?? 'Unknown';
            }

            // Log the deletion
            Log::info('Removing candidate from session', [
                'session_id' => $session->id,
                'session_number' => $session->session_number,
                'candidate_name' => $candidateName,
                'source_type' => $sourceType,
                'source_number' => $sourceNumber,
                'deleted_by' => auth()->id(),
                'deleted_at' => now()
            ]);

            // Delete related data first (use query builder to avoid N+1)
            $session->cvReview()->delete();
            $session->psikotes()->delete();
            $session->tesTeori()->delete();
            $session->interviews()->delete();
            $session->offering()->delete();
            $session->mcu()->delete();
            $session->hiring()->delete();
            $session->documents()->delete();

            // Store candidate reference before deletion
            $candidate = $session->candidate;

            // Delete the session
            $session->delete();

            // Update candidate global status to available after session removal
            if ($candidate) {
                $candidate->update(['global_status' => 'available']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Candidate {$candidateName} has been successfully removed from {$sourceType} {$sourceNumber}."
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Session not found when removing candidate', [
                'session_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Session not found. It may have been already deleted.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
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

            // Find the session with relationships
            $session = RecruitmentSession::with(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring', 'mppDetail', 'fptk'])->findOrFail($sessionId);

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
                // Reload session with fresh relationships after saving CV review
                $session->refresh();
                $session->load(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);

                // Complete CV review stage and advance to next stage
                $calculatedProgress = $session->calculateActualProgress();
                $session->update([
                    'stage_status' => 'completed',
                    'stage_completed_at' => now(),
                    'overall_progress' => $calculatedProgress,
                ]);

                // Automatically advance to next stage (psikotes)
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    // Reload again before calculating progress for next stage
                    $session->refresh();
                    $session->load(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);

                    $calculatedProgress = $session->calculateActualProgress();
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => $calculatedProgress,
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

            // Find the session with relationships
            $session = RecruitmentSession::with(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring', 'mppDetail', 'fptk'])->findOrFail($sessionId);

            // Validate request
            $request->validate([
                'online_score' => 'nullable|numeric|min:0',
                'offline_score' => 'nullable|numeric|min:0',
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
                // Reload session with fresh relationships after saving psikotes
                $session->refresh();
                $session->load(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);

                // Complete psikotes stage and advance to next stage
                $calculatedProgress = $session->calculateActualProgress();
                $session->update([
                    'stage_status' => 'completed',
                    'stage_completed_at' => now(),
                    'overall_progress' => $calculatedProgress,
                ]);

                // Automatically advance to next stage (tes teori)
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    // Reload again before calculating progress for next stage
                    $session->refresh();
                    $session->load(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);

                    $calculatedProgress = $session->calculateActualProgress();
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => $calculatedProgress,
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

            // Find the session with relationships
            $session = RecruitmentSession::with(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring', 'mppDetail', 'fptk'])->findOrFail($sessionId);

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
                // Reload session with fresh relationships after saving tes teori
                $session->refresh();
                $session->load(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);

                // Complete tes teori stage and advance to next stage
                $calculatedProgress = $session->calculateActualProgress();
                $session->update([
                    'stage_status' => 'completed',
                    'stage_completed_at' => now(),
                    'overall_progress' => $calculatedProgress,
                ]);

                // Automatically advance to next stage (interview HR)
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    // Reload again before calculating progress for next stage
                    $session->refresh();
                    $session->load(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);

                    $calculatedProgress = $session->calculateActualProgress();
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => $calculatedProgress,
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

            // Find the session with relationships
            $session = RecruitmentSession::with(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring', 'mppDetail', 'fptk'])->findOrFail($sessionId);

            // Validate request
            $request->validate([
                'type' => 'required|in:hr,user,trainer',
                'result' => 'required|in:recommended,not_recommended',
                'notes' => 'required|string',
                'reviewed_at' => 'required|date',
            ], [
                'type.required' => 'Interview type is required.',
                'type.in' => 'Interview type must be either HR, Trainer, or User.',
                'result.required' => 'Interview result is required.',
                'result.in' => 'Interview result must be either recommended or not recommended.',
                'notes.required' => 'Interview notes are required.',
                'reviewed_at.required' => 'Review date is required.',
                'reviewed_at.date' => 'Review date must be a valid date.',
            ]);

            $type = $request->type;
            $result = $request->result;

            // Check if session is in interview stage
            if ($session->current_stage !== 'interview') {
                return back()->with('toast_error', 'Session is not in interview stage.');
            }

            // Check if interview already exists - prevent duplicate interviews
            $existingInterview = $session->interviews()->where('type', $type)->first();
            if ($existingInterview) {
                return back()->with('toast_error', ucfirst($type) . ' interview has already been completed for this session. Cannot create duplicate interviews.');
            }

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
                // Check if all required interviews are completed
                $hrInterview = $session->interviews()->where('type', 'hr')->first();
                $userInterview = $session->interviews()->where('type', 'user')->first();
                $trainerInterview = $session->interviews()->where('type', 'trainer')->first();

                // Determine required interviews based on theory test requirement
                $requiredInterviews = [];
                $requiredInterviews[] = $hrInterview;
                $requiredInterviews[] = $userInterview;

                // Add trainer interview only if theory test is required
                if (!$session->shouldSkipTheoryTest()) {
                    $requiredInterviews[] = $trainerInterview;
                }

                // Check if all required interviews are completed and passed
                $allCompleted = collect($requiredInterviews)->filter()->count() === count($requiredInterviews);
                $allPassed = collect($requiredInterviews)->filter()->where('result', 'recommended')->count() === count($requiredInterviews);

                if ($allCompleted && $allPassed) {
                    // Reload session with fresh relationships after saving interview
                    $session->refresh();
                    $session->load(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);

                    // All required interviews passed, advance to next stage (offering)
                    $nextStage = $session->getNextStageAttribute();
                    if ($nextStage) {
                        $calculatedProgress = $session->calculateActualProgress();
                        $session->update([
                            'current_stage' => $nextStage,
                            'stage_status' => 'pending',
                            'stage_started_at' => now(),
                            'overall_progress' => $calculatedProgress,
                        ]);

                        DB::commit();

                        $interviewTypes = collect($requiredInterviews)->pluck('type')->map('ucfirst')->implode(', ');
                        return back()->with('toast_success', 'All interviews (' . $interviewTypes . ') completed successfully. Candidate advanced to ' . ucfirst($nextStage) . ' stage.');
                    } else {
                        DB::commit();
                        $interviewTypes = collect($requiredInterviews)->pluck('type')->map('ucfirst')->implode(', ');
                        return back()->with('toast_success', 'All interviews (' . $interviewTypes . ') completed successfully.');
                    }
                } else {
                    // Current interview passed, but waiting for other interviews
                    // Stay in interview stage, just update status
                    $session->update([
                        'stage_status' => 'in_progress',
                    ]);

                    DB::commit();

                    $completedCount = collect($requiredInterviews)->filter()->count();
                    $totalCount = count($requiredInterviews);
                    return back()->with('toast_success', ucfirst($type) . ' interview completed successfully. Progress: ' . $completedCount . '/' . $totalCount . ' interviews completed.');
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
                // Reload session with fresh relationships after saving offering
                $session->refresh();
                $session->load(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);

                // Accept offering, advance to MCU stage
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    $calculatedProgress = $session->calculateActualProgress();
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => $calculatedProgress,
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
                // Reload session with fresh relationships after saving MCU
                $session->refresh();
                $session->load(['cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);

                // Advance to Hire stage
                $nextStage = $session->getNextStageAttribute();
                if ($nextStage) {
                    $calculatedProgress = $session->calculateActualProgress();
                    $session->update([
                        'current_stage' => $nextStage,
                        'stage_status' => 'pending',
                        'stage_started_at' => now(),
                        'overall_progress' => $calculatedProgress,
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
            'employee.identity_card' => 'required|string|unique:employees,identity_card',
            'employee.emp_pob' => 'required|string',
            'employee.emp_dob' => 'required|date',
            'employee.religion_id' => 'required|exists:religions,id',
            'employee.gender' => 'nullable|in:male,female',
            'administration' => 'required|array',
            'administration.nik' => 'required|string|unique:administrations,nik',
            'administration.doh' => 'required|date',
            'administration.poh' => 'required|string',
            'administration.class' => 'required|in:Staff,Non Staff',
            'administration.position_id' => 'required|exists:positions,id',
            'administration.project_id' => 'required|exists:projects,id',
            'administration.level_id' => 'required_if:agreement_type,pkwt|required_if:agreement_type,pkwtt|nullable|exists:levels,id',
            'administration.foc' => 'required_if:agreement_type,pkwt|nullable|date',
            'hiring_letter_number_id' => 'required|exists:letter_numbers,id',
            'agreement_type' => 'required|in:pkwt,pkwtt,magang,harian',
            'notes' => 'nullable|string',
            'reviewed_at' => 'required|date',
        ], [
            'employee.fullname.required' => 'Fullname is required',
            'employee.identity_card.required' => 'Identity Card No is required',
            'employee.identity_card.unique' => 'Identity Card already exists. Please use a different Identity Card.',
            'employee.emp_pob.required' => 'Place of Birth is required',
            'employee.emp_dob.required' => 'Date of Birth is required',
            'employee.religion_id.required' => 'Religion is required',
            'administration.nik.required' => 'NIK is required',
            'administration.nik.unique' => 'NIK already exists. Please use a different NIK.',
            'administration.doh.required' => 'Date of Hire is required',
            'administration.poh.required' => 'Place of Hire is required',
            'administration.class.required' => 'Class is required',
            'administration.position_id.required' => 'Position is required',
            'administration.project_id.required' => 'Project is required',
            'administration.level_id.required_if' => 'Level is required for PKWT and PKWTT agreements',
            'administration.foc.required_if' => 'FOC is required for PKWT agreement',
            'hiring_letter_number_id.required' => 'Hiring Letter Number is required',
            'agreement_type.required' => 'Agreement Type is required',
            'reviewed_at.required' => 'Review Date is required',
        ]);

        try {
            DB::beginTransaction();

            $session = RecruitmentSession::with(['candidate', 'hiring', 'fptk', 'mppDetail.mpp'])->findOrFail($sessionId);

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

            // Get agreement type from FPTK or MPP Detail
            $agreementType = null;
            if ($session->fptk_id && $session->fptk) {
                // For FPTK: get from employment_type
                $agreementType = \App\Models\RecruitmentHiring::getAgreementTypeFromEmploymentType($session->fptk->employment_type);
            } elseif ($session->mpp_detail_id && $session->mppDetail) {
                // For MPP: get from agreement_type in MPP Detail
                $agreementType = $session->mppDetail->agreement_type ?? 'pkwt';
            }

            // Fallback to pkwt if still null
            if (!$agreementType) {
                $agreementType = 'pkwt';
            }

            // Create or update Hiring record with full formatted letter number
            $session->hiring()->updateOrCreate(
                ['session_id' => $sessionId],
                [
                    'agreement_type' => $agreementType, // Auto-set from FPTK employment_type
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

            // Get position to automatically determine department
            // For FPTK: use fptk->position_id, for MPP: use mppDetail->position_id
            $positionId = $adminData['position_id'] ?? null;
            if (!$positionId) {
                if ($session->fptk_id && $session->fptk) {
                    $positionId = $session->fptk->position_id ?? null;
                } elseif ($session->mpp_detail_id && $session->mppDetail) {
                    $positionId = $session->mppDetail->position_id ?? null;
                }
            }
            $position = \App\Models\Position::with('department')->find($positionId);

            // Get project_id
            $projectId = $adminData['project_id'] ?? null;
            if (!$projectId) {
                if ($session->fptk_id && $session->fptk) {
                    $projectId = $session->fptk->project_id ?? null;
                } elseif ($session->mpp_detail_id && $session->mppDetail && $session->mppDetail->mpp) {
                    $projectId = $session->mppDetail->mpp->project_id ?? null;
                }
            }

            // Get request number (FPTK number or MPP number)
            $requestNumber = $adminData['no_fptk'] ?? null;
            if (!$requestNumber) {
                if ($session->fptk_id && $session->fptk) {
                    $requestNumber = $session->fptk->request_number ?? null;
                } elseif ($session->mpp_detail_id && $session->mppDetail && $session->mppDetail->mpp) {
                    $requestNumber = $session->mppDetail->mpp->mpp_number ?? null;
                }
            }

            // Map administration payload
            $administrationPayload = [
                'employee_id' => $employee->id,
                'project_id' => $projectId,
                'position_id' => $positionId,
                'grade_id' => $adminData['grade_id'] ?? null,
                'level_id' => $adminData['level_id'] ?? null,
                'nik' => $adminData['nik'],
                'class' => $adminData['class'],
                'doh' => $adminData['doh'],
                'poh' => $adminData['poh'],
                'foc' => $agreementType === 'pkwt' ? ($adminData['foc'] ?? null) : null,
                'agreement' => $adminData['agreement'] ?? strtoupper($agreementType),
                'no_fptk' => $requestNumber,
                'is_active' => 1,
                'user_id' => auth()->id(),
            ];

            Administration::create($administrationPayload);

            // Update candidate global status to hired at Hiring stage
            // Business rule: once Hiring is saved (agreement issued), candidate becomes globally hired
            if ($session->candidate && $session->candidate->global_status !== 'hired') {
                $session->candidate->update(['global_status' => 'hired']);
            }

            // Mark stage as completed and complete the session
            $session->update([
                'stage_status' => 'completed',
                'stage_completed_at' => now(),
                'status' => 'hired',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => 'Hire completed successfully',
                'overall_progress' => 100.0,
            ]);

            // Update FPTK positions filled if session is from FPTK
            if ($session->fptk_id && $session->fptk) {
                $session->fptk->incrementPositionsFilled();
            }

            // Update MPP Detail existing quantity if session is from MPP
            if ($session->mpp_detail_id && $session->mppDetail) {
                // Reload hiring relationship to ensure we have the latest data
                $session->load('hiring');

                // Get agreement_type from hiring record if available, otherwise use the one we determined
                $finalAgreementType = $agreementType;
                if ($session->hiring && $session->hiring->agreement_type) {
                    $finalAgreementType = $session->hiring->agreement_type;
                }

                // Auto-increment based on MPP Detail needs (which one is still needed)
                // This will check diff_s and diff_ns to determine staff or non-staff
                // Agreement type is only used as tie-breaker if both have same diff
                $session->mppDetail->autoIncrementExistingQuantity($finalAgreementType);

                // Check fulfillment
                $session->mppDetail->checkFulfillment();
            }

            DB::commit();
            return back()->with('toast_success', 'Hiring saved. Candidate hired successfully.');
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



    public function transitionStage(Request $request, $sessionId)
    {
        // Validate permission - only users with edit-stages permission can transition stages
        $this->authorize('recruitment-sessions.edit-stages');

        $request->validate([
            'target_stage' => 'required|in:cv_review,psikotes,tes_teori,interview,offering,mcu,hire',
            'reason' => 'required|string|max:500',
            'force_transition' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            $session = RecruitmentSession::with(['candidate', 'fptk', 'mppDetail.mpp'])->findOrFail($sessionId);

            $targetStage = $request->target_stage;
            $currentStage = $session->current_stage;

            // Prevent transition to same stage
            if ($targetStage === $currentStage) {
                return back()->with('toast_error', 'Cannot transition to the same stage.');
            }

            // Get valid stages for this session type
            $validStages = $this->getValidStagesForSession($session);

            if (!in_array($targetStage, $validStages)) {
                return back()->with('toast_error', 'Invalid target stage for this session type.');
            }

            // Validate transition logic unless force transition is enabled
            if (!$request->force_transition) {
                $transitionValidation = $this->validateStageTransition($session, $targetStage);
                if (!$transitionValidation['valid']) {
                    return back()->with('toast_error', $transitionValidation['message']);
                }
            }

            // Calculate new progress based on target stage
            $newProgress = $this->calculateProgressForStage($session, $targetStage);

            // Update session stage
            $session->update([
                'current_stage' => $targetStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $newProgress,
                'stage_completed_at' => null, // Reset completion timestamp
            ]);

            // Log the transition
            Log::info('Recruitment session stage transitioned', [
                'session_id' => $sessionId,
                'from_stage' => $currentStage,
                'to_stage' => $targetStage,
                'reason' => $request->reason,
                'forced' => $request->force_transition ?? false,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            $message = "Stage transitioned from " . ucfirst(str_replace('_', ' ', $currentStage)) .
                " to " . ucfirst(str_replace('_', ' ', $targetStage)) . " successfully.";

            return back()->with('toast_success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to transition recruitment session stage', [
                'session_id' => $sessionId,
                'target_stage' => $request->target_stage,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('toast_error', 'Failed to transition stage. Please try again.');
        }
    }

    /**
     * Get valid stages for a session based on its type (FPTK/MMP and employment type)
     */
    private function getValidStagesForSession($session)
    {
        // For magang and harian (simplified process)
        if (
            $session->fptk_id && $session->fptk &&
            in_array($session->fptk->employment_type, ['magang', 'harian'])
        ) {
            return ['mcu', 'hire'];
        }

        // Standard stages for regular employment types
        $stages = ['cv_review', 'psikotes', 'tes_teori', 'interview', 'offering', 'mcu', 'hire'];

        // Remove tes_teori if should be skipped
        if ($session->shouldSkipTheoryTest()) {
            $stages = array_diff($stages, ['tes_teori']);
        }

        return array_values($stages);
    }

    /**
     * Validate if stage transition is allowed
     */
    private function validateStageTransition($session, $targetStage)
    {
        $currentStage = $session->current_stage;
        $validStages = $this->getValidStagesForSession($session);

        // Get current stage index
        $currentIndex = array_search($currentStage, $validStages);
        $targetIndex = array_search($targetStage, $validStages);

        if ($currentIndex === false || $targetIndex === false) {
            return [
                'valid' => false,
                'message' => 'Invalid stage transition.'
            ];
        }

        // Allow forward transitions (advancing stages)
        if ($targetIndex > $currentIndex) {
            return ['valid' => true];
        }

        // For backward transitions, check if previous stages are completed
        if ($targetIndex < $currentIndex) {
            // Check if any stages between current and target have failed assessments
            for ($i = $targetIndex; $i < $currentIndex; $i++) {
                $stageToCheck = $validStages[$i];
                if ($this->hasFailedAssessment($session, $stageToCheck)) {
                    return [
                        'valid' => false,
                        'message' => "Cannot transition backward. Stage '" . ucfirst(str_replace('_', ' ', $stageToCheck)) . "' has failed assessment."
                    ];
                }
            }
        }

        return ['valid' => true];
    }

    /**
     * Check if a stage has failed assessment
     */
    private function hasFailedAssessment($session, $stage)
    {
        switch ($stage) {
            case 'cv_review':
                $assessment = $session->cvReview;
                return $assessment && $assessment->decision === 'not_recommended';

            case 'psikotes':
                $assessment = $session->psikotes;
                return $assessment && $assessment->result === 'fail';

            case 'tes_teori':
                $assessment = $session->tesTeori;
                return $assessment && $assessment->result === 'fail';

            case 'interview':
                return $session->getInterviewStatus() === 'danger';

            case 'offering':
                $assessment = $session->offering;
                return $assessment && $assessment->result === 'rejected';

            case 'mcu':
                $assessment = $session->mcu;
                return $assessment && $assessment->result === 'unfit';

            default:
                return false;
        }
    }

    /**
     * Calculate progress percentage based on completed stages
     * Progress is dynamic: (completed_stages / total_valid_stages) * 100
     * This gives accurate progress based on actual completion status
     */
    private function calculateProgressForStage($session, $targetStage)
    {
        $validStages = $this->getValidStagesForSession($session);

        if (!in_array($targetStage, $validStages)) {
            return 0;
        }

        $totalStages = count($validStages);
        $completedStages = 0;

        // Count how many stages are actually completed (have valid assessments)
        foreach ($validStages as $stage) {
            if ($session->isStageCompleted($stage)) {
                $completedStages++;
            }
        }

        // Calculate progress as percentage of completed stages vs total stages
        if ($totalStages === 0) {
            return 0;
        }

        $progress = ($completedStages / $totalStages) * 100;

        return round($progress);
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
