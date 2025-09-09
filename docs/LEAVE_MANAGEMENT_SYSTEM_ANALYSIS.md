# Analisis Komprehensif Sistem Manajemen Cuti

## 1. Analisis Form yang Ada

### 1.1 Form Izin Meninggalkan Pekerjaan (Cuti Tahunan)

**Kode Form:** ARKA/HCS/IV/04.05, Rev.2

**Data yang Diperlukan:**

-   **Informasi Karyawan:** Nama, NIK, Jabatan
-   **Jenis Cuti:**
    -   Cuti Tahunan/R.R/Annual Leave
    -   Izin dengan Upah (12 jenis dengan durasi berbeda)
    -   Izin tanpa Upah
-   **Periode Cuti:** Tanggal mulai, tanggal kembali
-   **Perhitungan Hak Cuti:** Entitlements, taken, available, remaining
-   **Transportasi:** Biaya transport, tiket
-   **Persetujuan:** Employee, Supervisor/Dept Head, Administration

### 1.2 Form Cuti Panjang (Long Service Leave)

**Kode Form:** ARKA/HCS/IV/04.06, Rev.2

**Data yang Diperlukan:**

-   **Informasi Karyawan:** Nama, DOH, POH, NIK, Jabatan, Lokasi Kerja
-   **Periode Cuti:** First day, last day, back to work
-   **Perhitungan Cuti Panjang:** Eligibility, accumulated, entitlement, balance
-   **Persetujuan:** Employee, Supervisor/Superintendent, Management
-   **Distribusi:** HCS Head Office, Site Administration, Accounting, Employee

## 2. Analisis Prosedur Cuti (10 Poin)

### 2.1 Mapping Prosedur ke Kebutuhan Database

| Prosedur | Kebutuhan Database                             | Keterangan                          |
| -------- | ---------------------------------------------- | ----------------------------------- |
| 5.3.1    | `leave_requests.operational_impact`            | Tidak mengganggu operasional        |
| 5.3.2    | `leave_requests.department_approval`           | Persetujuan divisi/departemen       |
| 5.3.3    | `handover_letters` table                       | Surat pengalihan tugas untuk staff+ |
| 5.3.4    | `leave_entitlements.periodic_duration`         | Durasi cuti periodik                |
| 5.3.5    | `leave_requests.is_partial`                    | Cuti tidak dapat parsial            |
| 5.3.6    | `handover_letters` table                       | Hand over untuk >3 hari             |
| 5.3.7    | `leave_requests.hcs_processing`                | Administrasi HCS                    |
| 5.3.8    | `leave_lumpsums` table                         | Lumpsum cuti                        |
| 5.3.9    | `leave_transportation.personal_responsibility` | Tanggung jawab tiket                |
| 5.3.10   | `leave_requests.emergency_leave`               | Cuti emergency 7 hari               |

## 3. Struktur Tabel yang Diperlukan

### 3.1 Tabel Utama: `leave_requests`

```sql
CREATE TABLE leave_requests (
    id CHAR(36) PRIMARY KEY,
    letter_number_id BIGINT UNSIGNED NULL,
    letter_number VARCHAR(50) NULL,
    leave_number VARCHAR(255) NOT NULL,
    leave_date DATE NOT NULL,

    -- Employee Information
    employee_id CHAR(36) NOT NULL,
    administration_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,

    -- Leave Type and Details
    leave_type ENUM('annual', 'paid', 'unpaid', 'long_service', 'emergency') NOT NULL,
    leave_subtype VARCHAR(50) NULL, -- untuk paid leave (2.01, 2.02, dll)
    leave_reason TEXT NULL,

    -- Leave Period
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    return_date DATE NOT NULL,
    total_days INT NOT NULL,

    -- Leave Calculation
    entitled_days INT NULL,
    taken_days INT NULL,
    available_days INT NULL,
    requested_days INT NOT NULL,
    remaining_days INT NULL,
    leave_period VARCHAR(50) NULL,

    -- Special Cases
    is_partial BOOLEAN DEFAULT FALSE,
    is_emergency BOOLEAN DEFAULT FALSE,
    requires_handover BOOLEAN DEFAULT FALSE,
    operational_impact TEXT NULL,

    -- Transportation
    transport_cost DECIMAL(15,2) NULL,
    ticket_info VARCHAR(255) NULL,
    transportation_id BIGINT UNSIGNED NULL,

    -- Status and Workflow
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'cancelled', 'taken', 'completed') DEFAULT 'draft',

    -- Timestamps
    submitted_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    taken_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,

    -- Audit
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Foreign Keys
    FOREIGN KEY (letter_number_id) REFERENCES letter_numbers(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (administration_id) REFERENCES administrations(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (transportation_id) REFERENCES transportations(id),
    FOREIGN KEY (created_by) REFERENCES users(id),

    -- Indexes
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_leave_type (leave_type),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_letter_number (letter_number_id)
);
```

### 3.2 Tabel: `leave_entitlements`

```sql
CREATE TABLE leave_entitlements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id CHAR(36) NOT NULL,
    administration_id BIGINT UNSIGNED NULL,

    -- Annual Leave
    annual_entitlement INT DEFAULT 0,
    annual_taken INT DEFAULT 0,
    annual_remaining INT DEFAULT 0,

    -- Long Service Leave
    lsl_eligibility INT DEFAULT 0,
    lsl_accumulated INT DEFAULT 0,
    lsl_entitlement INT DEFAULT 0,
    lsl_balance INT DEFAULT 0,

    -- Paid Leave (by type)
    paid_marriage INT DEFAULT 0,
    paid_circumcision INT DEFAULT 0,
    paid_baptism INT DEFAULT 0,
    paid_child_marriage INT DEFAULT 0,
    paid_family_death INT DEFAULT 0,
    paid_birth INT DEFAULT 0,
    paid_household_death INT DEFAULT 0,
    paid_sick INT DEFAULT 0,
    paid_period INT DEFAULT 0,
    paid_maternity INT DEFAULT 0,
    paid_miscarriage INT DEFAULT 0,
    paid_government_duty INT DEFAULT 0,
    paid_pilgrimage INT DEFAULT 0,

    -- Leave Period
    leave_period VARCHAR(50) NULL,
    period_start DATE NULL,
    period_end DATE NULL,

    -- System Fields
    is_active BOOLEAN DEFAULT TRUE,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (administration_id) REFERENCES administrations(id),
    FOREIGN KEY (user_id) REFERENCES users(id),

    UNIQUE KEY unique_employee_period (employee_id, leave_period),
    INDEX idx_employee (employee_id),
    INDEX idx_period (leave_period)
);
```

### 3.3 Tabel: `handover_letters`

```sql
CREATE TABLE handover_letters (
    id CHAR(36) PRIMARY KEY,
    letter_number_id BIGINT UNSIGNED NULL,
    letter_number VARCHAR(50) NULL,
    handover_number VARCHAR(255) NOT NULL,
    handover_date DATE NOT NULL,

    -- Related Leave Request
    leave_request_id CHAR(36) NOT NULL,

    -- Employee Information
    employee_id CHAR(36) NOT NULL,
    administration_id BIGINT UNSIGNED NULL,

    -- Handover Details
    handover_reason TEXT NOT NULL,
    handover_duration VARCHAR(255) NOT NULL,
    handover_start DATE NOT NULL,
    handover_end DATE NOT NULL,

    -- Work Assignment
    assigned_to_id CHAR(36) NULL, -- employee yang ditugaskan
    work_description TEXT NULL,
    special_instructions TEXT NULL,

    -- Status
    status ENUM('draft', 'submitted', 'approved', 'active', 'completed') DEFAULT 'draft',

    -- Timestamps
    submitted_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    activated_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,

    -- Audit
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (letter_number_id) REFERENCES letter_numbers(id),
    FOREIGN KEY (leave_request_id) REFERENCES leave_requests(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (administration_id) REFERENCES administrations(id),
    FOREIGN KEY (assigned_to_id) REFERENCES employees(id),
    FOREIGN KEY (created_by) REFERENCES users(id),

    INDEX idx_leave_request (leave_request_id),
    INDEX idx_employee (employee_id),
    INDEX idx_status (status)
);
```

### 3.4 Tabel: `leave_lumpsums`

```sql
CREATE TABLE leave_lumpsums (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leave_request_id CHAR(36) NOT NULL,

    -- Lumpsum Details
    lumpsum_amount DECIMAL(15,2) NOT NULL,
    lumpsum_type ENUM('standard', 'emergency', 'special') DEFAULT 'standard',
    lumpsum_period VARCHAR(50) NULL,

    -- Payment Information
    payment_status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
    payment_date DATE NULL,
    payment_method VARCHAR(100) NULL,
    payment_reference VARCHAR(255) NULL,

    -- HCS Processing
    hcs_processed_by BIGINT UNSIGNED NULL,
    hcs_processed_at TIMESTAMP NULL,
    hcs_notes TEXT NULL,

    -- Audit
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (leave_request_id) REFERENCES leave_requests(id),
    FOREIGN KEY (hcs_processed_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),

    INDEX idx_leave_request (leave_request_id),
    INDEX idx_payment_status (payment_status)
);
```

### 3.5 Tabel: `leave_transportation`

```sql
CREATE TABLE leave_transportation (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leave_request_id CHAR(36) NOT NULL,

    -- Transportation Details
    transport_type ENUM('plane', 'train', 'bus', 'car', 'other') NOT NULL,
    transport_class VARCHAR(50) NULL, -- economy, business, first
    departure_location VARCHAR(255) NOT NULL,
    arrival_location VARCHAR(255) NOT NULL,

    -- Ticket Information
    ticket_number VARCHAR(255) NULL,
    ticket_cost DECIMAL(15,2) NULL,
    ticket_status ENUM('pending', 'booked', 'issued', 'used', 'cancelled') DEFAULT 'pending',

    -- Schedule
    departure_date DATE NOT NULL,
    departure_time TIME NULL,
    arrival_date DATE NOT NULL,
    arrival_time TIME NULL,

    -- Personal Responsibility (5.3.9)
    is_personal_responsibility BOOLEAN DEFAULT FALSE,
    personal_responsibility_reason TEXT NULL,
    replacement_ticket_cost DECIMAL(15,2) NULL,

    -- Audit
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (leave_request_id) REFERENCES leave_requests(id),
    FOREIGN KEY (created_by) REFERENCES users(id),

    INDEX idx_leave_request (leave_request_id),
    INDEX idx_ticket_status (ticket_status)
);
```

### 3.6 Tabel: `leave_attachments`

```sql
CREATE TABLE leave_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leave_request_id CHAR(36) NOT NULL,

    -- Attachment Details
    attachment_type ENUM('medical_certificate', 'government_document', 'other') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NULL,
    mime_type VARCHAR(100) NULL,

    -- Description
    description TEXT NULL,

    -- Audit
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (leave_request_id) REFERENCES leave_requests(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id),

    INDEX idx_leave_request (leave_request_id),
    INDEX idx_attachment_type (attachment_type)
);
```

## 4. Integrasi dengan Sistem yang Ada

### 4.1 Letter Numbering System

-   **Kategori Baru:** "Cuti Tahunan", "Cuti Panjang", "Hand Over"
-   **Integration:** `leave_requests.letter_number_id` → `letter_numbers.id`
-   **Numbering:** ARKA/HCS/IV/04.05 (Annual), ARKA/HCS/IV/04.06 (Long Service)

### 4.2 Approval System

-   **Integration:** `approval_plans` dengan `document_type = 'leave_request'`
-   **Workflow:** Employee → Supervisor → Dept Head → HCS → Management
-   **Status Mapping:** draft → submitted → approved → taken → completed

### 4.3 Employee Administration

-   **Integration:** `leave_requests.administration_id` → `administrations.id`
-   **Data Source:** Employee info, project, position, grade, level

## 5. Business Rules Implementation

### 5.1 Leave Type Rules

```php
// Annual Leave Rules
- Tidak dapat parsial (5.3.5)
- >3 hari perlu handover (5.3.6)
- Berdasarkan hak cuti tahunan

// Paid Leave Rules
- 2.01 Marriage: 3 days
- 2.02 Circumcision: 2 days
- 2.03 Baptism: 2 days
- 2.04 Child Marriage: 2 days
- 2.05 Family Death: 2 days
- 2.06 Birth: 2 days
- 2.07 Household Death: 1 day
- 2.08 Sick: Doctor recommendation
- 2.09 Period: Consult administration
- 2.10 Maternity: Consult administration
- 2.11 Miscarriage: Consult administration
- 2.12 Government Duty: Show document
- 2.13 Pilgrimage: Consult administration

// Emergency Leave Rules (5.3.10)
- 7 days maximum
- Deducted from next period
- No lumpsum, no flight ticket
- Site project only
```

### 5.2 Approval Workflow

```php
// Standard Workflow
1. Employee submits request
2. Supervisor/Dept Head recommendation
3. HCS processing (lumpsum, transportation)
4. Management approval
5. Leave execution
6. Return confirmation

// Emergency Workflow
1. Employee submits emergency request
2. Immediate supervisor approval
3. HCS notification
4. Leave execution
5. Post-approval documentation
```

## 6. Data Validation Rules

### 6.1 Leave Request Validation

-   Start date tidak boleh di masa lalu
-   End date harus setelah start date
-   Total days tidak boleh melebihi available days
-   Emergency leave maksimal 7 hari
-   Staff+ level perlu handover untuk >3 hari

### 6.2 Entitlement Validation

-   Annual leave tidak boleh negatif
-   Paid leave sesuai dengan jenis dan limit
-   Long service leave berdasarkan masa kerja
-   Leave period harus valid

## 7. Reporting Requirements

### 7.1 Leave Reports

-   Leave balance per employee
-   Leave utilization by department
-   Leave trends by period
-   Emergency leave statistics
-   Handover compliance report

### 7.2 Administrative Reports

-   Lumpsum payment report
-   Transportation cost report
-   Leave approval timeline
-   Department leave capacity

## 8. Integration Points

### 8.1 Existing Systems

-   **Employee Management:** `employees`, `administrations`
-   **Project Management:** `projects`, `user_project`
-   **Letter System:** `letter_numbers`, `letter_categories`
-   **Approval System:** `approval_plans`, `approval_stages`
-   **Transportation:** `transportations`

### 8.2 External Systems

-   **Payroll System:** Lumpsum integration
-   **Travel Management:** Ticket booking
-   **HR Analytics:** Leave statistics

## 9. Security Considerations

### 9.1 Data Access

-   Employee hanya bisa akses cuti sendiri
-   Supervisor bisa akses cuti tim
-   HCS bisa akses semua cuti
-   Management bisa akses semua cuti

### 9.2 Audit Trail

-   Semua perubahan dicatat
-   Approval history tersimpan
-   File attachment tracking
-   System log untuk security

## 10. Performance Considerations

### 10.1 Database Optimization

-   Index pada foreign keys
-   Index pada date ranges
-   Index pada status fields
-   Partitioning untuk data besar

### 10.2 Caching Strategy

-   Leave entitlements cache
-   Employee data cache
-   Approval workflow cache
-   Report data cache

## 11. Migration Strategy

### 11.1 Data Migration

-   Existing leave data (jika ada)
-   Employee entitlement calculation
-   Historical leave records
-   Approval workflow setup

### 11.2 System Integration

-   Letter numbering setup
-   Approval workflow configuration
-   User permission setup
-   Report template creation

## 12. Testing Strategy

### 12.1 Unit Testing

-   Leave calculation logic
-   Validation rules
-   Business rules
-   Data integrity

### 12.2 Integration Testing

-   Approval workflow
-   Letter numbering
-   Employee data integration
-   Report generation

### 12.3 User Acceptance Testing

-   Form submission
-   Approval process
-   Report accuracy
-   System performance

---

**Catatan:** Analisis ini memberikan foundation yang komprehensif untuk implementasi sistem manajemen cuti yang terintegrasi dengan sistem yang sudah ada, mengikuti prosedur perusahaan dan memenuhi kebutuhan operasional.
