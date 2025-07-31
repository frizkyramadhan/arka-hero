<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentSession;
use App\Services\RecruitmentSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class RecruitmentCandidateController extends Controller
{
    protected $sessionService;

    public function __construct(RecruitmentSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of candidates
     */
    public function index(Request $request)
    {
        $query = RecruitmentCandidate::with(['sessions.fptk.department', 'sessions.fptk.position']);

        // Apply filters
        if ($request->filled('global_status')) {
            $query->where('global_status', $request->global_status);
        }

        if ($request->filled('education_level')) {
            $query->where('education_level', 'LIKE', '%' . $request->education_level . '%');
        }

        if ($request->filled('experience_years')) {
            $query->where('experience_years', '>=', $request->experience_years);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('candidate_number', 'LIKE', "%{$search}%");
            });
        }

        $candidates = $query->latest()->paginate(15);

        // Get available FPTKs for the apply modal
        $availableFptks = RecruitmentRequest::with(['department', 'position'])
            ->where('status', 'approved')
            ->whereColumn('positions_filled', '<', 'required_qty')
            ->get();

        return view('recruitment.candidates.index', compact('candidates', 'availableFptks'));
    }

    /**
     * Show the form for creating a new candidate
     */
    public function create()
    {
        return view('recruitment.candidates.create');
    }

    /**
     * Store a newly created candidate
     */
    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:recruitment_candidates,email',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:1000',
            'date_of_birth' => 'required|date|before:today',
            'education_level' => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0|max:50',
            'skills' => 'nullable|string|max:1000',
            'previous_companies' => 'nullable|string|max:1000',
            'current_salary' => 'nullable|numeric|min:0',
            'expected_salary' => 'nullable|numeric|min:0',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        try {
            DB::beginTransaction();

            $candidateData = [
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'education_level' => $request->education_level,
                'experience_years' => $request->experience_years,
                'skills' => $request->skills,
                'previous_companies' => $request->previous_companies,
                'current_salary' => $request->current_salary,
                'expected_salary' => $request->expected_salary,
                'global_status' => 'available',
            ];

            // Handle CV file upload
            if ($request->hasFile('cv_file')) {
                $cvFile = $request->file('cv_file');
                $fileName = time() . '_' . $cvFile->getClientOriginalName();
                $filePath = $cvFile->storeAs('cv_files', $fileName, 'private');
                $candidateData['cv_file_path'] = $filePath;
            }

            $candidate = RecruitmentCandidate::create($candidateData);

            DB::commit();

            return redirect()->route('recruitment.candidates.show', $candidate->id)
                ->with('success', 'Kandidat berhasil dibuat. Nomor: ' . $candidate->candidate_number);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error creating candidate: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat kandidat. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified candidate
     */
    public function show($id)
    {
        $candidate = RecruitmentCandidate::with([
            'sessions.fptk.department',
            'sessions.fptk.position',
            'sessions.fptk.project',
            'sessions.assessments',
            'sessions.offers'
        ])->findOrFail($id);

        return view('recruitment.candidates.show', compact('candidate'));
    }

    /**
     * Show the form for editing candidate
     */
    public function edit($id)
    {
        $candidate = RecruitmentCandidate::findOrFail($id);

        return view('recruitment.candidates.edit', compact('candidate'));
    }

    /**
     * Update the specified candidate
     */
    public function update(Request $request, $id)
    {
        $candidate = RecruitmentCandidate::findOrFail($id);

        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:recruitment_candidates,email,' . $candidate->id,
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:1000',
            'date_of_birth' => 'required|date|before:today',
            'education_level' => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0|max:50',
            'skills' => 'nullable|string|max:1000',
            'previous_companies' => 'nullable|string|max:1000',
            'current_salary' => 'nullable|numeric|min:0',
            'expected_salary' => 'nullable|numeric|min:0',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        try {
            DB::beginTransaction();

            $candidateData = [
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'education_level' => $request->education_level,
                'experience_years' => $request->experience_years,
                'skills' => $request->skills,
                'previous_companies' => $request->previous_companies,
                'current_salary' => $request->current_salary,
                'expected_salary' => $request->expected_salary,
            ];

            // Handle CV file upload
            if ($request->hasFile('cv_file')) {
                // Delete old CV file if exists
                if ($candidate->cv_file_path) {
                    Storage::disk('private')->delete($candidate->cv_file_path);
                }

                $cvFile = $request->file('cv_file');
                $fileName = time() . '_' . $cvFile->getClientOriginalName();
                $filePath = $cvFile->storeAs('cv_files', $fileName, 'private');
                $candidateData['cv_file_path'] = $filePath;
            }

            $candidate->update($candidateData);

            DB::commit();

            return redirect()->route('recruitment.candidates.show', $candidate->id)
                ->with('success', 'Kandidat berhasil diupdate.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error updating candidate: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengupdate kandidat. Silakan coba lagi.');
        }
    }

    /**
     * Apply to FPTK (Create recruitment session)
     */
    public function applyToFPTK(Request $request, $candidateId)
    {
        $request->validate([
            'fptk_id' => 'required|exists:recruitment_requests,id',
            'source' => 'required|string|max:100',
            'cover_letter' => 'nullable|string|max:2000',
        ]);

        $candidate = RecruitmentCandidate::findOrFail($candidateId);
        $fptk = RecruitmentRequest::findOrFail($request->fptk_id);

        // Check if candidate can apply
        if ($candidate->global_status !== 'available') {
            return redirect()->back()
                ->with('error', 'Kandidat tidak dapat mengajukan aplikasi karena statusnya ' . $candidate->global_status);
        }

        // Check if FPTK can receive applications
        if (!$fptk->canReceiveApplications()) {
            return redirect()->back()
                ->with('error', 'FPTK tidak dapat menerima aplikasi saat ini.');
        }

        // Check if candidate already applied to this FPTK
        $existingSession = RecruitmentSession::where('fptk_id', $fptk->id)
            ->where('candidate_id', $candidate->id)
            ->first();

        if ($existingSession) {
            return redirect()->back()
                ->with('error', 'Kandidat sudah mengajukan aplikasi untuk FPTK ini.');
        }

        try {
            DB::beginTransaction();

            // Create recruitment session
            $session = $this->sessionService->createSession(
                $fptk->id,
                $candidate->id,
                [
                    'source' => $request->source,
                    'cover_letter' => $request->cover_letter,
                    'applied_date' => now(),
                ]
            );

            if ($session) {
                // Update candidate status to in_process
                $candidate->update(['global_status' => 'in_process']);

                DB::commit();

                return redirect()->route('recruitment.sessions.show', $session->id)
                    ->with('success', 'Aplikasi berhasil diajukan. Nomor session: ' . $session->session_number);
            } else {
                DB::rollback();
                return redirect()->back()
                    ->with('error', 'Gagal membuat session recruitment. Silakan coba lagi.');
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error applying to FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengajukan aplikasi. Silakan coba lagi.');
        }
    }

    /**
     * Blacklist candidate
     */
    public function blacklist(Request $request, $id)
    {
        $request->validate([
            'blacklist_reason' => 'required|string|max:1000'
        ]);

        $candidate = RecruitmentCandidate::findOrFail($id);

        try {
            $candidate->update([
                'global_status' => 'blacklisted',
                'blacklist_reason' => $request->blacklist_reason,
                'blacklisted_at' => now(),
                'blacklisted_by' => Auth::id(),
            ]);

            return redirect()->route('recruitment.candidates.show', $candidate->id)
                ->with('success', 'Kandidat berhasil di-blacklist.');
        } catch (Exception $e) {
            Log::error('Error blacklisting candidate: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat blacklist kandidat. Silakan coba lagi.');
        }
    }

    /**
     * Remove from blacklist
     */
    public function removeFromBlacklist($id)
    {
        $candidate = RecruitmentCandidate::findOrFail($id);

        if ($candidate->global_status !== 'blacklisted') {
            return redirect()->back()
                ->with('error', 'Kandidat tidak dalam status blacklist.');
        }

        try {
            $candidate->update([
                'global_status' => 'available',
                'blacklist_reason' => null,
                'blacklisted_at' => null,
                'blacklisted_by' => null,
            ]);

            return redirect()->route('recruitment.candidates.show', $candidate->id)
                ->with('success', 'Kandidat berhasil dihapus dari blacklist.');
        } catch (Exception $e) {
            Log::error('Error removing candidate from blacklist: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus dari blacklist. Silakan coba lagi.');
        }
    }

    /**
     * Download CV file
     */
    public function downloadCV($id)
    {
        $candidate = RecruitmentCandidate::findOrFail($id);

        if (!$candidate->cv_file_path) {
            return redirect()->back()
                ->with('error', 'CV file tidak ditemukan.');
        }

        if (!Storage::disk('private')->exists($candidate->cv_file_path)) {
            return redirect()->back()
                ->with('error', 'CV file tidak ditemukan di storage.');
        }

        $fileName = 'CV_' . str_replace(' ', '_', $candidate->fullname) . '_' . $candidate->candidate_number . '.pdf';

        return response()->download(storage_path('app/private/' . $candidate->cv_file_path), $fileName);
    }

    /**
     * Get candidate data for AJAX
     */
    public function getCandidateData($id)
    {
        $candidate = RecruitmentCandidate::with(['sessions.fptk'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $candidate->id,
                'candidate_number' => $candidate->candidate_number,
                'fullname' => $candidate->fullname,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'education_level' => $candidate->education_level,
                'experience_years' => $candidate->experience_years,
                'skills' => $candidate->skills,
                'global_status' => $candidate->global_status,
                'current_salary' => $candidate->current_salary,
                'expected_salary' => $candidate->expected_salary,
                'applications_count' => $candidate->applications_count,
                'success_rate' => $candidate->success_rate,
                'has_cv' => !empty($candidate->cv_file_path),
                'sessions' => $candidate->sessions->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'session_number' => $session->session_number,
                        'fptk_number' => $session->fptk->request_number,
                        'position' => $session->fptk->position->name,
                        'current_stage' => $session->current_stage,
                        'status' => $session->status,
                        'applied_date' => $session->applied_date,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Get available FPTKs for candidate application
     */
    public function getAvailableFPTKs($candidateId)
    {
        $candidate = RecruitmentCandidate::findOrFail($candidateId);

        // Get FPTKs that can receive applications and candidate hasn't applied to
        $availableFPTKs = RecruitmentRequest::with(['department', 'position', 'project'])
            ->where('status', 'approved')
            ->whereColumn('positions_filled', '<', 'required_qty')
            ->whereNotIn('id', function ($query) use ($candidateId) {
                $query->select('fptk_id')
                    ->from('recruitment_sessions')
                    ->where('candidate_id', $candidateId);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $availableFPTKs->map(function ($fptk) {
                return [
                    'id' => $fptk->id,
                    'request_number' => $fptk->request_number,
                    'letter_number' => $fptk->getFPTKLetterNumber(),
                    'department' => $fptk->department->name,
                    'position' => $fptk->position->name,
                    'project' => $fptk->project->name,
                    'required_qty' => $fptk->required_qty,
                    'remaining_positions' => $fptk->remaining_positions,
                    'required_date' => $fptk->required_date,
                    'employment_type' => $fptk->employment_type,
                ];
            })
        ]);
    }
}
