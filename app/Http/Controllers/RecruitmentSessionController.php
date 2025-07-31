<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentSession;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentAssessment;
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
        $this->middleware('auth');
    }

    /**
     * Display a listing of recruitment sessions
     */
    public function index(Request $request)
    {
        $query = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'candidate',
            'assessments'
        ]);

        // Apply filters
        if ($request->filled('current_stage')) {
            $query->where('current_stage', $request->current_stage);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('position_id')) {
            $query->whereHas('fptk', function ($q) use ($request) {
                $q->where('position_id', $request->position_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('session_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('candidate', function ($q) use ($search) {
                        $q->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('fptk', function ($q) use ($search) {
                        $q->where('request_number', 'LIKE', "%{$search}%")
                            ->orWhere('letter_number', 'LIKE', "%{$search}%");
                    });
            });
        }

        $sessions = $query->latest()->paginate(15);

        // Data for filters
        $departments = \App\Models\Department::get();
        $positions = \App\Models\Position::get();
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

        return view('recruitment.sessions.index', compact('sessions', 'departments', 'positions', 'stages'));
    }

    /**
     * Display the specified session
     */
    public function show($id)
    {
        $session = RecruitmentSession::with([
            'fptk.department',
            'fptk.position',
            'fptk.project',
            'fptk.level',
            'fptk.requestedBy',
            'candidate',
            'assessments',
            'offers',
            'documents'
        ])->findOrFail($id);

        $timeline = $this->sessionService->getSessionTimeline($id);
        $progressPercentage = $this->sessionService->getProgressPercentage($session);

        return view('recruitment.sessions.show', compact('session', 'timeline', 'progressPercentage'));
    }

    /**
     * Advance session to next stage
     */
    public function advanceStage(Request $request, $id)
    {
        $session = RecruitmentSession::findOrFail($id);

        if ($session->status !== 'in_process') {
            return redirect()->back()
                ->with('error', 'Session tidak dapat diadvance karena sudah selesai.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'assessment_data' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $success = $this->sessionService->advanceToNextStage($session, [
                'notes' => $request->notes,
                'assessment_data' => $request->assessment_data,
                'advanced_by' => Auth::id(),
                'advanced_at' => now(),
            ]);

            if ($success) {
                DB::commit();
                return redirect()->route('recruitment.sessions.show', $session->id)
                    ->with('success', 'Session berhasil diadvance ke stage berikutnya.');
            } else {
                DB::rollback();
                return redirect()->back()
                    ->with('error', 'Gagal advance session. Silakan coba lagi.');
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error advancing session: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat advance session. Silakan coba lagi.');
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
            return redirect()->back()
                ->with('error', 'Session tidak dapat ditolak karena sudah selesai.');
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
                return redirect()->route('recruitment.sessions.show', $session->id)
                    ->with('success', 'Session berhasil ditolak.');
            } else {
                DB::rollback();
                return redirect()->back()
                    ->with('error', 'Gagal menolak session. Silakan coba lagi.');
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error rejecting session: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menolak session. Silakan coba lagi.');
        }
    }

    /**
     * Complete session (hire candidate)
     */
    public function complete(Request $request, $id)
    {
        $session = RecruitmentSession::findOrFail($id);

        if ($session->status !== 'in_process') {
            return redirect()->back()
                ->with('error', 'Session tidak dapat diselesaikan karena sudah selesai.');
        }

        if ($session->current_stage !== 'onboarding') {
            return redirect()->back()
                ->with('error', 'Session hanya dapat diselesaikan pada stage onboarding.');
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
                return redirect()->route('recruitment.sessions.show', $session->id)
                    ->with('success', 'Session berhasil diselesaikan. Kandidat telah di-hire.');
            } else {
                DB::rollback();
                return redirect()->back()
                    ->with('error', 'Gagal menyelesaikan session. Silakan coba lagi.');
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error completing session: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyelesaikan session. Silakan coba lagi.');
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
            return redirect()->back()
                ->with('error', 'Session tidak dapat dibatalkan karena sudah selesai.');
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

            return redirect()->route('recruitment.sessions.show', $session->id)
                ->with('success', 'Session berhasil dibatalkan.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error cancelling session: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membatalkan session. Silakan coba lagi.');
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
                ->with('error', 'Session tidak dapat ditarik karena sudah selesai.');
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
                ->with('success', 'Aplikasi berhasil ditarik.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error withdrawing session: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menarik aplikasi. Silakan coba lagi.');
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

        return view('recruitment.sessions.dashboard', compact('stats', 'sessionsByStage', 'recentSessions'));
    }
}
