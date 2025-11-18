# Analisis Komprehensif: Akses dan Batasan untuk User Non-HR di ARKA HERO

**Tanggal:** 2025-01-XX  
**Tujuan:** Menganalisa fitur-fitur yang dapat diakses oleh user di luar tim HR dan batasan-batasan yang perlu diterapkan

---

## 1. EXECUTIVE SUMMARY

Aplikasi ARKA HERO saat ini dirancang khusus untuk tim HR dengan akses penuh ke semua modul. Untuk memperluas penggunaan ke user di luar HR, perlu dilakukan analisa mendalam tentang:

1. **Fitur-fitur yang relevan untuk non-HR users**
2. **Batasan akses yang perlu diterapkan**
3. **Self-service capabilities yang dapat diberikan**
4. **Approval workflows yang melibatkan non-HR users**

---

## 2. SISTEM AUTENTIKASI & AUTHORIZATION SAAT INI

### 2.1 Role-Based Access Control (RBAC)

Aplikasi menggunakan **Spatie Permission** dengan struktur:

**Role yang Sudah Ada:**

-   `administrator` - Full system access
-   `hr-staff-000h` - Limited HR access
-   `hr-supervisor` - HR supervisor access
-   `hr-manager` - HR management access
-   `project-manager` - View employees, view official travels, recommend official travels
-   `div-manager` - View employees, view official travels, recommend official travels

**Permission Categories:**

-   Dashboard permissions
-   User management permissions
-   Role & permission management
-   Employee management permissions
-   Official travel permissions
-   Letter number permissions
-   Master data permissions

### 2.2 Middleware Protection

-   `auth` - Authentication required
-   `admin_auth` - Admin level check
-   `permission` - Permission-based access
-   `role` - Role-based access
-   `role_or_permission` - Role or permission access

---

## 3. FITUR-FITUR YANG RELEVAN UNTUK NON-HR USERS

### 3.1 Self-Service Features (Employee Self-Service)

#### A. Leave Management - Self-Service

**Akses yang Dapat Diberikan:**

1. **View Own Leave Requests**

    - Melihat daftar leave request milik sendiri
    - Filter berdasarkan status (pending, approved, rejected, closed)
    - View detail leave request (dates, type, status, approval flow)

2. **Create Leave Request**

    - Submit leave request untuk diri sendiri
    - Pilih leave type berdasarkan entitlement
    - Input dates, reason (jika diperlukan)
    - Upload supporting documents (jika diperlukan)
    - View leave balance sebelum submit

3. **Edit Own Leave Request** (Hanya untuk status draft/pending)

    - Edit leave request yang belum diapprove
    - Cancel leave request yang masih pending

4. **View Own Leave Entitlements**
    - Melihat sisa cuti yang tersedia
    - View history penggunaan cuti
    - View entitlement periods

**Batasan:**

-   ❌ Tidak dapat melihat leave requests karyawan lain
-   ❌ Tidak dapat approve/reject leave requests
-   ❌ Tidak dapat manage leave entitlements
-   ❌ Tidak dapat generate bulk leave requests
-   ❌ Tidak dapat access leave reports (kecuali laporan pribadi)
-   ❌ Tidak dapat close leave requests (hanya HR)

**Data Scope:**

-   Hanya dapat mengakses data dengan `employee_id` yang terkait dengan user account
-   User harus memiliki relasi `employee_id` di tabel `users`

#### B. Official Travel - Self-Service

**Akses yang Dapat Diberikan:**

1. **View Own Official Travels**

    - Melihat daftar official travel milik sendiri
    - Filter berdasarkan status (draft, submitted, approved, rejected, closed)
    - View detail official travel (destination, dates, purpose, approval status)

2. **Create Official Travel Request**

    - Submit official travel request untuk diri sendiri
    - Input destination, purpose, dates
    - Add followers (jika diperlukan)
    - Select transportation & accommodation
    - View approval workflow

3. **Edit Own Official Travel** (Hanya untuk status draft)

    - Edit official travel yang masih draft
    - Cancel official travel yang masih pending

4. **Track Official Travel Status**
    - Melihat status approval
    - Melihat arrival/departure stamps (jika sudah approved)
    - Download official travel letter (jika sudah approved)

**Batasan:**

-   ❌ Tidak dapat melihat official travels karyawan lain
-   ❌ Tidak dapat approve/reject official travels
-   ❌ Tidak dapat stamp arrival/departure (hanya HR)
-   ❌ Tidak dapat close official travels
-   ❌ Tidak dapat access official travel reports
-   ❌ Tidak dapat manage transportation/accommodation master data

**Data Scope:**

-   Hanya dapat mengakses data dengan `traveler_id` yang terkait dengan user account
-   User harus memiliki relasi `administration_id` di tabel `users`

#### C. Profile Management - Self-Service

**Akses yang Dapat Diberikan:**

1. **View Own Employee Profile**

    - Melihat data pribadi (personal details)
    - Melihat data administrasi (position, department, project, DOH)
    - Melihat data bank accounts
    - Melihat data tax identification
    - Melihat data licenses
    - Melihat data insurances
    - Melihat data families
    - Melihat data educations
    - Melihat data courses
    - Melihat data job experiences
    - Melihat data emergency contacts

2. **Update Own Profile** (Limited fields)
    - Update emergency contacts
    - Update bank accounts (dengan approval HR)
    - Update tax identification (dengan approval HR)
    - Update personal contact information (phone, email, address)

**Batasan:**

-   ❌ Tidak dapat mengubah data administrasi (position, department, project, DOH)
-   ❌ Tidak dapat mengubah data personal yang sensitif (NIK, fullname, DOB)
-   ❌ Tidak dapat mengubah data licenses, insurances (perlu approval HR)
-   ❌ Tidak dapat melihat profile karyawan lain
-   ❌ Tidak dapat access employee management features

#### D. Change Password

**Akses yang Dapat Diberikan:**

-   ✅ Change own password
-   ✅ Update password requirements

**Batasan:**

-   ❌ Tidak dapat reset password user lain

---

### 3.2 Approval Features (Untuk Manager/Supervisor)

#### A. Approval Requests Dashboard

**Akses yang Dapat Diberikan:**

1. **View Pending Approvals**

    - Melihat daftar approval requests yang menunggu approval
    - Filter berdasarkan document type (official travel, leave request, recruitment request)
    - View detail document yang perlu diapprove

2. **Process Approvals**
    - Approve/reject leave requests (jika sebagai approver)
    - Approve/reject official travels (jika sebagai approver)
    - Approve/reject recruitment requests (jika sebagai approver)
    - Add remarks pada approval

**Batasan:**

-   ❌ Hanya dapat approve/reject jika user terdaftar sebagai approver di approval stages
-   ❌ Tidak dapat melihat approval requests yang bukan tanggung jawabnya
-   ❌ Tidak dapat manage approval stages configuration
-   ❌ Tidak dapat bypass approval workflow

**Data Scope:**

-   Hanya dapat melihat approval plans dengan `approver_id` = current user id
-   Approval order harus sequential (harus approve order 1 dulu sebelum order 2)

#### B. Recommendation Features (Untuk Project Manager/Division Manager)

**Akses yang Dapat Diberikan:**

1. **Recommend Official Travels**
    - Recommend official travel requests dari tim/department
    - View official travels yang perlu direcommend

**Batasan:**

-   ❌ Tidak dapat approve official travels (hanya recommend)
-   ❌ Tidak dapat manage recommendation workflow

---

### 3.3 View-Only Features (Untuk Manager/Supervisor)

#### A. Employee Directory (Limited Access)

**Akses yang Dapat Diberikan:**

1. **View Employee List** (Filtered by scope)
    - Melihat daftar karyawan dalam scope authority (department/project)
    - View basic employee information (name, NIK, position, department)
    - Search employees

**Batasan:**

-   ❌ Tidak dapat melihat detail lengkap karyawan (personal data, salary, etc.)
-   ❌ Tidak dapat edit/delete employee data
-   ❌ Tidak dapat create new employees
-   ❌ Tidak dapat access employee reports
-   ❌ Scope terbatas berdasarkan department/project assignment

**Data Scope:**

-   Hanya dapat melihat employees dalam department/project yang sama
-   Tidak dapat melihat employees dari department/project lain

#### B. Dashboard - Limited View

**Akses yang Dapat Diberikan:**

1. **Personal Dashboard**

    - View own leave requests summary
    - View own official travels summary
    - View pending approvals count
    - View own profile summary

2. **Team Dashboard** (Jika sebagai manager)
    - View team leave requests (dalam scope)
    - View team official travels (dalam scope)
    - View team statistics (limited)

**Batasan:**

-   ❌ Tidak dapat melihat HR dashboard (employee dashboard, recruitment dashboard, etc.)
-   ❌ Tidak dapat melihat master data statistics
-   ❌ Tidak dapat access full analytics

---

## 4. FITUR-FITUR YANG TIDAK DAPAT DIAKSES NON-HR USERS

### 4.1 HR-Only Features

1. **Employee Management**

    - ❌ Create/Edit/Delete employees
    - ❌ Employee import/export
    - ❌ Employee termination
    - ❌ Employee registration approval
    - ❌ Employee bond management
    - ❌ Bond violation management

2. **Recruitment Management**

    - ❌ Create/Edit/Delete recruitment requests (FPTK)
    - ❌ Manage candidates
    - ❌ Manage recruitment sessions
    - ❌ Recruitment reports
    - ❌ Man Power Plan (MPP)

3. **Leave Management - Administrative**

    - ❌ Manage leave entitlements
    - ❌ Generate bulk leave requests
    - ❌ Close leave requests
    - ❌ Leave reports (comprehensive)
    - ❌ Leave entitlement generation

4. **Official Travel - Administrative**

    - ❌ Stamp arrival/departure
    - ❌ Close official travels
    - ❌ Official travel reports
    - ❌ Manage transportation/accommodation

5. **Letter Number Management**

    - ❌ Create/Edit/Delete letter numbers
    - ❌ Manage letter categories/subjects
    - ❌ Letter number reports

6. **Master Data Management**

    - ❌ Manage banks, religions, projects, departments, positions
    - ❌ Manage grades, levels
    - ❌ Manage transportations, accommodations
    - ❌ Manage leave types

7. **User & Role Management**

    - ❌ Manage users
    - ❌ Manage roles & permissions
    - ❌ System configuration

8. **Reports & Analytics**
    - ❌ Comprehensive HR reports
    - ❌ Analytics dashboards
    - ❌ Data exports (bulk)

---

## 5. DATA SCOPE & FILTERING REQUIREMENTS

### 5.1 Employee Data Filtering

**Non-HR Users harus hanya dapat mengakses:**

-   Data employee sendiri (berdasarkan `user.employee_id`)
-   Data employees dalam scope authority (jika sebagai manager/supervisor)

**Filtering Logic:**

```php
// Self-service: Only own data
$query->where('employee_id', Auth::user()->employee_id);

// Manager scope: Department/Project based
$query->whereHas('administrations', function($q) {
    $q->where('department_id', Auth::user()->department_id)
      ->orWhere('project_id', Auth::user()->project_id);
});
```

### 5.2 Leave Request Filtering

**Non-HR Users:**

-   Hanya dapat melihat leave requests dengan `employee_id` = own employee_id
-   Atau leave requests dari team members (jika sebagai manager)

**Filtering Logic:**

```php
// Self-service
$query->where('employee_id', Auth::user()->employee_id);

// Manager scope
$query->whereHas('employee.administrations', function($q) {
    $q->where('department_id', Auth::user()->department_id);
});
```

### 5.3 Official Travel Filtering

**Non-HR Users:**

-   Hanya dapat melihat official travels dengan `traveler_id` = own administration_id
-   Atau official travels dari team members (jika sebagai manager)

**Filtering Logic:**

```php
// Self-service
$query->where('traveler_id', Auth::user()->administration_id);

// Manager scope
$query->whereHas('traveler.administrations', function($q) {
    $q->where('department_id', Auth::user()->department_id);
});
```

---

## 6. PERMISSION STRUCTURE YANG DIPERLUKAN

### 6.1 Self-Service Permissions

```php
// Leave Management - Self Service
'leave-requests.self-service.view-own'
'leave-requests.self-service.create-own'
'leave-requests.self-service.edit-own'
'leave-requests.self-service.cancel-own'
'leave-requests.self-service.view-own-entitlements'

// Official Travel - Self Service
'official-travels.self-service.view-own'
'official-travels.self-service.create-own'
'official-travels.self-service.edit-own'
'official-travels.self-service.cancel-own'

// Profile - Self Service
'profile.self-service.view-own'
'profile.self-service.update-own'
'profile.self-service.change-password'
```

### 6.2 Approval Permissions

```php
// Approval - General
'approvals.view-pending'
'approvals.process'

// Approval - Specific Document Types
'approvals.leave-requests.approve'
'approvals.official-travels.approve'
'approvals.recruitment-requests.approve'
```

### 6.3 Manager/Supervisor Permissions

```php
// Team View
'team.employees.view'
'team.leave-requests.view'
'team.official-travels.view'
'team.dashboard.view'

// Recommendation
'official-travels.recommend'
```

---

## 7. ROLE RECOMMENDATIONS

### 7.1 Employee Role (Basic Self-Service)

**Permissions:**

-   `leave-requests.self-service.*`
-   `official-travels.self-service.*`
-   `profile.self-service.*`
-   `dashboard.personal.view`

**Use Case:**

-   Karyawan biasa yang hanya perlu submit leave/official travel requests
-   View own data only

### 7.2 Supervisor Role (Self-Service + Team View)

**Permissions:**

-   All Employee Role permissions
-   `team.employees.view`
-   `team.leave-requests.view`
-   `team.official-travels.view`
-   `team.dashboard.view`
-   `approvals.view-pending`
-   `approvals.process`

**Use Case:**

-   Supervisor yang perlu approve requests dari tim
-   View team data untuk monitoring

### 7.3 Manager Role (Self-Service + Team View + Recommendations)

**Permissions:**

-   All Supervisor Role permissions
-   `official-travels.recommend`
-   `approvals.recruitment-requests.approve` (jika diperlukan)

**Use Case:**

-   Manager yang perlu recommend official travels
-   Approve requests dari department/project

---

## 8. IMPLEMENTATION CONSIDERATIONS

### 8.1 User-Employee Relationship

**Requirement:**

-   Setiap user non-HR harus memiliki relasi ke `employees` table
-   Field `user.employee_id` harus diisi
-   Field `user.administration_id` harus diisi (untuk official travel)

**Migration Needed:**

```php
// Add employee_id and administration_id to users table
Schema::table('users', function (Blueprint $table) {
    $table->unsignedBigInteger('employee_id')->nullable()->after('id');
    $table->unsignedBigInteger('administration_id')->nullable()->after('employee_id');

    $table->foreign('employee_id')->references('id')->on('employees');
    $table->foreign('administration_id')->references('id')->on('administrations');
});
```

### 8.2 Data Filtering Middleware

**Requirement:**

-   Middleware untuk auto-filter data berdasarkan user scope
-   Apply filtering di semua queries untuk non-HR users

**Implementation:**

```php
// Middleware: FilterEmployeeData
class FilterEmployeeData
{
    public function handle($request, Closure $next)
    {
        if (!auth()->user()->hasRole(['administrator', 'hr-*'])) {
            // Apply filtering logic
        }
        return $next($request);
    }
}
```

### 8.3 View Modifications

**Requirement:**

-   Hide HR-only menu items dari sidebar
-   Hide HR-only buttons/actions
-   Show only relevant data in tables

**Implementation:**

```blade
@can('employees.show')
    <li class="nav-item">
        <a href="{{ route('employees.index') }}">Employee</a>
    </li>
@endcan

@can('leave-requests.self-service.view-own')
    <li class="nav-item">
        <a href="{{ route('leave.requests.my-requests') }}">My Leave Requests</a>
    </li>
@endcan
```

### 8.4 Controller Modifications

**Requirement:**

-   Add scope filtering di semua controller methods
-   Add authorization checks
-   Separate self-service methods dari admin methods

**Example:**

```php
// LeaveRequestController
public function myRequests()
{
    $leaveRequests = LeaveRequest::where('employee_id', Auth::user()->employee_id)
        ->with(['leaveType', 'approvalPlans'])
        ->get();

    return view('leave-requests.my-requests', compact('leaveRequests'));
}
```

---

## 9. SECURITY CONSIDERATIONS

### 9.1 Data Isolation

-   ✅ Ensure non-HR users cannot access other employees' data
-   ✅ Validate employee_id/administration_id pada setiap request
-   ✅ Use parameter binding untuk prevent SQL injection
-   ✅ Implement row-level security

### 9.2 Authorization Checks

-   ✅ Check permissions pada setiap action
-   ✅ Validate user scope sebelum approve/reject
-   ✅ Prevent privilege escalation
-   ✅ Audit log untuk semua actions

### 9.3 Input Validation

-   ✅ Validate employee_id belongs to current user
-   ✅ Validate dates, amounts, etc.
-   ✅ Sanitize user inputs
-   ✅ Prevent mass assignment vulnerabilities

---

## 10. USER EXPERIENCE CONSIDERATIONS

### 10.1 Simplified Navigation

-   Show only relevant menu items
-   Hide complex HR features
-   Focus on self-service workflows

### 10.2 Clear Status Indicators

-   Show approval status clearly
-   Display pending actions
-   Provide notifications for status changes

### 10.3 Help & Guidance

-   Provide tooltips for complex fields
-   Show validation messages clearly
-   Guide users through approval workflows

---

## 11. TESTING REQUIREMENTS

### 11.1 Functional Testing

-   Test self-service create/edit/delete flows
-   Test approval workflows
-   Test data filtering
-   Test authorization checks

### 11.2 Security Testing

-   Test data isolation
-   Test privilege escalation attempts
-   Test SQL injection prevention
-   Test XSS prevention

### 11.3 User Acceptance Testing

-   Test dengan actual non-HR users
-   Gather feedback on UX
-   Validate workflows match business requirements

---

## 12. MIGRATION PLAN

### Phase 1: Foundation

1. Add employee_id & administration_id to users table
2. Create new permissions for self-service
3. Create new roles (employee, supervisor, manager)
4. Update user-employee relationships

### Phase 2: Self-Service Features

1. Implement leave request self-service
2. Implement official travel self-service
3. Implement profile self-service
4. Update views to hide HR features

### Phase 3: Approval Features

1. Implement approval dashboard for non-HR
2. Add approval processing capabilities
3. Add recommendation features

### Phase 4: Testing & Rollout

1. Internal testing
2. User acceptance testing
3. Training for non-HR users
4. Gradual rollout

---

## 13. SUMMARY

### 13.1 What Non-HR Users CAN Do

✅ **Self-Service:**

-   Submit & manage own leave requests
-   Submit & manage own official travel requests
-   View & update own profile (limited)
-   Change own password
-   View own leave entitlements

✅ **Approval:**

-   Approve/reject requests (jika sebagai approver)
-   View pending approvals
-   Add remarks on approvals

✅ **View (Limited):**

-   View team data (jika sebagai manager/supervisor)
-   View personal dashboard
-   View team dashboard (limited)

### 13.2 What Non-HR Users CANNOT Do

❌ **HR Administrative Functions:**

-   Employee management (create/edit/delete)
-   Recruitment management
-   Leave entitlement management
-   Official travel administrative functions
-   Letter number management
-   Master data management
-   User & role management
-   Comprehensive reports

❌ **Data Access:**

-   Cannot view other employees' personal data
-   Cannot access HR-only dashboards
-   Cannot view comprehensive analytics

---

## 14. NEXT STEPS

1. **Review & Approval:** Review dokumen ini dengan stakeholders
2. **Permission Design:** Finalize permission structure
3. **Role Design:** Finalize role definitions
4. **Implementation Planning:** Create detailed implementation plan
5. **Development:** Start Phase 1 implementation
6. **Testing:** Begin testing phase
7. **Rollout:** Gradual rollout to non-HR users

---

**Document Version:** 1.0  
**Last Updated:** 2025-01-XX  
**Author:** AI Assistant  
**Status:** Draft - Pending Review
