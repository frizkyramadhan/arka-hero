# Recruitment System - Comprehensive Analysis

## 📋 Overview

Sistem recruitment yang akan dibangun memiliki 9 tahapan yang terintegrasi dengan sistem HR yang sudah ada:

1. **FPTK** - Formulir Permintaan Tenaga Kerja
2. **CV Application** - Pengumpulan dan review CV
3. **Psikotes** - Tes psikologi
4. **Tes Teori** - Tes pengetahuan/kompetensi
5. **Interview HR dan User** - Wawancara dengan HR dan user department
6. **Offering** - Penawaran kerja
7. **MCU** - Medical Check Up
8. **Hire** - Pengangkatan karyawan
9. **Onboarding** - Orientasi karyawan baru

## 🏗️ Database Design

### Core Tables

#### 1. recruitment_requests (FPTK)

```sql
CREATE TABLE recruitment_requests (
    id UUID PRIMARY KEY,
    request_number VARCHAR(50) UNIQUE NOT NULL, -- Format: No.000/HCS-HO/PRF/1/2017

    -- Informasi Dasar
    department_id BIGINT UNSIGNED NOT NULL, -- Divisi/Departement
    project_id BIGINT UNSIGNED NOT NULL, -- Site Project
    position_id BIGINT UNSIGNED NOT NULL, -- Jabatan Diperlukan
    level_id BIGINT UNSIGNED NOT NULL, -- Level Diperlukan
    required_qty INTEGER NOT NULL, -- Jumlah Diperlukan
    required_date DATE NOT NULL, -- Waktu Diperlukan

    -- Status Pekerjaan
    employment_type ENUM('pkwtt', 'pkwt', 'harian', 'magang') NOT NULL, -- PKWTT, PKWT, Harian, Magang

    -- Alasan Permintaan
    request_reason ENUM('replacement_resign', 'replacement_promotion', 'additional_workplan', 'other') NOT NULL,
    other_reason TEXT NULL, -- Jika alasan = 'other'

    -- Uraian Singkat Pekerjaan
    job_description TEXT NULL

    -- Persyaratan
    required_gender ENUM('male', 'female', 'any') DEFAULT 'any', -- Jenis Kelamin
    required_age_min INTEGER NULL, -- Usia minimal
    required_age_max INTEGER NULL, -- Usia maksimal
    required_marital_status ENUM('single', 'married', 'any') DEFAULT 'any', -- Status Perkawinan
    required_education VARCHAR(255) NULL, -- Pendidikan Minimal
    required_skills TEXT NULL, -- Kemampuan Wajib
    required_experience TEXT NULL, -- Pengalaman Kerja
    required_physical TEXT NULL, -- Persyaratan Fisik
    required_mental TEXT NULL, -- Persyaratan Mental
    other_requirements TEXT NULL, -- Keterangan Lain

    -- Workflow & Approval
    requested_by BIGINT UNSIGNED NOT NULL, -- Diajukan Oleh (Dept Head/Section Head)
    submitted_at TIMESTAMP NULL, -- Tanggal Pengajuan

    -- HR Review
    hr_reviewed_by BIGINT UNSIGNED NULL, -- HR&GA Section Head
    hr_reviewed_at TIMESTAMP NULL,
    hr_notes TEXT NULL,

    -- Project Manager Approval
    pm_approved_by BIGINT UNSIGNED NULL, -- Project Manager
    pm_approved_at TIMESTAMP NULL,
    pm_notes TEXT NULL,

    -- Final Approval
    final_approved_by BIGINT UNSIGNED NULL, -- Operation Director / HCS Division Manager
    final_approved_at TIMESTAMP NULL,
    final_notes TEXT NULL,

    -- Status Management
    status ENUM('draft', 'submitted', 'hr_review', 'pm_approved', 'final_approved', 'rejected', 'cancelled', 'expired') DEFAULT 'draft',
    rejection_reason TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (level_id) REFERENCES levels(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (hr_reviewed_by) REFERENCES users(id),
    FOREIGN KEY (pm_approved_by) REFERENCES users(id),
    FOREIGN KEY (final_approved_by) REFERENCES users(id)
);
```

#### 2. recruitment_candidates

```sql
CREATE TABLE recruitment_candidates (
    id UUID PRIMARY KEY,
    candidate_number VARCHAR(50) UNIQUE NOT NULL,
    recruitment_request_id UUID NOT NULL,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    date_of_birth DATE,
    education_level VARCHAR(100),
    experience_years INTEGER,
    current_status ENUM('active', 'withdrawn', 'rejected', 'hired') DEFAULT 'active',
    source VARCHAR(100), -- iklan media massa / internet, bank data / database pelamar, relasi / rekomendasi, job fair / agent / consultant, sekolah / perguruan tinggi, seleksi administrasi, promosi, mutasi
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (recruitment_request_id) REFERENCES recruitment_requests(id)
);
```

#### 3. recruitment_stages

```sql
CREATE TABLE recruitment_stages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stage_name VARCHAR(100) NOT NULL,
    stage_order INTEGER NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default stages
INSERT INTO recruitment_stages (stage_name, stage_order) VALUES
('FPTK', 1),
('CV Review', 2),
('Psikotes', 3),
('Tes Teori', 4),
('Interview HR', 5),
('Interview User', 6),
('Offering', 7),
('MCU', 8),
('Hire', 9),
('Onboarding', 10);
```

#### 4. recruitment_stage_results

```sql
CREATE TABLE recruitment_stage_results (
    id UUID PRIMARY KEY,
    candidate_id UUID NOT NULL,
    stage_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'passed', 'failed', 'skipped') DEFAULT 'pending',
    score DECIMAL(5,2) NULL,
    notes TEXT,
    scheduled_date TIMESTAMP NULL,
    completed_date TIMESTAMP NULL,
    evaluator_id BIGINT UNSIGNED NULL,
    documents JSON, -- Store uploaded files info
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (candidate_id) REFERENCES recruitment_candidates(id),
    FOREIGN KEY (stage_id) REFERENCES recruitment_stages(id),
    FOREIGN KEY (evaluator_id) REFERENCES users(id),
    UNIQUE KEY unique_candidate_stage (candidate_id, stage_id)
);
```

#### 5. recruitment_interviews

```sql
CREATE TABLE recruitment_interviews (
    id UUID PRIMARY KEY,
    stage_result_id UUID NOT NULL,
    interview_type ENUM('hr', 'user', 'panel') NOT NULL,
    scheduled_date TIMESTAMP NOT NULL,
    duration_minutes INTEGER DEFAULT 60,
    location VARCHAR(255),
    meeting_link TEXT,
    interviewer_ids JSON, -- Array of user IDs
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    overall_score DECIMAL(5,2),
    recommendation ENUM('strongly_recommend', 'recommend', 'neutral', 'not_recommend') NULL,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (stage_result_id) REFERENCES recruitment_stage_results(id)
);
```

#### 6. recruitment_offers

```sql
CREATE TABLE recruitment_offers (
    id UUID PRIMARY KEY,
    candidate_id UUID NOT NULL,
    offer_letter_number VARCHAR(50) UNIQUE NOT NULL,
    position_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    basic_salary DECIMAL(15,2) NOT NULL,
    allowances JSON, -- {'transport': 500000, 'meal': 300000, etc.}
    benefits JSON, -- {'health_insurance': true, 'bpjs': true, etc.}
    employment_type ENUM('permanent', 'contract', 'internship') NOT NULL,
    contract_duration INTEGER NULL, -- in months for contract
    start_date DATE NOT NULL,
    offer_valid_until DATE NOT NULL,
    status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired') DEFAULT 'draft',
    sent_at TIMESTAMP NULL,
    responded_at TIMESTAMP NULL,
    response_notes TEXT,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (candidate_id) REFERENCES recruitment_candidates(id),
    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### 7. recruitment_documents

```sql
CREATE TABLE recruitment_documents (
    id UUID PRIMARY KEY,
    candidate_id UUID NOT NULL,
    document_type VARCHAR(100) NOT NULL, -- 'cv', 'cover_letter', 'certificate', 'test_result', etc.
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INTEGER NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (candidate_id) REFERENCES recruitment_candidates(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
```

## 📊 Model Structure

### 1. RecruitmentRequest Model

```php
<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentRequest extends Model
{
    use Uuids;

    protected $fillable = [
        'request_number', 'department_id', 'site_project', 'position_required',
        'quantity', 'required_date', 'employment_type', 'request_reason', 'other_reason',
        'job_description_1', 'job_description_2', 'job_description_3',
        'required_gender', 'required_age_min', 'required_age_max', 'required_marital_status',
        'required_education', 'required_skills', 'required_experience',
        'required_physical', 'required_mental', 'other_requirements',
        'requested_by', 'submitted_at', 'hr_reviewed_by', 'hr_reviewed_at', 'hr_notes',
        'pm_approved_by', 'pm_approved_at', 'pm_notes',
        'final_approved_by', 'final_approved_at', 'final_notes',
        'status', 'rejection_reason'
    ];

    protected $casts = [
        'required_date' => 'date',
        'submitted_at' => 'datetime',
        'hr_reviewed_at' => 'datetime',
        'pm_approved_at' => 'datetime',
        'final_approved_at' => 'datetime'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function hrReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_reviewed_by');
    }

    public function pmApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pm_approved_by');
    }

    public function finalApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_approved_by');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(RecruitmentCandidate::class);
    }

    // Helper methods for workflow status
    public function isSubmitted(): bool
    {
        return !is_null($this->submitted_at);
    }

    public function isHrReviewed(): bool
    {
        return !is_null($this->hr_reviewed_at);
    }

    public function isPmApproved(): bool
    {
        return !is_null($this->pm_approved_at);
    }

    public function isFinalApproved(): bool
    {
        return !is_null($this->final_approved_at);
    }
}
```

### 2. RecruitmentCandidate Model

```php
<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentCandidate extends Model
{
    use Uuids;

    protected $fillable = [
        'candidate_number', 'recruitment_request_id', 'fullname', 'email',
        'phone', 'address', 'date_of_birth', 'education_level',
        'experience_years', 'current_status', 'source', 'applied_at'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'applied_at' => 'datetime'
    ];

    public function recruitmentRequest(): BelongsTo
    {
        return $this->belongsTo(RecruitmentRequest::class);
    }

    public function stageResults(): HasMany
    {
        return $this->hasMany(RecruitmentStageResult::class, 'candidate_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RecruitmentDocument::class, 'candidate_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(RecruitmentOffer::class, 'candidate_id');
    }

    public function getCurrentStage()
    {
        return $this->stageResults()
            ->with('stage')
            ->where('status', 'pending')
            ->orderBy('stage_id')
            ->first();
    }
}
```

## 🔄 Workflow Management

### 1. Recruitment Process Flow

```php
<?php

namespace App\Services;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentStage;
use App\Models\RecruitmentStageResult;

class RecruitmentWorkflowService
{
    public function initializeCandidateStages(RecruitmentCandidate $candidate)
    {
        $stages = RecruitmentStage::where('is_active', true)
            ->orderBy('stage_order')
            ->get();

        foreach ($stages as $stage) {
            RecruitmentStageResult::create([
                'candidate_id' => $candidate->id,
                'stage_id' => $stage->id,
                'status' => $stage->stage_order === 1 ? 'pending' : 'pending'
            ]);
        }
    }

    public function advanceToNextStage(RecruitmentCandidate $candidate, $currentStageId)
    {
        // Mark current stage as completed
        $currentResult = RecruitmentStageResult::where('candidate_id', $candidate->id)
            ->where('stage_id', $currentStageId)
            ->first();

        if ($currentResult) {
            $currentResult->update(['status' => 'passed', 'completed_date' => now()]);
        }

        // Find next stage
        $nextStage = RecruitmentStage::where('stage_order', '>',
            RecruitmentStage::find($currentStageId)->stage_order)
            ->orderBy('stage_order')
            ->first();

        if ($nextStage) {
            $nextResult = RecruitmentStageResult::where('candidate_id', $candidate->id)
                ->where('stage_id', $nextStage->id)
                ->first();

            if ($nextResult) {
                $nextResult->update(['status' => 'pending']);
            }
        }
    }

    public function rejectCandidate(RecruitmentCandidate $candidate, $stageId, $reason)
    {
        // Mark current stage as failed
        RecruitmentStageResult::where('candidate_id', $candidate->id)
            ->where('stage_id', $stageId)
            ->update([
                'status' => 'failed',
                'notes' => $reason,
                'completed_date' => now()
            ]);

        // Update candidate status
        $candidate->update(['current_status' => 'rejected']);
    }
}
```

## 🎯 Controller Structure

### 1. RecruitmentRequestController

```php
<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentRequest;
use App\Services\RecruitmentWorkflowService;
use Illuminate\Http\Request;

class RecruitmentRequestController extends Controller
{
    protected $workflowService;

    public function __construct(RecruitmentWorkflowService $workflowService)
    {
        $this->middleware('auth');
        $this->workflowService = $workflowService;
    }

    public function index()
    {
        $title = 'Recruitment Requests';
        $subtitle = 'FPTK - Formulir Permintaan Tenaga Kerja';
        return view('recruitment.requests.index', compact('title', 'subtitle'));
    }

    public function create()
    {
        $departments = Department::where('department_status', '1')->get();

        return view('recruitment.requests.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Informasi Dasar
            'department_id' => 'required|exists:departments,id',
            'site_project' => 'required|string|max:255',
            'position_required' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'required_date' => 'required|date|after:today',

            // Status Pekerjaan
            'employment_type' => 'required|in:pkwtt,pkwt,harian,magang',

            // Alasan Permintaan
            'request_reason' => 'required|in:replacement_resign,replacement_promotion,additional_workplan,other',
            'other_reason' => 'nullable|string|required_if:request_reason,other',

            // Uraian Singkat Pekerjaan
            'job_description_1' => 'nullable|string',
            'job_description_2' => 'nullable|string',
            'job_description_3' => 'nullable|string',

            // Persyaratan
            'required_gender' => 'required|in:male,female,any',
            'required_age_min' => 'nullable|integer|min:17|max:65',
            'required_age_max' => 'nullable|integer|min:17|max:65|gte:required_age_min',
            'required_marital_status' => 'required|in:single,married,any',
            'required_education' => 'nullable|string|max:255',
            'required_skills' => 'nullable|string',
            'required_experience' => 'nullable|string',
            'required_physical' => 'nullable|string',
            'required_mental' => 'nullable|string',
            'other_requirements' => 'nullable|string'
        ]);

        $validated['requested_by'] = auth()->id();
        $validated['request_number'] = $this->generateRequestNumber();
        $validated['status'] = 'draft';

        $fptk = RecruitmentRequest::create($validated);

        return redirect()->route('recruitment.requests.show', $fptk)
            ->with('success', 'FPTK berhasil dibuat');
    }

    /**
     * Submit FPTK for approval
     */
    public function submit(RecruitmentRequest $request)
    {
        if ($request->status !== 'draft') {
            return redirect()->back()->with('error', 'FPTK sudah disubmit');
        }

        $request->update([
            'status' => 'submitted',
            'submitted_at' => now()
        ]);

        return redirect()->route('recruitment.requests.show', $request)
            ->with('success', 'FPTK berhasil disubmit untuk review');
    }

    /**
     * HR Review FPTK
     */
    public function hrReview(Request $request, RecruitmentRequest $fptk)
    {
        $validated = $request->validate([
            'hr_notes' => 'nullable|string',
            'action' => 'required|in:approve,reject'
        ]);

        if ($validated['action'] === 'approve') {
            $fptk->update([
                'status' => 'hr_review',
                'hr_reviewed_by' => auth()->id(),
                'hr_reviewed_at' => now(),
                'hr_notes' => $validated['hr_notes']
            ]);
            $message = 'FPTK telah direview oleh HR';
        } else {
            $fptk->update([
                'status' => 'rejected',
                'hr_reviewed_by' => auth()->id(),
                'hr_reviewed_at' => now(),
                'hr_notes' => $validated['hr_notes'],
                'rejection_reason' => $validated['hr_notes']
            ]);
            $message = 'FPTK telah ditolak oleh HR';
        }

        return redirect()->route('recruitment.requests.show', $fptk)
            ->with('success', $message);
    }

    /**
     * Project Manager Approval
     */
    public function pmApproval(Request $request, RecruitmentRequest $fptk)
    {
        $validated = $request->validate([
            'pm_notes' => 'nullable|string',
            'action' => 'required|in:approve,reject'
        ]);

        if ($validated['action'] === 'approve') {
            $fptk->update([
                'status' => 'pm_approved',
                'pm_approved_by' => auth()->id(),
                'pm_approved_at' => now(),
                'pm_notes' => $validated['pm_notes']
            ]);
            $message = 'FPTK telah disetujui oleh Project Manager';
        } else {
            $fptk->update([
                'status' => 'rejected',
                'pm_approved_by' => auth()->id(),
                'pm_approved_at' => now(),
                'pm_notes' => $validated['pm_notes'],
                'rejection_reason' => $validated['pm_notes']
            ]);
            $message = 'FPTK telah ditolak oleh Project Manager';
        }

        return redirect()->route('recruitment.requests.show', $fptk)
            ->with('success', $message);
    }

    /**
     * Final Approval by Operation Director
     */
    public function finalApproval(Request $request, RecruitmentRequest $fptk)
    {
        $validated = $request->validate([
            'final_notes' => 'nullable|string',
            'action' => 'required|in:approve,reject'
        ]);

        if ($validated['action'] === 'approve') {
            $fptk->update([
                'status' => 'final_approved',
                'final_approved_by' => auth()->id(),
                'final_approved_at' => now(),
                'final_notes' => $validated['final_notes']
            ]);
            $message = 'FPTK telah disetujui secara final dan dapat digunakan untuk recruitment';
        } else {
            $fptk->update([
                'status' => 'rejected',
                'final_approved_by' => auth()->id(),
                'final_approved_at' => now(),
                'final_notes' => $validated['final_notes'],
                'rejection_reason' => $validated['final_notes']
            ]);
            $message = 'FPTK telah ditolak oleh Operation Director';
        }

        return redirect()->route('recruitment.requests.show', $fptk)
            ->with('success', $message);
    }

    private function generateRequestNumber()
    {
        $prefix = 'No.';
        $year = date('Y');
        $month = date('m');

        $lastNumber = RecruitmentRequest::where('request_number', 'like', "{$prefix}%/HCS-HO/PRF/{$month}/{$year}")
            ->orderBy('request_number', 'desc')
            ->first();

        if ($lastNumber) {
            // Extract sequence number from format: No.001/HCS-HO/PRF/1/2017
            preg_match('/No\.(\d+)\//', $lastNumber->request_number, $matches);
            $lastSequence = intval($matches[1]);
            $sequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }

        return "{$prefix}{$sequence}/HCS-HO/PRF/{$month}/{$year}";
    }
}
```

### 2. RecruitmentCandidateController

```php
<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentRequest;
use App\Services\RecruitmentWorkflowService;
use Illuminate\Http\Request;

class RecruitmentCandidateController extends Controller
{
    protected $workflowService;

    public function __construct(RecruitmentWorkflowService $workflowService)
    {
        $this->middleware('auth');
        $this->workflowService = $workflowService;
    }

    public function index()
    {
        $title = 'Candidates';
        $subtitle = 'Manage Recruitment Candidates';
        return view('recruitment.candidates.index', compact('title', 'subtitle'));
    }

    public function create(RecruitmentRequest $request)
    {
        return view('recruitment.candidates.create', compact('request'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recruitment_request_id' => 'required|exists:recruitment_requests,id',
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string',
            'date_of_birth' => 'required|date',
            'education_level' => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0',
            'source' => 'required|string|max:100'
        ]);

        $validated['candidate_number'] = $this->generateCandidateNumber();
        $validated['applied_at'] = now();

        $candidate = RecruitmentCandidate::create($validated);

        // Initialize candidate stages
        $this->workflowService->initializeCandidateStages($candidate);

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Kandidat berhasil ditambahkan');
    }

    private function generateCandidateNumber()
    {
        $prefix = 'CAND';
        $year = date('Y');
        $month = date('m');

        $lastNumber = RecruitmentCandidate::where('candidate_number', 'like', "{$prefix}/{$year}/{$month}%")
            ->orderBy('candidate_number', 'desc')
            ->first();

        if ($lastNumber) {
            $lastSequence = intval(substr($lastNumber->candidate_number, -4));
            $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return "{$prefix}/{$year}/{$month}/{$sequence}";
    }
}
```

## 🚪 Permissions & Authorization

### 1. Permission Structure

```php
// database/seeders/RecruitmentPermissionSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RecruitmentPermissionSeeder extends Seeder
{
    public function run()
    {
        // Recruitment permissions
        $permissions = [
            // FPTK permissions
            'recruitment.fptk.show',
            'recruitment.fptk.create',
            'recruitment.fptk.edit',
            'recruitment.fptk.delete',
            'recruitment.fptk.approve',

            // Candidate permissions
            'recruitment.candidates.show',
            'recruitment.candidates.create',
            'recruitment.candidates.edit',
            'recruitment.candidates.delete',

            // Stage permissions
            'recruitment.stages.cv-review',
            'recruitment.stages.psikotes',
            'recruitment.stages.theory-test',
            'recruitment.stages.interview-hr',
            'recruitment.stages.interview-user',
            'recruitment.stages.offering',
            'recruitment.stages.mcu',
            'recruitment.stages.hire',
            'recruitment.stages.onboarding',

            // Document permissions
            'recruitment.documents.upload',
            'recruitment.documents.download',
            'recruitment.documents.verify',

            // Reporting permissions
            'recruitment.reports.show',
            'recruitment.reports.export',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Update existing roles
        $hrManagerRole = Role::findByName('hr-manager');
        $hrManagerRole->givePermissionTo([
            'recruitment.fptk.show',
            'recruitment.fptk.create',
            'recruitment.fptk.edit',
            'recruitment.fptk.approve',
            'recruitment.candidates.show',
            'recruitment.candidates.create',
            'recruitment.candidates.edit',
            'recruitment.stages.cv-review',
            'recruitment.stages.psikotes',
            'recruitment.stages.theory-test',
            'recruitment.stages.interview-hr',
            'recruitment.stages.offering',
            'recruitment.stages.mcu',
            'recruitment.stages.hire',
            'recruitment.stages.onboarding',
            'recruitment.documents.upload',
            'recruitment.documents.download',
            'recruitment.documents.verify',
            'recruitment.reports.show',
            'recruitment.reports.export',
        ]);

        $hrSupervisorRole = Role::findByName('hr-supervisor');
        $hrSupervisorRole->givePermissionTo([
            'recruitment.fptk.show',
            'recruitment.fptk.create',
            'recruitment.fptk.edit',
            'recruitment.candidates.show',
            'recruitment.candidates.create',
            'recruitment.candidates.edit',
            'recruitment.stages.cv-review',
            'recruitment.stages.psikotes',
            'recruitment.stages.theory-test',
            'recruitment.stages.interview-hr',
            'recruitment.documents.upload',
            'recruitment.documents.download',
            'recruitment.reports.show',
        ]);

        // Create specific recruitment roles
        $recruiterRole = Role::findOrCreate('recruiter');
        $recruiterRole->givePermissionTo([
            'recruitment.candidates.show',
            'recruitment.candidates.create',
            'recruitment.candidates.edit',
            'recruitment.stages.cv-review',
            'recruitment.stages.psikotes',
            'recruitment.stages.theory-test',
            'recruitment.documents.upload',
            'recruitment.documents.download',
        ]);

        $interviewerRole = Role::findOrCreate('interviewer');
        $interviewerRole->givePermissionTo([
            'recruitment.candidates.show',
            'recruitment.stages.interview-hr',
            'recruitment.stages.interview-user',
            'recruitment.documents.download',
        ]);
    }
}
```

## 📁 File Management

### 1. File Upload Service

```php
<?php

namespace App\Services;

use App\Models\RecruitmentDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecruitmentFileService
{
    public function uploadDocument(UploadedFile $file, $candidateId, $documentType, $uploadedBy)
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "recruitment-documents/{$candidateId}";
        $filePath = $file->storeAs($path, $filename, 'private');

        return RecruitmentDocument::create([
            'candidate_id' => $candidateId,
            'document_type' => $documentType,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $filename,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => $uploadedBy,
        ]);
    }

    public function downloadDocument($documentId)
    {
        $document = RecruitmentDocument::findOrFail($documentId);

        if (!Storage::disk('private')->exists($document->file_path)) {
            throw new \Exception('File not found');
        }

        return Storage::disk('private')->download(
            $document->file_path,
            $document->original_filename
        );
    }

    public function deleteDocument($documentId)
    {
        $document = RecruitmentDocument::findOrFail($documentId);

        if (Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        $document->delete();
    }
}
```

## 📧 Notification System

### 1. Email Notifications

```php
<?php

namespace App\Mail;

use App\Models\RecruitmentCandidate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RecruitmentCandidate $candidate,
        public $interviewDetails
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Interview Invitation - ' . $this->candidate->fullname,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recruitment.interview-invitation',
            with: [
                'candidate' => $this->candidate,
                'details' => $this->interviewDetails
            ]
        );
    }
}
```

### 2. System Notifications

```php
<?php

namespace App\Notifications;

use App\Models\RecruitmentCandidate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class CandidateStageUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public RecruitmentCandidate $candidate,
        public string $stageName,
        public string $status
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'candidate_id' => $this->candidate->id,
            'candidate_name' => $this->candidate->fullname,
            'stage_name' => $this->stageName,
            'status' => $this->status,
            'message' => "Kandidat {$this->candidate->fullname} telah {$this->status} pada tahap {$this->stageName}"
        ];
    }
}
```

## 🎨 UI/UX Design

### 1. Recruitment Dashboard

```php
// resources/views/recruitment/dashboard.blade.php
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recruitment Dashboard</h3>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $stats['active_requests'] }}</h3>
                                    <p>Active FPTK</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $stats['active_candidates'] }}</h3>
                                    <p>Active Candidates</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['pending_interviews'] }}</h3>
                                    <p>Pending Interviews</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $stats['pending_offers'] }}</h3>
                                    <p>Pending Offers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-handshake"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### 2. Candidate Progress Tracker

```php
// resources/views/recruitment/candidates/show.blade.php
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recruitment Progress</h3>
    </div>
    <div class="card-body">
        <div class="recruitment-progress">
            @foreach($candidate->stageResults as $stageResult)
                <div class="stage-item {{ $stageResult->status }}">
                    <div class="stage-icon">
                        @switch($stageResult->stage->stage_name)
                            @case('FPTK')
                                <i class="fas fa-file-alt"></i>
                                @break
                            @case('CV Review')
                                <i class="fas fa-file-pdf"></i>
                                @break
                            @case('Psikotes')
                                <i class="fas fa-brain"></i>
                                @break
                            @case('Interview HR')
                                <i class="fas fa-user-tie"></i>
                                @break
                            @case('Offering')
                                <i class="fas fa-handshake"></i>
                                @break
                            @case('MCU')
                                <i class="fas fa-stethoscope"></i>
                                @break
                            @case('Hire')
                                <i class="fas fa-user-check"></i>
                                @break
                            @case('Onboarding')
                                <i class="fas fa-user-plus"></i>
                                @break
                            @default
                                <i class="fas fa-check-circle"></i>
                        @endswitch
                    </div>
                    <div class="stage-content">
                        <h4>{{ $stageResult->stage->stage_name }}</h4>
                        <span class="status-badge {{ $stageResult->status }}">
                            {{ ucfirst($stageResult->status) }}
                        </span>
                        @if($stageResult->completed_date)
                            <p class="completion-date">
                                Completed: {{ $stageResult->completed_date->format('d M Y') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
```

## 🚀 Implementation Timeline

### Phase 1: Foundation (Week 1-2)

-   [ ] Database migration files
-   [ ] Basic models and relationships
-   [ ] Authentication and authorization setup
-   [ ] Base controllers and routes

### Phase 2: Core Features (Week 3-4)

-   [ ] FPTK (Recruitment Request) management
-   [ ] Candidate registration and management
-   [ ] Basic workflow implementation
-   [ ] File upload system

### Phase 3: Advanced Features (Week 5-6)

-   [ ] Interview scheduling system
-   [ ] Offering management
-   [ ] MCU tracking
-   [ ] Onboarding process

### Phase 4: UI/UX & Integration (Week 7-8)

-   [ ] Dashboard and reporting
-   [ ] Email notifications
-   [ ] System integration with existing HR modules
-   [ ] Testing and bug fixes

## 🔧 Technical Considerations

### 1. Integration Points

-   **Employee System**: Successful candidates akan otomatis dibuat sebagai Employee
-   **User Management**: Integration dengan existing role-based access control
-   **Project Management**: FPTK terhubung dengan project yang membutuhkan tenaga kerja
-   **Document Management**: Konsisten dengan sistem file management yang sudah ada

### 2. Performance Optimization

-   **Database Indexing**: Proper indexing pada field yang sering di-query
-   **Caching**: Cache untuk dropdown data dan statistics
-   **Queue System**: Background processing untuk email dan notifications
-   **File Storage**: Efficient file storage dengan private access

### 3. Security Measures

-   **File Upload Validation**: Strict validation untuk document upload
-   **Access Control**: Role-based access untuk setiap stage
-   **Data Encryption**: Sensitive data encryption
-   **Audit Trail**: Complete audit log untuk recruitment activities

## 📈 Success Metrics

### 1. Key Performance Indicators

-   **Time to Hire**: Average time dari FPTK approval sampai hire
-   **Candidate Conversion Rate**: Percentage kandidat yang berhasil hired
-   **Process Efficiency**: Reduction in manual processes
-   **User Satisfaction**: Feedback dari HR team dan hiring managers

### 2. Reporting Features

-   **Recruitment Pipeline Report**: Status kandidat di setiap stage
-   **Time-based Analytics**: Recruitment trends over time
-   **Department-wise Statistics**: Recruitment by department/project
-   **Cost Analysis**: Recruitment cost per hire

---

## 🎯 Next Steps

1. **Database Design Review**: Validate table structure dengan stakeholders
2. **UI/UX Mockups**: Create detailed wireframes dan user flows
3. **Technical Architecture**: Finalize service architecture dan dependencies
4. **Project Planning**: Detailed sprint planning dan resource allocation
5. **Testing Strategy**: Comprehensive testing plan untuk semua scenarios

---

## 🔄 Update: Penyesuaian dengan Form FPTK Standar

### Perubahan Struktur Database

Berdasarkan analisa Form Permintaan Tenaga Kerja (FPTK) standar perusahaan, struktur tabel `recruitment_requests` telah disesuaikan dengan field-field yang ada dalam formulir:

#### 1. **Informasi Dasar**

-   `department_id` - Divisi/Departement
-   `site_project` - Site Project (free text)
-   `position_required` - Jabatan Diperlukan (free text)
-   `quantity` - Jumlah Diperlukan
-   `required_date` - Waktu Diperlukan

#### 2. **Status Pekerjaan**

-   `employment_type` - ENUM('pkwtt', 'pkwt', 'harian', 'magang')

#### 3. **Alasan Permintaan**

-   `request_reason` - Kategori alasan permintaan
-   `other_reason` - Alasan lain jika diperlukan

#### 4. **Uraian Singkat Pekerjaan**

-   `job_description_1`, `job_description_2`, `job_description_3` - Numbered list pekerjaan

#### 5. **Persyaratan Lengkap**

-   `required_gender` - Jenis kelamin
-   `required_age_min/max` - Rentang usia
-   `required_marital_status` - Status perkawinan
-   `required_education` - Pendidikan minimal
-   `required_skills` - Kemampuan wajib
-   `required_experience` - Pengalaman kerja
-   `required_physical` - Persyaratan fisik
-   `required_mental` - Persyaratan mental
-   `other_requirements` - Keterangan lain

#### 6. **Multi-level Approval Workflow**

-   **HR Review** - HR&GA Section Head
-   **PM Approval** - Project Manager
-   **Final Approval** - Operation Director / HCS Division Manager

### Penomoran Sistem

Request number mengikuti format standar: `No.001/HCS-HO/PRF/1/2017`

### Workflow Status

1. `draft` - Sedang dibuat
2. `submitted` - Disubmit untuk review
3. `hr_review` - Review oleh HR
4. `pm_approved` - Disetujui PM
5. `final_approved` - Disetujui final
6. `rejected` - Ditolak
7. `cancelled` - Dibatalkan

### Implementasi Controller

Telah ditambahkan method untuk:

-   `submit()` - Submit FPTK untuk approval
-   `hrReview()` - HR review dan approval
-   `pmApproval()` - Project Manager approval
-   `finalApproval()` - Final approval oleh Operation Director

### Keunggulan Sistem

1. **Sesuai Standar** - Mengikuti form FPTK yang sudah digunakan
2. **Complete Workflow** - Multi-level approval sesuai hierarki
3. **Detailed Requirements** - Persyaratan kandidat yang lengkap
4. **Audit Trail** - Tracking lengkap approval process
5. **Flexible Integration** - Mudah disesuaikan dengan sistem yang ada

_Document ini akan terus diupdate sesuai dengan perkembangan requirements dan implementation progress._
