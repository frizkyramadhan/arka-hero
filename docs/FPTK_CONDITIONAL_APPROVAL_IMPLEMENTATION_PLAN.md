# FPTK Conditional Approval Flow Implementation Plan

## 📋 **ANALISIS KONDISI SAAT INI**

### **Current Flow:**

```
Semua FPTK → Gusti (Order 1) → Eddy (Order 2) → Rachman (Order 2)
```

### **Masalah:**

-   Tidak ada pembedaan approval flow berdasarkan `request_reason`
-   Semua FPTK menggunakan approval flow yang sama
-   Tidak ada conditional logic berdasarkan jenis request

---

## 🎯 **TARGET FLOW YANG DIINGINKAN**

### **1. Pengganti Karyawan (Replacement)**

```
FPTK (replacement_resign, replacement_promotion) → HCS Division Manager
```

### **2. Penambahan Tenaga Kerja All Project**

```
FPTK (additional_workplan) → Operational General Manager → HCS Division Manager
```

### **3. Penambahan Tenaga Kerja HO/BO/APS**

```
FPTK (additional_workplan) → HCS Division Manager → HCL Director
```

---

## 🔧 **IMPLEMENTATION PLAN**

### **Phase 1: Database Schema Updates**

#### **A. Tambahkan Field ke `approval_stages`**

```sql
ALTER TABLE approval_stages
ADD COLUMN request_reason VARCHAR(50) NULL AFTER document_type,
ADD COLUMN project_type VARCHAR(50) NULL AFTER request_reason;
```

#### **B. Update `approval_stage_details`**

```sql
ALTER TABLE approval_stage_details
ADD COLUMN request_reason VARCHAR(50) NULL AFTER department_id;
```

### **Phase 2: Model Updates**

#### **A. Update `ApprovalStage` Model**

```php
// app/Models/ApprovalStage.php
protected $fillable = [
    'approver_id',
    'document_type',
    'request_reason', // NEW
    'project_type',   // NEW
    'approval_order',
    'created_at',
    'updated_at'
];

// Add scopes for conditional approval
public function scopeForRequestReason($query, $requestReason)
{
    return $query->where('request_reason', $requestReason);
}

public function scopeForProjectType($query, $projectType)
{
    return $query->where('project_type', $projectType);
}
```

#### **B. Update `ApprovalStageDetail` Model**

```php
// app/Models/ApprovalStageDetail.php
protected $fillable = [
    'approval_stage_id',
    'project_id',
    'department_id',
    'request_reason', // NEW
    'created_at',
    'updated_at'
];
```

### **Phase 3: Controller Logic Updates**

#### **A. Update `ApprovalPlanController::create_approval_plan()`**

**Current Logic:**

```php
$approvers = ApprovalStage::with(['approver', 'details'])
    ->where('document_type', $document_type)
    ->whereHas('details', function ($query) use ($project, $department_id) {
        $query->where('project_id', $project)
            ->where('department_id', $department_id);
    })
    ->orderBy('approval_order', 'asc')
    ->get();
```

**New Logic:**

```php
public function create_approval_plan($document_type, $document_id)
{
    // ... existing code ...

    if ($document_type == 'recruitment_request') {
        $document = RecruitmentRequest::findOrFail($document_id);
        $project = $document->project_id;
        $department_id = $document->department_id;
        $request_reason = $document->request_reason; // NEW

        // Determine project type based on project
        $project_type = $this->getProjectType($project); // NEW

        // Get conditional approvers based on request_reason and project_type
        $approvers = $this->getConditionalApprovers($request_reason, $project_type, $project, $department_id);
    }

    // ... rest of the logic ...
}

private function getConditionalApprovers($request_reason, $project_type, $project, $department_id)
{
    $query = ApprovalStage::with(['approver', 'details'])
        ->where('document_type', 'recruitment_request')
        ->whereHas('details', function ($q) use ($project, $department_id, $request_reason) {
            $q->where('project_id', $project)
              ->where('department_id', $department_id)
              ->where('request_reason', $request_reason); // NEW
        });

    // Apply conditional logic based on request_reason
    switch ($request_reason) {
        case 'replacement_resign':
        case 'replacement_promotion':
            // Only HCS Division Manager
            $query->where('request_reason', 'replacement');
            break;

        case 'additional_workplan':
            if ($project_type === 'HO' || $project_type === 'BO' || $project_type === 'APS') {
                // HCS Division Manager → HCL Director
                $query->where('request_reason', 'additional_ho_bo_aps');
            } else {
                // Operational General Manager → HCS Division Manager
                $query->where('request_reason', 'additional_all_project');
            }
            break;
    }

    return $query->orderBy('approval_order', 'asc')->get();
}

private function getProjectType($project_id)
{
    // Determine project type based on project
    $project = Project::find($project_id);

    if (str_contains($project->project_name, 'HO')) {
        return 'HO';
    } elseif (str_contains($project->project_name, 'BO')) {
        return 'BO';
    } elseif (str_contains($project->project_name, 'APS')) {
        return 'APS';
    } else {
        return 'ALL_PROJECT';
    }
}
```

### **Phase 4: Approval Stage Configuration**

#### **A. Create New Approval Stages**

**For Replacement Requests:**

```sql
-- HCS Division Manager for replacement
INSERT INTO approval_stages (approver_id, document_type, request_reason, approval_order)
VALUES ('hr-manager-id', 'recruitment_request', 'replacement', 1);
```

**For Additional Workplan - All Project:**

```sql
-- Operational General Manager
INSERT INTO approval_stages (approver_id, document_type, request_reason, approval_order)
VALUES ('operational-general-manager-id', 'recruitment_request', 'additional_all_project', 1);

-- HCS Division Manager
INSERT INTO approval_stages (approver_id, document_type, request_reason, approval_order)
VALUES ('hr-manager-id', 'recruitment_request', 'additional_all_project', 2);
```

**For Additional Workplan - HO/BO/APS:**

```sql
-- HCS Division Manager
INSERT INTO approval_stages (approver_id, document_type, request_reason, approval_order)
VALUES ('hr-manager-id', 'recruitment_request', 'additional_ho_bo_aps', 1);

-- HCL Director
INSERT INTO approval_stages (approver_id, document_type, request_reason, approval_order)
VALUES ('hcl-director-id', 'recruitment_request', 'additional_ho_bo_aps', 2);
```

#### **B. Create Approval Stage Details**

```sql
-- For each approval stage, create details for all relevant project-department combinations
INSERT INTO approval_stage_details (approval_stage_id, project_id, department_id, request_reason)
SELECT
    as.id,
    asd.project_id,
    asd.department_id,
    as.request_reason
FROM approval_stages as
CROSS JOIN approval_stage_details asd
WHERE as.document_type = 'recruitment_request'
AND asd.approval_stage_id IN (SELECT id FROM approval_stages WHERE document_type = 'recruitment_request' AND request_reason IS NULL);
```

### **Phase 5: UI Updates**

#### **A. Update Approval Preview**

```php
// app/Http/Controllers/ApprovalStageController.php
public function preview(Request $request)
{
    $project_id = $request->project_id;
    $department_id = $request->department_id;
    $request_reason = $request->request_reason; // NEW

    // Get conditional approval flow
    $approvalFlow = $this->getConditionalApprovalFlow($project_id, $department_id, $request_reason);

    return response()->json([
        'approval_flow' => $approvalFlow
    ]);
}
```

#### **B. Update FPTK Form**

```javascript
// Add request_reason change handler
$("#request_reason").on("change", function () {
    updateApprovalPreview();
});

function updateApprovalPreview() {
    const projectId = $("#project_id").val();
    const departmentId = $("#department_id").val();
    const requestReason = $("#request_reason").val(); // NEW

    if (!projectId || !departmentId || !requestReason) {
        return;
    }

    // Call preview API with request_reason
    $.get(
        "/approval-stages/preview",
        {
            project_id: projectId,
            department_id: departmentId,
            request_reason: requestReason, // NEW
        },
        function (response) {
            displayApprovalPreview(response.approval_flow);
        }
    );
}
```

---

## 🧪 **TESTING SCENARIOS**

### **Test Case 1: Replacement Request**

-   **Input:** FPTK dengan `request_reason = 'replacement_resign'`
-   **Expected:** Hanya HCS Division Manager yang approve
-   **Flow:** Gusti → Approved

### **Test Case 2: Additional Workplan - All Project**

-   **Input:** FPTK dengan `request_reason = 'additional_workplan'` di project selain HO/BO/APS
-   **Expected:** Operational General Manager → HCS Division Manager
-   **Flow:** Operational GM → HCS DM → Approved

### **Test Case 3: Additional Workplan - HO/BO/APS**

-   **Input:** FPTK dengan `request_reason = 'additional_workplan'` di project HO/BO/APS
-   **Expected:** HCS Division Manager → HCL Director
-   **Flow:** HCS DM → HCL Director → Approved

---

## 📝 **IMPLEMENTATION STEPS**

1. **Database Migration** - Add new fields to tables
2. **Model Updates** - Update ApprovalStage and ApprovalStageDetail models
3. **Controller Logic** - Implement conditional approval logic
4. **Approval Stage Configuration** - Create new approval stages for each scenario
5. **UI Updates** - Update forms and preview functionality
6. **Testing** - Test all scenarios
7. **Documentation** - Update system documentation

---

## ⚠️ **CONSIDERATIONS**

1. **Backward Compatibility** - Existing FPTK should continue to work
2. **Role Assignment** - Ensure proper role assignment for new approvers
3. **Data Migration** - Migrate existing approval stages if needed
4. **Error Handling** - Handle cases where no approvers are configured
5. **Logging** - Add proper logging for conditional approval flows

---

## 🎯 **SUCCESS CRITERIA**

-   [ ] Replacement requests only require HCS Division Manager approval
-   [ ] Additional workplan requests follow correct approval flow based on project type
-   [ ] UI shows correct approval preview based on request reason
-   [ ] All existing functionality remains intact
-   [ ] Proper error handling for missing configurations
