# ARKA HERO - General Affair (GA) Modules Analysis

**Purpose**: Technical analysis for developing GA modules based on existing ARKA HERO core patterns  
**Date**: 2026-01-14  
**Version**: 1.0

---

## Executive Summary

This document analyzes the development of 5 major GA modules to be integrated into ARKA HERO:
1. **Office Supplies Module** - Supply request, distribution, and stock opname management
2. **Vehicle Administration Module** - Fleet management with ArkFleet integration
3. **Property Management System (PMS)** - Building, room, and guest accommodation management
4. **Ticket Reservations Module** - Travel ticket booking system
5. **Meeting Room Reservations Module** - Meeting room booking with dual approval

All modules will follow ARKA HERO's established patterns:
- Laravel 10.x with Eloquent ORM
- UUID-based primary keys via Uuids trait
- Spatie Permission for RBAC
- AdminLTE 3 for UI
- DataTables for listing pages
- Multi-level approval workflow integration
- Toast notifications for user feedback
- RESTful API with Sanctum authentication

---

## Module 1: Office Supplies Module

### Overview
Complete office supply management system with request workflow, distribution tracking, and stock opname capabilities.

### Database Schema

#### 1. supplies
Master data for office supply items.

```sql
CREATE TABLE supplies (
    id CHAR(36) PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100), -- ATK, IT Equipment, Pantry, etc.
    unit VARCHAR(50) NOT NULL, -- pcs, box, ream, pack, etc.
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 0,
    max_stock INT,
    price DECIMAL(15,2),
    supplier VARCHAR(255),
    location VARCHAR(255),
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_status (status)
);
```

#### 2. supply_requests
Employee requests for supplies with approval workflow.

```sql
CREATE TABLE supply_requests (
    id CHAR(36) PRIMARY KEY,
    form_number VARCHAR(50) UNIQUE NOT NULL, -- 24SR-00001
    employee_id CHAR(36) NOT NULL,
    department_id CHAR(36) NOT NULL,
    project_id CHAR(36),
    purpose TEXT NOT NULL,
    status ENUM('pending', 'approved_by_dept_head', 'approved_by_ga_admin', 'fulfilled', 'rejected', 'cancelled') DEFAULT 'pending',
    requested_at TIMESTAMP,
    requested_by CHAR(36),
    approved_by_dept_head_at TIMESTAMP,
    approved_by_dept_head_by CHAR(36),
    approved_by_ga_admin_at TIMESTAMP,
    approved_by_ga_admin_by CHAR(36),
    fulfilled_at TIMESTAMP,
    fulfilled_by CHAR(36),
    rejection_reason TEXT,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (approved_by_dept_head_by) REFERENCES users(id),
    FOREIGN KEY (approved_by_ga_admin_by) REFERENCES users(id),
    FOREIGN KEY (fulfilled_by) REFERENCES users(id),
    
    INDEX idx_status (status),
    INDEX idx_employee (employee_id),
    INDEX idx_department (department_id)
);
```

#### 3. supply_request_items
Line items for supply requests.

```sql
CREATE TABLE supply_request_items (
    id CHAR(36) PRIMARY KEY,
    supply_request_id CHAR(36) NOT NULL,
    supply_id CHAR(36) NOT NULL,
    quantity_requested INT NOT NULL,
    quantity_approved INT,
    quantity_fulfilled INT,
    unit VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (supply_request_id) REFERENCES supply_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (supply_id) REFERENCES supplies(id),
    
    INDEX idx_request (supply_request_id),
    INDEX idx_supply (supply_id)
);
```

#### 4. supply_transactions
Stock movements (in/out/adjustment).

```sql
CREATE TABLE supply_transactions (
    id CHAR(36) PRIMARY KEY,
    transaction_number VARCHAR(50) UNIQUE NOT NULL,
    supply_id CHAR(36) NOT NULL,
    transaction_type ENUM('in', 'out', 'adjustment') NOT NULL,
    quantity INT NOT NULL,
    stock_before INT NOT NULL,
    stock_after INT NOT NULL,
    reference_type VARCHAR(50), -- supply_request, stock_opname, manual
    reference_id CHAR(36),
    transaction_date TIMESTAMP NOT NULL,
    performed_by CHAR(36) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (supply_id) REFERENCES supplies(id),
    FOREIGN KEY (performed_by) REFERENCES users(id),
    
    INDEX idx_supply (supply_id),
    INDEX idx_type (transaction_type),
    INDEX idx_date (transaction_date)
);
```

#### 5. supply_distributions
Fulfillment of approved requests with receiver verification.

```sql
CREATE TABLE supply_distributions (
    id CHAR(36) PRIMARY KEY,
    supply_request_id CHAR(36) NOT NULL,
    distribution_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending', 'sent', 'received', 'verified_by_receiver', 'rejected') DEFAULT 'pending',
    sent_at TIMESTAMP,
    sent_by CHAR(36),
    received_at TIMESTAMP,
    received_by CHAR(36),
    verified_at TIMESTAMP,
    rejection_reason TEXT,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (supply_request_id) REFERENCES supply_requests(id),
    FOREIGN KEY (sent_by) REFERENCES users(id),
    FOREIGN KEY (received_by) REFERENCES users(id),
    
    INDEX idx_request (supply_request_id),
    INDEX idx_status (status)
);
```

#### 6. stock_opname_sessions
Stock opname session management.

```sql
CREATE TABLE stock_opname_sessions (
    id CHAR(36) PRIMARY KEY,
    session_number VARCHAR(50) UNIQUE NOT NULL,
    session_name VARCHAR(255) NOT NULL,
    session_date DATE NOT NULL,
    status ENUM('draft', 'in_progress', 'completed', 'cancelled') DEFAULT 'draft',
    started_by CHAR(36) NOT NULL,
    started_at TIMESTAMP,
    completed_by CHAR(36),
    completed_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (started_by) REFERENCES users(id),
    FOREIGN KEY (completed_by) REFERENCES users(id),
    
    INDEX idx_status (status),
    INDEX idx_date (session_date)
);
```

#### 7. stock_opname_items
Individual item counts during opname.

```sql
CREATE TABLE stock_opname_items (
    id CHAR(36) PRIMARY KEY,
    session_id CHAR(36) NOT NULL,
    supply_id CHAR(36) NOT NULL,
    system_stock INT NOT NULL,
    physical_stock INT NOT NULL,
    difference INT NOT NULL,
    variance_percentage DECIMAL(5,2),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (session_id) REFERENCES stock_opname_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (supply_id) REFERENCES supplies(id),
    
    UNIQUE KEY unique_session_supply (session_id, supply_id),
    INDEX idx_session (session_id),
    INDEX idx_supply (supply_id)
);
```

#### 8. stock_adjustments
Resulting stock adjustments from opname.

```sql
CREATE TABLE stock_adjustments (
    id CHAR(36) PRIMARY KEY,
    opname_item_id CHAR(36) NOT NULL,
    supply_id CHAR(36) NOT NULL,
    adjustment_number VARCHAR(50) UNIQUE NOT NULL,
    stock_before INT NOT NULL,
    stock_after INT NOT NULL,
    adjustment_quantity INT NOT NULL,
    reason TEXT NOT NULL,
    adjusted_by CHAR(36) NOT NULL,
    adjusted_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (opname_item_id) REFERENCES stock_opname_items(id),
    FOREIGN KEY (supply_id) REFERENCES supplies(id),
    FOREIGN KEY (adjusted_by) REFERENCES users(id),
    
    INDEX idx_supply (supply_id),
    INDEX idx_adjusted_at (adjusted_at)
);
```

### Models Structure

**app/Models/Supply.php**
```php
<?php
namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supply extends Model
{
    use HasFactory, Uuids;
    
    protected $fillable = [
        'code', 'name', 'description', 'category', 'unit',
        'stock', 'min_stock', 'max_stock', 'price', 
        'supplier', 'location', 'image', 'status'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer'
    ];
    
    // Relationships
    public function requestItems()
    {
        return $this->hasMany(SupplyRequestItem::class);
    }
    
    public function transactions()
    {
        return $this->hasMany(SupplyTransaction::class);
    }
    
    public function opnameItems()
    {
        return $this->hasMany(StockOpnameItem::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }
    
    // Accessors
    public function getIsLowStockAttribute()
    {
        return $this->stock <= $this->min_stock;
    }
}
```

**app/Models/SupplyRequest.php**
```php
<?php
namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class SupplyRequest extends Model
{
    use Uuids;
    
    protected $fillable = [
        'form_number', 'employee_id', 'department_id', 'project_id',
        'purpose', 'status', 'requested_at', 'requested_by',
        'approved_by_dept_head_at', 'approved_by_dept_head_by',
        'approved_by_ga_admin_at', 'approved_by_ga_admin_by',
        'fulfilled_at', 'fulfilled_by', 'rejection_reason', 'notes'
    ];
    
    protected $casts = [
        'requested_at' => 'datetime',
        'approved_by_dept_head_at' => 'datetime',
        'approved_by_ga_admin_at' => 'datetime',
        'fulfilled_at' => 'datetime'
    ];
    
    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function items()
    {
        return $this->hasMany(SupplyRequestItem::class);
    }
    
    public function distribution()
    {
        return $this->hasOne(SupplyDistribution::class);
    }
    
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved_by_ga_admin');
    }
}
```

### Controllers

**app/Http/Controllers/SupplyController.php**
- Standard CRUD for supply master data
- Import/Export functionality (Excel)
- Low stock alert view
- Stock history per item

**app/Http/Controllers/SupplyRequestController.php**
- Create supply request with multiple items
- Submit for approval workflow
- Department head approval
- GA admin approval
- Rejection handling
- Self-service view for employees (my requests)

**app/Http/Controllers/SupplyDistributionController.php**
- Create distribution from approved request
- Mark as sent
- Receiver verification
- Rejection handling

**app/Http/Controllers/StockOpnameController.php**
- Create opname session
- Enter physical counts
- Calculate variances
- Generate stock adjustments
- Opname reports

### API Endpoints

```
# Supplies Master Data
GET     /api/v1/supplies                    - List all supplies
POST    /api/v1/supplies                    - Create supply
GET     /api/v1/supplies/{id}               - Get supply details
PUT     /api/v1/supplies/{id}               - Update supply
DELETE  /api/v1/supplies/{id}               - Delete supply
GET     /api/v1/supplies/low-stock          - Get low stock items
POST    /api/v1/supplies/search             - Search supplies

# Supply Requests
GET     /api/v1/supply-requests             - List requests
POST    /api/v1/supply-requests             - Create request
GET     /api/v1/supply-requests/{id}        - Get request details
PUT     /api/v1/supply-requests/{id}        - Update request
DELETE  /api/v1/supply-requests/{id}        - Cancel request
POST    /api/v1/supply-requests/{id}/submit             - Submit for approval
POST    /api/v1/supply-requests/{id}/approve-dept-head  - Dept head approval
POST    /api/v1/supply-requests/{id}/approve-ga-admin   - GA admin approval
POST    /api/v1/supply-requests/{id}/reject             - Reject request
GET     /api/v1/supply-requests/my-requests             - Employee's requests

# Supply Distributions
GET     /api/v1/supply-distributions        - List distributions
POST    /api/v1/supply-distributions        - Create distribution
GET     /api/v1/supply-distributions/{id}   - Get distribution details
POST    /api/v1/supply-distributions/{id}/send    - Mark as sent
POST    /api/v1/supply-distributions/{id}/receive - Receiver verification
POST    /api/v1/supply-distributions/{id}/reject  - Reject distribution

# Stock Opname
GET     /api/v1/stock-opname/sessions       - List sessions
POST    /api/v1/stock-opname/sessions       - Create session
GET     /api/v1/stock-opname/sessions/{id}  - Get session details
POST    /api/v1/stock-opname/sessions/{id}/start    - Start opname
POST    /api/v1/stock-opname/sessions/{id}/complete - Complete opname
POST    /api/v1/stock-opname/items          - Add/update item count
POST    /api/v1/stock-opname/adjustments    - Generate adjustments

# Transactions
GET     /api/v1/supply-transactions         - List transactions
GET     /api/v1/supply-transactions/{id}    - Transaction details
POST    /api/v1/supply-transactions/manual  - Manual stock adjustment
```

### UI Structure

```
resources/views/supplies/
├── index.blade.php              # DataTable list
├── create.blade.php             # Create form
├── edit.blade.php               # Edit form
├── show.blade.php               # Detail view with stock history
├── action.blade.php             # Action buttons partial
└── low-stock.blade.php          # Low stock alert dashboard

resources/views/supply-requests/
├── index.blade.php              # All requests (DataTable)
├── create.blade.php             # Multi-item request form
├── show.blade.php               # Request detail with approval flow
├── my-requests.blade.php        # Employee self-service view
└── action.blade.php             # Action buttons

resources/views/supply-distributions/
├── index.blade.php              # Distribution list
├── create.blade.php             # Create from approved request
├── show.blade.php               # Distribution detail with status
└── verify.blade.php             # Receiver verification form

resources/views/stock-opname/
├── sessions.blade.php           # Opname sessions list
├── create-session.blade.php     # Create new session
├── count-items.blade.php        # Physical count entry
├── variance-report.blade.php    # Variance analysis
└── adjustments.blade.php        # Generated adjustments
```

### Business Logic & Workflows

**Supply Request Workflow**
```
1. Employee creates request with items → Status: pending
2. Submit to Department Head → Send notification
3. Department Head reviews:
   - Approve → Status: approved_by_dept_head → Forward to GA Admin
   - Reject → Status: rejected → End
4. GA Admin reviews:
   - Approve → Status: approved_by_ga_admin → Ready for fulfillment
   - Reject → Status: rejected → End
   - Approve with quantity adjustment
5. GA creates distribution → Status: fulfilled
6. Receiver verifies → Distribution: verified_by_receiver
```

**Stock Opname Workflow**
```
1. GA Admin creates opname session → Status: draft
2. Start session → Status: in_progress → Lock all transactions
3. Enter physical counts for each item
4. System calculates variances
5. Review variances → Generate adjustments
6. Complete session → Status: completed → Unlock transactions
7. Stock adjustments automatically created → Update stock levels
```

**Stock Transaction Rules**
- IN: Purchase order, return from employee
- OUT: Distribution fulfillment, damaged items
- ADJUSTMENT: From stock opname, manual correction
- All transactions create audit trail
- Stock cannot go below 0
- Triggers low stock alert when stock <= min_stock

### Integration Points

1. **Employee Module**: Request creator, receiver verification
2. **Department Module**: Department-wise request filtering
3. **Project Module**: Project-specific supply allocation
4. **User Module**: Approval workflow, permissions
5. **Notification System**: Email/in-app notifications for approvals

### Permissions

```
# Supply Management
supplies.view
supplies.create
supplies.edit
supplies.delete
supplies.import
supplies.export

# Supply Requests
supply-requests.view
supply-requests.create
supply-requests.edit
supply-requests.delete
supply-requests.view-own        # Employees can view their requests
supply-requests.approve-dept    # Department Head
supply-requests.approve-ga      # GA Admin

# Distributions
supply-distributions.view
supply-distributions.create
supply-distributions.send
supply-distributions.verify     # Receivers can verify

# Stock Opname
stock-opname.view
stock-opname.create
stock-opname.start
stock-opname.complete
stock-opname.adjust
```

---

## Module 2: Vehicle Administration Module

### Overview
Fleet management system with vehicle master data, fuel tracking, maintenance scheduling, and document management with ArkFleet integration capability.

### Database Schema

#### 1. vehicles
Vehicle master data with ArkFleet integration fields.

```sql
CREATE TABLE vehicles (
    id CHAR(36) PRIMARY KEY,
    vehicle_number VARCHAR(50) UNIQUE NOT NULL, -- Internal number
    license_plate VARCHAR(20) UNIQUE NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    color VARCHAR(50),
    type ENUM('sedan', 'suv', 'mpv', 'truck', 'bus', 'motorcycle', 'other') NOT NULL,
    ownership ENUM('company', 'rental', 'employee') NOT NULL,
    vin VARCHAR(100), -- Vehicle Identification Number
    engine_number VARCHAR(100),
    transmission ENUM('manual', 'automatic') NOT NULL,
    fuel_type ENUM('gasoline', 'diesel', 'electric', 'hybrid') NOT NULL,
    capacity INT, -- Passenger capacity
    status ENUM('active', 'maintenance', 'inactive', 'sold', 'accident') DEFAULT 'active',
    odometer INT DEFAULT 0, -- Current mileage
    purchase_date DATE,
    purchase_price DECIMAL(15,2),
    assigned_to CHAR(36), -- Employee ID
    assigned_project_id CHAR(36),
    arkfleet_vehicle_id VARCHAR(100), -- Integration with ArkFleet
    arkfleet_sync_at TIMESTAMP,
    notes TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (assigned_to) REFERENCES employees(id),
    FOREIGN KEY (assigned_project_id) REFERENCES projects(id),
    
    INDEX idx_license_plate (license_plate),
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_assigned_to (assigned_to)
);
```

#### 2. fuel_records
Fuel consumption tracking.

```sql
CREATE TABLE fuel_records (
    id CHAR(36) PRIMARY KEY,
    vehicle_id CHAR(36) NOT NULL,
    fuel_date DATE NOT NULL,
    odometer INT NOT NULL,
    fuel_type VARCHAR(50) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL, -- Liters
    price_per_liter DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(15,2) NOT NULL,
    fuel_station VARCHAR(255),
    driver_id CHAR(36), -- Employee who refueled
    receipt_number VARCHAR(100),
    receipt_image VARCHAR(255),
    notes TEXT,
    created_by CHAR(36) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (driver_id) REFERENCES employees(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_fuel_date (fuel_date),
    INDEX idx_driver (driver_id)
);
```

#### 3. vehicle_maintenances
Maintenance scheduling and history.

```sql
CREATE TABLE vehicle_maintenances (
    id CHAR(36) PRIMARY KEY,
    vehicle_id CHAR(36) NOT NULL,
    maintenance_number VARCHAR(50) UNIQUE NOT NULL,
    maintenance_type ENUM('scheduled', 'repair', 'accident_repair', 'inspection') NOT NULL,
    category VARCHAR(100), -- Oil Change, Tire Rotation, Body Repair, etc.
    scheduled_date DATE,
    actual_date DATE,
    odometer INT,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    workshop_name VARCHAR(255),
    workshop_address TEXT,
    description TEXT NOT NULL,
    cost DECIMAL(15,2),
    invoice_number VARCHAR(100),
    invoice_image VARCHAR(255),
    next_maintenance_date DATE,
    next_maintenance_odometer INT,
    performed_by VARCHAR(255), -- Mechanic name
    completed_by CHAR(36),
    completed_at TIMESTAMP,
    notes TEXT,
    created_by CHAR(36) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (completed_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_status (status),
    INDEX idx_type (maintenance_type)
);
```

#### 4. vehicle_documents
Document management with revisions.

```sql
CREATE TABLE vehicle_documents (
    id CHAR(36) PRIMARY KEY,
    vehicle_id CHAR(36) NOT NULL,
    document_type ENUM('stnk', 'kir', 'insurance', 'tax', 'permit', 'other') NOT NULL,
    document_number VARCHAR(100),
    document_name VARCHAR(255) NOT NULL,
    issue_date DATE,
    expiry_date DATE,
    issuing_authority VARCHAR(255),
    current_revision INT DEFAULT 1,
    status ENUM('active', 'expired', 'pending_renewal', 'archived') DEFAULT 'active',
    notes TEXT,
    created_by CHAR(36) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_type (document_type),
    INDEX idx_expiry (expiry_date),
    INDEX idx_status (status)
);
```

#### 5. vehicle_document_revisions
Revision tracking for documents.

```sql
CREATE TABLE vehicle_document_revisions (
    id CHAR(36) PRIMARY KEY,
    document_id CHAR(36) NOT NULL,
    revision_number INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    issue_date DATE,
    expiry_date DATE,
    uploaded_by CHAR(36) NOT NULL,
    uploaded_at TIMESTAMP NOT NULL,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (document_id) REFERENCES vehicle_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    
    UNIQUE KEY unique_document_revision (document_id, revision_number),
    INDEX idx_document (document_id),
    INDEX idx_expiry (expiry_date)
);
```

### Models

**app/Models/Vehicle.php**
```php
<?php
namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use Uuids;
    
    protected $fillable = [
        'vehicle_number', 'license_plate', 'brand', 'model', 'year',
        'color', 'type', 'ownership', 'vin', 'engine_number',
        'transmission', 'fuel_type', 'capacity', 'status', 'odometer',
        'purchase_date', 'purchase_price', 'assigned_to', 'assigned_project_id',
        'arkfleet_vehicle_id', 'arkfleet_sync_at', 'notes', 'image'
    ];
    
    protected $casts = [
        'year' => 'integer',
        'capacity' => 'integer',
        'odometer' => 'integer',
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'arkfleet_sync_at' => 'datetime'
    ];
    
    // Relationships
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }
    
    public function assignedProject()
    {
        return $this->belongsTo(Project::class, 'assigned_project_id');
    }
    
    public function fuelRecords()
    {
        return $this->hasMany(FuelRecord::class);
    }
    
    public function maintenances()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }
    
    public function documents()
    {
        return $this->hasMany(VehicleDocument::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['active', 'maintenance'])
                     ->whereNull('assigned_to');
    }
    
    // Accessors
    public function getExpiringDocumentsAttribute()
    {
        return $this->documents()
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('status', 'active')
            ->get();
    }
}
```

### Controllers

**app/Http/Controllers/VehicleController.php**
- CRUD for vehicle master data
- Vehicle assignment to employee/project
- Vehicle status management
- Integration with ArkFleet (sync)
- Export vehicle list

**app/Http/Controllers/FuelRecordController.php**
- Record fuel transactions
- Fuel consumption reports
- Cost analysis per vehicle
- Monthly fuel summary

**app/Http/Controllers/VehicleMaintenanceController.php**
- Schedule maintenance
- Record actual maintenance
- Maintenance history per vehicle
- Upcoming maintenance dashboard
- Cost tracking

**app/Http/Controllers/VehicleDocumentController.php**
- Upload vehicle documents
- Manage document revisions
- Expiry alerts
- Document renewal tracking

### API Endpoints

```
# Vehicles
GET     /api/v1/vehicles                    - List vehicles
POST    /api/v1/vehicles                    - Create vehicle
GET     /api/v1/vehicles/{id}               - Get details
PUT     /api/v1/vehicles/{id}               - Update vehicle
DELETE  /api/v1/vehicles/{id}               - Delete vehicle
GET     /api/v1/vehicles/available          - Available vehicles
POST    /api/v1/vehicles/{id}/assign        - Assign to employee
POST    /api/v1/vehicles/{id}/sync-arkfleet - Sync with ArkFleet

# Fuel Records
GET     /api/v1/fuel-records                - List records
POST    /api/v1/fuel-records                - Create record
GET     /api/v1/fuel-records/{id}           - Get details
PUT     /api/v1/fuel-records/{id}           - Update record
DELETE  /api/v1/fuel-records/{id}           - Delete record
GET     /api/v1/fuel-records/vehicle/{id}   - Records by vehicle
GET     /api/v1/fuel-records/reports/monthly - Monthly summary

# Maintenance
GET     /api/v1/vehicle-maintenances        - List maintenances
POST    /api/v1/vehicle-maintenances        - Schedule maintenance
GET     /api/v1/vehicle-maintenances/{id}   - Get details
PUT     /api/v1/vehicle-maintenances/{id}   - Update maintenance
POST    /api/v1/vehicle-maintenances/{id}/complete - Mark complete
GET     /api/v1/vehicle-maintenances/upcoming - Upcoming schedule

# Documents
GET     /api/v1/vehicle-documents           - List documents
POST    /api/v1/vehicle-documents           - Upload document
GET     /api/v1/vehicle-documents/{id}      - Get details
PUT     /api/v1/vehicle-documents/{id}      - Update document
DELETE  /api/v1/vehicle-documents/{id}      - Delete document
POST    /api/v1/vehicle-documents/{id}/revise - Upload new revision
GET     /api/v1/vehicle-documents/expiring  - Expiring documents
```

### Business Logic

**Fuel Consumption Tracking**
- Calculate km/liter efficiency
- Track cost per km
- Monthly fuel budget monitoring
- Anomaly detection (unusual consumption)

**Maintenance Scheduling**
- Scheduled maintenance based on date OR odometer
- Automatic reminders 7 days before due
- Track maintenance costs per vehicle
- Generate maintenance history report

**Document Management**
- Automatic expiry notifications (30, 14, 7 days before)
- Revision history with file versioning
- Document renewal workflow
- Bulk document upload

**ArkFleet Integration**
- Sync vehicle data from ArkFleet
- Push maintenance records to ArkFleet
- Fuel consumption sync
- Real-time GPS tracking (if available)

---

## Module 3: Property Management System (PMS)

### Overview
Building and room management for company properties, including guest room reservations with check-in/check-out workflow and room maintenance scheduling.

### Database Schema

#### 1. buildings
Building master data.

```sql
CREATE TABLE buildings (
    id CHAR(36) PRIMARY KEY,
    building_code VARCHAR(50) UNIQUE NOT NULL,
    building_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(10),
    floors INT NOT NULL,
    total_rooms INT DEFAULT 0,
    building_type ENUM('office', 'warehouse', 'dormitory', 'guest_house', 'mixed') NOT NULL,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    manager_id CHAR(36), -- Building manager employee
    contact_person VARCHAR(255),
    contact_phone VARCHAR(20),
    description TEXT,
    amenities JSON, -- ["parking", "generator", "cctv", "security_24h"]
    image VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (manager_id) REFERENCES employees(id),
    
    INDEX idx_status (status),
    INDEX idx_type (building_type)
);
```

#### 2. rooms
Room master data linked to buildings.

```sql
CREATE TABLE rooms (
    id CHAR(36) PRIMARY KEY,
    building_id CHAR(36) NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    room_name VARCHAR(255),
    floor INT NOT NULL,
    room_type ENUM('single', 'double', 'twin', 'suite', 'dormitory', 'office', 'meeting', 'other') NOT NULL,
    capacity INT NOT NULL,
    bed_type VARCHAR(100), -- Single Bed, Double Bed, Twin Beds
    area_sqm DECIMAL(8,2), -- Room area in square meters
    status ENUM('available', 'occupied', 'maintenance', 'reserved', 'inactive') DEFAULT 'available',
    price_per_night DECIMAL(15,2),
    amenities JSON, -- ["ac", "tv", "wifi", "bathroom", "kitchen", "desk"]
    description TEXT,
    images JSON, -- ["image1.jpg", "image2.jpg"]
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (building_id) REFERENCES buildings(id),
    
    UNIQUE KEY unique_room_number (building_id, room_number),
    INDEX idx_building (building_id),
    INDEX idx_status (status),
    INDEX idx_type (room_type)
);
```

#### 3. room_reservations
Guest room booking system.

```sql
CREATE TABLE room_reservations (
    id CHAR(36) PRIMARY KEY,
    reservation_number VARCHAR(50) UNIQUE NOT NULL,
    room_id CHAR(36) NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    guest_employee_id CHAR(36), -- If guest is employee
    guest_phone VARCHAR(20) NOT NULL,
    guest_email VARCHAR(255),
    guest_id_number VARCHAR(50), -- KTP/Passport
    guest_company VARCHAR(255),
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_nights INT NOT NULL,
    total_guests INT NOT NULL,
    purpose TEXT,
    status ENUM('pending', 'approved', 'checked_in', 'checked_out', 'cancelled', 'no_show') DEFAULT 'pending',
    booked_by CHAR(36) NOT NULL,
    booked_at TIMESTAMP NOT NULL,
    approved_by CHAR(36),
    approved_at TIMESTAMP,
    checked_in_at TIMESTAMP,
    checked_in_by CHAR(36),
    checked_out_at TIMESTAMP,
    checked_out_by CHAR(36),
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP,
    cancelled_by CHAR(36),
    special_requests TEXT,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (guest_employee_id) REFERENCES employees(id),
    FOREIGN KEY (booked_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (checked_in_by) REFERENCES users(id),
    FOREIGN KEY (checked_out_by) REFERENCES users(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),
    
    INDEX idx_room (room_id),
    INDEX idx_status (status),
    INDEX idx_check_in (check_in_date),
    INDEX idx_check_out (check_out_date)
);
```

#### 4. room_maintenances
Maintenance scheduling for rooms.

```sql
CREATE TABLE room_maintenances (
    id CHAR(36) PRIMARY KEY,
    maintenance_number VARCHAR(50) UNIQUE NOT NULL,
    room_id CHAR(36) NOT NULL,
    maintenance_type ENUM('cleaning', 'repair', 'renovation', 'inspection', 'preventive') NOT NULL,
    category VARCHAR(100), -- Plumbing, Electrical, Furniture, AC, etc.
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time TIME,
    actual_start_date DATE,
    actual_end_date DATE,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    description TEXT NOT NULL,
    cost DECIMAL(15,2),
    performed_by VARCHAR(255), -- Technician name
    vendor_name VARCHAR(255),
    blocks_availability BOOLEAN DEFAULT TRUE,
    completion_notes TEXT,
    completed_by CHAR(36),
    completed_at TIMESTAMP,
    notes TEXT,
    created_by CHAR(36) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (completed_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    
    INDEX idx_room (room_id),
    INDEX idx_status (status),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_priority (priority)
);
```

### Models

**app/Models/Building.php**
```php
<?php
namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use Uuids;
    
    protected $fillable = [
        'building_code', 'building_name', 'address', 'city', 'province',
        'postal_code', 'floors', 'total_rooms', 'building_type', 'status',
        'manager_id', 'contact_person', 'contact_phone', 'description',
        'amenities', 'image', 'notes'
    ];
    
    protected $casts = [
        'floors' => 'integer',
        'total_rooms' => 'integer',
        'amenities' => 'array'
    ];
    
    // Relationships
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
    
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

**app/Models/Room.php**
```php
<?php
namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use Uuids;
    
    protected $fillable = [
        'building_id', 'room_number', 'room_name', 'floor', 'room_type',
        'capacity', 'bed_type', 'area_sqm', 'status', 'price_per_night',
        'amenities', 'description', 'images', 'notes'
    ];
    
    protected $casts = [
        'floor' => 'integer',
        'capacity' => 'integer',
        'area_sqm' => 'decimal:2',
        'price_per_night' => 'decimal:2',
        'amenities' => 'array',
        'images' => 'array'
    ];
    
    // Relationships
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    
    public function reservations()
    {
        return $this->hasMany(RoomReservation::class);
    }
    
    public function maintenances()
    {
        return $this->hasMany(RoomMaintenance::class);
    }
    
    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('room_type', $type);
    }
    
    // Methods
    public function isAvailableForDates($checkIn, $checkOut)
    {
        return !$this->reservations()
            ->where(function($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                      ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                      ->orWhere(function($q) use ($checkIn, $checkOut) {
                          $q->where('check_in_date', '<=', $checkIn)
                            ->where('check_out_date', '>=', $checkOut);
                      });
            })
            ->whereIn('status', ['pending', 'approved', 'checked_in'])
            ->exists();
    }
}
```

**app/Models/RoomReservation.php**
```php
<?php
namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RoomReservation extends Model
{
    use Uuids;
    
    protected $fillable = [
        'reservation_number', 'room_id', 'guest_name', 'guest_employee_id',
        'guest_phone', 'guest_email', 'guest_id_number', 'guest_company',
        'check_in_date', 'check_out_date', 'total_nights', 'total_guests',
        'purpose', 'status', 'booked_by', 'booked_at', 'approved_by',
        'approved_at', 'checked_in_at', 'checked_in_by', 'checked_out_at',
        'checked_out_by', 'cancellation_reason', 'cancelled_at', 'cancelled_by',
        'special_requests', 'notes'
    ];
    
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_nights' => 'integer',
        'total_guests' => 'integer',
        'booked_at' => 'datetime',
        'approved_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];
    
    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    
    public function guestEmployee()
    {
        return $this->belongsTo(Employee::class, 'guest_employee_id');
    }
    
    public function bookedByUser()
    {
        return $this->belongsTo(User::class, 'booked_by');
    }
    
    // Boot
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($reservation) {
            $reservation->total_nights = Carbon::parse($reservation->check_in_date)
                ->diffInDays(Carbon::parse($reservation->check_out_date));
        });
    }
}
```

### Controllers

**app/Http/Controllers/BuildingController.php**
- CRUD for buildings
- Building occupancy report
- Export building list

**app/Http/Controllers/RoomController.php**
- CRUD for rooms
- Room availability calendar
- Check availability for dates
- Room images upload

**app/Http/Controllers/RoomReservationController.php**
- Create reservation with availability check
- Approve reservation
- Check-in process
- Check-out process
- Cancellation handling
- Reservation calendar view
- My reservations (employee portal)

**app/Http/Controllers/RoomMaintenanceController.php**
- Schedule maintenance
- Block room availability during maintenance
- Complete maintenance
- Maintenance history per room

### API Endpoints

```
# Buildings
GET     /api/v1/buildings                   - List buildings
POST    /api/v1/buildings                   - Create building
GET     /api/v1/buildings/{id}              - Get details
PUT     /api/v1/buildings/{id}              - Update building
DELETE  /api/v1/buildings/{id}              - Delete building
GET     /api/v1/buildings/{id}/rooms        - Rooms in building

# Rooms
GET     /api/v1/rooms                       - List rooms
POST    /api/v1/rooms                       - Create room
GET     /api/v1/rooms/{id}                  - Get details
PUT     /api/v1/rooms/{id}                  - Update room
DELETE  /api/v1/rooms/{id}                  - Delete room
GET     /api/v1/rooms/available             - Available rooms
POST    /api/v1/rooms/check-availability    - Check for dates

# Room Reservations
GET     /api/v1/room-reservations           - List reservations
POST    /api/v1/room-reservations           - Create reservation
GET     /api/v1/room-reservations/{id}      - Get details
PUT     /api/v1/room-reservations/{id}      - Update reservation
POST    /api/v1/room-reservations/{id}/approve    - Approve
POST    /api/v1/room-reservations/{id}/check-in  - Check in
POST    /api/v1/room-reservations/{id}/check-out - Check out
POST    /api/v1/room-reservations/{id}/cancel    - Cancel
GET     /api/v1/room-reservations/calendar        - Calendar view
GET     /api/v1/room-reservations/my-reservations - Employee portal

# Room Maintenance
GET     /api/v1/room-maintenances           - List maintenances
POST    /api/v1/room-maintenances           - Schedule maintenance
GET     /api/v1/room-maintenances/{id}      - Get details
PUT     /api/v1/room-maintenances/{id}      - Update maintenance
POST    /api/v1/room-maintenances/{id}/complete - Complete
```

### Business Logic

**Reservation Workflow**
```
1. Employee/GA creates reservation → Check availability
2. Status: pending → Send approval notification
3. GA Admin approves → Status: approved → Room status: reserved
4. Check-in → Status: checked_in → Room status: occupied
5. Check-out → Status: checked_out → Room status: available
```

**Room Availability Logic**
- Check existing reservations for date range
- Check maintenance schedules that block availability
- Calculate available nights
- Support multi-room booking

**Maintenance Scheduling**
- If blocks_availability = true → Room unavailable during maintenance
- Automatic notifications before scheduled maintenance
- Track maintenance costs per room
- Generate maintenance history report

---

## Module 4: Ticket Reservations Module

### Overview
Travel ticket booking request system for employees with approval workflow and document attachment support.

### Database Schema

#### 1. ticket_reservations
Travel ticket booking requests.

```sql
CREATE TABLE ticket_reservations (
    id CHAR(36) PRIMARY KEY,
    reservation_number VARCHAR(50) UNIQUE NOT NULL,
    employee_id CHAR(36) NOT NULL,
    passenger_name VARCHAR(255) NOT NULL,
    passenger_id_number VARCHAR(50),
    ticket_type ENUM('flight', 'train', 'bus', 'ship', 'other') NOT NULL,
    travel_purpose TEXT NOT NULL,
    departure_city VARCHAR(100) NOT NULL,
    arrival_city VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,
    departure_time TIME,
    return_date DATE,
    return_time TIME,
    is_round_trip BOOLEAN DEFAULT FALSE,
    preferred_airline VARCHAR(100),
    preferred_class ENUM('economy', 'business', 'first') DEFAULT 'economy',
    seat_preference VARCHAR(100),
    estimated_cost DECIMAL(15,2),
    actual_cost DECIMAL(15,2),
    status ENUM('pending', 'approved', 'booked', 'completed', 'rejected', 'cancelled') DEFAULT 'pending',
    requested_by CHAR(36) NOT NULL,
    requested_at TIMESTAMP NOT NULL,
    approved_by CHAR(36),
    approved_at TIMESTAMP,
    booked_by CHAR(36),
    booked_at TIMESTAMP,
    booking_reference VARCHAR(100),
    rejection_reason TEXT,
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP,
    cancelled_by CHAR(36),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (booked_by) REFERENCES users(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),
    
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_departure_date (departure_date),
    INDEX idx_ticket_type (ticket_type)
);
```

#### 2. reservation_documents
Document attachments for ticket reservations.

```sql
CREATE TABLE reservation_documents (
    id CHAR(36) PRIMARY KEY,
    reservation_id CHAR(36) NOT NULL,
    document_type ENUM('ticket', 'invoice', 'approval_letter', 'boarding_pass', 'receipt', 'other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    uploaded_by CHAR(36) NOT NULL,
    uploaded_at TIMESTAMP NOT NULL,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (reservation_id) REFERENCES ticket_reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    
    INDEX idx_reservation (reservation_id),
    INDEX idx_type (document_type)
);
```

### Models

**app/Models/TicketReservation.php**
```php
<?php
namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class TicketReservation extends Model
{
    use Uuids;
    
    protected $fillable = [
        'reservation_number', 'employee_id', 'passenger_name', 'passenger_id_number',
        'ticket_type', 'travel_purpose', 'departure_city', 'arrival_city',
        'departure_date', 'departure_time', 'return_date', 'return_time',
        'is_round_trip', 'preferred_airline', 'preferred_class', 'seat_preference',
        'estimated_cost', 'actual_cost', 'status', 'requested_by', 'requested_at',
        'approved_by', 'approved_at', 'booked_by', 'booked_at', 'booking_reference',
        'rejection_reason', 'cancellation_reason', 'cancelled_at', 'cancelled_by', 'notes'
    ];
    
    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'is_round_trip' => 'boolean',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'booked_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];
    
    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    public function documents()
    {
        return $this->hasMany(ReservationDocument::class, 'reservation_id');
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
```

### Controllers

**app/Http/Controllers/TicketReservationController.php**
- Create ticket reservation request
- Submit for approval
- Approve/reject reservation
- Book ticket (update with booking reference)
- Upload ticket documents
- Cancellation handling
- My reservations (employee portal)
- Export reservation list

### API Endpoints

```
# Ticket Reservations
GET     /api/v1/ticket-reservations         - List reservations
POST    /api/v1/ticket-reservations         - Create reservation
GET     /api/v1/ticket-reservations/{id}    - Get details
PUT     /api/v1/ticket-reservations/{id}    - Update reservation
POST    /api/v1/ticket-reservations/{id}/approve  - Approve
POST    /api/v1/ticket-reservations/{id}/reject   - Reject
POST    /api/v1/ticket-reservations/{id}/book     - Mark as booked
POST    /api/v1/ticket-reservations/{id}/complete - Complete
POST    /api/v1/ticket-reservations/{id}/cancel   - Cancel
GET     /api/v1/ticket-reservations/my-requests   - Employee portal

# Reservation Documents
POST    /api/v1/reservation-documents       - Upload document
GET     /api/v1/reservation-documents/{id}  - Download document
DELETE  /api/v1/reservation-documents/{id}  - Delete document
```

### Business Logic

**Reservation Workflow**
```
1. Employee creates reservation → Status: pending
2. Submit for approval → Notify approver
3. Approver reviews:
   - Approve → Status: approved → GA can book
   - Reject → Status: rejected → End
4. GA books ticket → Status: booked → Upload ticket document
5. Travel completed → Status: completed
```

**Document Management**
- Attach approval letter before approval
- Attach ticket after booking
- Attach boarding pass after travel
- Attach invoice for finance claim

---

## Module 5: Meeting Room Reservations Module

### Overview
Meeting room booking system with dual approval workflow (Department Head → GA Admin) and consumption request for meeting supplies.

### Database Schema

#### 1. meeting_rooms
Meeting room master data.

```sql
CREATE TABLE meeting_rooms (
    id CHAR(36) PRIMARY KEY,
    room_code VARCHAR(50) UNIQUE NOT NULL,
    room_name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    floor INT,
    capacity INT NOT NULL,
    room_type ENUM('small', 'medium', 'large', 'auditorium', 'boardroom') NOT NULL,
    amenities JSON, -- ["projector", "whiteboard", "video_conference", "wifi", "ac", "sound_system"]
    hourly_rate DECIMAL(10,2),
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    image VARCHAR(255),
    description TEXT,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_capacity (capacity)
);
```

#### 2. meeting_room_reservations
Meeting room booking system with dual approval.

```sql
CREATE TABLE meeting_room_reservations (
    id CHAR(36) PRIMARY KEY,
    reservation_number VARCHAR(50) UNIQUE NOT NULL,
    meeting_room_id CHAR(36) NOT NULL,
    employee_id CHAR(36) NOT NULL,
    department_id CHAR(36) NOT NULL,
    meeting_title VARCHAR(255) NOT NULL,
    meeting_purpose TEXT NOT NULL,
    meeting_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    total_hours DECIMAL(5,2),
    attendees_count INT NOT NULL,
    attendees_list TEXT, -- Names or employee IDs
    setup_requirements TEXT,
    equipment_needed TEXT,
    catering_needed BOOLEAN DEFAULT FALSE,
    catering_details TEXT,
    status ENUM('pending', 'approved_by_dept_head', 'approved_by_ga_admin', 'allocated', 'completed', 'rejected', 'cancelled') DEFAULT 'pending',
    requested_by CHAR(36) NOT NULL,
    requested_at TIMESTAMP NOT NULL,
    approved_by_dept_head_at TIMESTAMP,
    approved_by_dept_head_by CHAR(36),
    approved_by_ga_admin_at TIMESTAMP,
    approved_by_ga_admin_by CHAR(36),
    allocated_at TIMESTAMP,
    allocated_by CHAR(36),
    completed_at TIMESTAMP,
    rejection_reason TEXT,
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP,
    cancelled_by CHAR(36),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (meeting_room_id) REFERENCES meeting_rooms(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (approved_by_dept_head_by) REFERENCES users(id),
    FOREIGN KEY (approved_by_ga_admin_by) REFERENCES users(id),
    FOREIGN KEY (allocated_by) REFERENCES users(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),
    
    INDEX idx_meeting_room (meeting_room_id),
    INDEX idx_status (status),
    INDEX idx_meeting_date (meeting_date),
    INDEX idx_employee (employee_id),
    INDEX idx_department (department_id)
);
```

#### 3. meeting_room_consumptions
Consumption requests for meeting supplies.

```sql
CREATE TABLE meeting_room_consumptions (
    id CHAR(36) PRIMARY KEY,
    reservation_id CHAR(36) NOT NULL,
    supply_id CHAR(36) NOT NULL,
    quantity_requested INT NOT NULL,
    quantity_allocated INT,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (reservation_id) REFERENCES meeting_room_reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (supply_id) REFERENCES supplies(id),
    
    INDEX idx_reservation (reservation_id),
    INDEX idx_supply (supply_id)
);
```

### Models

**app/Models/MeetingRoom.php**
```php
<?php
namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class MeetingRoom extends Model
{
    use Uuids;
    
    protected $fillable = [
        'room_code', 'room_name', 'location', 'floor', 'capacity',
        'room_type', 'amenities', 'hourly_rate', 'status',
        'image', 'description', 'notes'
    ];
    
    protected $casts = [
        'floor' => 'integer',
        'capacity' => 'integer',
        'hourly_rate' => 'decimal:2',
        'amenities' => 'array'
    ];
    
    // Relationships
    public function reservations()
    {
        return $this->hasMany(MeetingRoomReservation::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeByCapacity($query, $min)
    {
        return $query->where('capacity', '>=', $min);
    }
    
    // Methods
    public function isAvailableForDateTime($date, $startTime, $endTime, $excludeReservationId = null)
    {
        $query = $this->reservations()
            ->where('meeting_date', $date)
            ->where(function($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function($subQ) use ($startTime, $endTime) {
                      $subQ->where('start_time', '<=', $startTime)
                           ->where('end_time', '>=', $endTime);
                  });
            })
            ->whereIn('status', ['pending', 'approved_by_dept_head', 'approved_by_ga_admin', 'allocated']);
        
        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }
        
        return !$query->exists();
    }
}
```

**app/Models/MeetingRoomReservation.php**
```php
<?php
namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MeetingRoomReservation extends Model
{
    use Uuids;
    
    protected $fillable = [
        'reservation_number', 'meeting_room_id', 'employee_id', 'department_id',
        'meeting_title', 'meeting_purpose', 'meeting_date', 'start_time', 'end_time',
        'total_hours', 'attendees_count', 'attendees_list', 'setup_requirements',
        'equipment_needed', 'catering_needed', 'catering_details', 'status',
        'requested_by', 'requested_at', 'approved_by_dept_head_at', 'approved_by_dept_head_by',
        'approved_by_ga_admin_at', 'approved_by_ga_admin_by', 'allocated_at', 'allocated_by',
        'completed_at', 'rejection_reason', 'cancellation_reason', 'cancelled_at', 'cancelled_by', 'notes'
    ];
    
    protected $casts = [
        'meeting_date' => 'date',
        'total_hours' => 'decimal:2',
        'attendees_count' => 'integer',
        'catering_needed' => 'boolean',
        'requested_at' => 'datetime',
        'approved_by_dept_head_at' => 'datetime',
        'approved_by_ga_admin_at' => 'datetime',
        'allocated_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];
    
    // Relationships
    public function meetingRoom()
    {
        return $this->belongsTo(MeetingRoom::class);
    }
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function consumptions()
    {
        return $this->hasMany(MeetingRoomConsumption::class, 'reservation_id');
    }
    
    // Boot
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($reservation) {
            $start = Carbon::parse($reservation->start_time);
            $end = Carbon::parse($reservation->end_time);
            $reservation->total_hours = $start->diffInMinutes($end) / 60;
        });
    }
}
```

### Controllers

**app/Http/Controllers/MeetingRoomController.php**
- CRUD for meeting rooms
- Room availability calendar
- Check availability for date/time

**app/Http/Controllers/MeetingRoomReservationController.php**
- Create reservation with availability check
- Add consumption items (supplies needed)
- Submit for approval
- Department Head approval
- GA Admin approval (final)
- Allocate meeting room
- Complete reservation
- Cancellation handling
- My reservations (employee portal)

### API Endpoints

```
# Meeting Rooms
GET     /api/v1/meeting-rooms               - List rooms
POST    /api/v1/meeting-rooms               - Create room
GET     /api/v1/meeting-rooms/{id}          - Get details
PUT     /api/v1/meeting-rooms/{id}          - Update room
DELETE  /api/v1/meeting-rooms/{id}          - Delete room
GET     /api/v1/meeting-rooms/available     - Available rooms
POST    /api/v1/meeting-rooms/check-availability - Check for date/time

# Meeting Room Reservations
GET     /api/v1/meeting-room-reservations   - List reservations
POST    /api/v1/meeting-room-reservations   - Create reservation
GET     /api/v1/meeting-room-reservations/{id} - Get details
PUT     /api/v1/meeting-room-reservations/{id} - Update reservation
POST    /api/v1/meeting-room-reservations/{id}/approve-dept-head - Dept approval
POST    /api/v1/meeting-room-reservations/{id}/approve-ga-admin  - GA approval
POST    /api/v1/meeting-room-reservations/{id}/allocate          - Allocate room
POST    /api/v1/meeting-room-reservations/{id}/complete          - Complete
POST    /api/v1/meeting-room-reservations/{id}/cancel            - Cancel
GET     /api/v1/meeting-room-reservations/calendar               - Calendar view
GET     /api/v1/meeting-room-reservations/my-reservations        - Employee portal
```

### Business Logic

**Dual Approval Workflow**
```
1. Employee creates reservation → Check availability → Status: pending
2. Submit for approval → Notify Department Head
3. Department Head reviews:
   - Approve → Status: approved_by_dept_head → Forward to GA Admin
   - Reject → Status: rejected → End
4. GA Admin reviews:
   - Approve → Status: approved_by_ga_admin
   - Reject → Status: rejected → End
5. GA allocates room → Status: allocated → Room blocked
6. Meeting completed → Status: completed → Room available
```

**Availability Logic**
- Check existing reservations for date and time overlap
- Support half-hour time slots
- Buffer time between meetings (e.g., 15 minutes for cleanup)
- Calendar visualization with color-coded status

**Supply Consumption Integration**
- Request supplies (markers, snacks, etc.) during reservation
- Integrate with Office Supplies Module
- Automatic supply deduction on allocation
- Track meeting room supply usage

---

## Common Integration Patterns

### 1. Approval Workflow Integration
All modules with approval workflows can leverage existing `approval_stages`, `approval_plans`, and `approval_stage_details` tables:

**Integration Example for Supply Request**
```php
// After supply request is submitted
$approvalPlan = ApprovalPlan::create([
    'document_type' => 'supply_request',
    'document_id' => $supplyRequest->id,
    'project_id' => $supplyRequest->project_id,
    'status' => 'pending'
]);

// Load approval stages for the project
$stages = ApprovalStage::where('project_id', $supplyRequest->project_id)
    ->where('document_type', 'supply_request')
    ->orderBy('approval_order')
    ->get();

// Create approval stage details
foreach ($stages as $stage) {
    ApprovalStageDetail::create([
        'approval_plan_id' => $approvalPlan->id,
        'stage_name' => $stage->stage_name,
        'approver_id' => $stage->approver_id,
        'approval_order' => $stage->approval_order,
        'status' => $stage->approval_order == 1 ? 'pending' : 'waiting'
    ]);
}
```

### 2. Letter Numbering Integration
Modules requiring form numbers can integrate with Letter Number system:

**Integration Example**
```php
// For Supply Request form number: 24SR-00001
$letterNumber = LetterNumber::requestNumber([
    'category_id' => $supplyRequestCategoryId,
    'subject_id' => $supplyRequestSubjectId,
    'project_id' => $request->project_id,
    'document_type' => 'supply_request',
    'document_id' => $supplyRequest->id
]);

$supplyRequest->update([
    'form_number' => $letterNumber->full_number
]);
```

### 3. Employee Self-Service Portal
All modules support employee portals using existing patterns:

**Routes Example**
```php
// Web Routes for Employee Portal
Route::middleware(['auth'])->group(function () {
    Route::get('/supply-requests/my-requests', [SupplyRequestController::class, 'myRequests']);
    Route::get('/room-reservations/my-reservations', [RoomReservationController::class, 'myReservations']);
    Route::get('/ticket-reservations/my-requests', [TicketReservationController::class, 'myRequests']);
    Route::get('/meeting-reservations/my-bookings', [MeetingRoomReservationController::class, 'myBookings']);
});
```

### 4. Notification System
Integrate with existing notification system for workflow notifications:

**Notification Example**
```php
// Send notification on approval
Notification::create([
    'user_id' => $approver->id,
    'type' => 'supply_request_approval',
    'title' => 'Supply Request Approval',
    'message' => "Supply request {$request->form_number} awaits your approval",
    'link' => route('supply-requests.show', $request->id),
    'read' => false
]);

// Send email
Mail::to($approver->email)->send(new SupplyRequestApprovalMail($request));
```

### 5. Export/Import Functionality
Follow existing Excel export/import patterns:

**Export Example**
```php
// app/Exports/SupplyExport.php
namespace App\Exports;

use App\Models\Supply;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SupplyExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Supply::all();
    }
    
    public function headings(): array
    {
        return ['Code', 'Name', 'Category', 'Unit', 'Stock', 'Min Stock', 'Status'];
    }
}

// In Controller
public function export()
{
    return Excel::download(new SupplyExport, 'supplies.xlsx');
}
```

---

## Implementation Roadmap

### Phase 1: Foundation (Week 1-2)
1. **Database Migrations**
   - Create all table migrations
   - Set up foreign keys and indexes
   - Create seeders for master data

2. **Models & Relationships**
   - Create all Eloquent models
   - Define relationships
   - Add UUID trait
   - Add scopes and accessors

3. **Basic CRUD Controllers**
   - Master data controllers (Supplies, Vehicles, Buildings, Meeting Rooms)
   - API endpoints
   - Basic validation

### Phase 2: Core Features (Week 3-4)
1. **Supply Request Module**
   - Request creation with items
   - Approval workflow
   - Distribution system
   - Stock transactions

2. **Vehicle Module**
   - Vehicle CRUD
   - Fuel tracking
   - Basic maintenance

3. **PMS Module**
   - Building and room CRUD
   - Basic reservation system

### Phase 3: Advanced Features (Week 5-6)
1. **Stock Opname System**
   - Opname sessions
   - Variance calculation
   - Stock adjustments

2. **Vehicle Advanced**
   - Document management with revisions
   - Maintenance scheduling
   - ArkFleet integration prep

3. **Room Reservations Advanced**
   - Availability calendar
   - Check-in/check-out workflow
   - Room maintenance

### Phase 4: Additional Modules (Week 7-8)
1. **Ticket Reservations**
   - Complete CRUD
   - Approval workflow
   - Document management

2. **Meeting Room Reservations**
   - Dual approval workflow
   - Supply consumption integration
   - Calendar visualization

### Phase 5: Integration & Polish (Week 9-10)
1. **System Integration**
   - Approval workflow integration
   - Letter numbering integration
   - Notification system
   - Employee portals

2. **Reports & Analytics**
   - Supply usage reports
   - Vehicle cost analysis
   - Room occupancy reports
   - Meeting room utilization

3. **Testing & Documentation**
   - Unit tests
   - Feature tests
   - API documentation
   - User manual

### Phase 6: Deployment & Training (Week 11-12)
1. **Deployment**
   - Production deployment
   - Data migration
   - Performance optimization

2. **Training**
   - User training sessions
   - Admin training
   - Documentation handover

---

## Technical Requirements

### Server Requirements
- Same as ARKA HERO core (Laravel 10.x compatible)
- PHP 8.1+
- MySQL 8.0+
- Storage for documents and images
- Cron for scheduled tasks (maintenance reminders, document expiry alerts)

### Additional Dependencies
```json
{
    "intervention/image": "^2.7", // For image processing (room photos, vehicle images)
    "simplesoftwareio/simple-qrcode": "^4.2", // For QR codes (room check-in, vehicle tracking)
    "spatie/laravel-backup": "^8.0" // For automated backups
}
```

### Queue Jobs
```php
// Jobs to be created
app/Jobs/
├── SendSupplyRequestApprovalNotification.php
├── SendLowStockAlert.php
├── SendDocumentExpiryReminder.php
├── SendMaintenanceReminder.php
├── SendRoomReservationConfirmation.php
└── SyncVehicleToArkFleet.php
```

---

## Permissions Structure

### Supply Management
```
ga.supplies.view
ga.supplies.create
ga.supplies.edit
ga.supplies.delete
ga.supplies.export
ga.supplies.import
ga.supply-requests.view-all
ga.supply-requests.view-own
ga.supply-requests.create
ga.supply-requests.approve-dept
ga.supply-requests.approve-ga
ga.supply-distributions.create
ga.supply-distributions.verify
ga.stock-opname.manage
```

### Vehicle Administration
```
ga.vehicles.view
ga.vehicles.create
ga.vehicles.edit
ga.vehicles.delete
ga.vehicles.assign
ga.fuel-records.view
ga.fuel-records.create
ga.vehicle-maintenance.manage
ga.vehicle-documents.manage
```

### Property Management
```
ga.buildings.manage
ga.rooms.manage
ga.room-reservations.view-all
ga.room-reservations.view-own
ga.room-reservations.create
ga.room-reservations.approve
ga.room-reservations.checkin
ga.room-maintenances.manage
```

### Ticket Reservations
```
ga.ticket-reservations.view-all
ga.ticket-reservations.view-own
ga.ticket-reservations.create
ga.ticket-reservations.approve
ga.ticket-reservations.book
```

### Meeting Rooms
```
ga.meeting-rooms.manage
ga.meeting-reservations.view-all
ga.meeting-reservations.view-own
ga.meeting-reservations.create
ga.meeting-reservations.approve-dept
ga.meeting-reservations.approve-ga
```

---

## Testing Strategy

### Unit Tests
- Model relationships
- Business logic calculations (stock, availability, etc.)
- Helper functions

### Feature Tests
- API endpoint responses
- CRUD operations
- Workflow transitions
- Approval processes

### Integration Tests
- Approval workflow integration
- Letter number integration
- Notification system
- Employee portal access

---

## Postman API Collection Structure

```
ARKA HERO - GA Modules
├── 1. Office Supplies
│   ├── Supplies Master
│   │   ├── GET List Supplies
│   │   ├── POST Create Supply
│   │   ├── GET Supply Details
│   │   ├── PUT Update Supply
│   │   └── DELETE Delete Supply
│   ├── Supply Requests
│   │   ├── GET List Requests
│   │   ├── POST Create Request
│   │   ├── POST Submit for Approval
│   │   ├── POST Approve by Dept Head
│   │   └── POST Approve by GA Admin
│   ├── Distributions
│   └── Stock Opname
│
├── 2. Vehicle Administration
│   ├── Vehicles
│   ├── Fuel Records
│   ├── Maintenance
│   └── Documents
│
├── 3. Property Management
│   ├── Buildings
│   ├── Rooms
│   ├── Room Reservations
│   └── Room Maintenance
│
├── 4. Ticket Reservations
│   ├── Create Reservation
│   ├── Approve Reservation
│   ├── Book Ticket
│   └── Upload Documents
│
└── 5. Meeting Room Reservations
    ├── Meeting Rooms
    ├── Create Reservation
    ├── Approve by Dept Head
    ├── Approve by GA Admin
    └── Add Consumptions
```

---

## Success Metrics

### Performance Metrics
- Page load time < 2 seconds
- API response time < 500ms
- DataTable rendering < 1 second for 1000+ records

### Business Metrics
- Supply request approval time < 24 hours
- Stock opname accuracy > 98%
- Vehicle document expiry compliance 100%
- Room occupancy rate tracking
- Meeting room utilization rate tracking

---

## Conclusion

This analysis provides a complete blueprint for developing 5 GA modules in ARKA HERO. All modules:
- Follow existing ARKA HERO patterns
- Integrate with core systems (Employees, Projects, Departments)
- Support approval workflows
- Provide self-service portals
- Include comprehensive API endpoints
- Maintain data integrity with proper relationships
- Support reporting and analytics

Implementation should be done iteratively, starting with foundation work and progressively adding advanced features. Each module can be developed and deployed independently while maintaining integration with the core system.

---

**Document Version**: 1.0  
**Last Updated**: 2026-01-14  
**Next Review**: After Phase 1 completion
