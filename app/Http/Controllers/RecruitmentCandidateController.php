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
        $this->middleware('permission:recruitment-candidates.show')->only('index', 'show', 'search', 'getRecruitmentCandidates', 'getCandidateData', 'getAvailableFPTKs', 'print', 'downloadCV');
        $this->middleware('permission:recruitment-candidates.create')->only('create', 'store', 'applyToFPTK');
        $this->middleware('permission:recruitment-candidates.edit')->only('edit', 'update', 'blacklist', 'removeFromBlacklist');
        $this->middleware('permission:recruitment-candidates.delete')->only('destroy', 'deleteCV');
    }

    /**
     * Display a listing of candidates
     */
    public function index(Request $request)
    {
        // Data for filters
        $educationLevels = [
            'SD',
            'SMP',
            'SMA/SMK',
            'D1',
            'D2',
            'D3',
            'S1',
            'S2',
            'S3'
        ];
        $globalStatuses = [
            'available' => 'Available',
            'in_process' => 'In Process',
            'hired' => 'Hired',
            'rejected' => 'Rejected',
            'blacklisted' => 'Blacklisted'
        ];
        $years = range(date('Y'), date('Y') - 5);

        // Get available FPTKs for the apply modal
        $availableFptks = RecruitmentRequest::with(['department', 'position'])
            ->where('status', 'approved')
            ->get();

        $title = 'Recruitment Candidates';
        $subtitle = 'List of Recruitment Candidates';

        return view('recruitment.candidates.index', compact('educationLevels', 'globalStatuses', 'years', 'title', 'subtitle', 'availableFptks'));
    }

    /**
     * Search candidates for AJAX requests
     */
    public function search(Request $request)
    {
        $query = $request->get('query');

        if (strlen($query) < 3) {
            return response()->json([
                'candidates' => [],
                'message' => 'Please enter at least 3 characters to search'
            ]);
        }

        $candidates = RecruitmentCandidate::where('fullname', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('candidate_number', 'LIKE', "%{$query}%")
            ->orWhere('position_applied', 'LIKE', "%{$query}%")
            ->where('global_status', '!=', 'blacklisted')
            ->limit(10)
            ->get(['id', 'fullname as name', 'email', 'phone', 'position_applied']);

        return response()->json([
            'candidates' => $candidates,
            'message' => $candidates->count() . ' candidates found'
        ]);
    }

    /**
     * Get all recruitment candidates for DataTables
     */
    public function getRecruitmentCandidates(Request $request)
    {
        $query = RecruitmentCandidate::with([
            'sessions.fptk.department',
            'sessions.fptk.position',
            'sessions.fptk.project'
        ]);

        // Apply filters
        if ($request->filled('candidate_number')) {
            $query->where('candidate_number', 'LIKE', "%{$request->candidate_number}%");
        }

        if ($request->filled('fullname')) {
            $query->where('fullname', 'LIKE', "%{$request->fullname}%");
        }

        if ($request->filled('email')) {
            $query->where('email', 'LIKE', "%{$request->email}%");
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'LIKE', "%{$request->phone}%");
        }

        if ($request->filled('global_status')) {
            $query->where('global_status', $request->global_status);
        }

        if ($request->filled('education_level')) {
            $query->where('education_level', $request->education_level);
        }

        if ($request->filled('position_applied')) {
            $query->where('position_applied', 'LIKE', "%{$request->position_applied}%");
        }

        if ($request->filled('registration_date_from')) {
            $query->whereDate('created_at', '>=', $request->registration_date_from);
        }

        if ($request->filled('registration_date_to')) {
            $query->whereDate('created_at', '<=', $request->registration_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('candidate_number', 'LIKE', "%{$search}%")
                    ->orWhere('position_applied', 'LIKE', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('candidate_number', function ($candidate) {
                return $candidate->candidate_number;
            })
            ->addColumn('fullname', function ($candidate) {
                return $candidate->fullname;
            })
            ->addColumn('email', function ($candidate) {
                return $candidate->email;
            })
            ->addColumn('phone', function ($candidate) {
                return $candidate->phone;
            })
            ->addColumn('education_level', function ($candidate) {
                return $candidate->education_level;
            })
            ->addColumn('position_applied', function ($candidate) {
                return $candidate->position_applied ?: '-';
            })
            ->addColumn('experience_years', function ($candidate) {
                return $candidate->experience_years . ' years';
            })
            ->addColumn('global_status', function ($candidate) {
                $badges = [
                    'available' => '<span class="badge badge-success">Available</span>',
                    'in_process' => '<span class="badge badge-warning">In Process</span>',
                    'hired' => '<span class="badge badge-info">Hired</span>',
                    'rejected' => '<span class="badge badge-danger">Rejected</span>',
                    'blacklisted' => '<span class="badge badge-dark">Blacklisted</span>'
                ];
                return $badges[$candidate->global_status] ?? '<span class="badge badge-light">' . ucfirst($candidate->global_status) . '</span>';
            })
            ->addColumn('applications_count', function ($candidate) {
                return $candidate->sessions->count();
            })
            ->addColumn('success_rate', function ($candidate) {
                $totalSessions = $candidate->sessions->count();
                if ($totalSessions === 0) return '0%';

                $successfulSessions = $candidate->sessions->where('status', 'hired')->count();
                return round(($successfulSessions / $totalSessions) * 100, 1) . '%';
            })
            ->addColumn('created_at', function ($candidate) {
                return $candidate->created_at->format('d/m/Y H:i');
            })
            ->addColumn('action', function ($candidate) {
                return view('recruitment.candidates.action', compact('candidate'))->render();
            })
            ->rawColumns(['global_status', 'action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new candidate
     */
    public function create()
    {
        $title = 'Recruitment Candidates';
        $subtitle = 'Add New Candidate';

        return view('recruitment.candidates.create', compact('title', 'subtitle'));
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
            'position_applied' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:2000',
            'skills' => 'nullable|string|max:1000',
            'previous_companies' => 'nullable|string|max:1000',
            'current_salary' => 'nullable|numeric|min:0',
            'expected_salary' => 'nullable|numeric|min:0',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx,zip,rar|max:10240', // 10MB max
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
                'position_applied' => $request->position_applied,
                'remarks' => $request->remarks,
                'skills' => $request->skills,
                'previous_companies' => $request->previous_companies,
                'current_salary' => $request->current_salary,
                'expected_salary' => $request->expected_salary,
                'global_status' => 'available',
                'created_by' => Auth::id(),
            ];

            // Handle CV file upload
            if ($request->hasFile('cv_file')) {
                $cvFile = $request->file('cv_file');
                $fileName = $cvFile->getClientOriginalName();
                // We'll set the file path after creating the candidate to get the ID
                $candidateData['cv_file_path'] = null; // Temporary, will be updated after creation
            }

            $candidate = RecruitmentCandidate::create($candidateData);

            // Handle CV file upload after candidate creation to get the ID
            if ($request->hasFile('cv_file')) {
                $cvFile = $request->file('cv_file');
                $fileName = $cvFile->getClientOriginalName();
                $filePath = $cvFile->storeAs('cv_files/' . $candidate->id, $fileName, 'private');
                $candidate->update(['cv_file_path' => $filePath]);
            }

            DB::commit();

            return redirect()->route('recruitment.candidates.show', $candidate->id)
                ->with('toast_success', 'Candidate successfully created. Number: ' . $candidate->candidate_number);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error creating candidate: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'An error occurred while creating the candidate. Please try again.');
        }
    }

    /**
     * Display the specified candidate
     */
    public function show($id)
    {
        $title = 'Recruitment Candidates';
        $subtitle = 'Candidate Details';

        $candidate = RecruitmentCandidate::with([
            'sessions.fptk.department',
            'sessions.fptk.position',
            'sessions.fptk.project',
            'sessions.cvReview',
            'sessions.psikotes',
            'sessions.tesTeori',
            'sessions.interviews',
            'sessions.offering',
            'sessions.mcu',
            'sessions.hiring',
            'sessions.onboarding'
        ])->findOrFail($id);

        // Get available FPTKs for the apply modal
        $availableFptks = RecruitmentRequest::with(['department', 'position'])
            ->where('status', 'approved')
            ->whereNotIn('id', function ($query) use ($id) {
                $query->select('fptk_id')
                    ->from('recruitment_sessions')
                    ->where('candidate_id', $id);
            })
            ->get();

        return view('recruitment.candidates.show', compact('candidate', 'title', 'subtitle', 'availableFptks'));
    }

    /**
     * Show the form for editing candidate
     */
    public function edit($id)
    {
        $title = 'Recruitment Candidates';
        $subtitle = 'Edit Candidate';

        $candidate = RecruitmentCandidate::findOrFail($id);

        return view('recruitment.candidates.edit', compact('candidate', 'title', 'subtitle'));
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
            'position_applied' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:2000',
            'skills' => 'nullable|string|max:1000',
            'previous_companies' => 'nullable|string|max:1000',
            'current_salary' => 'nullable|numeric|min:0',
            'expected_salary' => 'nullable|numeric|min:0',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx,zip,rar|max:10240', // 10MB max
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
                'position_applied' => $request->position_applied,
                'remarks' => $request->remarks,
                'skills' => $request->skills,
                'previous_companies' => $request->previous_companies,
                'current_salary' => $request->current_salary,
                'expected_salary' => $request->expected_salary,
                'updated_by' => Auth::id(),
            ];

            // Handle CV file upload
            if ($request->hasFile('cv_file')) {
                // Delete old CV file if exists
                if ($candidate->cv_file_path) {
                    Storage::disk('private')->delete($candidate->cv_file_path);
                }

                $cvFile = $request->file('cv_file');
                $fileName = $cvFile->getClientOriginalName();
                $filePath = $cvFile->storeAs('cv_files/' . $candidate->id, $fileName, 'private');
                $candidateData['cv_file_path'] = $filePath;
            }

            $candidate->update($candidateData);

            DB::commit();

            return redirect()->route('recruitment.candidates.show', $candidate->id)
                ->with('toast_success', 'Candidate successfully updated.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error updating candidate: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'An error occurred while updating the candidate. Please try again.');
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
                ->with('toast_error', 'Candidate cannot submit application due to status: ' . $candidate->global_status);
        }

        // Check if FPTK can receive applications
        if (!$fptk->canReceiveApplications()) {
            return redirect()->back()
                ->with('toast_error', 'FPTK cannot receive applications at this time.');
        }

        // Check if candidate already applied to this FPTK
        $existingSession = RecruitmentSession::where('fptk_id', $fptk->id)
            ->where('candidate_id', $candidate->id)
            ->first();

        if ($existingSession) {
            return redirect()->back()
                ->with('toast_error', 'Candidate has already applied for this FPTK.');
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
                    ->with('toast_success', 'Application successfully submitted. Session number: ' . $session->session_number);
            } else {
                DB::rollback();
                return redirect()->back()
                    ->with('toast_error', 'Failed to create recruitment session. Please try again.');
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error applying to FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'An error occurred while submitting the application. Please try again.');
        }
    }

    /**
     * Blacklist candidate
     */
    public function blacklist(Request $request, $id)
    {
        $request->validate([
            'blacklist_reason' => 'required|string|max:2000'
        ]);

        $candidate = RecruitmentCandidate::findOrFail($id);

        try {
            $candidate->update([
                'global_status' => 'blacklisted',
                'blacklist_reason' => $request->blacklist_reason,
                'blacklisted_at' => now(),
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('recruitment.candidates.show', $candidate->id)
                ->with('toast_success', 'Candidate successfully blacklisted.');
        } catch (Exception $e) {
            Log::error('Error blacklisting candidate: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'An error occurred while blacklisting the candidate. Please try again.');
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
                ->with('toast_error', 'Candidate is not in blacklisted status.');
        }

        try {
            $candidate->update([
                'global_status' => 'available',
                'blacklist_reason' => null,
                'blacklisted_at' => null,
            ]);

            return redirect()->route('recruitment.candidates.show', $candidate->id)
                ->with('toast_success', 'Candidate successfully removed from blacklist.');
        } catch (Exception $e) {
            Log::error('Error removing candidate from blacklist: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'An error occurred while removing from blacklist. Please try again.');
        }
    }

    /**
     * Delete candidate
     */
    public function destroy($id)
    {
        $candidate = RecruitmentCandidate::findOrFail($id);

        // Only allow deletion if no active sessions
        if ($candidate->sessions()->whereIn('status', ['active', 'in_process'])->exists()) {
            return redirect()->route('recruitment.candidates.show', $id)
                ->with('toast_error', 'Candidate cannot be deleted while having active recruitment sessions.');
        }

        try {
            DB::beginTransaction();

            // Delete CV file if exists
            if ($candidate->cv_file_path) {
                Storage::disk('private')->delete($candidate->cv_file_path);
            }

            $candidate->delete();

            DB::commit();

            return redirect()->route('recruitment.candidates.index')
                ->with('toast_success', 'Candidate successfully deleted.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error deleting candidate: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'An error occurred while deleting the candidate. Please try again.');
        }
    }

    /**
     * Print candidate details
     */
    public function print($id)
    {
        $candidate = RecruitmentCandidate::with([
            'sessions.fptk.department',
            'sessions.fptk.position',
            'sessions.fptk.project',
            'sessions.cvReview',
            'sessions.psikotes',
            'sessions.tesTeori',
            'sessions.interviews',
            'sessions.offering',
            'sessions.mcu',
            'sessions.hiring',
            'sessions.onboarding'
        ])->findOrFail($id);

        return view('recruitment.candidates.print', compact('candidate'));
    }

    /**
     * Download CV file
     */
    public function downloadCV($id)
    {
        $candidate = RecruitmentCandidate::findOrFail($id);

        if (!$candidate->cv_file_path) {
            return redirect()->back()
                ->with('toast_error', 'CV file not found.');
        }

        if (!Storage::disk('private')->exists($candidate->cv_file_path)) {
            return redirect()->back()
                ->with('toast_error', 'CV file not found in storage.');
        }

        // Create a completely safe filename
        $safeName = preg_replace('/[^a-zA-Z0-9]/', '', $candidate->fullname);
        $safeNumber = preg_replace('/[^a-zA-Z0-9]/', '', $candidate->candidate_number);

        // Get file extension from original file
        $extension = pathinfo($candidate->cv_file_path, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = 'pdf'; // fallback
        }

        $downloadFileName = 'CV_' . $safeName . '_' . $safeNumber . '.' . $extension;

        return response()->download(storage_path('app/private/' . $candidate->cv_file_path), $downloadFileName);
    }

    /**
     * Delete CV file
     */
    public function deleteCV($id)
    {
        $candidate = RecruitmentCandidate::findOrFail($id);

        if (!$candidate->cv_file_path) {
            return redirect()->back()
                ->with('toast_error', 'CV file not found.');
        }

        try {
            // Delete file from storage
            if (Storage::disk('private')->exists($candidate->cv_file_path)) {
                Storage::disk('private')->delete($candidate->cv_file_path);
            }

            // Update database record
            $candidate->update([
                'cv_file_path' => null,
                'updated_by' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('toast_success', 'CV file successfully deleted.');
        } catch (Exception $e) {
            Log::error('Error deleting CV file: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'An error occurred while deleting the CV file. Please try again.');
        }
    }

    /**
     * Get candidate data for AJAX
     */
    public function getCandidateData($id = null)
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
