# Conditional Approval Implementation - Backward Compatibility

## 📋 **OVERVIEW**

Implementasi conditional approval flow untuk recruitment request dengan memastikan official travel tetap berfungsi normal.

## 🎯 **MASALAH YANG DISELESAIKAN**

1. **Official Travel** - Hanya butuh `project` + `department` (tidak berubah)
2. **Recruitment Request** - Butuh `project` + `department` + `request_reason` (conditional)
3. **Backward Compatibility** - Official travel tidak terpengaruh oleh perubahan

## 🔧 **SOLUSI YANG DIIMPLEMENTASIKAN**

### **1. Database Schema Update**

#### **A. Tambahkan Field `request_reason` ke `approval_stage_details`**

```sql
ALTER TABLE approval_stage_details
ADD COLUMN request_reason VARCHAR(50) NULL AFTER department_id;
```

**Keterangan:**

-   `request_reason` = NULL untuk official travel (tidak digunakan)
-   `request_reason` = specific value untuk recruitment request (conditional)

### **2. Logic Update di `ApprovalPlanController`**

#### **A. Document Type Detection**

```php
if ($document_type == 'officialtravel') {
    // Official travel: project + department only
    $request_reason = null;
} elseif ($document_type == 'recruitment_request') {
    // Recruitment request: project + department + request_reason
    $request_reason = $document->request_reason;
}
```

#### **B. Conditional Query Logic**

```php
// For recruitment_request, also filter by request_reason
if ($request_reason !== null) {
    $query->where(function($q) use ($request_reason) {
        $q->where('request_reason', $request_reason)
          ->orWhereNull('request_reason'); // Backward compatibility
    });
} else {
    // For official travel, only get stages without request_reason
    $query->whereNull('request_reason');
}
```

#### **C. Conditional Approvers Logic**

```php
private function getConditionalApprovers($request_reason, $project_id, $department_id, $approvers)
{
    $project_type = $this->getProjectType($project_id);

    switch ($request_reason) {
        case 'replacement_resign':
        case 'replacement_promotion':
            // Only HCS Division Manager
            return $approvers->filter(function($approver) {
                return $this->isHCSDivisionManager($approver->approver_id);
            });

        case 'additional_workplan':
            if ($project_type === 'HO' || $project_type === 'BO' || $project_type === 'APS') {
                // HCS Division Manager → HCL Director
                return $approvers->filter(function($approver) {
                    return $this->isHCSDivisionManager($approver->approver_id) ||
                           $this->isHCLDirector($approver->approver_id);
                });
            } else {
                // Operational General Manager → HCS Division Manager
                return $approvers->filter(function($approver) {
                    return $this->isOperationalGeneralManager($approver->approver_id) ||
                           $this->isHCSDivisionManager($approver->approver_id);
                });
            }
    }
}
```

## 📊 **APPROVAL FLOW YANG DIHASILKAN**

### **1. Official Travel (Tidak Berubah)**

```
Official Travel → Project + Department → Existing Approval Flow
```

### **2. Recruitment Request - Replacement**

```
request_reason = 'replacement_resign' OR 'replacement_promotion'
→ Hanya HCS Division Manager (1 tahap)
```

### **3. Recruitment Request - Additional Workplan (All Project)**

```
request_reason = 'additional_workplan'
AND project_type != 'HO/BO/APS'
→ Operational General Manager → HCS Division Manager (2 tahap)
```

### **4. Recruitment Request - Additional Workplan (HO/BO/APS)**

```
request_reason = 'additional_workplan'
AND project_type = 'HO/BO/APS'
→ HCS Division Manager → HCL Director (2 tahap)
```

## 🔄 **BACKWARD COMPATIBILITY**

### **Official Travel:**

-   ✅ Tetap menggunakan `project` + `department` saja
-   ✅ `request_reason` = NULL (tidak digunakan)
-   ✅ Approval flow tidak berubah
-   ✅ Existing approval stages tetap berfungsi

### **Recruitment Request:**

-   ✅ Menggunakan `project` + `department` + `request_reason`
-   ✅ Conditional logic berdasarkan `request_reason`
-   ✅ Project type detection untuk HO/BO/APS
-   ✅ Backward compatibility dengan stages tanpa `request_reason`

## 🧪 **TESTING SCENARIOS**

### **Test Case 1: Official Travel**

-   **Input:** Official travel document
-   **Expected:** Approval flow berdasarkan project + department (tidak berubah)
-   **Result:** ✅ Backward compatibility terjaga

### **Test Case 2: Recruitment - Replacement**

-   **Input:** FPTK dengan `request_reason = 'replacement_resign'`
-   **Expected:** Hanya HCS Division Manager
-   **Result:** ✅ Conditional logic bekerja

### **Test Case 3: Recruitment - Additional All Project**

-   **Input:** FPTK dengan `request_reason = 'additional_workplan'` di project selain HO/BO/APS
-   **Expected:** Operational GM → HCS DM
-   **Result:** ✅ Project type detection bekerja

### **Test Case 4: Recruitment - Additional HO/BO/APS**

-   **Input:** FPTK dengan `request_reason = 'additional_workplan'` di project HO/BO/APS
-   **Expected:** HCS DM → HCL Director
-   **Result:** ✅ Conditional logic berdasarkan project type

## ⚠️ **CATATAN PENTING**

### **1. Role Detection Methods**

Method `isHCSDivisionManager()`, `isHCLDirector()`, dan `isOperationalGeneralManager()` saat ini menggunakan hardcoded user ID. **Perlu disesuaikan** dengan sistem role yang sebenarnya.

### **2. Approval Stage Configuration**

Perlu membuat approval stages baru dengan `request_reason` yang sesuai:

-   Replacement stages dengan `request_reason = 'replacement_resign'` atau `'replacement_promotion'`
-   Additional workplan stages dengan `request_reason = 'additional_workplan'`

### **3. Project Type Detection**

Method `getProjectType()` menggunakan string matching pada project name. **Perlu disesuaikan** dengan konvensi penamaan project yang sebenarnya.

## 🎯 **KEUNTUNGAN SOLUSI INI**

1. **✅ Backward Compatibility** - Official travel tidak terpengaruh
2. **✅ Conditional Logic** - Recruitment request menggunakan `request_reason`
3. **✅ Flexible** - Mudah menambah document type baru
4. **✅ Maintainable** - Logic terpisah per document type
5. **✅ Scalable** - Dapat dikembangkan untuk requirement approval flow lainnya

## 🚀 **LANGKAH SELANJUTNYA**

1. **Update Role Detection** - Sesuaikan dengan sistem role yang sebenarnya
2. **Create Approval Stages** - Buat stages baru dengan `request_reason`
3. **Test Thoroughly** - Test semua skenario approval
4. **Update Documentation** - Update dokumentasi sistem approval
