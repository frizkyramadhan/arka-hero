# Analisis Komprehensif Sistem Approval untuk Official Travel dan Recruitment Request

**Date:** 6 Oktober 2025  
**Dokumen:** Analisis mendalam struktur database dan logic approval system

---

## 1. OVERVIEW SISTEM APPROVAL

Sistem approval di ARKA HERO menggunakan arsitektur **Dynamic Approval System** yang fleksibel dan dapat dikonfigurasi tanpa hard-coding. Sistem ini terdiri dari 4 tabel utama:

1. **approval_stages** - Template approval (siapa yang harus approve)
2. **approval_stage_details** - Detail konfigurasi per project/department/reason
3. **approval_plans** - Instance approval untuk setiap document submission
4. **officialtravels** / **recruitment_requests** - Document yang memerlukan approval

---

## 2. STRUKTUR DATABASE

### 2.1 Tabel: `approval_stages`

**Purpose:** Template master untuk approval workflow

```sql
CREATE TABLE approval_stages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    approver_id VARCHAR(255),           -- User ID yang bertugas sebagai approver
    document_type VARCHAR(20),          -- 'officialtravel' atau 'recruitment_request'
    approval_order INT DEFAULT 1,       -- Urutan approval (1, 2, 3, dst)
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

**Catatan Penting:**

-   `approver_id` adalah ID user yang akan menjadi approver
-   `document_type` menentukan jenis dokumen yang akan diapprove
-   `approval_order` menentukan urutan sequential approval (order 1 harus approve dulu sebelum order 2, dst)
-   **TIDAK ada field `is_sequential`** (sudah dihapus via migration `2025_08_27_094407_remove_is_sequential_from_approval_stages_table.php`)

**Unique Constraint:**

```sql
UNIQUE(approver_id, document_type, approval_order)
```

Via migration `2025_08_25_135751_add_unique_constraint_to_approval_stages_table.php`

**Migration History:**

1. Created: `2025_07_25_170142_create_approval_stages_table.php`
2. Modified project field: `2025_07_28_170203_modify_approval_stages_project_to_project_id.php`
3. Added approval_order: `2025_08_25_114240_add_approval_order_to_approval_stages_table.php`
4. Added unique constraint: `2025_08_25_135751_add_unique_constraint_to_approval_stages_table.php`
5. Removed is_sequential: `2025_08_27_094407_remove_is_sequential_from_approval_stages_table.php`
6. Updated unique constraint: `2025_09_09_000001_update_approval_stage_details_unique_constraint.php`

---

### 2.2 Tabel: `approval_stage_details`

**Purpose:** Detail konfigurasi approval per kombinasi project, department, dan request_reason

```sql
CREATE TABLE approval_stage_details (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    approval_stage_id BIGINT,           -- FK ke approval_stages
    project_id BIGINT,                  -- FK ke projects
    department_id VARCHAR(255),         -- Department ID
    request_reason VARCHAR(50) NULL,    -- Conditional approval untuk recruitment_request
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (approval_stage_id) REFERENCES approval_stages(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    UNIQUE (approval_stage_id, project_id, department_id)
)
```

**Catatan Penting:**

-   Tabel ini adalah **pivot table** yang menghubungkan approval_stages dengan kombinasi project/department/request_reason
-   `request_reason` **NULLABLE** - hanya digunakan untuk `recruitment_request`, untuk `officialtravel` harus **NULL**
-   Satu approval_stage bisa punya multiple details (beda project/department)
-   Conditional approval berdasarkan `request_reason` untuk recruitment_request:
    -   `replacement_resign` - Penggantian karena resign
    -   `replacement_promotion` - Penggantian karena promosi
    -   `additional_workplan` - Tambahan karena rencana kerja
    -   `other` - Alasan lainnya

**Migration History:**

1. Created: `2025_12_31_235959_create_approval_stage_details_table.php`
2. Added request_reason: `2025_09_08_120614_add_request_reason_to_approval_stage_details.php`
3. Updated unique constraint: `2025_09_09_000001_update_approval_stage_details_unique_constraint.php`

**Unique Constraint:**

```sql
UNIQUE(approval_stage_id, project_id, department_id)
-- Named as: unique_stage_detail
```

---

### 2.3 Tabel: `approval_plans`

**Purpose:** Instance approval yang dibuat saat document disubmit

```sql
CREATE TABLE approval_plans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    document_id CHAR(36),               -- UUID dari officialtravel atau recruitment_request
    document_type VARCHAR(255),         -- 'officialtravel' atau 'recruitment_request'
    approver_id BIGINT,                 -- User ID approver
    status INT DEFAULT 0,               -- 0=Pending, 1=Approved, 2=Rejected, 3=Cancelled, 4=Revised
    remarks VARCHAR(255) NULL,          -- Catatan approver
    is_open BOOLEAN DEFAULT TRUE,       -- Approval aktif atau sudah ditutup
    is_read BOOLEAN DEFAULT TRUE,       -- Sudah dibaca approver atau belum
    approval_order INT NULL,            -- Urutan approval (copied dari approval_stages)
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

**Status Codes:**

-   `0` = **Pending** - Menunggu approval
-   `1` = **Approved** - Sudah diapprove
-   `2` = **Rejected** - Ditolak
-   `3` = **Cancelled** - Dibatalkan
-   `4` = **Revised** - Perlu revisi (not currently used)

**Catatan Penting:**

-   Record ini dibuat otomatis saat document disubmit
-   `approval_order` di-copy dari `approval_stages.approval_order`
-   `is_open = true` berarti approval masih aktif, `false` berarti sudah ditutup (karena rejection atau cancellation)
-   Satu document bisa punya multiple approval_plans (satu per approver)

**Migration History:**

1. Created: `2025_07_25_170224_create_approval_plans_table.php`
2. Added approval_order: `2025_08_25_140555_add_approval_order_to_approval_plans_table.php`

---

### 2.4 Tabel: `officialtravels`

**Purpose:** Official Travel documents (Surat Perjalanan Dinas)

```sql
CREATE TABLE officialtravels (
    id CHAR(36) PRIMARY KEY,            -- UUID
    letter_number_id BIGINT NULL,       -- FK ke letter_numbers
    letter_number VARCHAR(50) NULL,     -- Nomor surat
    official_travel_number VARCHAR(255), -- Nomor surat perjalanan dinas
    official_travel_date DATE,          -- Tanggal perjalanan
    official_travel_origin BIGINT NULL, -- FK ke projects (project asal traveler)
    status VARCHAR(20),                 -- 'draft', 'submitted', 'approved', 'rejected', 'cancelled', 'closed'
    traveler_id BIGINT,                 -- FK ke administrations (main traveler)
    purpose VARCHAR(255),               -- Tujuan perjalanan
    destination VARCHAR(255),           -- Tempat tujuan
    duration VARCHAR(255),              -- Durasi perjalanan
    departure_from DATE,                -- Tanggal keberangkatan
    transportation_id BIGINT,           -- FK ke transportations
    accommodation_id BIGINT,            -- FK ke accommodations
    created_by BIGINT,                  -- FK ke users
    submit_at TIMESTAMP NULL,           -- Kapan disubmit untuk approval
    approved_at TIMESTAMP NULL,         -- Kapan diapprove (added by new system)

    -- Legacy fields (not used anymore with new approval system)
    recommendation_status ENUM('pending', 'approved', 'rejected'),
    recommendation_remark VARCHAR(255) NULL,
    recommendation_by BIGINT NULL,
    recommendation_date DATETIME NULL,
    recommendation_timestamps TIMESTAMP NULL,
    approval_status ENUM('pending', 'approved', 'rejected'),
    approval_remark VARCHAR(255) NULL,
    approval_by BIGINT NULL,
    approval_date DATETIME NULL,
    approval_timestamps TIMESTAMP NULL,

    -- Arrival/Departure tracking (after approval)
    arrival_at_destination DATETIME NULL,
    arrival_check_by BIGINT NULL,
    arrival_remark VARCHAR(255) NULL,
    arrival_timestamps TIMESTAMP NULL,
    departure_from_destination DATETIME NULL,
    departure_check_by BIGINT NULL,
    departure_remark VARCHAR(255) NULL,
    departure_timestamps TIMESTAMP NULL,

    -- Claim tracking
    is_claimed ENUM('yes', 'no') DEFAULT 'no',
    claimed_at DATETIME NULL,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (letter_number_id) REFERENCES letter_numbers(id),
    FOREIGN KEY (official_travel_origin) REFERENCES projects(id),
    FOREIGN KEY (traveler_id) REFERENCES administrations(id),
    FOREIGN KEY (transportation_id) REFERENCES transportations(id),
    FOREIGN KEY (accommodation_id) REFERENCES accommodations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
)
```

**Status Flow:**

```
draft → submitted → approved → closed
        └──────→ rejected
        └──────→ cancelled
```

**Migration History:**

1. Created: `2025_06_25_143000_create_officialtravels_table.php`
2. Added approval fields: `2025_07_25_170240_add_approval_fields_to_officialtravels_table.php`
3. Removed legacy fields: `2025_09_15_133236_remove_legacy_approval_fields_from_officialtravels_table.php`

---

### 2.5 Tabel: `recruitment_requests`

**Purpose:** Recruitment Request documents (FPTK - Formulir Permintaan Tenaga Kerja)

```sql
CREATE TABLE recruitment_requests (
    id CHAR(36) PRIMARY KEY,            -- UUID
    letter_number_id BIGINT NULL,       -- FK ke letter_numbers
    letter_number VARCHAR(50) NULL,     -- Nomor surat
    request_number VARCHAR(50) UNIQUE,  -- Nomor FPTK

    -- Basic Information
    department_id BIGINT,               -- FK ke departments
    project_id BIGINT,                  -- FK ke projects
    position_id BIGINT,                 -- FK ke positions
    level_id BIGINT,                    -- FK ke levels
    required_qty INT,                   -- Jumlah yang dibutuhkan
    required_date DATE,                 -- Tanggal dibutuhkan
    employment_type ENUM('pkwtt', 'pkwt', 'harian', 'magang'),

    -- Request Reason (for conditional approval)
    request_reason ENUM('replacement_resign', 'replacement_promotion', 'additional_workplan', 'other'),
    other_reason TEXT NULL,

    -- Job Requirements
    job_description TEXT NULL,
    required_gender ENUM('male', 'female', 'any') DEFAULT 'any',
    required_age_min INT NULL,
    required_age_max INT NULL,
    required_marital_status ENUM('single', 'married', 'any') DEFAULT 'any',
    required_education VARCHAR(500) NULL,
    required_skills TEXT NULL,
    required_experience TEXT NULL,
    required_physical TEXT NULL,
    required_mental TEXT NULL,
    other_requirements TEXT NULL,
    requires_theory_test BOOLEAN DEFAULT FALSE,

    -- Approval Workflow
    created_by BIGINT,                  -- FK ke users
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'cancelled', 'closed') DEFAULT 'draft',
    submit_at TIMESTAMP NULL,           -- Kapan disubmit untuk approval
    approved_at TIMESTAMP NULL,         -- Kapan diapprove (added by new system)

    -- Legacy Approval Tracking (not used with new approval system)
    known_by BIGINT NULL COMMENT 'HR&GA Section Head who acknowledges',
    known_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    known_at TIMESTAMP NULL,
    known_remark TEXT NULL,
    known_timestamps TIMESTAMP NULL,
    approved_by_pm BIGINT NULL COMMENT 'Project Manager who approves',
    pm_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    pm_approved_at TIMESTAMP NULL,
    pm_approval_remark TEXT NULL,
    pm_approval_timestamps TIMESTAMP NULL,
    approved_by_director BIGINT NULL COMMENT 'Director/Manager who approves',
    director_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    director_approved_at TIMESTAMP NULL,
    director_approval_remark TEXT NULL,
    director_approval_timestamps TIMESTAMP NULL,

    -- Position Tracking
    positions_filled INT DEFAULT 0 COMMENT 'Tracking filled positions',

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (letter_number_id) REFERENCES letter_numbers(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (level_id) REFERENCES levels(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (known_by) REFERENCES users(id),
    FOREIGN KEY (approved_by_pm) REFERENCES users(id),
    FOREIGN KEY (approved_by_director) REFERENCES users(id)
)
```

**Status Flow:**

```
draft → submitted → approved → closed (when positions_filled >= required_qty)
        └──────→ rejected
        └──────→ cancelled
```

**Migration History:**

1. Created: `2025_07_04_100000_create_recruitment_requests_table.php`
2. Added approval fields: `2025_07_25_170249_add_approval_fields_to_recruitment_requests_table.php`
3. Added theory test: `2025_08_19_110333_add_requires_theory_test_to_recruitment_requests.php`
4. Removed legacy fields: `2025_09_08_104243_remove_legacy_approval_fields_from_recruitment_requests_table.php`

---

## 3. LOGIC FLOW - OFFICIAL TRAVEL

### 3.1 Konfigurasi Approval Stage

**Contoh Setup:**

```php
// Setup approval workflow untuk Official Travel
// Project: 000H (HO)
// Department: 2 (HR)
// Approvers: User 5 → User 3 → User 1

// Step 1: Create Approval Stage untuk Approver pertama
$stage1 = ApprovalStage::create([
    'approver_id' => 5,
    'document_type' => 'officialtravel',
    'approval_order' => 1
]);

// Step 2: Add detail untuk project & department
ApprovalStageDetail::create([
    'approval_stage_id' => $stage1->id,
    'project_id' => 1, // 000H
    'department_id' => 2, // HR
    'request_reason' => null // MUST BE NULL for officialtravel
]);

// Step 3: Create Approval Stage untuk Approver kedua
$stage2 = ApprovalStage::create([
    'approver_id' => 3,
    'document_type' => 'officialtravel',
    'approval_order' => 2
]);

ApprovalStageDetail::create([
    'approval_stage_id' => $stage2->id,
    'project_id' => 1,
    'department_id' => 2,
    'request_reason' => null
]);

// Step 4: Create Approval Stage untuk Approver ketiga
$stage3 = ApprovalStage::create([
    'approver_id' => 1,
    'document_type' => 'officialtravel',
    'approval_order' => 3
]);

ApprovalStageDetail::create([
    'approval_stage_id' => $stage3->id,
    'project_id' => 1,
    'department_id' => 2,
    'request_reason' => null
]);
```

**Catatan Penting:**

-   `request_reason` HARUS NULL untuk officialtravel
-   `approval_order` menentukan urutan sequential (1 → 2 → 3)
-   Department diambil dari `traveler.position.department_id`
-   Project diambil dari `official_travel_origin`

---

### 3.2 Submit Official Travel

**Controller:** `OfficialtravelController::store()` atau `submitForApproval()`

```php
// 1. User creates Official Travel (status = 'draft')
$officialtravel = Officialtravel::create([
    'official_travel_number' => $travelNumber,
    'official_travel_date' => $request->official_travel_date,
    'official_travel_origin' => $request->official_travel_origin, // project_id
    'traveler_id' => $request->traveler_id,
    'status' => $status, // 'draft' atau 'submitted'
    'created_by' => auth()->id(),
    'submit_at' => $submitAt,
    // ... other fields
]);

// 2. If submitted (not draft), create approval plans
if ($request->submit_action === 'submit') {
    $response = app(ApprovalPlanController::class)
        ->create_approval_plan('officialtravel', $officialtravel->id);
}
```

**Proses di `create_approval_plan()`:**

```php
// ApprovalPlanController::create_approval_plan()

// 1. Get document and extract info
$document = Officialtravel::findOrFail($document_id);
$project = $document->official_travel_origin; // project_id
$traveler = $document->traveler;
$department_id = $traveler->position->department_id;
$request_reason = null; // Always null for officialtravel

// 2. Find matching approval stages
$approvers = ApprovalStage::with(['approver', 'details'])
    ->where('document_type', 'officialtravel')
    ->whereHas('details', function ($query) use ($project, $department_id, $request_reason) {
        $query->where('project_id', $project)
              ->where('department_id', $department_id)
              ->whereNull('request_reason'); // officialtravel must have NULL request_reason
    })
    ->orderBy('approval_order', 'asc')
    ->get();

// 3. Create approval_plans for each approver
foreach ($approvers as $approver) {
    ApprovalPlan::create([
        'document_id' => $document_id,
        'document_type' => 'officialtravel',
        'approver_id' => $approver->approver_id,
        'approval_order' => $approver->approval_order,
        'status' => 0, // Pending
        'is_open' => true,
    ]);
}

// 4. Update document
$document->update([
    'submit_at' => now()
]);
```

**Query untuk mencari approval stages:**

```sql
-- Mencari approval stages yang sesuai
SELECT approval_stages.*, approval_stage_details.*
FROM approval_stages
INNER JOIN approval_stage_details ON approval_stage_details.approval_stage_id = approval_stages.id
WHERE approval_stages.document_type = 'officialtravel'
  AND approval_stage_details.project_id = 1  -- official_travel_origin
  AND approval_stage_details.department_id = 2  -- traveler.position.department_id
  AND approval_stage_details.request_reason IS NULL  -- MUST BE NULL
ORDER BY approval_stages.approval_order ASC;
```

---

### 3.3 Approval Process

**Sequential Approval Logic:**

```php
// ApprovalPlanController::update()

// 1. Find approval plan
$approval_plan = ApprovalPlan::findOrFail($id);

// 2. Check if can be processed (sequential validation)
if (!$approval_plan->canBeProcessed()) {
    return redirect()->back()->with('toast_error',
        'Previous approvals must be completed first.');
}

// Method canBeProcessed() di ApprovalPlan model
public function canBeProcessed()
{
    if (empty($this->approval_order)) {
        return true; // Fallback
    }

    // Count approved previous orders
    $previousApprovals = ApprovalPlan::where('document_id', $this->document_id)
        ->where('document_type', $this->document_type)
        ->where('approval_order', '<', $this->approval_order)
        ->where('status', 1) // Approved
        ->count();

    $expectedPrevious = $this->approval_order - 1;

    // Previous orders must be completed first
    return $previousApprovals >= $expectedPrevious;
}

// 3. Update approval plan
$approval_plan->update([
    'status' => $request->status, // 1=Approved, 2=Rejected
    'remarks' => $request->remarks,
]);

// 4. Get document
$document = Officialtravel::findOrFail($approval_plan->document_id);

// 5. Check if rejected
if ($request->status == 2) {
    $document->update(['status' => 'rejected']);
    // Close all open approval plans
    $this->closeOpenApprovalPlans('officialtravel', $document->id);
}

// 6. Check if all approvals completed
if ($this->areAllSequentialApprovalsCompleted($approval_plan)) {
    $document->update([
        'status' => 'approved',
        'approved_at' => now(),
    ]);
}
```

**Method `areAllSequentialApprovalsCompleted()`:**

```php
private function areAllSequentialApprovalsCompleted($approvalPlan)
{
    $allApprovals = ApprovalPlan::where('document_id', $approvalPlan->document_id)
        ->where('document_type', $approvalPlan->document_type)
        ->where('is_open', true)
        ->get();

    // Check if all are approved (status = 1)
    foreach ($allApprovals as $approval) {
        if ($approval->status != 1) {
            return false;
        }
    }

    return true;
}
```

**Approval Flow Diagram:**

```
Approval Order 1 (User 5) → PENDING
  ↓ (must approve first)
Approval Order 2 (User 3) → BLOCKED (cannot process yet)
  ↓
Approval Order 3 (User 1) → BLOCKED (cannot process yet)

After User 5 approves:
Approval Order 1 (User 5) → APPROVED ✓
  ↓
Approval Order 2 (User 3) → CAN PROCESS NOW
  ↓
Approval Order 3 (User 1) → BLOCKED (waiting for order 2)

After User 3 approves:
Approval Order 1 (User 5) → APPROVED ✓
  ↓
Approval Order 2 (User 3) → APPROVED ✓
  ↓
Approval Order 3 (User 1) → CAN PROCESS NOW

After User 1 approves:
Approval Order 1 (User 5) → APPROVED ✓
  ↓
Approval Order 2 (User 3) → APPROVED ✓
  ↓
Approval Order 3 (User 1) → APPROVED ✓
  ↓
Document Status → APPROVED (officialtravel.status = 'approved')
```

---

### 3.4 Rejection Handling

```php
// If any approver rejects (status = 2):

// 1. Update document status to rejected
$document->update(['status' => 'rejected']);

// 2. Close all open approval plans
ApprovalPlan::where('document_id', $document->id)
    ->where('document_type', 'officialtravel')
    ->where('is_open', 1)
    ->update(['is_open' => 0]);

// All subsequent approvers cannot process anymore
```

---

## 4. LOGIC FLOW - RECRUITMENT REQUEST

### 4.1 Konfigurasi Approval Stage dengan Conditional Logic

**Contoh Setup untuk FPTK:**

```php
// Setup approval workflow untuk Recruitment Request
// Project: 1 (000H - HO)
// Department: 2 (HR)
// Request Reason: replacement_resign
// Approvers: User 8 (HCS Division Manager)

// Step 1: Create Approval Stage
$stage1 = ApprovalStage::create([
    'approver_id' => 8, // HCS Division Manager
    'document_type' => 'recruitment_request',
    'approval_order' => 1
]);

// Step 2: Add detail dengan request_reason
ApprovalStageDetail::create([
    'approval_stage_id' => $stage1->id,
    'project_id' => 1, // 000H
    'department_id' => 2, // HR
    'request_reason' => 'replacement_resign' // CONDITIONAL
]);

// Setup untuk request_reason: additional_workplan
// Approvers: User 10 (Operational GM) → User 8 (HCS Division Manager)

$stage2 = ApprovalStage::create([
    'approver_id' => 10,
    'document_type' => 'recruitment_request',
    'approval_order' => 1
]);

ApprovalStageDetail::create([
    'approval_stage_id' => $stage2->id,
    'project_id' => 1,
    'department_id' => 2,
    'request_reason' => 'additional_workplan'
]);

$stage3 = ApprovalStage::create([
    'approver_id' => 8,
    'document_type' => 'recruitment_request',
    'approval_order' => 2
]);

ApprovalStageDetail::create([
    'approval_stage_id' => $stage3->id,
    'project_id' => 1,
    'department_id' => 2,
    'request_reason' => 'additional_workplan'
]);
```

**Catatan Penting:**

-   `request_reason` HARUS DIISI untuk recruitment_request
-   Different `request_reason` dapat memiliki approval flow yang berbeda
-   Department diambil langsung dari `recruitment_requests.department_id`
-   Project diambil dari `recruitment_requests.project_id`

---

### 4.2 Submit Recruitment Request

**Controller:** `RecruitmentRequestController::store()` atau `submitForApproval()`

```php
// 1. User creates FPTK (status = 'draft')
$fptk = RecruitmentRequest::create([
    'request_number' => $fptkNumber,
    'department_id' => $request->department_id,
    'project_id' => $request->project_id,
    'position_id' => $request->position_id,
    'level_id' => $request->level_id,
    'required_qty' => $request->required_qty,
    'employment_type' => $request->employment_type,
    'request_reason' => $request->request_reason, // IMPORTANT!
    'other_reason' => $request->other_reason,
    'status' => $status, // 'draft' atau 'submitted'
    'created_by' => auth()->id(),
    'submit_at' => $submitAt,
    // ... other fields
]);

// 2. If submitted, create approval plans
if ($request->submit_action === 'submit') {
    $response = app(ApprovalPlanController::class)
        ->create_approval_plan('recruitment_request', $fptk->id);
}
```

**Proses di `create_approval_plan()`:**

```php
// ApprovalPlanController::create_approval_plan()

// 1. Get document and extract info
$document = RecruitmentRequest::findOrFail($document_id);
$project = $document->project_id;
$department_id = $document->department_id;
$request_reason = $document->request_reason; // IMPORTANT! Not null

// 2. Find matching approval stages
$approvers = ApprovalStage::with(['approver', 'details'])
    ->where('document_type', 'recruitment_request')
    ->whereHas('details', function ($query) use ($project, $department_id, $request_reason) {
        $query->where('project_id', $project)
              ->where('department_id', $department_id)
              ->where('request_reason', $request_reason); // MATCH request_reason!
    })
    ->orderBy('approval_order', 'asc')
    ->get();

// 3. Apply conditional logic (currently returns all - logic disabled)
$approvers = $this->getConditionalApprovers($request_reason, $project, $department_id, $approvers);

// 4. Create approval_plans for each approver
foreach ($approvers as $approver) {
    ApprovalPlan::create([
        'document_id' => $document_id,
        'document_type' => 'recruitment_request',
        'approver_id' => $approver->approver_id,
        'approval_order' => $approver->approval_order,
        'status' => 0, // Pending
        'is_open' => true,
    ]);
}

// 5. Update document
$document->update([
    'submit_at' => now()
]);
```

**Query untuk mencari approval stages:**

```sql
-- Mencari approval stages untuk replacement_resign
SELECT approval_stages.*, approval_stage_details.*
FROM approval_stages
INNER JOIN approval_stage_details ON approval_stage_details.approval_stage_id = approval_stages.id
WHERE approval_stages.document_type = 'recruitment_request'
  AND approval_stage_details.project_id = 1  -- recruitment_requests.project_id
  AND approval_stage_details.department_id = 2  -- recruitment_requests.department_id
  AND approval_stage_details.request_reason = 'replacement_resign'  -- MUST MATCH
ORDER BY approval_stages.approval_order ASC;

-- Hasil untuk replacement_resign: 1 approver (HCS Division Manager)
-- Hasil untuk additional_workplan: 2 approvers (Operational GM → HCS Division Manager)
```

---

### 4.3 Conditional Approval Logic

**Current Implementation:** `getConditionalApprovers()` returns all approvers (filtering disabled)

```php
// Method ini saat ini TIDAK melakukan filtering tambahan
// Karena filtering sudah dilakukan di main query berdasarkan request_reason
private function getConditionalApprovers($request_reason, $project_id, $department_id, $approvers)
{
    // Return all configured approvers - already filtered by main query
    return $approvers;
}
```

**Legacy Conditional Logic (Commented Out):**

```php
// Legacy logic yang pernah digunakan (sekarang di-comment):
/*
switch ($request_reason) {
    case 'replacement_resign':
    case 'replacement_promotion':
        // Only HCS Division Manager
        return $approvers->filter(function ($approver) {
            return $this->isHCSDivisionManager($approver->approver_id);
        });

    case 'additional_workplan':
        // Check project type
        if ($project_type === 'HO' || $project_type === 'BO' || $project_type === 'APS') {
            // HCS Division Manager → HCL Director
            return $approvers->filter(function ($approver) {
                return $this->isHCSDivisionManager($approver->approver_id) ||
                       $this->isHCLDirector($approver->approver_id);
            });
        } else {
            // Operational GM → HCS Division Manager
            return $approvers->filter(function ($approver) {
                return $this->isOperationalGeneralManager($approver->approver_id) ||
                       $this->isHCSDivisionManager($approver->approver_id);
            });
        }

    case 'other':
        return $approvers;

    default:
        return $approvers;
}
*/
```

**Penjelasan:**

-   Conditional logic sekarang ditangani melalui **database configuration** (approval_stage_details.request_reason)
-   Tidak perlu hard-coding role checking di code
-   Lebih fleksibel dan mudah di-maintain
-   Admin dapat setup approval workflow yang berbeda untuk setiap request_reason

---

### 4.4 Approval Process (Same as Official Travel)

Proses approval untuk recruitment_request **SAMA** dengan official travel:

1. Sequential validation via `canBeProcessed()`
2. Update approval_plan status
3. Check if all approvals completed
4. Update document status to 'approved'

**Satu-satunya perbedaan:** Query untuk mencari approval stages menggunakan `request_reason` field.

---

## 5. PERBEDAAN UTAMA ANTARA OFFICIAL TRAVEL DAN RECRUITMENT REQUEST

### 5.1 Official Travel

| Aspek                     | Detail                                                                              |
| ------------------------- | ----------------------------------------------------------------------------------- |
| **Document Type**         | `officialtravel`                                                                    |
| **Project Source**        | `official_travel_origin` (FK ke projects)                                           |
| **Department Source**     | `traveler.position.department_id` (indirect)                                        |
| **Request Reason**        | **NULL** (tidak digunakan)                                                          |
| **Conditional Logic**     | **TIDAK ADA** - semua menggunakan workflow yang sama                                |
| **Approval Stage Query**  | Hanya filter by `project_id` dan `department_id`, `request_reason` **MUST BE NULL** |
| **Status After Approved** | `approved` → dapat diclosed manual menjadi `closed`                                 |
| **Additional Features**   | Tracking arrival/departure, claim management                                        |

---

### 5.2 Recruitment Request

| Aspek                     | Detail                                                                                       |
| ------------------------- | -------------------------------------------------------------------------------------------- |
| **Document Type**         | `recruitment_request`                                                                        |
| **Project Source**        | `project_id` (FK ke projects)                                                                |
| **Department Source**     | `department_id` (direct)                                                                     |
| **Request Reason**        | **REQUIRED** (`replacement_resign`, `replacement_promotion`, `additional_workplan`, `other`) |
| **Conditional Logic**     | **ADA** - approval flow berbeda per `request_reason`                                         |
| **Approval Stage Query**  | Filter by `project_id`, `department_id`, **DAN `request_reason` MUST MATCH**                 |
| **Status After Approved** | `approved` → auto `closed` when `positions_filled >= required_qty`                           |
| **Additional Features**   | Recruitment session tracking, candidate management                                           |

---

## 6. SEQUENTIAL APPROVAL LOGIC

### 6.1 Approval Order Mechanism

**Konsep:**

-   Approval harus dilakukan secara **berurutan** berdasarkan `approval_order`
-   Approver dengan `approval_order = 2` TIDAK BISA approve sebelum `approval_order = 1` selesai
-   Multiple approvers dengan `approval_order` yang **SAMA** dapat approve **secara paralel**

**Contoh:**

```
Scenario 1: Sequential
Order 1: User A → Order 2: User B → Order 3: User C
(Must approve in sequence: A → B → C)

Scenario 2: Parallel + Sequential
Order 1: User A, User B (can approve in parallel)
Order 2: User C (can only process after BOTH A and B approved)
```

### 6.2 Validation Logic

**Method:** `ApprovalPlan::canBeProcessed()`

```php
public function canBeProcessed()
{
    // If no approval_order, allow (fallback)
    if (empty($this->approval_order)) {
        return true;
    }

    // Count how many previous orders are approved
    $previousApprovals = ApprovalPlan::where('document_id', $this->document_id)
        ->where('document_type', $this->document_type)
        ->where('approval_order', '<', $this->approval_order)
        ->where('status', 1) // Approved
        ->count();

    // Expected: approval_order - 1
    // Example: if current is order 3, need at least 2 previous approvals (order 1 and 2)
    $expectedPrevious = $this->approval_order - 1;

    return $previousApprovals >= $expectedPrevious;
}
```

**Contoh Perhitungan:**

```
Approval Plans:
1. User A - Order 1 - Status: Approved (1)
2. User B - Order 2 - Status: Pending (0)
3. User C - Order 3 - Status: Pending (0)

User B wants to approve (Order 2):
- previousApprovals = count where order < 2 AND status = 1 → 1 (User A)
- expectedPrevious = 2 - 1 = 1
- 1 >= 1 → TRUE ✓ Can process

User C wants to approve (Order 3):
- previousApprovals = count where order < 3 AND status = 1 → 1 (only User A)
- expectedPrevious = 3 - 1 = 2
- 1 >= 2 → FALSE ✗ Cannot process (User B must approve first)
```

---

## 7. APPROVAL COMPLETION DETECTION

### 7.1 Method: `areAllSequentialApprovalsCompleted()`

```php
private function areAllSequentialApprovalsCompleted($approvalPlan)
{
    // Get all OPEN approval plans for this document
    $allApprovals = ApprovalPlan::where('document_id', $approvalPlan->document_id)
        ->where('document_type', $approvalPlan->document_type)
        ->where('is_open', true)
        ->get();

    // Empty check
    if ($allApprovals->isEmpty()) {
        return false;
    }

    // Check if ALL are approved (status = 1)
    foreach ($allApprovals as $approval) {
        if ($approval->status != 1) {
            return false; // At least one not approved yet
        }
    }

    return true; // All approved!
}
```

### 7.2 Document Status Update

```php
// When all approvals completed:
if ($this->areAllSequentialApprovalsCompleted($approval_plan)) {
    $document->update([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    Log::info("Document approved successfully", [
        'document_type' => $document_type,
        'document_id' => $document->id,
        'approved_at' => now(),
    ]);
}
```

---

## 8. ERROR HANDLING & EDGE CASES

### 8.1 No Approval Stages Configured

```php
// If no approvers found:
if ($approvers->count() === 0) {
    throw new \Exception(
        "No approval stages configured for this project and department combination. " .
        "Please contact administrator to set up approval workflow."
    );
}
```

**Handling di Controller:**

```php
try {
    $response = app(ApprovalPlanController::class)
        ->create_approval_plan('officialtravel', $officialtravel->id);
} catch (\Exception $e) {
    return redirect()->back()
        ->with('toast_error', 'Failed to create approval plans: ' . $e->getMessage())
        ->withInput();
}
```

### 8.2 Duplicate Approval Plans

```php
// Check before creating
$existing_plan = ApprovalPlan::where('document_id', $document_id)
    ->where('document_type', $document_type)
    ->where('approver_id', $approver->approver_id)
    ->first();

if ($existing_plan) {
    Log::warning("Approval plan already exists", [
        'document_id' => $document_id,
        'approver_id' => $approver->approver_id
    ]);
    continue; // Skip
}
```

### 8.3 Empty Approval Order

```php
// Validate approval_order before creating
if (empty($approver->approval_order)) {
    Log::error("Approval order is empty for approver {$approver->approver_id}");
    $error_count++;
    continue;
}
```

### 8.4 Sequential Violation Attempt

```php
// In update() method
if (!$approval_plan->canBeProcessed()) {
    $response = [
        'success' => false,
        'message' => 'Previous approvals must be completed first. ' .
                     'Please wait for earlier approvers to process their approvals.'
    ];

    if ($request->ajax()) {
        return response()->json($response, 422);
    }

    return redirect()->back()->with('toast_error', $response['message']);
}
```

---

## 9. IMPORTANT NOTES & BEST PRACTICES

### 9.1 Official Travel

✅ **DO:**

-   Set `request_reason = NULL` in approval_stage_details
-   Use `traveler.position.department_id` for department matching
-   Use `official_travel_origin` for project matching

❌ **DON'T:**

-   Set any value to `request_reason` (must be NULL)
-   Use legacy approval fields (recommendation*\*, approval*\*)
-   Skip approval order setup

### 9.2 Recruitment Request

✅ **DO:**

-   Always set `request_reason` in approval_stage_details
-   Match `request_reason` exactly when querying approval stages
-   Setup different approval workflows for different `request_reason` values
-   Use `department_id` directly (not from position)

❌ **DON'T:**

-   Leave `request_reason` NULL in approval_stage_details
-   Use legacy approval fields (known*\*, pm_approval*_, director*approval*_)
-   Mix approval_order between different request_reason configurations

### 9.3 General

✅ **DO:**

-   Always set `approval_order` sequentially (1, 2, 3, ...)
-   Test sequential approval flow thoroughly
-   Log approval events for audit trail
-   Handle errors gracefully with user-friendly messages
-   Clear cache after approval actions: `cache()->forget('pending_approvals_' . $userId)`

❌ **DON'T:**

-   Skip `approval_order` values (e.g., 1 → 3, skip 2)
-   Hard-code role checks in conditional logic
-   Allow approval without sequential validation
-   Modify `is_open = false` approval plans

---

## 10. DATABASE QUERIES CHEAT SHEET

### 10.1 Find Approval Stages for Official Travel

```sql
-- Example: Project 000H (id=1), Department HR (id=2)
SELECT
    ast.id as stage_id,
    ast.approver_id,
    ast.approval_order,
    u.name as approver_name,
    asd.project_id,
    p.project_code,
    asd.department_id,
    d.department_name,
    asd.request_reason
FROM approval_stages ast
INNER JOIN approval_stage_details asd ON asd.approval_stage_id = ast.id
INNER JOIN users u ON u.id = ast.approver_id
INNER JOIN projects p ON p.id = asd.project_id
INNER JOIN departments d ON d.id = asd.department_id
WHERE ast.document_type = 'officialtravel'
  AND asd.project_id = 1
  AND asd.department_id = 2
  AND asd.request_reason IS NULL
ORDER BY ast.approval_order ASC;
```

### 10.2 Find Approval Stages for Recruitment Request

```sql
-- Example: Project 000H (id=1), Department HR (id=2), Reason: replacement_resign
SELECT
    ast.id as stage_id,
    ast.approver_id,
    ast.approval_order,
    u.name as approver_name,
    asd.project_id,
    p.project_code,
    asd.department_id,
    d.department_name,
    asd.request_reason
FROM approval_stages ast
INNER JOIN approval_stage_details asd ON asd.approval_stage_id = ast.id
INNER JOIN users u ON u.id = ast.approver_id
INNER JOIN projects p ON p.id = asd.project_id
INNER JOIN departments d ON d.id = asd.department_id
WHERE ast.document_type = 'recruitment_request'
  AND asd.project_id = 1
  AND asd.department_id = 2
  AND asd.request_reason = 'replacement_resign'
ORDER BY ast.approval_order ASC;
```

### 10.3 Check Approval Plans for a Document

```sql
-- Example: Check approval plans for officialtravel with id = 'abc-123'
SELECT
    ap.id,
    ap.document_id,
    ap.document_type,
    ap.approver_id,
    u.name as approver_name,
    ap.approval_order,
    ap.status,
    ap.remarks,
    ap.is_open,
    ap.created_at,
    ap.updated_at,
    CASE ap.status
        WHEN 0 THEN 'Pending'
        WHEN 1 THEN 'Approved'
        WHEN 2 THEN 'Rejected'
        WHEN 3 THEN 'Cancelled'
        WHEN 4 THEN 'Revised'
    END as status_text
FROM approval_plans ap
INNER JOIN users u ON u.id = ap.approver_id
WHERE ap.document_id = 'abc-123'
  AND ap.document_type = 'officialtravel'
  AND ap.is_open = 1
ORDER BY ap.approval_order ASC;
```

### 10.4 Check Approval Progress

```sql
-- Check how many approvals completed vs total
SELECT
    document_type,
    document_id,
    COUNT(*) as total_approvals,
    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as rejected_count
FROM approval_plans
WHERE document_id = 'abc-123'
  AND document_type = 'officialtravel'
  AND is_open = 1
GROUP BY document_type, document_id;
```

### 10.5 Find Pending Approvals for a User

```sql
-- Find all pending approvals assigned to User ID 5
SELECT
    ap.id,
    ap.document_id,
    ap.document_type,
    ap.approval_order,
    ap.created_at,
    CASE
        WHEN ap.document_type = 'officialtravel' THEN ot.official_travel_number
        WHEN ap.document_type = 'recruitment_request' THEN rr.request_number
    END as document_number,
    CASE
        WHEN ap.document_type = 'officialtravel' THEN ot.status
        WHEN ap.document_type = 'recruitment_request' THEN rr.status
    END as document_status
FROM approval_plans ap
LEFT JOIN officialtravels ot ON ot.id = ap.document_id AND ap.document_type = 'officialtravel'
LEFT JOIN recruitment_requests rr ON rr.id = ap.document_id AND ap.document_type = 'recruitment_request'
WHERE ap.approver_id = 5
  AND ap.status = 0  -- Pending
  AND ap.is_open = 1
ORDER BY ap.created_at DESC;
```

---

## 11. MIGRATION TIMELINE

### Chronological Order

1. **2025-06-25**: Create officialtravels table
2. **2025-07-04**: Create recruitment_requests table
3. **2025-07-25**: Create approval system (approval_stages, approval_plans)
4. **2025-07-25**: Add approval fields to officialtravels and recruitment_requests
5. **2025-07-28**: Modify approval_stages.project to project_id
6. **2025-08-19**: Add requires_theory_test to recruitment_requests
7. **2025-08-25**: Add approval_order to approval_stages and approval_plans
8. **2025-08-25**: Add unique constraint to approval_stages
9. **2025-08-27**: Remove is_sequential from approval_stages (always sequential now)
10. **2025-09-08**: Add request_reason to approval_stage_details
11. **2025-09-08**: Remove legacy approval fields from recruitment_requests
12. **2025-09-09**: Update unique constraint on approval_stage_details
13. **2025-09-15**: Remove legacy approval fields from officialtravels
14. **2025-12-31**: Create approval_stage_details table (date is placeholder - created last)

---

## 12. SUMMARY & KEY TAKEAWAYS

### 12.1 Architecture Highlights

1. **Dynamic Configuration**: Approval workflows dikonfigurasi via database, tidak hard-coded
2. **Sequential Approval**: Enforced via `approval_order` field dengan validation logic
3. **Conditional Approval**: Recruitment request mendukung workflow berbeda berdasarkan `request_reason`
4. **Flexible & Scalable**: Mudah menambah/modify approval stages tanpa mengubah code
5. **Clear Audit Trail**: Semua approval activity tercatat di `approval_plans` dengan timestamps

### 12.2 Core Differences Summary

| Feature              | Official Travel                             | Recruitment Request             |
| -------------------- | ------------------------------------------- | ------------------------------- |
| Conditional Logic    | ❌ No                                       | ✅ Yes (via request_reason)     |
| request_reason field | NULL                                        | REQUIRED                        |
| Department Source    | Indirect (traveler → position → department) | Direct (department_id)          |
| Project Source       | official_travel_origin                      | project_id                      |
| Auto-close Logic     | ❌ Manual                                   | ✅ Auto (when positions filled) |
| Additional Tracking  | Arrival/Departure/Claim                     | Sessions/Candidates             |

### 12.3 Critical Implementation Rules

1. **Official Travel**: `request_reason` MUST BE NULL in approval_stage_details
2. **Recruitment Request**: `request_reason` MUST MATCH document's request_reason
3. **Sequential Order**: Always respect `approval_order` - no skipping allowed
4. **Parallel Processing**: Same `approval_order` can be processed simultaneously
5. **Rejection**: Any rejection closes all open approval plans immediately

---

## 13. REFERENCES

### Documentation

-   `docs/DYNAMIC_APPROVAL_SYSTEM_ANALYSIS.md` - Detailed approval system analysis
-   `docs/FPTK_CONDITIONAL_APPROVAL_IMPLEMENTATION_PLAN.md` - FPTK conditional approval plan
-   `docs/LEGACY_APPROVAL_REMOVAL.md` - Legacy approval fields removal documentation

### Key Files

-   `app/Http/Controllers/ApprovalPlanController.php` - Core approval logic
-   `app/Http/Controllers/OfficialtravelController.php` - Official travel operations
-   `app/Http/Controllers/RecruitmentRequestController.php` - FPTK operations
-   `app/Models/ApprovalPlan.php` - Approval plan model with sequential logic
-   `app/Models/ApprovalStage.php` - Approval stage template
-   `app/Models/ApprovalStageDetail.php` - Stage configuration details

### Database Migrations

-   See section 11 for complete migration timeline

---

**End of Document**

---

_Generated on: 6 Oktober 2025_  
_Version: 1.0_  
_Author: AI Development Team - ARKA HERO_
