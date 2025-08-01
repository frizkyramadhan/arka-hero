<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentRequest;
use App\Models\Department;
use App\Models\Project;
use App\Models\Position;
use App\Models\Level;
use App\Models\LetterNumber;
use App\Models\User;
use App\Services\RecruitmentLetterNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RecruitmentRequestController extends Controller
{
    protected $letterNumberService;

    public function __construct(RecruitmentLetterNumberService $letterNumberService)
    {
        $this->letterNumberService = $letterNumberService;
    }

    /**
     * Display a listing of FPTK
     */
    public function index(Request $request)
    {
        // Data for filters
        $departments = Department::get();
        $positions = Position::get();
        $levels = Level::get();
        $years = range(date('Y'), date('Y') - 5);

        $title = 'Recruitment Requests (FPTK)';
        $subtitle = 'List of Recruitment Requests';

        return view('recruitment.requests.index', compact('departments', 'positions', 'levels', 'years', 'title', 'subtitle'));
    }

    /**
     * Get all recruitment requests for DataTables
     */
    public function getRecruitmentRequests(Request $request)
    {
        $query = RecruitmentRequest::with([
            'department',
            'project',
            'position',
            'level',
            'createdBy',
            'letterNumber'
        ]);

        // Apply filters
        if ($request->filled('request_number')) {
            $query->where('request_number', 'LIKE', "%{$request->request_number}%");
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->orderBy('created_at', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('request_number', function ($fptk) {
                return $fptk->request_number;
            })
            ->addColumn('letter_number', function ($fptk) {
                return $fptk->getFPTKLetterNumber() ?: '-';
            })
            ->addColumn('department', function ($fptk) {
                return $fptk->department ? $fptk->department->department_name : '-';
            })
            ->addColumn('position', function ($fptk) {
                return $fptk->position ? $fptk->position->position_name : '-';
            })
            ->addColumn('level', function ($fptk) {
                return $fptk->level ? $fptk->level->name : '-';
            })

            ->addColumn('employment_type', function ($fptk) {
                $types = [
                    'pkwtt' => 'PKWTT',
                    'pkwt' => 'PKWT',
                    'harian' => 'Harian',
                    'magang' => 'Magang'
                ];
                return $types[$fptk->employment_type] ?? $fptk->employment_type;
            })
            ->addColumn('status', function ($fptk) {
                $badges = [
                    'draft' => '<span class="badge badge-secondary">Draft</span>',
                    'submitted' => '<span class="badge badge-warning">Submitted</span>',
                    'approved' => '<span class="badge badge-success">Approved</span>',
                    'rejected' => '<span class="badge badge-danger">Rejected</span>',
                    'cancelled' => '<span class="badge badge-warning">Cancelled</span>',
                    'closed' => '<span class="badge badge-info">Closed</span>'
                ];
                return $badges[$fptk->status] ?? '<span class="badge badge-light">' . ucfirst($fptk->status) . '</span>';
            })
            ->addColumn('created_by', function ($fptk) {
                return $fptk->createdBy ? $fptk->createdBy->name : '-';
            })
            ->addColumn('action', function ($fptk) {
                return view('recruitment.requests.action', compact('fptk'))->render();
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new FPTK
     */
    public function create()
    {
        $title = 'Recruitment';
        $subtitle = 'Add Recruitment Request (FPTK)';
        $departments = Department::get();
        $projects = Project::get();
        $positions = Position::get();
        $levels = Level::get();

        // Get users with approval permissions
        // $acknowledgers = User::permission('recruitment-requests.acknowledge')->get();
        // $approvers = User::permission('recruitment-requests.approve')->get();

        // Travel Number will be generated based on selected letter number
        $romanMonth = $this->numberToRoman(now()->month);
        $recruitmentNumber = sprintf("[Letter Number]/HCS-[Project Code]/FPTK/%s/%s", $romanMonth, now()->year);

        return view('recruitment.requests.create', compact('departments', 'projects', 'positions', 'levels', 'title', 'subtitle', 'recruitmentNumber'));
    }

    /**
     * Store a newly created FPTK
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'project_id' => 'required|exists:projects,id',
            'position_id' => 'required|exists:positions,id',
            'level_id' => 'required|exists:levels,id',
            // Letter numbering integration fields
            'number_option' => 'nullable|in:existing',
            'letter_number_id' => 'nullable|exists:letter_numbers,id',
            'required_qty' => 'required|integer|min:1|max:50',
            'required_date' => 'required|date',
            'employment_type' => 'required|in:pkwtt,pkwt,harian,magang',
            'request_reason' => 'required|in:replacement_resign,replacement_promotion,additional_workplan,other',
            'other_reason' => 'required_if:request_reason,other|nullable|string|max:1000',
            'job_description' => 'required|string|max:2000',
            'required_gender' => 'required|in:male,female,any',
            'required_age_min' => 'nullable|integer|min:17|max:65',
            'required_age_max' => 'nullable|integer|min:17|max:65|gte:required_age_min',
            'required_marital_status' => 'required|in:single,married,any',
            'required_education' => 'nullable|string|max:500',
            'required_skills' => 'nullable|string|max:1000',
            'required_experience' => 'nullable|string|max:1000',
            'required_physical' => 'nullable|string|max:500',
            'required_mental' => 'nullable|string|max:500',
            'other_requirements' => 'nullable|string|max:1000',

            // Approval hierarchy fields
            // 'known_by' => 'required|exists:users,id',
            // 'approved_by_pm' => 'required|exists:users,id',
            // 'approved_by_director' => 'required|exists:users,id',

            // Submit action
            'submit_action' => 'required|in:draft,submit',
        ]);

        try {
            DB::beginTransaction();

            // Handle letter number integration and generate FPTK number
            $letterNumberId = null;
            $letterNumberString = null;
            $letterNumberRecord = null;
            $romanMonth = $this->numberToRoman(now()->month);

            if ($request->letter_number_id) {
                // Use existing letter number
                $letterNumberRecord = LetterNumber::find($request->letter_number_id);
                if ($letterNumberRecord && $letterNumberRecord->status === 'reserved') {
                    $letterNumberId = $letterNumberRecord->id;
                    $letterNumberString = $letterNumberRecord->letter_number;
                } else {
                    throw new \Exception('Selected letter number is not available or not reserved. Current status: ' . ($letterNumberRecord ? $letterNumberRecord->status : 'not found'));
                }
            }

            // Get project code for FPTK number
            $project = Project::find($request->project_id);
            $projectCode = $project ? $project->project_code : 'HO';

            // Generate FPTK number in format: [Letter Number]/HCS-[Project Code]/FPTK/[Roman Month]/[Year]
            $fptkNumber = null;
            if ($letterNumberString) {
                // Extract only the numeric part from letter number (remove FPTK prefix)
                $numericPart = $letterNumberString;
                if (str_starts_with($letterNumberString, 'FPTK')) {
                    $numericPart = str_replace('FPTK', '', $letterNumberString);
                }
                // Format as 4 digits
                $numericPart = str_pad((int) $numericPart, 4, '0', STR_PAD_LEFT);

                $fptkNumber = sprintf(
                    '%s/HCS-%s/FPTK/%s/%s',
                    $numericPart,
                    $projectCode,
                    $romanMonth,
                    now()->year
                );
            }

            // Determine status based on submit action
            $status = $request->submit_action === 'submit' ? 'submitted' : 'draft';
            $submitAt = $request->submit_action === 'submit' ? now() : null;

            $fptk = RecruitmentRequest::create([
                'letter_number_id' => $letterNumberId,
                'letter_number' => $letterNumberString,
                'request_number' => $fptkNumber,
                'department_id' => $request->department_id,
                'project_id' => $request->project_id,
                'position_id' => $request->position_id,
                'level_id' => $request->level_id,
                'required_qty' => $request->required_qty,
                'required_date' => $request->required_date,
                'employment_type' => $request->employment_type,
                'request_reason' => $request->request_reason,
                'other_reason' => $request->other_reason,
                'job_description' => $request->job_description,
                'required_gender' => $request->required_gender,
                'required_age_min' => $request->required_age_min,
                'required_age_max' => $request->required_age_max,
                'required_marital_status' => $request->required_marital_status,
                'required_education' => $request->required_education,
                'required_skills' => $request->required_skills,
                'required_experience' => $request->required_experience,
                'required_physical' => $request->required_physical,
                'required_mental' => $request->required_mental,
                'other_requirements' => $request->other_requirements,

                // Approval hierarchy fields
                // 'known_by' => $request->known_by,
                // 'approved_by_pm' => $request->approved_by_pm,
                // 'approved_by_director' => $request->approved_by_director,

                'created_by' => Auth::id(),
                'status' => $status,
                'submit_at' => $submitAt,
            ]);

            // Mark letter number as used if selected
            if ($letterNumberRecord) {
                $letterNumberRecord->markAsUsed('recruitment_request', $fptk->id);

                // Log the letter number usage for debugging
                Log::info('Letter Number marked as used for FPTK', [
                    'letter_number_id' => $letterNumberRecord->id,
                    'letter_number' => $letterNumberRecord->letter_number,
                    'fptk_id' => $fptk->id,
                    'fptk_number' => $fptk->request_number
                ]);
            }

            // If submitted, create approval plans
            if ($request->submit_action === 'submit') {
                $response = app(ApprovalPlanController::class)->create_approval_plan('recruitment_request', $fptk->id);
                if (!$response) {
                    return redirect()->back()
                        ->with('toast_error', 'Failed to create approval plans. Please try again.')
                        ->withInput();
                }
            }

            DB::commit();

            $message = 'FPTK berhasil dibuat. Nomor: ' . $fptk->request_number;
            if ($letterNumberString) {
                $message .= ' dengan Nomor Surat: ' . $letterNumberString;
            }

            if ($request->submit_action === 'submit') {
                $message .= ' Status: Submitted for approval.';
            } else {
                $message .= ' Status: Saved as draft.';
            }

            return redirect('recruitment/requests')->with('toast_success', $message);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error creating FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'Terjadi kesalahan saat membuat FPTK. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified FPTK
     */
    public function show($id)
    {
        $title = 'Recruitment Requests (FPTK)';
        $subtitle = 'Detail Recruitment Request';
        $fptk = RecruitmentRequest::with([
            'department',
            'project',
            'position',
            'level',
            'createdBy',
            // 'acknowledger',
            // 'projectManagerApprover',
            // 'directorApprover',
            'letterNumber.category',
            'sessions.candidate',
            'sessions.assessments',
            'activeSessions'
        ])->findOrFail($id);

        $letterInfo = $fptk->getLetterNumberInfo();

        return view('recruitment.requests.show', compact('fptk', 'letterInfo', 'title', 'subtitle'));
    }

    /**
     * Print FPTK as PDF or printer-friendly format
     */
    public function print($id)
    {
        $fptk = RecruitmentRequest::with([
            'department',
            'project',
            'position',
            'level',
            'createdBy',
            'acknowledger',
            'projectManagerApprover',
            'directorApprover',
            'letterNumber.category',
            'sessions.candidate',
            'sessions.assessments',
            'activeSessions'
        ])->findOrFail($id);

        $letterInfo = $fptk->getLetterNumberInfo();

        return view('recruitment.requests.print', compact('fptk', 'letterInfo'));
    }

    /**
     * Show the form for editing FPTK
     */
    public function edit($id)
    {
        $title = 'Recruitment Requests (FPTK)';
        $subtitle = 'Edit Recruitment Request';

        $fptk = RecruitmentRequest::findOrFail($id);

        // Only allow editing if status is draft
        if ($fptk->status !== 'draft') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK hanya dapat diedit dalam status draft.');
        }

        $departments = Department::get();
        $projects = Project::get();
        $positions = Position::get();
        $levels = Level::get();

        // Get users with approval permissions
        // $acknowledgers = User::permission('recruitment-requests.acknowledge')->get();
        // $approvers = User::permission('recruitment-requests.approve')->get();

        return view('recruitment.requests.edit', compact('fptk', 'departments', 'projects', 'positions', 'levels', 'title', 'subtitle'));
    }

    /**
     * Update the specified FPTK
     */
    public function update(Request $request, $id)
    {
        $fptk = RecruitmentRequest::findOrFail($id);

        // Only allow updating if status is draft
        if ($fptk->status !== 'draft') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK hanya dapat diupdate dalam status draft.');
        }

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'project_id' => 'required|exists:projects,id',
            'position_id' => 'required|exists:positions,id',
            'level_id' => 'required|exists:levels,id',
            'required_qty' => 'required|integer|min:1|max:50',
            'required_date' => 'required|date',
            'employment_type' => 'required|in:pkwtt,pkwt,harian,magang',
            'request_reason' => 'required|in:replacement_resign,replacement_promotion,additional_workplan,other',
            'other_reason' => 'required_if:request_reason,other|nullable|string|max:1000',
            'job_description' => 'required|string|max:2000',
            'required_gender' => 'required|in:male,female,any',
            'required_age_min' => 'nullable|integer|min:17|max:65',
            'required_age_max' => 'nullable|integer|min:17|max:65|gte:required_age_min',
            'required_marital_status' => 'required|in:single,married,any',
            'required_education' => 'nullable|string|max:500',
            'required_skills' => 'nullable|string|max:1000',
            'required_experience' => 'nullable|string|max:1000',
            'required_physical' => 'nullable|string|max:500',
            'required_mental' => 'nullable|string|max:500',
            'other_requirements' => 'nullable|string|max:1000',
            // Approval hierarchy fields
            // 'known_by' => 'required|exists:users,id',
            // 'approved_by_pm' => 'required|exists:users,id',
            // 'approved_by_director' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $fptk->update([
                'department_id' => $request->department_id,
                'project_id' => $request->project_id,
                'position_id' => $request->position_id,
                'level_id' => $request->level_id,
                'required_qty' => $request->required_qty,
                'required_date' => $request->required_date,
                'employment_type' => $request->employment_type,
                'request_reason' => $request->request_reason,
                'other_reason' => $request->other_reason,
                'job_description' => $request->job_description,
                'required_gender' => $request->required_gender,
                'required_age_min' => $request->required_age_min,
                'required_age_max' => $request->required_age_max,
                'required_marital_status' => $request->required_marital_status,
                'required_education' => $request->required_education,
                'required_skills' => $request->required_skills,
                'required_experience' => $request->required_experience,
                'required_physical' => $request->required_physical,
                'required_mental' => $request->required_mental,
                'other_requirements' => $request->other_requirements,
                // Approval hierarchy fields
                // 'known_by' => $request->known_by,
                // 'approved_by_pm' => $request->approved_by_pm,
                // 'approved_by_director' => $request->approved_by_director,
            ]);

            DB::commit();

            return redirect()->route('recruitment.requests.show', $fptk->id)
                ->with('toast_success', 'FPTK berhasil diupdate.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error updating FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'Terjadi kesalahan saat mengupdate FPTK. Silakan coba lagi.');
        }
    }

    /**
     * Submit FPTK for approval
     */
    public function submitForApproval($id)
    {
        try {
            $fptk = RecruitmentRequest::findOrFail($id);

            // Check if already submitted
            if ($fptk->status === 'submitted') {
                return redirect()->back()->with('toast_error', 'Recruitment request has already been submitted for approval.');
            }

            // Check if status is draft
            if ($fptk->status !== 'draft') {
                return redirect()->back()->with('toast_error', 'Only draft recruitment requests can be submitted for approval.');
            }

            // Create approval plans using the same logic as in store method
            $response = app(ApprovalPlanController::class)->create_approval_plan('recruitment_request', $fptk->id);

            if (!$response) {
                return redirect()->back()
                    ->with('toast_error', 'Failed to create approval plans. Please try again.');
            }

            // Update status to submitted
            $fptk->update([
                'status' => 'submitted',
                'submit_at' => now(),
            ]);

            return redirect()->route('recruitment.requests.show', $fptk->id)
                ->with('toast_success', 'Recruitment request has been submitted for approval. ' . $response . ' approver(s) will review your request.');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to submit for approval. ' . $e->getMessage());
        }
    }

    /**
     * HR Acknowledgment (known_by)
     */
    public function acknowledge(Request $request, $id)
    {
        $fptk = RecruitmentRequest::findOrFail($id);

        // Check if user is the assigned acknowledger
        if (Auth::id() != $fptk->known_by) {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'Anda tidak memiliki izin untuk melakukan acknowledgment FPTK ini.');
        }

        if ($fptk->known_status !== 'pending') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK sudah diproses acknowledgment.');
        }

        $request->validate([
            'acknowledgment_status' => 'required|in:approved,rejected',
            'acknowledgment_remark' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $fptk->update([
                'known_status' => $request->acknowledgment_status,
                'known_remark' => $request->acknowledgment_remark,
                'known_at' => now(),
                'known_timestamps' => now()
            ]);

            // If rejected, update status
            if ($request->acknowledgment_status === 'rejected') {
                $fptk->update(['status' => 'rejected']);
            }

            DB::commit();

            $status = $request->acknowledgment_status === 'approved' ? 'disetujui' : 'ditolak';
            return redirect()->route('recruitment.requests.show', $fptk->id)
                ->with('toast_success', "Acknowledgment FPTK berhasil $status.");
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error acknowledging FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'Terjadi kesalahan saat melakukan acknowledgment. Silakan coba lagi.');
        }
    }

    /**
     * Project Manager Approval
     */
    public function approveByPM(Request $request, $id)
    {
        $fptk = RecruitmentRequest::findOrFail($id);

        // Check if user is the assigned PM approver
        if (Auth::id() != $fptk->approved_by_pm) {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'Anda tidak memiliki izin untuk melakukan approval PM FPTK ini.');
        }

        // Check if HR acknowledgment is approved
        if ($fptk->known_status !== 'approved') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK harus disetujui HR terlebih dahulu.');
        }

        if ($fptk->pm_approval_status !== 'pending') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK sudah diproses approval PM.');
        }

        $request->validate([
            'pm_approval_status' => 'required|in:approved,rejected',
            'pm_approval_remark' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $fptk->update([
                'pm_approval_status' => $request->pm_approval_status,
                'pm_approval_remark' => $request->pm_approval_remark,
                'pm_approved_at' => now(),
                'pm_approval_timestamps' => now()
            ]);

            // If rejected, update final status
            if ($request->pm_approval_status === 'rejected') {
                $fptk->update(['status' => 'rejected']);
            }

            DB::commit();

            $status = $request->pm_approval_status === 'approved' ? 'disetujui' : 'ditolak';
            return redirect()->route('recruitment.requests.show', $fptk->id)
                ->with('toast_success', "Approval PM FPTK berhasil $status.");
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error PM approving FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'Terjadi kesalahan saat melakukan approval PM. Silakan coba lagi.');
        }
    }

    /**
     * Director Approval
     */
    public function approveByDirector(Request $request, $id)
    {
        $fptk = RecruitmentRequest::findOrFail($id);

        // Check if user is the assigned director approver
        if (Auth::id() != $fptk->approved_by_director) {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'Anda tidak memiliki izin untuk melakukan approval Director FPTK ini.');
        }

        // Check if PM approval is approved
        if ($fptk->pm_approval_status !== 'approved') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK harus disetujui Project Manager terlebih dahulu.');
        }

        if ($fptk->director_approval_status !== 'pending') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK sudah diproses approval Director.');
        }

        $request->validate([
            'director_approval_status' => 'required|in:approved,rejected',
            'director_approval_remark' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $fptk->update([
                'director_approval_status' => $request->director_approval_status,
                'director_approval_remark' => $request->director_approval_remark,
                'director_approved_at' => now(),
                'director_approval_timestamps' => now()
            ]);

            // If approved by director, update final status and assign letter number
            if ($request->director_approval_status === 'approved') {
                $fptk->update(['status' => 'approved']);

                // Auto-assign letter number if not already assigned
                if (!$fptk->hasLetterNumber()) {
                    $success = $this->letterNumberService->assignLetterNumberToFPTK($fptk);
                    if ($success) {
                        Log::info('Letter number auto-assigned for approved FPTK', [
                            'fptk_id' => $fptk->id,
                            'letter_number' => $fptk->fresh()->getFPTKLetterNumber()
                        ]);
                    }
                }
            } else {
                $fptk->update(['status' => 'rejected']);
            }

            DB::commit();

            $status = $request->director_approval_status === 'approved' ? 'disetujui' : 'ditolak';
            $message = "Approval Director FPTK berhasil $status.";

            if ($request->director_approval_status === 'approved' && $fptk->hasLetterNumber()) {
                $message .= ' Nomor surat: ' . $fptk->fresh()->getFPTKLetterNumber();
            }

            return redirect()->route('recruitment.requests.show', $fptk->id)
                ->with('toast_success', $message);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error Director approving FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'Terjadi kesalahan saat melakukan approval Director. Silakan coba lagi.');
        }
    }

    /**
     * Approve FPTK (Legacy method - kept for backward compatibility)
     */
    public function approve(Request $request, $id)
    {
        $fptk = RecruitmentRequest::findOrFail($id);

        if ($fptk->status !== 'submitted') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK hanya dapat disetujui dalam status submitted.');
        }

        try {
            DB::beginTransaction();

            // Determine which approval level the current user can perform
            $userId = Auth::id();

            if ($userId == $fptk->known_by && $fptk->known_status === 'pending') {
                // HR Acknowledgment
                $fptk->update([
                    'known_status' => 'approved',
                    'known_remark' => $request->notes ?? 'Approved by HR',
                    'known_at' => now(),
                    'known_timestamps' => now()
                ]);

                $message = 'HR Acknowledgment FPTK berhasil disetujui.';
            } elseif ($userId == $fptk->approved_by_pm && $fptk->pm_approval_status === 'pending' && $fptk->known_status === 'approved') {
                // Project Manager Approval
                $fptk->update([
                    'pm_approval_status' => 'approved',
                    'pm_approval_remark' => $request->notes ?? 'Approved by Project Manager',
                    'pm_approved_at' => now(),
                    'pm_approval_timestamps' => now()
                ]);

                $message = 'Project Manager Approval FPTK berhasil disetujui.';
            } elseif ($userId == $fptk->approved_by_director && $fptk->director_approval_status === 'pending' && $fptk->pm_approval_status === 'approved') {
                // Director Approval
                $fptk->update([
                    'director_approval_status' => 'approved',
                    'director_approval_remark' => $request->notes ?? 'Approved by Director',
                    'director_approved_at' => now(),
                    'director_approval_timestamps' => now(),
                    'status' => 'approved'
                ]);

                // Auto-assign letter number
                if (!$fptk->hasLetterNumber()) {
                    $success = $this->letterNumberService->assignLetterNumberToFPTK($fptk);
                    if ($success) {
                        $message = 'Director Approval FPTK berhasil disetujui. Nomor surat: ' . $fptk->fresh()->getFPTKLetterNumber();
                    } else {
                        $message = 'Director Approval FPTK berhasil disetujui.';
                    }
                } else {
                    $message = 'Director Approval FPTK berhasil disetujui. Nomor surat: ' . $fptk->fresh()->getFPTKLetterNumber();
                }
            } else {
                throw new \Exception('User tidak memiliki izin untuk melakukan approval pada tahap ini.');
            }

            DB::commit();

            return redirect()->route('recruitment.requests.show', $fptk->id)
                ->with('toast_success', $message);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error approving FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'Terjadi kesalahan saat menyetujui FPTK. ' . $e->getMessage());
        }
    }

    /**
     * Reject FPTK (Legacy method - kept for backward compatibility)
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $fptk = RecruitmentRequest::findOrFail($id);

        if ($fptk->status !== 'submitted') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK hanya dapat ditolak dalam status submitted.');
        }

        try {
            DB::beginTransaction();

            // Determine which approval level the current user can perform
            $userId = Auth::id();

            if ($userId == $fptk->known_by && $fptk->known_status === 'pending') {
                // HR Rejection
                $fptk->update([
                    'known_status' => 'rejected',
                    'known_remark' => $request->rejection_reason,
                    'known_at' => now(),
                    'known_timestamps' => now(),
                    'status' => 'rejected'
                ]);

                $message = 'FPTK berhasil ditolak oleh HR.';
            } elseif ($userId == $fptk->approved_by_pm && $fptk->pm_approval_status === 'pending' && $fptk->known_status === 'approved') {
                // Project Manager Rejection
                $fptk->update([
                    'pm_approval_status' => 'rejected',
                    'pm_approval_remark' => $request->rejection_reason,
                    'pm_approved_at' => now(),
                    'pm_approval_timestamps' => now(),
                    'status' => 'rejected'
                ]);

                $message = 'FPTK berhasil ditolak oleh Project Manager.';
            } elseif ($userId == $fptk->approved_by_director && $fptk->director_approval_status === 'pending' && $fptk->pm_approval_status === 'approved') {
                // Director Rejection
                $fptk->update([
                    'director_approval_status' => 'rejected',
                    'director_approval_remark' => $request->rejection_reason,
                    'director_approved_at' => now(),
                    'director_approval_timestamps' => now(),
                    'status' => 'rejected'
                ]);

                $message = 'FPTK berhasil ditolak oleh Director.';
            } else {
                throw new \Exception('User tidak memiliki izin untuk melakukan rejection pada tahap ini.');
            }

            DB::commit();

            return redirect()->route('recruitment.requests.show', $fptk->id)
                ->with('toast_success', $message);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error rejecting FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'Terjadi kesalahan saat menolak FPTK. ' . $e->getMessage());
        }
    }

    /**
     * Delete FPTK
     */
    public function destroy($id)
    {
        $fptk = RecruitmentRequest::findOrFail($id);

        // Only allow deletion if status is draft
        if ($fptk->status !== 'draft') {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK hanya dapat dihapus dalam status draft.');
        }

        try {
            DB::beginTransaction();

            // Release letter number if exists
            if ($fptk->hasLetterNumber()) {
                $this->letterNumberService->releaseLetterNumberFromFPTK($fptk);
            }

            $fptk->delete();

            DB::commit();

            return redirect()->route('recruitment.requests.index')
                ->with('toast_success', 'FPTK berhasil dihapus.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error deleting FPTK: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'Terjadi kesalahan saat menghapus FPTK. Silakan coba lagi.');
        }
    }

    /**
     * Assign letter number manually
     */
    public function assignLetterNumber($id)
    {
        $fptk = RecruitmentRequest::findOrFail($id);

        if ($fptk->hasLetterNumber()) {
            return redirect()->route('recruitment.requests.show', $id)
                ->with('toast_error', 'FPTK sudah memiliki nomor surat.');
        }

        try {
            $success = $this->letterNumberService->assignLetterNumberToFPTK($fptk);

            if ($success) {
                return redirect()->route('recruitment.requests.show', $id)
                    ->with('toast_success', 'Nomor surat berhasil di-assign: ' . $fptk->fresh()->getFPTKLetterNumber());
            } else {
                return redirect()->back()
                    ->with('toast_error', 'Gagal assign nomor surat. Silakan coba lagi.');
            }
        } catch (Exception $e) {
            Log::error('Error assigning letter number: ' . $e->getMessage());

            return redirect()->back()
                ->with('toast_error', 'Terjadi kesalahan saat assign nomor surat. Silakan coba lagi.');
        }
    }

    /**
     * Get FPTK data for AJAX
     */
    public function getFPTKData($id)
    {
        $fptk = RecruitmentRequest::with([
            'department',
            'project',
            'position',
            'level'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $fptk->id,
                'request_number' => $fptk->request_number,
                'letter_number' => $fptk->getFPTKLetterNumber(),
                'department' => $fptk->department->name,
                'project' => $fptk->project->name,
                'position' => $fptk->position->name,
                'level' => $fptk->level->name,
                'required_qty' => $fptk->required_qty,
                'remaining_positions' => $fptk->remaining_positions,
                'status' => $fptk->status,
                'can_receive_applications' => $fptk->canReceiveApplications(),
                'employment_type' => $fptk->employment_type,
                'job_description' => $fptk->job_description,
                'requirements' => [
                    'gender' => $fptk->required_gender,
                    'age_min' => $fptk->required_age_min,
                    'age_max' => $fptk->required_age_max,
                    'marital_status' => $fptk->required_marital_status,
                    'education' => $fptk->required_education,
                    'skills' => $fptk->required_skills,
                    'experience' => $fptk->required_experience,
                ]
            ]
        ]);
    }

    /**
     * Convert number to Roman Numeral
     */
    private function numberToRoman($number)
    {
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];

        $result = '';
        foreach ($map as $roman => $value) {
            while ($number >= $value) {
                $result .= $roman;
                $number -= $value;
            }
        }
        return $result;
    }

    /**
     * Show HR Acknowledgment form
     */
    // public function showAcknowledgmentForm($id)
    // {
    //     $fptk = RecruitmentRequest::with([
    //         'department',
    //         'project',
    //         'position',
    //         'level',
    //         'acknowledger'
    //     ])->findOrFail($id);

    //     // Check if user can acknowledge
    //     if (!auth()->user()->can('recruitment-requests.acknowledge')) {
    //         return redirect()->back()->with('toast_error', 'You do not have permission to acknowledge this FPTK');
    //     }

    //     // Check if user is assigned as acknowledger
    //     if (auth()->id() != $fptk->known_by) {
    //         return redirect()->back()->with('toast_error', 'You are not assigned as acknowledger for this FPTK');
    //     }

    //     // Check if already acknowledged
    //     if ($fptk->known_status != 'pending') {
    //         return redirect()->back()->with('toast_error', 'This FPTK has already been acknowledged');
    //     }

    //     $title = 'Recruitment Requests (FPTK)';
    //     $subtitle = 'HR Acknowledgment';

    //     return view('recruitment.requests.acknowledge', compact('title', 'subtitle', 'fptk'));
    // }

    /**
     * Show Project Manager Approval form
     */
    // public function showPMApprovalForm($id)
    // {
    //     $fptk = RecruitmentRequest::with([
    //         'department',
    //         'project',
    //         'position',
    //         'level',
    //         'acknowledger',
    //         'projectManagerApprover'
    //     ])->findOrFail($id);

    //     // Check if user can approve
    //     if (!auth()->user()->can('recruitment-requests.approve')) {
    //         return redirect()->back()->with('toast_error', 'You do not have permission to approve this FPTK');
    //     }

    //     // Check if user is assigned as PM approver
    //     if (auth()->id() != $fptk->approved_by_pm) {
    //         return redirect()->back()->with('toast_error', 'You are not assigned as PM approver for this FPTK');
    //     }

    //     // Check if HR acknowledgment is approved
    //     if ($fptk->known_status != 'approved') {
    //         return redirect()->back()->with('toast_error', 'Cannot approve FPTK that has not been acknowledged by HR');
    //     }

    //     // Check if already approved by PM
    //     if ($fptk->pm_approval_status != 'pending') {
    //         return redirect()->back()->with('toast_error', 'This FPTK has already been approved by Project Manager');
    //     }

    //     $title = 'Recruitment Requests (FPTK)';
    //     $subtitle = 'Project Manager Approval';

    //     return view('recruitment.requests.approve-pm', compact('title', 'subtitle', 'fptk'));
    // }

    /**
     * Show Director Approval form
     */
    // public function showDirectorApprovalForm($id)
    // {
    //     $fptk = RecruitmentRequest::with([
    //         'department',
    //         'project',
    //         'position',
    //         'level',
    //         'acknowledger',
    //         'projectManagerApprover',
    //         'directorApprover'
    //     ])->findOrFail($id);

    //     // Check if user can approve
    //     if (!auth()->user()->can('recruitment-requests.approve')) {
    //         return redirect()->back()->with('toast_error', 'You do not have permission to approve this FPTK');
    //     }

    //     // Check if user is assigned as director approver
    //     if (auth()->id() != $fptk->approved_by_director) {
    //         return redirect()->back()->with('toast_error', 'You are not assigned as director approver for this FPTK');
    //     }

    //     // Check if PM approval is approved
    //     if ($fptk->pm_approval_status != 'approved') {
    //         return redirect()->back()->with('toast_error', 'Cannot approve FPTK that has not been approved by Project Manager');
    //     }

    //     // Check if already approved by director
    //     if ($fptk->director_approval_status != 'pending') {
    //         return redirect()->back()->with('toast_error', 'This FPTK has already been approved by Director');
    //     }

    //     $title = 'Recruitment Requests (FPTK)';
    //     $subtitle = 'Director Approval';

    //     return view('recruitment.requests.approve-director', compact('title', 'subtitle', 'fptk'));
    // }
}
