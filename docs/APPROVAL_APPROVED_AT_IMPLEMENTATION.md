# 📋 Approval System: `approved_at` Field Implementation

## 🎯 **Overview**

This document explains how the `approved_at` field is implemented in the approval system for recruitment requests, ensuring that the field is properly updated when the final approval step is completed.

---

## 🏗️ **Current Implementation**

### **1. Database Structure**

The `recruitment_requests` table includes the `approved_at` field:

```sql
-- From migration: 2025_07_25_170249_add_approval_fields_to_recruitment_requests_table.php
ALTER TABLE recruitment_requests
ADD COLUMN approved_at TIMESTAMP NULL;
```

### **2. Model Configuration**

The `RecruitmentRequest` model has been updated to include the `approved_at` field:

```php
// app/Models/RecruitmentRequest.php
protected $fillable = [
    // ... other fields
    'approved_at',
];

protected $casts = [
    // ... other casts
    'approved_at' => 'datetime',
];

protected $dates = [
    // ... other dates
    'approved_at',
];
```

---

## 🔄 **Approval Flow Logic**

### **1. Approval Plan Creation**

When a recruitment request is submitted, approval plans are created:

```php
// app/Http/Controllers/RecruitmentRequestController.php
public function submitForApproval($id)
{
    // Create approval plans using ApprovalPlanController
    $response = app(ApprovalPlanController::class)->create_approval_plan('recruitment_request', $fptk->id);

    // Update status to submitted
    $fptk->update([
        'status' => 'submitted',
        'submit_at' => now(),
    ]);
}
```

### **2. Approval Processing**

The `ApprovalPlanController::update()` method handles individual approval decisions:

```php
// app/Http/Controllers/ApprovalPlanController.php
public function update(Request $request, $id)
{
    // Update approval plan status
    $approval_plan->update([
        'status' => $request->status, // 1 = approved, 2 = rejected
        'remarks' => $request->remarks,
    ]);

    // Check if all approvals are completed
    if ($this->areAllSequentialApprovalsCompleted($approval_plan)) {
        // Update document status and approved_at timestamp
        $updateData = [
            'status' => 'approved',
            'approved_at' => $approval_plan->updated_at,
        ];

        $document->update($updateData);

        // Log the approval completion
        Log::info("Document approved successfully", [
            'document_type' => $document_type,
            'document_id' => $document->id,
            'approved_at' => $approval_plan->updated_at,
            'approver_id' => $approval_plan->approver_id
        ]);
    }
}
```

### **3. Final Approval Check**

The `areAllSequentialApprovalsCompleted()` method determines when all approvals are finished:

```php
private function areAllSequentialApprovalsCompleted($approvalPlan)
{
    $allApprovals = ApprovalPlan::where('document_id', $approvalPlan->document_id)
        ->where('document_type', $approvalPlan->document_type)
        ->where('is_open', true)
        ->get();

    // If no approvals exist, return false
    if ($allApprovals->isEmpty()) {
        return false;
    }

    // Check if all approvals are completed (status = 1 for approved)
    foreach ($allApprovals as $approval) {
        if ($approval->status != 1) { // Not approved
            return false;
        }
    }

    // All approvals are completed
    return true;
}
```

---

## 📊 **Bulk Approval Support**

The system also supports bulk approval operations:

```php
// app/Http/Controllers/ApprovalPlanController.php
public function bulkApprove(Request $request)
{
    // Process each approval plan
    foreach ($request->ids as $id) {
        $approval_plan = ApprovalPlan::findOrFail($id);

        // Update approval plan
        $approval_plan->update([
            'status' => 1, // Approved
            'remarks' => $request->remarks,
        ]);

        // Check if all approvers have approved
        $allApprovalPlans = ApprovalPlan::where('document_id', $document->id)
            ->where('document_type', $document_type)
            ->where('is_open', 1)
            ->get();

        $approved_count = $allApprovalPlans->where('status', 1)->count();

        if ($approved_count === $allApprovalPlans->count()) {
            // Update document status to approved
            $updateData = [
                'status' => 'approved',
                'approved_at' => now(),
            ];

            $document->update($updateData);
        }
    }
}
```

---

## 🎯 **Key Benefits**

### **1. Proper Timestamp Tracking**

-   **`approved_at`** is set when the final approval step is completed
-   **`submit_at`** is set when the document is submitted for approval
-   Both timestamps are properly logged and tracked

### **2. Sequential Approval Support**

-   Supports both sequential and parallel approval workflows
-   Ensures proper approval order validation
-   Prevents out-of-order approvals

### **3. Comprehensive Logging**

-   All approval actions are logged with timestamps
-   Tracks which approver completed the final step
-   Maintains audit trail for compliance

### **4. Bulk Operations**

-   Supports bulk approval for multiple documents
-   Maintains data consistency across operations
-   Efficient processing of multiple approvals

---

## 🔍 **Usage Examples**

### **1. Check Approval Status**

```php
$recruitmentRequest = RecruitmentRequest::find($id);

if ($recruitmentRequest->status === 'approved') {
    echo "Approved at: " . $recruitmentRequest->approved_at->format('Y-m-d H:i:s');
}
```

### **2. Approval Timeline**

```php
$recruitmentRequest = RecruitmentRequest::find($id);

$timeline = [
    'created' => $recruitmentRequest->created_at,
    'submitted' => $recruitmentRequest->submit_at,
    'approved' => $recruitmentRequest->approved_at,
];

// Calculate approval duration
if ($recruitmentRequest->approved_at) {
    $duration = $recruitmentRequest->submit_at->diffInDays($recruitmentRequest->approved_at);
    echo "Approval took {$duration} days";
}
```

---

## 🚀 **Future Enhancements**

### **1. Additional Timestamps**

Consider adding more granular timestamps:

-   `first_approval_at` - When first approval was received
-   `last_approval_at` - When final approval was received
-   `rejection_at` - When document was rejected

### **2. Approval History**

Track individual approval steps:

-   `approval_step_1_at`
-   `approval_step_2_at`
-   etc.

### **3. Performance Metrics**

Use timestamps for:

-   Average approval time calculation
-   Bottleneck identification
-   SLA monitoring

---

## 📝 **Summary**

The `approved_at` field implementation ensures that:

1. ✅ **Proper Timestamp Tracking**: Records when final approval is completed
2. ✅ **Sequential Approval Support**: Works with multi-step approval workflows
3. ✅ **Bulk Operations**: Supports efficient bulk approval processing
4. ✅ **Comprehensive Logging**: Maintains full audit trail
5. ✅ **Data Consistency**: Updates are atomic and consistent

This implementation provides a robust foundation for tracking approval timelines and maintaining compliance with approval workflows.
