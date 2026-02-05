# Flight Request Form (FRF) - Technical Design

**Module**: Flight Management (GAMMA Section)  
**Date**: 2026-01-21  
**Version**: 1.0

---

## Overview

Flight Request Form (FRF) adalah sistem permintaan tiket penerbangan untuk karyawan ARKA. Module ini terintegrasi dengan Leave Request dan Official Travel Request, dengan approval workflow melalui `approval_plans`.

### Database Structure (6 Tables)

1. **business_partners** - Master data vendor/supplier untuk booking tiket
2. **flight_requests** - Main header form (employee info, status, approval tracking)
3. **flight_request_details** - User input untuk booking request (tanggal, rute, maskapai, waktu)
4. **flight_request_issuances** - Letter of Guarantee/LG (independent) - 1 LG bisa untuk beberapa FR
5. **flight_request_issuance** (pivot) - Menghubungkan FR dengan LG (Many-to-Many)
6. **flight_request_issuance_details** - Detail tiket per LG (booking code, passenger name, ticket price)

### Key Features

1. **Multi-Source Request**:
   - Standalone (mandiri)
   - Based on Leave Request
   - Based on Official Travel Request

2. **Flexible Employee Data**:
   - Bisa menggunakan data dari `employees` table (dengan employee_id)
   - Bisa input manual (nama, NIK) jika employee tidak ada di database
   - Berguna untuk external consultant, vendor, atau new hire yang belum ada di sistem

3. **Two-Phase Process**:
   - **Phase 1 (User)**: Mengajukan FRF dengan detail booking request:
     - Tanggal, rute (departure/arrival city)
     - Maskapai pilihan, waktu penerbangan
     - Data disimpan di: `flight_request_details`
   - **Phase 2 (HCS)**: Issues Letter of Guarantee (LG):
     - Header LG: issued_number, issued_by, issued_at
     - Data disimpan di: `flight_request_issuances`
     - Detail tiket per LG (bisa multiple): booking_code, passenger_name, ticket_price
     - Data disimpan di: `flight_request_issuance_details`

4. **Multi-Ticket Support**:
   - 1 Flight Request bisa punya 1 LG
   - 1 LG bisa punya beberapa tiket (untuk multiple passengers)
   - Contoh: Family trip, team travel

5. **Vendor/Business Partner Integration**:
   - Master data untuk vendor/supplier yang digunakan booking tiket
   - Tracking vendor mana yang digunakan untuk setiap LG
   - Support multiple vendor (Garuda, Lion Air, travel agent, dll)

6. **Letter Number Integration**:
   - Terintegrasi dengan sistem `letter_numbers` (sama seperti Official Travel)
   - HCS bisa assign letter number saat issue LG
   - Letter number bisa reserved dulu, kemudian assigned saat LG di-issue
   - Format: letter_number_id (FK) dan letter_number (string untuk display)

7. **Approval Integration**: Menggunakan `approval_plans` seperti official travel dan leave request

---

## Database Schema

### 1. business_partners (Master Data - Vendor/Supplier)

Table master untuk menyimpan data vendor/supplier yang digunakan untuk booking tiket

```sql
CREATE TABLE business_partners (
    -- Primary Key
    id CHAR(36) PRIMARY KEY,
    
    -- Business Partner Info
    bp_code VARCHAR(50) UNIQUE NOT NULL,  -- Format: BP-2026-001
    bp_name VARCHAR(255) NOT NULL,
    bp_address TEXT NULL,
    bp_phone VARCHAR(20) NULL,
    
    -- Additional Info
    bp_type ENUM('vendor', 'supplier', 'contractor', 'consultant', 'other') DEFAULT 'vendor',
    status ENUM('active', 'inactive') DEFAULT 'active',
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_bp_code (bp_code),
    INDEX idx_bp_name (bp_name),
    INDEX idx_status (status)
);
```

**Example Business Partners:**
- BP-001: Garuda Indonesia Travel Service
- BP-002: Lion Air Ticketing Office
- BP-003: PT Nusantara Tour & Travel

### 2. flight_requests (Main Table - Header)

Main table untuk menyimpan header form Flight Request

```sql
CREATE TABLE flight_requests (
    -- Primary Key
    id CHAR(36) PRIMARY KEY,
    
    -- Form Identity
    form_number VARCHAR(50) UNIQUE NOT NULL,  -- Format: 24FRF-00001
    request_type ENUM('standalone', 'leave_based', 'travel_based') NOT NULL,
    
    -- Employee Information
    employee_id CHAR(36) NULL,  -- Nullable: bisa manual input atau dari employee table
    administration_id BIGINT UNSIGNED NULL,  -- FK ke administrations (snapshot saat FR dibuat)
    employee_name VARCHAR(255) NULL,  -- Nama manual jika tidak ada employee_id
    nik VARCHAR(50) NULL,  -- Bisa dari employee atau input manual
    position VARCHAR(255) NULL,  -- Bisa dari employee atau input manual
    department VARCHAR(255) NULL,  -- String dari administration
    project VARCHAR(255) NULL,  -- String dari administration
    phone_number VARCHAR(20) NULL,
    
    -- Travel Information
    purpose_of_travel TEXT NOT NULL,
    total_travel_days INT,
    
    -- Reference to Source Document
    leave_request_id CHAR(36) NULL,  -- If based on leave
    official_travel_id CHAR(36) NULL,  -- If based on official travel
    
    -- Status & Workflow
    status ENUM(
        'draft',
        'submitted',
        'approved',
        'issued',       -- HCS issued the LG (Letter of Guarantee)
        'completed',    -- Travel completed
        'rejected',
        'cancelled'
    ) DEFAULT 'draft',
    
    -- Manual Approvers (JSON array of user IDs)
    manual_approvers JSON NULL,  -- Same pattern as officialtravels and leave_requests
    
    -- Timestamps & Users
    requested_by BIGINT UNSIGNED NOT NULL,  -- FK to users.id (not UUID)
    requested_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,  -- When all approvers approve
    completed_at TIMESTAMP NULL,
    
    -- Rejection & Cancellation
    rejection_reason TEXT NULL,
    cancellation_reason TEXT NULL,
    cancelled_by BIGINT UNSIGNED NULL,  -- FK to users.id (not UUID)
    cancelled_at TIMESTAMP NULL,
    
    -- Additional Info
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (administration_id) REFERENCES administrations(id) ON DELETE SET NULL,
    -- department & project: No FK constraint (string values from administration)
    FOREIGN KEY (leave_request_id) REFERENCES leave_requests(id) ON DELETE SET NULL,
    FOREIGN KEY (official_travel_id) REFERENCES officialtravels(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_form_number (form_number),
    INDEX idx_employee (employee_id),
    INDEX idx_administration (administration_id),
    INDEX idx_status (status),
    INDEX idx_request_type (request_type),
    INDEX idx_leave_request (leave_request_id),
    INDEX idx_official_travel (official_travel_id),
    INDEX idx_requested_at (requested_at)
);
```

### 3. flight_request_details (User Input - Booking Request)

Table untuk menyimpan detail permintaan flight booking dari user (tanggal, rute, maskapai, waktu).
**Note:** Nama penumpang tidak diinput di sini, tapi di `flight_request_issuance_details` oleh HCS.

```sql
CREATE TABLE flight_request_details (
    -- Primary Key
    id CHAR(36) PRIMARY KEY,
    
    -- Reference
    flight_request_id CHAR(36) NOT NULL,
    
    -- Segment Info
    segment_order INT NOT NULL,  -- 1, 2 (for ordering)
    segment_type ENUM('departure', 'return') NOT NULL,
    
    -- Flight Request Info (User Input)
    flight_date DATE NOT NULL,
    departure_city VARCHAR(100) NOT NULL,
    arrival_city VARCHAR(100) NOT NULL,
    airline VARCHAR(100) NULL,  -- Preferred airline/maskapai
    flight_time TIME NULL,  -- Estimated flight time
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (flight_request_id) REFERENCES flight_requests(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_flight_request (flight_request_id),
    INDEX idx_flight_date (flight_date),
    INDEX idx_segment_order (segment_order)
);
```

### 4. flight_request_issuances (Letter of Guarantee - Independent)

Table untuk menyimpan Letter of Guarantee (LG) yang diissue oleh HCS - **Independent table**, 1 LG bisa untuk beberapa FR

```sql
CREATE TABLE flight_request_issuances (
    -- Primary Key
    id CHAR(36) PRIMARY KEY,
    
    -- Letter numbering integration fields
    letter_number_id BIGINT UNSIGNED NULL,
    letter_number VARCHAR(50) NULL,
    
    -- Issued Information (Letter of Guarantee)
    issued_number VARCHAR(100) UNIQUE NOT NULL,  -- Format: FR002/Arka/LG/I/2026
    issued_date DATE NOT NULL,
    
    -- Vendor/Business Partner
    business_partner_id CHAR(36) NULL,  -- Vendor yang digunakan untuk booking
    
    -- Issued By
    issued_by BIGINT UNSIGNED NOT NULL,  -- HCS Division Manager (FK to users.id)
    issued_at TIMESTAMP NOT NULL,
    
    -- Additional Info
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (letter_number_id) REFERENCES letter_numbers(id) ON DELETE SET NULL,
    FOREIGN KEY (business_partner_id) REFERENCES business_partners(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    -- Indexes
    INDEX idx_letter_number_id (letter_number_id),
    INDEX idx_issued_number (issued_number),
    INDEX idx_issued_date (issued_date),
    INDEX idx_business_partner (business_partner_id)
);
```

### 5. flight_request_issuance (Pivot Table - Many-to-Many)

Pivot table untuk menghubungkan Flight Requests dengan LG Issuances (Many-to-Many relationship)

```sql
CREATE TABLE flight_request_issuance (
    -- Primary Key
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Keys (Many-to-Many)
    flight_request_id CHAR(36) NOT NULL,
    flight_request_issuance_id CHAR(36) NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Key Constraints
    FOREIGN KEY (flight_request_id) REFERENCES flight_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (flight_request_issuance_id) REFERENCES flight_request_issuances(id) ON DELETE CASCADE,
    
    -- Unique Constraint (prevent duplicate)
    UNIQUE KEY fr_issuance_unique (flight_request_id, flight_request_issuance_id),
    
    -- Indexes
    INDEX idx_flight_request (flight_request_id),
    INDEX idx_issuance (flight_request_issuance_id)
);
```

### 6. flight_request_issuance_details (Ticket Details per LG)

Table untuk menyimpan detail tiket per LG - 1 LG bisa punya beberapa tiket yang diissued

```sql
CREATE TABLE flight_request_issuance_details (
    -- Primary Key
    id CHAR(36) PRIMARY KEY,
    
    -- Reference
    flight_request_issuance_id CHAR(36) NOT NULL,
    
    -- Ticket Detail Info
    ticket_order INT NOT NULL,  -- 1, 2, 3, dst (for ordering)
    booking_code VARCHAR(50) NULL,  -- KODE BOOKING: J7G2JI
    detail_reservation TEXT NULL,  -- Detail: "12 JAN 2026 // SUB TRK // 05.10"
    passenger_name VARCHAR(255) NOT NULL,  -- NAMA PENUMPANG: DWI NURTIKTO
    ticket_price DECIMAL(15,2) NULL,  -- TICKET PRICE: 1.200.700
    
    -- Additional Info
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (flight_request_issuance_id) REFERENCES flight_request_issuances(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_issuance_id (flight_request_issuance_id),
    INDEX idx_ticket_order (ticket_order)
);
```

---

## Many-to-Many Relationship Implementation

### Laravel BelongsToMany

```php
// FlightRequest Model
public function issuances()
{
    return $this->belongsToMany(
        FlightRequestIssuance::class,
        'flight_request_issuance',  // Pivot table name
        'flight_request_id',         // FK in pivot for current model
        'flight_request_issuance_id' // FK in pivot for related model
    )->withTimestamps();
}

// FlightRequestIssuance Model
public function flightRequests()
{
    return $this->belongsToMany(
        FlightRequest::class,
        'flight_request_issuance',
        'flight_request_issuance_id',
        'flight_request_id'
    )->withTimestamps();
}
```

### Usage Examples

```php
// Attach LG to FR
$flightRequest->issuances()->attach($issuanceId);

// Detach LG from FR
$flightRequest->issuances()->detach($issuanceId);

// Sync (replace all)
$flightRequest->issuances()->sync([$issuance1, $issuance2]);

// Get all LGs for a FR
$issuances = $flightRequest->issuances;

// Get all FRs for a LG
$flightRequests = $issuance->flightRequests;

// Check if FR has LG
if ($flightRequest->issuances()->exists()) {
    // Has issuances
}
```

---

## Letter Number Integration

Flight Request Issuances terintegrasi dengan sistem Letter Number yang sama seperti Official Travel.

### Letter Number Fields

```php
// In flight_request_issuances table
letter_number_id    // FK to letter_numbers table
letter_number       // String for display (e.g., "0045")
```

### Letter Number Workflow

1. **Reserve Letter Number** (Optional):
   - HCS reserve nomor surat dari sistem letter_numbers
   - Status: 'reserved'

2. **Assign Letter Number saat Issue LG**:
   - Pilih letter number yang sudah di-reserve
   - Atau auto-create new letter number
   - Status berubah: 'reserved' → 'used'

3. **Letter Number Format**:
   - Same as Official Travel
   - Example: 0045, 0046, 0047

### Integration with LetterNumber Model

```php
// In FlightRequestIssuance model
public function letterNumber()
{
    return $this->belongsTo(LetterNumber::class, 'letter_number_id');
}

// Assign letter number
public function assignLetterNumber($letterNumberId)
{
    $letterNumber = LetterNumber::find($letterNumberId);
    
    if ($letterNumber && $letterNumber->status === 'reserved') {
        $this->letter_number_id = $letterNumberId;
        $this->letter_number = $letterNumber->letter_number;
        $this->save();
        
        // Mark letter number as used
        $letterNumber->markAsUsed('flight_request_issuance', $this->id);
    }
}
```

---

## Database Relationship Structure

```
letter_numbers (Master Data - Letter Numbering System)
    └── hasMany: flight_request_issuances (N)

business_partners (Master Data - Vendor/Supplier)
    └── hasMany: flight_request_issuances (N)

flight_requests (N) ←──────┐
├── hasMany: flight_request_details (N)
│   └── Example: 2 segments (departure + return)
│                              │
└── belongsToMany ─────────────┤ Many-to-Many
    (pivot: flight_request_issuance)
                               │
flight_request_issuances (N) ──┘
├── belongsTo: letter_numbers (1) -- Letter Number Integration
├── belongsTo: business_partners (1) -- Vendor untuk booking
└── hasMany: flight_request_issuance_details (N)
    └── Example: 3 tickets untuk 3 penumpang berbeda
```

**Relationship Explanation:**
- 1 Flight Request → many LG Issuances (Many-to-Many)
- 1 LG Issuance → many Flight Requests (Many-to-Many)
- Pivot Table: `flight_request_issuance` menghubungkan keduanya

### Example Use Case: Group Travel (Many-to-Many)

**Scenario**: 3 karyawan berbeda (3 FR) booking tiket dengan 1 LG yang sama untuk efisiensi

**Flight Request #1:**
- Employee: John Doe
- Purpose: Project meeting

**Flight Request #2:**
- Employee: Jane Smith  
- Purpose: Project meeting

**Flight Request #3:**
- Employee: Bob Johnson
- Purpose: Project meeting

**Flight Request Details (masing-masing FR):**
1. Departure: Jakarta → Bali (12 Jan 2026)
2. Return: Bali → Jakarta (20 Jan 2026)

**LG Issuance (1 LG untuk 3 FR):**
- Letter Number: 0045 (from letter_numbers system)
- Issued Number: FR002/Arka/LG/I/2026
- Business Partner: BP-001 (Garuda Indonesia Travel Service)
- Issued By: HCS Manager
- Issued Date: 15 Jan 2026

**LG Issuance Details (Tickets):**
1. Ticket #1: John Doe - Rp 1.200.000 - Booking: J7G2JI
2. Ticket #2: Jane Smith - Rp 1.200.000 - Booking: J7G2JI  
3. Ticket #3: Bob Johnson - Rp 1.200.000 - Booking: J7G2JI

**Pivot Records:**
```
flight_request_issuance:
- FR#1 ↔ LG#1
- FR#2 ↔ LG#1
- FR#3 ↔ LG#1
```

**Result**: 3 Flight Requests share 1 LG dengan 3 tickets

---

## Employee Data Input Methods

Flight Request mendukung 2 metode input data karyawan:

### Method 1: Based on Employee (employee_id)
Untuk karyawan yang sudah terdaftar di database:
```php
flight_request {
    employee_id: "uuid-123",
    employee_name: null,  // Auto-filled from employees table
    nik: null,            // Auto-filled from employees table
    position: null,       // Auto-filled from employees table
    department_id: "uuid-dept",
    ...
}
```

### Method 2: Manual Input
Untuk external consultant, vendor, atau new hire yang belum ada di sistem:
```php
flight_request {
    employee_id: null,
    employee_name: "John Doe (External Consultant)",
    nik: "EXT-2026-001",
    position: "IT Consultant",
    department_id: "uuid-dept",  // Optional
    ...
}
```

**Use Cases Manual Input**:
- External consultant / vendor
- New employee (belum ada di database)
- Guest / partner dari client
- Temporary contractor

---

## Workflow & Status Flow

### Status Workflow

```
draft
  ↓ (submit)
submitted
  ↓ (approve by approvers via manual_approvers)
approved
  ↓ (HCS issues LG - Letter of Guarantee)
issued
  ↓ (travel completed)
completed

# Alternative Flows:
- Any status → rejected (by approver)
- Any status → cancelled (by user/admin)
```

### Approval Integration

Flight Request menggunakan sistem approval yang sama dengan `officialtravels` dan `leave_requests`:

1. **Manual Approvers**: JSON array berisi user IDs yang akan menjadi approvers
   ```json
   ["user-uuid-1", "user-uuid-2", "user-uuid-3"]
   ```

2. **Approval Plans**: Terintegrasi dengan table `approval_plans` untuk tracking workflow
   - `document_type`: 'flight_request'
   - `document_id`: flight_request.id
   - Status tracking melalui `approval_stage_details`

3. **Approval Flow**:
   - User creates FRF + fills `flight_request_details` → status: 'draft'
   - User submit → status: 'submitted'
   - System creates approval plan dengan manual_approvers
   - Approvers approve sequentially → status: 'approved' (semua approver sudah approve)
   - HCS creates `flight_request_issuances` (LG Header) → status: 'issued'
   - HCS adds `flight_request_issuance_details` (Ticket details, bisa multiple)
   - Travel completed → status: 'completed'

**Example Approval Plan Creation**:
```php
// When submitted
$approvalPlan = ApprovalPlan::create([
    'document_type' => 'flight_request',
    'document_id' => $flightRequest->id,
    'project_id' => $flightRequest->project_id,
    'status' => 'pending'
]);

// Create approval stages from manual_approvers
if ($flightRequest->manual_approvers) {
    $approvers = json_decode($flightRequest->manual_approvers);
    foreach ($approvers as $index => $approverId) {
        ApprovalStageDetail::create([
            'approval_plan_id' => $approvalPlan->id,
            'stage_name' => 'Approval ' . ($index + 1),
            'approver_id' => $approverId,
            'approval_order' => $index + 1,
            'status' => $index == 0 ? 'pending' : 'waiting'
        ]);
    }
}
```

---

## Integration with Leave Request & Official Travel

### Add to LeaveRequest Model

```php
public function flightRequests()
{
    return $this->hasMany(FlightRequest::class, 'leave_request_id');
}
```

### Add to Officialtravel Model

```php
public function flightRequests()
{
    return $this->hasMany(FlightRequest::class, 'official_travel_id');
}
```

### Add Button in Leave Request Detail View

Add button to create flight request from approved leave request:

```blade
{{-- In resources/views/leave-requests/show.blade.php --}}
@if($leaveRequest->status == 'approved')
<a href="{{ route('flight-requests.create-from-leave', $leaveRequest->id) }}" 
   class="btn btn-primary">
    <i class="fas fa-plane"></i> Request Flight Ticket
</a>
@endif
```

---

## Sidebar Integration (GAMMA SECTION)

Add to `resources/views/layouts/partials/sidebar.blade.php`:

```blade
{{-- GAMMA SECTION - Flight Management --}}
<li class="nav-header">GAMMA</li>

<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-plane"></i>
        <p>
            Flight Management
            <i class="fas fa-angle-left right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('flight-requests.index') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Flight Requests</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('flight-requests.create') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>New Request</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('flight-requests.my-requests') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>My Flight Requests</p>
            </a>
        </li>
    </ul>
</li>
```

---

## Next Steps

1. ✅ **Design Complete** - This document
2. ✅ **Create Migrations** - 6 tables (Many-to-Many structure)
   - `business_partners` - Master data vendor/supplier
   - `flight_requests` - Main header form
   - `flight_request_details` - User input (booking request: tanggal, rute, maskapai, waktu)
   - `flight_request_issuances` - LG (independent): issued_number, vendor, issued_by, issued_at
   - `flight_request_issuance` (pivot) - Menghubungkan FR dengan LG (Many-to-Many)
   - `flight_request_issuance_details` - Ticket details per LG (booking_code, passenger_name, ticket_price)
3. ⏳ **Run Migrations** - Execute `php artisan migrate`
4. ⏳ **Create Models** - 5 models with relationships (+ pivot)
   - BusinessPartner (hasMany issuances)
   - FlightRequest (hasMany details, **belongsToMany** issuances via pivot)
   - FlightRequestDetail (belongsTo request)
   - FlightRequestIssuance (**belongsToMany** flightRequests via pivot, belongsTo letterNumber, belongsTo businessPartner, hasMany issuanceDetails)
   - FlightRequestIssuanceDetail (belongsTo issuance)
   - Pivot: `flight_request_issuance` (auto-handled by Laravel)
   - Note: LetterNumber model already exists from Official Travel system
5. ⏳ **Create Controllers** - Main + Detail + Issuance + BP controllers
6. ⏳ **Create Views** - Index, Create, Edit, Show
7. ⏳ **Add Routes** - Web + API routes
8. ⏳ **Update Sidebar** - Add GAMMA section
9. ⏳ **Testing** - All workflows

---

**Status**: Migrations Complete - Many-to-Many Structure  
**Next**: Run migrations, then create models  
**Structure**:
- ✅ 6 tables dengan **Many-to-Many relationship**
- ✅ `business_partners` - Master data vendor/supplier untuk booking tiket
- ✅ `flight_requests` - Header only (no issued info)
  - `employee_id` nullable - support manual input untuk non-employee
  - `employee_name` - untuk input manual nama jika tidak ada employee_id
- ✅ `flight_request_details` - User booking request (Phase 1)
- ✅ `flight_request_issuances` - LG (independent table)
  - `letter_number_id` - integration dengan letter numbering system
  - `business_partner_id` - reference ke vendor yang digunakan
  - **No direct FK to flight_requests** - uses pivot table instead
- ✅ `flight_request_issuance` (pivot) - **Many-to-Many relationship**
  - 1 FR bisa punya beberapa LG
  - 1 LG bisa untuk beberapa FR
- ✅ `flight_request_issuance_details` - Ticket details (Phase 2) - Support multiple tickets per LG
- ✅ Uses same approval pattern as officialtravels & leave_requests
