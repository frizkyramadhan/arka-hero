# Roster Balancing Implementation Plan

## Overview
Implementasi fitur balancing cuti untuk mengatur jumlah hari kerja karyawan roster, dengan tetap mempertahankan off days 14 hari. Periodic leave akan muncul lebih cepat/lambat sesuai adjustment.

## Prinsip Desain
- **Simple**: Gunakan struktur yang sudah ada (`roster_adjustments`)
- **Clean**: Tidak ada table baru, hanya extend existing functionality
- **Integrated**: UI terintegrasi dengan `roster.index` yang sudah ada
- **Minimal**: Hanya tambahkan yang diperlukan, tidak overengineer

---

## Phase 1: Backend - Service & Model Methods

### 1.1 Update Roster Model
**File**: `app/Models/Roster.php`

**Tambah method baru**:
```php
/**
 * Get adjusted work days (base work_days + net adjustment)
 */
public function getAdjustedWorkDays()
{
    $baseWorkDays = $this->getWorkDays();
    $netAdjustment = $this->getNetAdjustment();
    return max(0, $baseWorkDays + $netAdjustment);
}

/**
 * Get net adjustment from all roster adjustments
 */
public function getNetAdjustment()
{
    $positive = $this->rosterAdjustments()
        ->where('adjustment_type', '+days')
        ->sum('adjusted_value');
    
    $negative = $this->rosterAdjustments()
        ->where('adjustment_type', '-days')
        ->sum('adjusted_value');
    
    return $positive - $negative;
}

/**
 * Get manual balancing adjustments only (exclude leave request adjustments)
 */
public function getManualBalancingAdjustments()
{
    return $this->rosterAdjustments()
        ->whereNull('leave_request_id')
        ->orderBy('created_at', 'desc')
        ->get();
}
```

**Update method `calculateActualWorkDays()`**:
```php
public function calculateActualWorkDays()
{
    return $this->getAdjustedWorkDays();
}
```

### 1.2 Create RosterBalancingService
**File**: `app/Services/RosterBalancingService.php` (NEW)

```php
<?php

namespace App\Services;

use App\Models\Roster;
use App\Models\RosterAdjustment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RosterBalancingService
{
    /**
     * Apply manual balancing untuk work_days
     */
    public function applyBalancing($rosterId, $days, $reason, $effectiveDate = null)
    {
        $roster = Roster::findOrFail($rosterId);
        
        // Validate: days tidak boleh 0
        if ($days == 0) {
            throw new \InvalidArgumentException('Adjustment days cannot be zero');
        }
        
        // Create adjustment record
        $adjustment = RosterAdjustment::create([
            'roster_id' => $rosterId,
            'leave_request_id' => null, // Manual balancing
            'adjustment_type' => $days > 0 ? '+days' : '-days',
            'adjusted_value' => abs($days),
            'reason' => "Manual balancing: {$reason}",
        ]);
        
        // Update roster adjusted_days
        $roster->updateAdjustedDays();
        
        return $adjustment;
    }
    
    /**
     * Apply balancing untuk multiple rosters
     */
    public function applyBulkBalancing(array $rosterIds, $days, $reason, $effectiveDate = null)
    {
        $results = [];
        $errors = [];
        
        foreach ($rosterIds as $rosterId) {
            try {
                $adjustment = $this->applyBalancing($rosterId, $days, $reason, $effectiveDate);
                $results[] = [
                    'roster_id' => $rosterId,
                    'success' => true,
                    'adjustment_id' => $adjustment->id
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'roster_id' => $rosterId,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => count($errors) === 0,
            'results' => $results,
            'errors' => $errors
        ];
    }
    
    /**
     * Estimate next periodic leave date berdasarkan adjusted work_days
     */
    public function estimateNextPeriodicLeave($roster, $fromDate = null)
    {
        $fromDate = $fromDate ? Carbon::parse($fromDate) : now();
        $adjustedWorkDays = $roster->getAdjustedWorkDays();
        $offDays = $roster->getOffDays(); // Tetap 14 hari
        
        // Hitung kapan cycle berikutnya selesai
        $workPeriodEnd = $fromDate->copy()->addDays($adjustedWorkDays);
        
        // Periodic leave mulai setelah work_days selesai
        $periodicLeaveStart = $workPeriodEnd->copy()->addDay();
        $periodicLeaveEnd = $periodicLeaveStart->copy()->addDays($offDays - 1);
        
        return [
            'work_period_start' => $fromDate,
            'work_period_end' => $workPeriodEnd,
            'periodic_leave_start' => $periodicLeaveStart,
            'periodic_leave_end' => $periodicLeaveEnd,
            'adjusted_work_days' => $adjustedWorkDays,
            'off_days' => $offDays,
            'total_cycle_days' => $adjustedWorkDays + $offDays
        ];
    }
    
    /**
     * Get balancing history untuk roster
     */
    public function getHistory($rosterId)
    {
        return RosterAdjustment::where('roster_id', $rosterId)
            ->whereNull('leave_request_id') // Hanya manual balancing
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

---

## Phase 2: Backend - Controller Methods

### 2.1 Update RosterController
**File**: `app/Http/Controllers/RosterController.php`

**Tambah method baru**:

```php
use App\Services\RosterBalancingService;

/**
 * Apply balancing untuk selected rosters
 */
public function applyBalancing(Request $request)
{
    $request->validate([
        'roster_ids' => 'required|array|min:1',
        'roster_ids.*' => 'required|exists:rosters,id',
        'adjustment_days' => 'required|integer|not_in:0',
        'reason' => 'required|string|max:500',
        'effective_date' => 'nullable|date'
    ]);
    
    try {
        $service = app(RosterBalancingService::class);
        $result = $service->applyBulkBalancing(
            $request->roster_ids,
            $request->adjustment_days,
            $request->reason,
            $request->effective_date
        );
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Balancing applied successfully',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Some balancing failed',
                'data' => $result
            ], 422);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Get balancing preview untuk selected rosters
 */
public function getBalancingPreview(Request $request)
{
    $request->validate([
        'roster_ids' => 'required|array|min:1',
        'roster_ids.*' => 'required|exists:rosters,id',
        'adjustment_days' => 'required|integer|not_in:0',
        'effective_date' => 'nullable|date'
    ]);
    
    $service = app(RosterBalancingService::class);
    $previews = [];
    
    foreach ($request->roster_ids as $rosterId) {
        $roster = Roster::with(['employee', 'administration.level'])->find($rosterId);
        if (!$roster) continue;
        
        $currentWorkDays = $roster->getWorkDays();
        $adjustedWorkDays = $currentWorkDays + $request->adjustment_days;
        $estimate = $service->estimateNextPeriodicLeave(
            $roster,
            $request->effective_date ? Carbon::parse($request->effective_date) : now()
        );
        
        $previews[] = [
            'roster_id' => $rosterId,
            'employee_name' => $roster->employee->fullname ?? 'N/A',
            'current_work_days' => $currentWorkDays,
            'adjusted_work_days' => max(0, $adjustedWorkDays),
            'adjustment_days' => $request->adjustment_days,
            'estimate' => $estimate
        ];
    }
    
    return response()->json([
        'success' => true,
        'data' => $previews
    ]);
}

/**
 * Get balancing history untuk roster
 */
public function getBalancingHistory($rosterId)
{
    $roster = Roster::findOrFail($rosterId);
    $service = app(RosterBalancingService::class);
    
    $history = $service->getHistory($rosterId);
    
    return response()->json([
        'success' => true,
        'data' => $history->map(function ($adj) {
            return [
                'id' => $adj->id,
                'adjustment_type' => $adj->adjustment_type,
                'adjusted_value' => $adj->adjusted_value,
                'reason' => $adj->reason,
                'created_at' => $adj->created_at->format('Y-m-d H:i:s'),
                'description' => $adj->getAdjustmentDescription()
            ];
        })
    ]);
}
```

### 2.2 Update Routes
**File**: `routes/web.php`

**Tambah routes baru di dalam `rosters` group**:
```php
Route::prefix('rosters')->name('rosters.')->group(function () {
    // ... existing routes ...
    Route::post('/apply-balancing', [RosterController::class, 'applyBalancing'])->name('apply-balancing');
    Route::post('/balancing-preview', [RosterController::class, 'getBalancingPreview'])->name('balancing-preview');
    Route::get('/{roster}/balancing-history', [RosterController::class, 'getBalancingHistory'])->name('balancing-history');
});
```

---

## Phase 3: Frontend - UI Components

### 3.1 Update roster.index.blade.php
**File**: `resources/views/rosters/index.blade.php`

**A. Tambah checkbox column di table**:

```html
<!-- Di thead, setelah column # -->
<th class="text-center align-middle" style="width: 40px;">
    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" title="Select All">
</th>

<!-- Di tbody, setelah column # -->
<td class="text-center">
    @if($admin->roster)
        <input type="checkbox" 
               class="employee-checkbox" 
               data-roster-id="{{ $admin->roster->id }}"
               data-administration-id="{{ $admin->id }}"
               data-employee-name="{{ $admin->employee->fullname }}"
               onchange="updateBalanceButton()">
    @endif
</td>
```

**B. Tambah button "Balance Leave" di card header**:

```html
<!-- Di card-header, setelah Export button -->
<button class="btn btn-warning btn-sm mr-2" 
        onclick="openBalancingModal()" 
        id="balanceBtn" 
        disabled
        title="Balance Leave for Selected Employees">
    <i class="fas fa-balance-scale mr-1"></i> Balance Leave
</button>
```

**C. Tambah modal balancing** (sebelum `@endsection`):

```html
<!-- Balancing Modal -->
<div class="modal fade" id="balancingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title">
                    <i class="fas fa-balance-scale mr-2"></i>
                    Balance Leave
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="balancingForm">
                <div class="modal-body">
                    <!-- Selected Employees List -->
                    <div class="alert alert-info">
                        <i class="fas fa-users mr-2"></i>
                        <strong id="selectedCount">0</strong> employee(s) selected
                    </div>
                    
                    <div id="selectedEmployeesList" class="mb-3" style="max-height: 150px; overflow-y: auto;">
                        <!-- Will be populated by JavaScript -->
                    </div>
                    
                    <!-- Adjustment Input -->
                    <div class="form-group">
                        <label for="adjustment_days">
                            Adjustment Days <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <select class="form-control" id="adjustment_sign" style="width: 80px;">
                                    <option value="+">+</option>
                                    <option value="-">-</option>
                                </select>
                            </div>
                            <input type="number" 
                                   class="form-control" 
                                   id="adjustment_days" 
                                   name="adjustment_days"
                                   min="1" 
                                   max="365" 
                                   required
                                   onchange="updatePreview()">
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Positive (+) untuk tambah hari kerja, Negative (-) untuk kurangi hari kerja
                        </small>
                    </div>
                    
                    <!-- Effective Date (Optional) -->
                    <div class="form-group">
                        <label for="effective_date">Effective Date (Optional)</label>
                        <input type="date" 
                               class="form-control" 
                               id="effective_date" 
                               name="effective_date"
                               onchange="updatePreview()">
                        <small class="form-text text-muted">
                            Leave empty to apply from today
                        </small>
                    </div>
                    
                    <!-- Reason -->
                    <div class="form-group">
                        <label for="reason">
                            Reason <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="reason" 
                                  name="reason" 
                                  rows="3" 
                                  required
                                  placeholder="Enter reason for balancing..."></textarea>
                    </div>
                    
                    <!-- Preview Section -->
                    <div id="previewSection" class="card bg-light" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-eye mr-2"></i>Preview
                            </h6>
                        </div>
                        <div class="card-body" id="previewContent">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="applyBalancingBtn">
                        <i class="fas fa-check mr-1"></i>Apply Balancing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

**D. Tambah JavaScript functions** (di `@section('scripts')`):

```javascript
// Selected rosters
let selectedRosters = [];

// Toggle select all
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedRosters();
    updateBalanceButton();
}

// Update selected rosters array
function updateSelectedRosters() {
    selectedRosters = [];
    document.querySelectorAll('.employee-checkbox:checked').forEach(checkbox => {
        selectedRosters.push({
            roster_id: checkbox.dataset.rosterId,
            administration_id: checkbox.dataset.administrationId,
            employee_name: checkbox.dataset.employeeName
        });
    });
}

// Update balance button state
function updateBalanceButton() {
    const balanceBtn = document.getElementById('balanceBtn');
    const selectedCount = selectedRosters.length;
    balanceBtn.disabled = selectedCount === 0;
    
    if (selectedCount > 0) {
        balanceBtn.innerHTML = `<i class="fas fa-balance-scale mr-1"></i> Balance Leave (${selectedCount})`;
    } else {
        balanceBtn.innerHTML = `<i class="fas fa-balance-scale mr-1"></i> Balance Leave`;
    }
}

// Open balancing modal
function openBalancingModal() {
    updateSelectedRosters();
    
    if (selectedRosters.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one employee',
            toast: true,
            position: 'top-end'
        });
        return;
    }
    
    // Update selected count
    document.getElementById('selectedCount').textContent = selectedRosters.length;
    
    // Populate selected employees list
    const listContainer = document.getElementById('selectedEmployeesList');
    listContainer.innerHTML = '<ul class="list-group">' +
        selectedRosters.map(roster => 
            `<li class="list-group-item py-2">
                <i class="fas fa-user mr-2"></i>${roster.employee_name}
            </li>`
        ).join('') +
        '</ul>';
    
    // Reset form
    document.getElementById('balancingForm').reset();
    document.getElementById('adjustment_sign').value = '+';
    document.getElementById('previewSection').style.display = 'none';
    
    // Show modal
    $('#balancingModal').modal('show');
}

// Update preview
function updatePreview() {
    const adjustmentDays = parseInt(document.getElementById('adjustment_days').value);
    const adjustmentSign = document.getElementById('adjustment_sign').value;
    const effectiveDate = document.getElementById('effective_date').value;
    
    if (!adjustmentDays || adjustmentDays <= 0) {
        document.getElementById('previewSection').style.display = 'none';
        return;
    }
    
    const finalAdjustment = adjustmentSign === '+' ? adjustmentDays : -adjustmentDays;
    const rosterIds = selectedRosters.map(r => r.roster_id);
    
    // Show loading
    document.getElementById('previewContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading preview...</div>';
    document.getElementById('previewSection').style.display = 'block';
    
    // Fetch preview
    fetch('{{ route('rosters.balancing-preview') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            roster_ids: rosterIds,
            adjustment_days: finalAdjustment,
            effective_date: effectiveDate || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let previewHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
            previewHtml += '<thead><tr><th>Employee</th><th>Current</th><th>Adjusted</th><th>Next Periodic Leave</th></tr></thead>';
            previewHtml += '<tbody>';
            
            data.data.forEach(item => {
                previewHtml += `<tr>
                    <td>${item.employee_name}</td>
                    <td>${item.current_work_days} days</td>
                    <td><strong>${item.adjusted_work_days} days</strong> <span class="badge badge-${item.adjustment_days > 0 ? 'success' : 'danger'}">${item.adjustment_days > 0 ? '+' : ''}${item.adjustment_days}</span></td>
                    <td>${new Date(item.estimate.periodic_leave_start).toLocaleDateString()}</td>
                </tr>`;
            });
            
            previewHtml += '</tbody></table></div>';
            document.getElementById('previewContent').innerHTML = previewHtml;
        }
    })
    .catch(error => {
        console.error('Preview error:', error);
        document.getElementById('previewContent').innerHTML = '<div class="alert alert-danger">Failed to load preview</div>';
    });
}

// Handle form submission
document.getElementById('balancingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const adjustmentDays = parseInt(document.getElementById('adjustment_days').value);
    const adjustmentSign = document.getElementById('adjustment_sign').value;
    const effectiveDate = document.getElementById('effective_date').value;
    const reason = document.getElementById('reason').value;
    
    if (!adjustmentDays || adjustmentDays <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Input',
            text: 'Please enter valid adjustment days',
            toast: true,
            position: 'top-end'
        });
        return;
    }
    
    if (!reason.trim()) {
        Swal.fire({
            icon: 'error',
            title: 'Reason Required',
            text: 'Please enter reason for balancing',
            toast: true,
            position: 'top-end'
        });
        return;
    }
    
    const finalAdjustment = adjustmentSign === '+' ? adjustmentDays : -adjustmentDays;
    const rosterIds = selectedRosters.map(r => r.roster_id);
    
    // Show loading
    const submitBtn = document.getElementById('applyBalancingBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Applying...';
    submitBtn.disabled = true;
    
    // Submit
    fetch('{{ route('rosters.apply-balancing') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            roster_ids: rosterIds,
            adjustment_days: finalAdjustment,
            reason: reason,
            effective_date: effectiveDate || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            }).then(() => {
                $('#balancingModal').modal('hide');
                // Reload page to show updated data
                setTimeout(() => window.location.reload(), 500);
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to apply balancing',
                toast: true,
                position: 'top-end'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while applying balancing',
            toast: true,
            position: 'top-end'
        });
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Update selected rosters when checkbox changes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedRosters();
            updateBalanceButton();
        });
    });
});
```

---

## Phase 4: Display Balancing Info (Optional Enhancement)

### 4.1 Tambah indicator di roster table
**File**: `resources/views/rosters/index.blade.php`

**Tambah badge di employee name column jika ada balancing**:
```php
@if($admin->roster && $admin->roster->getNetAdjustment() != 0)
    <span class="badge badge-warning ml-2" title="Has balancing adjustment">
        <i class="fas fa-balance-scale"></i>
        {{ $admin->roster->getNetAdjustment() > 0 ? '+' : '' }}{{ $admin->roster->getNetAdjustment() }} days
    </span>
@endif
```

### 4.2 Tambah tooltip untuk balancing history
**File**: `resources/views/rosters/index.blade.php`

**Tambah icon di employee row untuk view history** (optional):
```html
@if($admin->roster && $admin->roster->getManualBalancingAdjustments()->count() > 0)
    <button class="btn btn-sm btn-info ml-1" 
            onclick="showBalancingHistory({{ $admin->roster->id }})"
            title="View Balancing History">
        <i class="fas fa-history"></i>
    </button>
@endif
```

---

## Testing Checklist

### Backend Testing
- [ ] Test `applyBalancing()` dengan single roster
- [ ] Test `applyBulkBalancing()` dengan multiple rosters
- [ ] Test validation (zero days, invalid roster_id, etc.)
- [ ] Test `getNetAdjustment()` calculation
- [ ] Test `getAdjustedWorkDays()` dengan berbagai scenario
- [ ] Test `estimateNextPeriodicLeave()` accuracy

### Frontend Testing
- [ ] Test checkbox selection (single, multiple, select all)
- [ ] Test modal opening dengan selected employees
- [ ] Test preview calculation
- [ ] Test form validation
- [ ] Test balancing submission
- [ ] Test error handling
- [ ] Test UI responsiveness

### Integration Testing
- [ ] Test balancing impact ke `roster_daily_status` generation
- [ ] Test periodic leave muncul sesuai adjusted work_days
- [ ] Test history tracking di `roster_adjustments`
- [ ] Test `adjusted_days` update di `rosters` table

---

## Implementation Order

1. **Phase 1**: Backend Service & Model Methods (1-2 hours)
2. **Phase 2**: Controller & Routes (30 minutes)
3. **Phase 3**: Frontend UI Components (2-3 hours)
4. **Phase 4**: Optional Enhancements (30 minutes)
5. **Testing**: Comprehensive testing (1-2 hours)

**Total Estimated Time**: 5-8 hours

---

## Notes

- Tidak perlu migration karena menggunakan table yang sudah ada
- Balancing hanya mempengaruhi work_days, off_days tetap 14 hari
- Periodic leave (status 'C') akan muncul lebih cepat/lambat sesuai adjustment
- History tersimpan di `roster_adjustments` dengan `leave_request_id = NULL`
- UI terintegrasi dengan existing `roster.index` page

