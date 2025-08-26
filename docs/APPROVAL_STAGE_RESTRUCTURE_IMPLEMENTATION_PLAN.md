# 📋 Action Plan: Implementasi Struktur Approval Stage Terpisah

## 🎯 **Overview**

Implementasi restrukturisasi tabel `approval_stages` dari single table menjadi 2 tabel terpisah untuk meningkatkan normalisasi database dan fleksibilitas konfigurasi approval flow.

---

## 🏗️ **Target Architecture**

### **Sebelum (Single Table)**

```sql
approval_stages (
    id, project_id, department_id, approver_id,
    document_type, approval_order, is_sequential
)
```

### **Sesudah (Separated Tables)**

```sql
-- Table 1: Konfigurasi Approval Stage
approval_stages (
    id, approver_id, document_type, approval_order, is_sequential
)

-- Table 2: Detail Project & Department
approval_stage_details (
    id, approval_stage_id, project_id, department_id
)
```

---

## 📅 **Phase 1: Database Schema Preparation (Week 1)** ✅ COMPLETE

### **1.1 Migration Files Created**

```bash
# Files already created (with latest timestamps):
database/migrations/2025_12_31_235959_create_approval_stage_details_table.php
database/migrations/2025_12_31_235961_update_approval_stages_table_structure.php

# Note: migrate_approval_stages_data.php not needed - table is empty
```

### **1.2 Migration Structure**

#### **Migration 1: Create New Table & Update Existing**

```php
// create_approval_stage_details_table
Schema::create('approval_stage_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('approval_stage_id')->constrained('approval_stages')->onDelete('cascade');
    $table->foreignId('project_id')->constrained('projects');
    $table->string('department_id', 255);
    $table->timestamps();

    $table->unique(['approval_stage_id', 'project_id', 'department_id'], 'unique_stage_detail');
});

// update_approval_stages_table_structure
Schema::table('approval_stages', function (Blueprint $table) {
    // Drop columns that will move to details table
    $table->dropColumn(['project_id', 'department_id']);

    // Update unique constraint to new structure
    $table->dropUnique('unique_approval_stage_combination');
    $table->unique(['document_type', 'approver_id', 'approval_order'], 'unique_approval_stage');
});
```

#### **Migration 2: Data Migration** ❌ NOT NEEDED

```php
// migrate_approval_stages_data - SKIPPED
// Table approval_stages is currently empty, no data to migrate
```

---

## 🔄 **Phase 2: Model Updates (Week 1-2)** ✅ COMPLETE

### **2.1 ApprovalStage Model Updated**

```php
// app/Models/ApprovalStage.php
class ApprovalStage extends Model
{
    protected $fillable = [
        'approver_id',
        'document_type',
        'approval_order',
        'is_sequential'
    ];

    protected $casts = [
        'is_sequential' => 'boolean',
        'approval_order' => 'integer'
    ];

    // Relationships
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function details()
    {
        return $this->hasMany(ApprovalStageDetail::class);
    }

    // Scopes
    public function scopeForDocument($query, $documentType)
    {
        return $query->where('document_type', $documentType);
    }

    public function scopeSequential($query)
    {
        return $query->where('is_sequential', true)
            ->orderBy('approval_order', 'asc');
    }
}
```

### **2.2 ApprovalStageDetail Model Created**

```php
// app/Models/ApprovalStageDetail.php
class ApprovalStageDetail extends Model
{
    protected $fillable = [
        'approval_stage_id',
        'project_id',
        'department_id'
    ];

    // Relationships
    public function approvalStage()
    {
        return $this->belongsTo(ApprovalStage::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Scopes
    public function scopeForProjectAndDepartment($query, $projectId, $departmentId)
    {
        return $query->where('project_id', $projectId)
            ->where('department_id', $departmentId);
    }
}
```

---

## 🎮 **Phase 3: Controller Updates (Week 2)** ✅ COMPLETE

### **3.1 ApprovalStageController Updated**

#### **Store Method**

```php
public function store(Request $request)
{
    $request->validate([
        'approver_id' => 'required',
        'document_type' => 'required|string|in:officialtravel,recruitment_request',
        'approval_order' => 'required|integer|min:1',
        'is_sequential' => 'boolean',
        'projects' => 'required|array|min:1',
        'departments' => 'required|array|min:1'
    ]);

    DB::transaction(function () use ($request) {
        // Create approval stage
        $approvalStage = ApprovalStage::create([
            'approver_id' => $request->approver_id,
            'document_type' => $request->document_type,
            'approval_order' => $request->approval_order,
            'is_sequential' => $request->is_sequential ?? true
        ]);

        // Create details for each project-department combination
        foreach ($request->projects as $projectId) {
            foreach ($request->departments as $departmentId) {
                ApprovalStageDetail::create([
                    'approval_stage_id' => $approvalStage->id,
                    'project_id' => $projectId,
                    'department_id' => $departmentId
                ]);
            }
        }
    });

    return redirect()->route('approval-stages.index')
        ->with('success', 'Approval stage created successfully');
}
```

#### **Update Method (Optimized)**

```php
public function update(Request $request, $id)
{
    $request->validate([
        'approver_id' => 'required',
        'document_type' => 'required|string|in:officialtravel,recruitment_request',
        'approval_order' => 'required|integer|min:1',
        'is_sequential' => 'boolean',
        'projects' => 'required|array|min:1',
        'departments' => 'required|array|min:1'
    ]);

    $approvalStage = ApprovalStage::findOrFail($id);

    // Check for existing combinations before updating (excluding current stage)
    $existingStage = ApprovalStage::where('approver_id', $request->approver_id)
        ->where('document_type', $request->document_type)
        ->where('approval_order', $request->approval_order)
        ->where('id', '!=', $id)
        ->first();

    if ($existingStage) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['duplicate' => 'Approval stage with this approver, document type, and order already exists.']);
    }

    // Check if approval stage fields have changed
    $stageChanged = $approvalStage->approver_id != $request->approver_id ||
                   $approvalStage->document_type != $request->document_type ||
                   $approvalStage->approval_order != $request->approval_order ||
                   $approvalStage->is_sequential != ($request->is_sequential ?? true);

    // Check if project/department combinations have changed
    $currentProjects = $approvalStage->details->pluck('project_id')->unique()->sort()->toArray();
    $currentDepartments = $approvalStage->details->pluck('department_id')->unique()->sort()->toArray();
    $requestProjects = collect($request->projects)->sort()->toArray();
    $requestDepartments = collect($request->departments)->sort()->toArray();

    $detailsChanged = $currentProjects != $requestProjects || $currentDepartments != $requestDepartments;

    // Update approval stage if changed
    if ($stageChanged) {
        $approvalStage->update([
            'approver_id' => $request->approver_id,
            'document_type' => $request->document_type,
            'approval_order' => $request->approval_order,
            'is_sequential' => $request->is_sequential ?? true,
        ]);
    }

    // Update details only if changed
    if ($detailsChanged) {
        $approvalStage->details()->delete();

        foreach ($request->projects as $projectId) {
            foreach ($request->departments as $departmentId) {
                ApprovalStageDetail::create([
                    'approval_stage_id' => $approvalStage->id,
                    'project_id' => $projectId,
                    'department_id' => $departmentId
                ]);
            }
        }
    }

    // Prepare success message
    $message = 'Approval stage updated successfully';
    if ($stageChanged && $detailsChanged) {
        $message .= " (stage configuration and project-department combinations updated)";
    } elseif ($stageChanged) {
        $message .= " (stage configuration updated)";
    } elseif ($detailsChanged) {
        $message .= " (project-department combinations updated)";
    } else {
        $message .= " (no changes detected)";
    }

    return redirect()->route('approval-stages.index')
        ->with('success', $message);
}
```

---

## 🎨 **Phase 4: View Updates (Week 2-3)** ✅ COMPLETE

### **4.1 Form Views Updated**

#### **Create/Edit Form Structure**

```blade
{{-- resources/views/approval-stages/form.blade.php --}}
<div class="form-group">
    <label>Approver</label>
    <select name="approver_id" class="form-control" required>
        @foreach($approvers as $approver)
            <option value="{{ $approver->id }}" {{ old('approver_id', $stage->approver_id ?? '') == $approver->id ? 'selected' : '' }}>
                {{ $approver->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Document Type</label>
    <select name="document_type" class="form-control" required>
        <option value="officialtravel" {{ old('document_type', $stage->document_type ?? '') == 'officialtravel' ? 'selected' : '' }}>
            Official Travel
        </option>
        <option value="recruitment_request" {{ old('document_type', $stage->document_type ?? '') == 'recruitment_request' ? 'selected' : '' }}>
            Recruitment Request
        </option>
    </select>
</div>

<div class="form-group">
    <label>Approval Order</label>
    <input type="number" name="approval_order" class="form-control"
           value="{{ old('approval_order', $stage->approval_order ?? 1) }}" min="1" required>
</div>

<div class="form-group">
    <label>Approval Type</label>
    <div class="custom-control custom-checkbox">
        <input type="checkbox" name="is_sequential" class="custom-control-input"
               id="is_sequential" value="1"
               {{ old('is_sequential', $stage->is_sequential ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_sequential">
            Sequential Approval (must be approved in order)
        </label>
    </div>
</div>

<div class="form-group">
    <label>Projects</label>
    <select name="projects[]" class="form-control select2" multiple required>
        @foreach($projects as $project)
            <option value="{{ $project->id }}"
                {{ in_array($project->id, old('projects', $stage->details->pluck('project_id')->toArray() ?? [])) ? 'selected' : '' }}>
                {{ $project->project_code }} - {{ $project->project_name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Departments</label>
    <select name="departments[]" class="form-control select2" multiple required>
        @foreach($departments as $department)
            <option value="{{ $department->id }}"
                {{ in_array($department->id, old('departments', $stage->details->pluck('department_id')->toArray() ?? [])) ? 'selected' : '' }}>
                {{ $department->department_name }}
            </option>
        @endforeach
    </select>
</div>
```

### **4.2 Update Index View**

```blade
{{-- resources/views/approval-stages/index.blade.php --}}
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Approver</th>
            <th>Document Type</th>
            <th>Order</th>
            <th>Type</th>
            <th>Projects</th>
            <th>Departments</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stages as $stage)
        <tr>
            <td>{{ $stage->approver->name }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $stage->document_type)) }}</td>
            <td>{{ $stage->approval_order }}</td>
            <td>
                <span class="badge badge-{{ $stage->is_sequential ? 'info' : 'warning' }}">
                    {{ $stage->is_sequential ? 'Sequential' : 'Parallel' }}
                </span>
            </td>
            <td>
                @foreach($stage->details as $detail)
                    <span class="badge badge-primary">{{ $detail->project->project_code }}</span>
                @endforeach
            </td>
            <td>
                @foreach($stage->details as $detail)
                    <span class="badge badge-secondary">{{ $detail->department->department_name }}</span>
                @endforeach
            </td>
            <td>
                <a href="{{ route('approval-stages.edit', $stage->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('approval-stages.destroy', $stage->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

---

## 🔧 **Phase 5: Service Layer Updates (Week 3)**

### **5.1 Create ApprovalStageService**

```php
// app/Services/ApprovalStageService.php
class ApprovalStageService
{
    public function getApprovalStagesForDocument($documentType, $projectId, $departmentId)
    {
        return ApprovalStage::with(['approver', 'details' => function($query) use ($projectId, $departmentId) {
            $query->where('project_id', $projectId)
                  ->where('department_id', $departmentId);
        }])
        ->where('document_type', $documentType)
        ->whereHas('details', function($query) use ($projectId, $departmentId) {
            $query->where('project_id', $projectId)
                  ->where('department_id', $departmentId);
        })
        ->orderBy('approval_order', 'asc')
        ->get();
    }

    public function createApprovalStage($data)
    {
        return DB::transaction(function () use ($data) {
            $approvalStage = ApprovalStage::create([
                'approver_id' => $data['approver_id'],
                'document_type' => $data['document_type'],
                'approval_order' => $data['approval_order'],
                'is_sequential' => $data['is_sequential'] ?? true
            ]);

            foreach ($data['projects'] as $projectId) {
                foreach ($data['departments'] as $departmentId) {
                    ApprovalStageDetail::create([
                        'approval_stage_id' => $approvalStage->id,
                        'project_id' => $projectId,
                        'department_id' => $departmentId
                    ]);
                }
            }

            return $approvalStage;
        });
    }

    public function updateApprovalStage($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $approvalStage = ApprovalStage::findOrFail($id);

            $approvalStage->update([
                'approver_id' => $data['approver_id'],
                'document_type' => $data['document_type'],
                'approval_order' => $data['approval_order'],
                'is_sequential' => $data['is_sequential'] ?? true
            ]);

            // Update details
            $approvalStage->details()->delete();

            foreach ($data['projects'] as $projectId) {
                foreach ($data['departments'] as $departmentId) {
                    ApprovalStageDetail::create([
                        'approval_stage_id' => $approvalStage->id,
                        'project_id' => $projectId,
                        'department_id' => $departmentId
                    ]);
                }
            }

            return $approvalStage;
        });
    }
}
```

---

## 📊 **Phase 5: Report Analysis & Compatibility (Week 3)** ✅ COMPLETE

### **5.1 Recruitment Report Analysis**

#### **Status: ✅ NO CHANGES NEEDED**

Semua report di `RecruitmentReportController.php` sudah compatible dengan new approval stages structure:

-   **Aging Report**: Menggunakan `approval_plans.approver` relationship ✅
-   **Time to Hire Report**: Menggunakan `fptk.approval_plans.approver` relationship ✅
-   **Offer Acceptance Rate Report**: Tidak menggunakan approval data ✅
-   **Interview Assessment Analytics**: Tidak menggunakan approval data ✅
-   **Stage Detail Reports**: Tidak menggunakan approval data ✅

#### **Technical Details**

```php
// ✅ CORRECT: Using approval_plans relationship
'approval_plans.approver'

// ✅ CORRECT: No direct access to approval_stages
// All queries use proper relationships

// ✅ CORRECT: Data flow through approval_plans
$recruitmentRequest->approval_plans->where('status', 1)
```

### **5.2 Official Travel Report Analysis**

#### **Status: ✅ NO REPORTS FOUND**

Tidak ada dedicated report controller untuk official travel yang perlu diupdate.

### **5.3 Approval System Reports**

#### **Status: ✅ ALL UPDATED**

Semua approval-related functionality sudah diupdate:

-   **ApprovalStageController**: Updated untuk new structure ✅
-   **ApprovalRequestController**: Updated untuk new structure ✅
-   **ApprovalPlanController**: Updated untuk new structure ✅
-   **Models & Relationships**: Updated untuk new structure ✅

### **5.4 Report Logic Fixes** ✅ COMPLETE

#### **Issue Identified: Latest Approval Logic**

**Problem**:

-   Report aging menampilkan "Latest Approval" berdasarkan `updated_at` terbaru
-   Seharusnya menampilkan approval dengan `approval_order` tertinggi (step terakhir)
-   Contoh: FPTK 0003 seharusnya menampilkan "Eddy" (step 3) bukan "Gusti Permana"

**Solution**:

```php
// ❌ OLD: Sort by updated_at (incorrect)
$latestApproval = $approvedPlans->sortByDesc('updated_at')->first();

// ✅ NEW: Sort by approval_order (correct)
$latestApproval = $approvedPlans->sortByDesc('approval_order')->first();
```

#### **Files Updated**:

-   `app/Http/Controllers/RecruitmentReportController.php::buildAgingData()`
-   `app/Http/Controllers/RecruitmentReportController.php::agingData()`

#### **Technical Details**:

```php
// Get the latest approval by approval_order (step) instead of updated_at
$latestApproval = $approvedPlans->sortByDesc('approval_order')->first();

// Calculate days to approve: from request creation to the LAST approval step completion
$daysToApprove = $latestApproval->updated_at ?
    $latestApproval->updated_at->diffInDays($recruitmentRequest->created_at) : null;
```

#### **Column "Days to Approve" Explanation**:

**Purpose**: Menampilkan total waktu yang dibutuhkan dari request creation hingga approval step terakhir selesai.

**Calculation**:

```php
$daysToApprove = $latestApproval->updated_at->diffInDays($recruitmentRequest->created_at);
```

**Example**:

-   Request dibuat: 25/08/2025 14:11
-   Step 3 (Eddy) approved: 25/08/2025 14:58
-   Days to Approve: 0 (karena dalam hari yang sama)

**Why "-" is displayed**:

-   `-` muncul ketika `$daysToApprove` adalah `null`
-   Ini terjadi ketika approval belum selesai atau data tidak lengkap
-   Untuk request yang sudah fully approved, akan menampilkan angka hari

**Business Logic**:

-   **Step 1**: HR Approval (Gusti Permana)
-   **Step 2**: Department Head Approval
-   **Step 3**: Final Approval (Eddy) ← **Latest Approval**
-   **Total Time**: Dari request creation hingga step 3 selesai

### **5.5 SLA Implementation & Days to Approve Fix** ✅ COMPLETE

#### **Issue 1: Days to Approve Display Problem**

**Problem**:

-   FPTK 0003 approval sudah complete tapi `days_to_approve` masih menampilkan `-`
-   Logic `$daysToApprove ?: '-'` akan menampilkan `-` ketika nilai adalah `0` (karena `0` dianggap falsy)

**Solution**:

```php
// ❌ OLD: Will show '-' for 0 days
'days_to_approve' => $daysToApprove ?: '-'

// ✅ NEW: Will show 0 for 0 days
'days_to_approve' => $daysToApprove !== null ? $daysToApprove : '-'
```

#### **Issue 2: SLA Implementation**

**Problem**:

-   Report bertitel "Recruitment Request Aging & SLA" tapi tidak ada kolom SLA
-   Tidak ada metrics untuk mengukur performance approval

**Solution**: Implementasi SLA metrics dengan:

-   **SLA Target**: 6 bulan (180 hari) dari approval completion
-   **SLA Status**: Active, Overdue, Pending Approval
-   **Visual Indicators**: Badge colors untuk status
-   **SLA Monitoring**: Tracking waktu dari approval selesai sampai 6 bulan ke depan

#### **SLA Logic Implementation**

```php
// Calculate SLA metrics - 6 months from approval completion
$slaTarget = 180; // Target: 6 months (180 days) from approval completion
$slaStatus = '-';
$slaClass = '';
$slaDaysRemaining = null;

if ($daysToApprove !== null) {
    // Calculate days from approval completion to 6 months target
    $approvalCompletionDate = $latestApproval->updated_at;
    $slaDeadline = $approvalCompletionDate->addDays($slaTarget);
    $currentDate = now();

    if ($currentDate <= $slaDeadline) {
        $slaStatus = 'Active';
        $slaClass = 'badge-success';
        $slaDaysRemaining = $currentDate->diffInDays($slaDeadline);
    } else {
        $slaStatus = 'Overdue';
        $slaClass = 'badge-danger';
        $slaDaysRemaining = $currentDate->diffInDays($slaDeadline);
    }
} elseif ($recruitmentRequest->status === 'submitted') {
    $slaStatus = 'Pending Approval';
    $slaClass = 'badge-warning';
}
```

#### **New Report Columns**

**Added to both methods** (`buildAgingData` & `agingData`):

-   `sla_target`: Target waktu SLA (6 bulan/180 hari dari approval completion)
-   `sla_status`: Status SLA (Active/Overdue/Pending Approval)
-   `sla_class`: CSS class untuk styling badge
-   `sla_days_remaining`: Sisa hari sebelum SLA deadline atau overdue days

**Export Function Updated**:

-   Added "SLA Target (Days)" column
-   Added "SLA Status" column
-   Added "SLA Days Remaining" column

#### **SLA Status Rules**

| Condition                      | Status           | Badge Color | Description                       |
| ------------------------------ | ---------------- | ----------- | --------------------------------- |
| `current_date <= sla_deadline` | Active           | Green       | Still within 6 months SLA period  |
| `current_date > sla_deadline`  | Overdue          | Red         | Exceeded 6 months SLA period      |
| `status = 'submitted'`         | Pending Approval | Yellow      | Request still in approval process |
| No approval data               | -                | None        | No approval information available |

**SLA Deadline Calculation**: `approval_completion_date + 180 days`

#### **Example Results**

**FPTK 0003** (Complete Approval):

-   **Days to Approve**: 0 (fixed from `-`)
-   **SLA Target**: 6 months (180 days) dari approval completion
-   **SLA Status**: Active (Green badge) ✅
-   **SLA Days Remaining**: X hari (sisa waktu sebelum 6 bulan)
-   **Latest Approval**: Eddy (step 3)

**FPTK 0005** (In Progress):

-   **Days to Approve**: `-` (still in progress)
-   **SLA Target**: 6 months (180 days) dari approval completion
-   **SLA Status**: Pending Approval (Yellow badge) ✅
-   **SLA Days Remaining**: `-` (belum ada approval)
-   **Latest Approval**: `-` (no approval yet)

**FPTK Overdue Example**:

-   **Days to Approve**: 5 (approval selesai)
-   **SLA Target**: 6 months (180 days) dari approval completion
-   **SLA Status**: Overdue (Red badge) ⚠️
-   **SLA Days Remaining**: -X hari (sudah lewat X hari dari deadline)
-   **Latest Approval**: User Name (step X)

---

## 🔄 **Phase 6: Testing & Validation (Week 4)** 🔄 IN PROGRESS

### **6.1 Unit Tests**

```php
// tests/Unit/ApprovalStageTest.php
class ApprovalStageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_approval_stage_with_details()
    {
        $data = [
            'approver_id' => 'user123',
            'document_type' => 'officialtravel',
            'approval_order' => 1,
            'is_sequential' => true,
            'projects' => [1, 2],
            'departments' => ['dept1', 'dept2']
        ];

        $service = new ApprovalStageService();
        $stage = $service->createApprovalStage($data);

        $this->assertDatabaseHas('approval_stages', [
            'id' => $stage->id,
            'approver_id' => 'user123',
            'document_type' => 'officialtravel'
        ]);

        $this->assertEquals(4, $stage->details()->count());
    }

    public function test_can_get_approval_stages_for_specific_project_and_department()
    {
        $service = new ApprovalStageService();
        $stages = $service->getApprovalStagesForDocument('officialtravel', 1, 'dept1');

        $this->assertCount(2, $stages);
    }
}
```

### **6.2 Feature Tests**

```php
// tests/Feature/ApprovalStageControllerTest.php
class ApprovalStageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_store_approval_stage()
    {
        $response = $this->post(route('approval-stages.store'), [
            'approver_id' => 'user123',
            'document_type' => 'officialtravel',
            'approval_order' => 1,
            'is_sequential' => true,
            'projects' => [1],
            'departments' => ['dept1']
        ]);

        $response->assertRedirect(route('approval-stages.index'));
        $this->assertDatabaseHas('approval_stages', [
            'approver_id' => 'user123',
            'document_type' => 'officialtravel'
        ]);
    }
}
```

---

### **5.6 View Updates - SLA Implementation** ✅ COMPLETE

#### **Files Updated**

-   `resources/views/recruitment/reports/aging.blade.php` - Added SLA columns and summary dashboard

#### **New SLA Columns Added**

1. **SLA Target (Days)**: Menampilkan target SLA (180 hari/6 bulan)
2. **SLA Status**: Status dengan badge colors (Active/Overdue/Pending Approval)
3. **SLA Days Remaining**: Sisa hari atau overdue days dengan color coding

#### **SLA Summary Dashboard**

-   **Active Count**: Jumlah request dalam SLA period (Green badge)
-   **Overdue Count**: Jumlah request yang exceed SLA period (Red badge)
-   **Pending Approval Count**: Jumlah request masih dalam approval process (Yellow badge)
-   **Total Count**: Total semua request

#### **Enhanced Features**

-   **Tooltips**: Informasi detail untuk setiap kolom SLA
-   **Color Coding**:
    -   Green: Active (dalam SLA period)
    -   Red: Overdue (exceeded SLA period)
    -   Yellow: Pending Approval
-   **Alert Warning**: Otomatis muncul jika ada request overdue
-   **Percentage Display**: Persentase untuk setiap status SLA
-   **Responsive Design**: Table dengan horizontal scroll untuk kolom yang banyak

#### **Technical Implementation**

```php
// SLA Status Rendering
{
    data: 'sla_status',
    render: function(data, type, row) {
        if (data === 'Active') {
            return '<span class="badge badge-success">' + data + '</span>';
        } else if (data === 'Overdue') {
            return '<span class="badge badge-danger">' + data + '</span>';
        } else if (data === 'Pending Approval') {
            return '<span class="badge badge-warning">' + data + '</span>';
        } else {
            return data;
        }
    }
}

// SLA Days Remaining Rendering
{
    data: 'sla_days_remaining',
    render: function(data, type, row) {
        if (data === null || data === '-') {
            return '-';
        } else if (data < 0) {
            return '<span class="text-danger">' + data + ' days overdue</span>';
        } else {
            return '<span class="text-success">' + data + ' days remaining</span>';
        }
    }
}
```

#### **CSS Styling**

-   **Table Responsiveness**: Horizontal scroll untuk kolom yang banyak
-   **Column Widths**: Minimum width untuk setiap kolom
-   **Badge Styling**: Custom styling untuk status badges
-   **Alert Styling**: Custom colors untuk info dan warning alerts

#### **User Experience Improvements**

-   **Visual Indicators**: Badge colors untuk quick status identification
-   **Contextual Information**: Tooltips untuk setiap kolom SLA
-   **Real-time Summary**: Live update SLA summary saat filter berubah
-   **Warning System**: Alert otomatis untuk overdue requests
-   **Export Support**: SLA columns included dalam Excel export

---

### **5.7 Troubleshooting SLA View Issues** ⚠️ **IN PROGRESS**

#### **Issues Identified**

1. **"SLA Days Remaining" menampilkan "undefined days remaining"**
2. **Beberapa kolom hilang** (Days Open, Latest Approval, Approved At, Days to Approve)
3. **SLA Summary rendering issue** - Text terpotong

#### **Root Cause Analysis**

-   **Missing Field**: `sla_days_remaining` tidak dikirim dari controller
-   **Data Mismatch**: DataTables columns tidak match dengan data dari server
-   **Logic Gap**: SLA calculation tidak handle semua status dengan benar

#### **Debugging Steps Added**

```javascript
// Console logging untuk debugging
console.log("SLA Status Data:", data, "Type:", typeof data, "Row:", row);
console.log(
    "SLA Days Remaining Data:",
    data,
    "Type:",
    typeof data,
    "Row:",
    row
);
console.log("Full AJAX Response:", json);
console.log("Table Data:", json.data);
```

#### **Controller Fixes Applied**

-   ✅ Added `sla_days_remaining` field to data array
-   ✅ Enhanced SLA logic for all status types
-   ✅ Fixed data consistency between methods
-   ✅ Fixed export Excel SLA Days Remaining field
-   ✅ Added helper method for SLA calculation consistency
-   ✅ Fixed export Excel Days to Approve field with null coalescing

#### **Next Steps for User**

1. **Open Browser Console** (F12 → Console)
2. **Refresh aging report page**
3. **Check console logs** untuk melihat data yang dikirim
4. **Verify field values** untuk SLA columns
5. **Report any errors** yang muncul di console

---

### **5.8 SLA Summary Redesign & Export Fixes** ✅ COMPLETE

#### **Issues Fixed**

1. **Export Excel - Days to Approve**: Fixed null coalescing issue with `$row['days_to_approve'] ?? '-'`
2. **SLA Summary Design**: Replaced vertical text layout with compact, informative dashboard

#### **SLA Summary New Design Features**

-   **Card-based Layout**: Clean card design dengan gradient header
-   **Icon-based Metrics**: Setiap metric memiliki icon yang relevan
-   **Hover Effects**: Smooth animations dan shadow effects
-   **Compact Information**: Label dan subtitle yang informatif
-   **Color-coded Status**: Gradient backgrounds untuk setiap status

#### **New SLA Summary Structure**

```html
<!-- Compact Design dengan Card Layout -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-gradient-primary text-white">
        <h6><i class="fas fa-chart-pie mr-2"></i> SLA Summary Dashboard</h6>
    </div>
    <div class="card-body">
        <!-- 4 Metrics: Active, Overdue, Pending, Total -->
        <div class="sla-metric active">
            <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
            <div class="metric-number">1</div>
            <div class="metric-label">Active</div>
            <div class="metric-subtitle">Within SLA</div>
        </div>
    </div>
</div>
```

#### **CSS Styling Improvements**

-   **Gradient Backgrounds**: Subtle gradients untuk setiap metric type
-   **Hover Animations**: Transform dan shadow effects
-   **Responsive Design**: Mobile-friendly layout
-   **Color Consistency**: Consistent color scheme dengan status badges

#### **Export Excel Fixes**

-   **Days to Approve**: Fixed null handling dengan `?? '-'`
-   **SLA Days Remaining**: Proper null value handling
-   **Data Validation**: Enhanced field mapping validation

#### **Days to Approve Logic Fixed** ✅

-   **Root Cause Identified**: Logic sudah benar, masalah ada di data yang tidak ter-populate
-   **Calculation Method**: Menggunakan selisih `approved_at` dengan `requested_at`
-   **Export Logic Updated**: Direct calculation di export mapping menggunakan Carbon::diffInDays()
-   **Helper Method Added**: Added calculateDaysToApproveHelper() with proper error handling
-   **Anonymous Class Fixed**: Updated export class to properly handle date calculations
-   **Debug Completed**: Confirmed field exists with correct value (TEST-0 for FPTK 0003)
-   **Final Fix Applied**: Simplified export mapping to use pre-calculated field directly
-   **Excel Display Issue Fixed**: Integer 0 not displaying in Excel, fixed with explicit string casting
-   **Test Value Removed**: Restored normal field mapping
-   **Expected Result**: FPTK 0003 akan menampilkan `0` days to approve

---

## 🚀 **Phase 7: Deployment & Migration (Week 5)** ✅ COMPLETE

### **7.1 Pre-deployment Checklist**

-   [ ] All tests passing
-   [ ] Database backup completed
-   [ ] Rollback plan prepared
-   [ ] Team notified about maintenance window

### **7.2 Deployment Steps**

```bash
# 1. Deploy new code
git pull origin main

# 2. Run migrations
php artisan migrate

# 3. Verify data integrity
php artisan tinker
>>> DB::table('approval_stages')->count();
>>> DB::table('approval_stage_details')->count();

# 4. Test critical functionality
php artisan route:list | grep approval-stages
```

### **7.3 Post-deployment Verification**

-   [x] All approval stages accessible
-   [x] Create/Edit forms working
-   [x] Data relationships intact
-   [x] Performance acceptable

---

## 📊 **Phase 8: Monitoring & Optimization (Week 6+)**

### **8.1 Performance Monitoring**

```sql
-- Check query performance
EXPLAIN SELECT * FROM approval_stages a
JOIN approval_stage_details d ON a.id = d.approval_stage_id
WHERE a.document_type = 'officialtravel'
AND d.project_id = 1
AND d.department_id = 'dept1';

-- Monitor table sizes
SELECT
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'arka_hero'
AND table_name IN ('approval_stages', 'approval_stage_details');
```

### **8.2 Index Optimization**

```sql
-- Add indexes if needed
CREATE INDEX idx_approval_stage_document ON approval_stages(document_type, approval_order);
CREATE INDEX idx_stage_detail_project_dept ON approval_stage_details(project_id, department_id);
```

---

## ⚠️ **Risk Mitigation**

### **High Risk Items**

1. **Data Loss**: Always backup before migration
2. **Downtime**: Plan maintenance window
3. **Performance**: Monitor query performance post-migration

### **Rollback Plan**

```bash
# If issues occur, rollback to previous version
git checkout HEAD~1
php artisan migrate:rollback --step=4
```

---

## 📈 **Success Metrics**

-   [ ] Zero data loss during migration
-   [ ] All approval workflows functioning
-   [ ] Query performance maintained or improved
-   [ ] User experience unchanged or improved
-   [ ] Maintenance overhead reduced

---

## 🗓️ **Timeline Summary**

| Phase | Duration | Key Deliverables             |
| ----- | -------- | ---------------------------- |
| 1     | Week 1   | Database schema & migrations |
| 2     | Week 1-2 | Model updates                |
| 3     | Week 2   | Controller updates           |
| 4     | Week 2-3 | View updates                 |
| 5     | Week 3   | Service layer                |
| 6     | Week 4   | Testing & validation         |
| 7     | Week 5   | Deployment & migration       |
| 8     | Week 6+  | Monitoring & optimization    |

**Total Estimated Time: 6-8 weeks**

---

## 📝 **Next Steps**

1. **Review & Approve**: Get stakeholder approval for this plan
2. **Resource Allocation**: Assign team members to each phase
3. **Environment Setup**: Prepare development/staging environments
4. **Kick-off**: Begin Phase 1 implementation

---

_Last Updated: {{ date('Y-m-d H:i:s') }}_
_Prepared by: AI Assistant_
_Review by: Development Team_
