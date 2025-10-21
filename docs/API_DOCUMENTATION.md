# ARKA HERO API Documentation

## Overview

Dokumentasi lengkap untuk semua endpoint API ARKA HERO yang dibuat berdasarkan route web yang ada. API ini menggunakan Laravel Sanctum untuk autentikasi dan mengikuti standar RESTful API.

## Base URL

```
http://localhost/arka-hero/api/v1
```

## Authentication

Semua endpoint (kecuali login) memerlukan token autentikasi Laravel Sanctum. Token harus dikirim dalam header:

```
Authorization: Bearer {your-token}
```

## Response Format

Semua response mengikuti format JSON standar:

```json
{
    "status": "success|error",
    "message": "Optional message",
    "data": "Response data"
}
```

---

## 1. Authentication API

### Login

**POST** `/auth/login`

**Request Body:**

```json
{
    "email": "admin@arka.co.id",
    "password": "admin"
}
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin",
            "email": "admin@arka.co.id"
        },
        "token": "1|abc123..."
    }
}
```

### Logout

**POST** `/auth/logout`

**Headers:** `Authorization: Bearer {token}`

### Get Current User

**GET** `/auth/user`

**Headers:** `Authorization: Bearer {token}`

---

## 2. Dashboard API

### Dashboard Statistics

**GET** `/dashboard/stats`

**Headers:** `Authorization: Bearer {token}`

### Pending Recommendations

**GET** `/dashboard/pending-recommendations`

### Pending Approvals

**GET** `/dashboard/pending-approvals`

### Pending Arrivals

**GET** `/dashboard/pending-arrivals`

### Pending Departures

**GET** `/dashboard/pending-departures`

### Employee Dashboard

**GET** `/dashboard/employees`

### Employees by Department

**GET** `/dashboard/employees-by-department`

### Employees by Project

**GET** `/dashboard/employees-by-project`

### Recent Employees

**GET** `/dashboard/recent-employees`

### Leave Management Dashboard

**GET** `/dashboard/leave-management/open-requests`
**GET** `/dashboard/leave-management/pending-cancellations`
**GET** `/dashboard/leave-management/paid-leave-without-docs`
**GET** `/dashboard/leave-management/stats`

---

## 3. Master Data API

### Banks

-   **GET** `/master/banks` - List all banks
-   **POST** `/master/banks` - Create new bank
-   **PUT** `/master/banks/{id}` - Update bank
-   **DELETE** `/master/banks/{id}` - Delete bank

### Religions

-   **GET** `/master/religions` - List all religions
-   **POST** `/master/religions` - Create new religion
-   **PUT** `/master/religions/{id}` - Update religion
-   **DELETE** `/master/religions/{id}` - Delete religion

### Positions

-   **GET** `/master/positions` - List all positions
-   **POST** `/master/positions` - Create new position
-   **PUT** `/master/positions/{id}` - Update position
-   **DELETE** `/master/positions/{id}` - Delete position
-   **POST** `/master/positions/import` - Import positions from Excel
-   **GET** `/master/positions/export` - Export positions to Excel

### Grades

-   **GET** `/master/grades` - List all grades
-   **POST** `/master/grades` - Create new grade
-   **PUT** `/master/grades/{id}` - Update grade
-   **DELETE** `/master/grades/{id}` - Delete grade
-   **POST** `/master/grades/{id}/toggle-status` - Toggle grade status

### Levels

-   **GET** `/master/levels` - List all levels
-   **POST** `/master/levels` - Create new level
-   **PUT** `/master/levels/{id}` - Update level
-   **DELETE** `/master/levels/{id}` - Delete level
-   **POST** `/master/levels/{id}/toggle-status` - Toggle level status

### Transportations

-   **GET** `/master/transportations` - List all transportations
-   **POST** `/master/transportations` - Create new transportation
-   **PUT** `/master/transportations/{id}` - Update transportation
-   **DELETE** `/master/transportations/{id}` - Delete transportation

### Accommodations

-   **GET** `/master/accommodations` - List all accommodations
-   **POST** `/master/accommodations` - Create new accommodation
-   **PUT** `/master/accommodations/{id}` - Update accommodation
-   **DELETE** `/master/accommodations/{id}` - Delete accommodation

---

## 4. Employee Management API

### Employees

-   **GET** `/employees` - List all employees
-   **POST** `/employees` - Create new employee
-   **GET** `/employees/{id}` - Get employee details
-   **PUT** `/employees/{id}` - Update employee
-   **DELETE** `/employees/{id}` - Delete employee
-   **POST** `/employees/import` - Import employees from Excel
-   **GET** `/employees/export` - Export employees to Excel
-   **GET** `/employees/{id}/print` - Print employee data

### Employee Sub-resources

#### Licenses

-   **GET** `/employees/{id}/licenses` - Get employee licenses
-   **POST** `/employees/{id}/licenses` - Add license
-   **DELETE** `/employees/{id}/licenses/{licenseId}` - Delete license

#### Insurances

-   **GET** `/employees/{id}/insurances` - Get employee insurances
-   **POST** `/employees/{id}/insurances` - Add insurance
-   **DELETE** `/employees/{id}/insurances/{insuranceId}` - Delete insurance

#### Families

-   **GET** `/employees/{id}/families` - Get employee families
-   **POST** `/employees/{id}/families` - Add family member
-   **DELETE** `/employees/{id}/families/{familyId}` - Delete family member

#### Courses

-   **GET** `/employees/{id}/courses` - Get employee courses
-   **POST** `/employees/{id}/courses` - Add course
-   **DELETE** `/employees/{id}/courses/{courseId}` - Delete course

#### Educations

-   **GET** `/employees/{id}/educations` - Get employee educations
-   **POST** `/employees/{id}/educations` - Add education
-   **DELETE** `/employees/{id}/educations/{educationId}` - Delete education

#### Job Experiences

-   **GET** `/employees/{id}/job-experiences` - Get employee job experiences
-   **POST** `/employees/{id}/job-experiences` - Add job experience
-   **DELETE** `/employees/{id}/job-experiences/{experienceId}` - Delete job experience

#### Banks

-   **GET** `/employees/{id}/banks` - Get employee banks
-   **POST** `/employees/{id}/banks` - Add bank account
-   **DELETE** `/employees/{id}/banks/{bankId}` - Delete bank account

#### Administrations

-   **GET** `/employees/{id}/administrations` - Get employee administrations
-   **POST** `/employees/{id}/administrations` - Add administration
-   **PUT** `/employees/{id}/administrations/{adminId}` - Update administration
-   **DELETE** `/employees/{id}/administrations/{adminId}` - Delete administration

#### Tax Identifications

-   **GET** `/employees/{id}/tax-identifications` - Get employee tax IDs
-   **POST** `/employees/{id}/tax-identifications` - Add tax ID
-   **DELETE** `/employees/{id}/tax-identifications/{taxId}` - Delete tax ID

#### Employee Bonds

-   **GET** `/employees/{id}/bonds` - Get employee bonds
-   **POST** `/employees/{id}/bonds` - Add bond
-   **PUT** `/employees/{id}/bonds/{bondId}` - Update bond
-   **DELETE** `/employees/{id}/bonds/{bondId}` - Delete bond
-   **PATCH** `/employees/{id}/bonds/{bondId}/complete` - Mark bond as completed

---

## 5. Official Travel API

### Official Travels

-   **GET** `/official-travels` - List all official travels
-   **POST** `/official-travels` - Create new official travel
-   **GET** `/official-travels/{id}` - Get official travel details
-   **PUT** `/official-travels/{id}` - Update official travel
-   **DELETE** `/official-travels/{id}` - Delete official travel
-   **POST** `/official-travels/{id}/submit` - Submit for approval
-   **POST** `/official-travels/{id}/arrival` - Mark arrival
-   **POST** `/official-travels/{id}/departure` - Mark departure
-   **PATCH** `/official-travels/{id}/close` - Close travel
-   **GET** `/official-travels/{id}/print` - Print travel document
-   **GET** `/official-travels/export` - Export travels to Excel

---

## 6. Recruitment API

### FPTK (Recruitment Requests)

-   **GET** `/recruitment/requests` - List all FPTKs
-   **POST** `/recruitment/requests` - Create new FPTK
-   **GET** `/recruitment/requests/{id}` - Get FPTK details
-   **PUT** `/recruitment/requests/{id}` - Update FPTK
-   **DELETE** `/recruitment/requests/{id}` - Delete FPTK
-   **POST** `/recruitment/requests/{id}/submit` - Submit for approval
-   **POST** `/recruitment/requests/{id}/acknowledge` - Acknowledge FPTK
-   **POST** `/recruitment/requests/{id}/approve-pm` - Approve by PM
-   **POST** `/recruitment/requests/{id}/approve-director` - Approve by Director
-   **GET** `/recruitment/requests/{id}/print` - Print FPTK

### Candidates

-   **GET** `/recruitment/candidates` - List all candidates
-   **POST** `/recruitment/candidates` - Create new candidate
-   **GET** `/recruitment/candidates/{id}` - Get candidate details
-   **PUT** `/recruitment/candidates/{id}` - Update candidate
-   **DELETE** `/recruitment/candidates/{id}` - Delete candidate
-   **POST** `/recruitment/candidates/{id}/apply-to-fptk` - Apply to FPTK
-   **POST** `/recruitment/candidates/{id}/blacklist` - Add to blacklist
-   **POST** `/recruitment/candidates/{id}/remove-from-blacklist` - Remove from blacklist
-   **GET** `/recruitment/candidates/{id}/download-cv` - Download CV
-   **DELETE** `/recruitment/candidates/{id}/delete-cv` - Delete CV
-   **GET** `/recruitment/candidates/{id}/print` - Print candidate data

### Sessions

-   **GET** `/recruitment/sessions` - List all sessions
-   **POST** `/recruitment/sessions` - Create new session
-   **GET** `/recruitment/sessions/{id}` - Get session details
-   **PUT** `/recruitment/sessions/{id}` - Update session
-   **DELETE** `/recruitment/sessions/{id}` - Delete session
-   **POST** `/recruitment/sessions/{id}/update-cv-review` - Update CV review
-   **POST** `/recruitment/sessions/{id}/update-psikotes` - Update psikotes
-   **POST** `/recruitment/sessions/{id}/update-tes-teori` - Update theory test
-   **POST** `/recruitment/sessions/{id}/update-interview` - Update interview
-   **POST** `/recruitment/sessions/{id}/update-offering` - Update offering
-   **POST** `/recruitment/sessions/{id}/update-mcu` - Update MCU
-   **POST** `/recruitment/sessions/{id}/update-hiring` - Update hiring
-   **POST** `/recruitment/sessions/{id}/close-request` - Close request

---

## 7. Leave Management API

### Leave Requests

-   **GET** `/leave/requests` - List all leave requests
-   **POST** `/leave/requests` - Create new leave request
-   **GET** `/leave/requests/{id}` - Get leave request details
-   **PUT** `/leave/requests/{id}` - Update leave request
-   **DELETE** `/leave/requests/{id}` - Delete leave request
-   **POST** `/leave/requests/{id}/approve` - Approve leave request
-   **POST** `/leave/requests/{id}/reject` - Reject leave request
-   **POST** `/leave/requests/{id}/close` - Close leave request
-   **POST** `/leave/requests/{id}/cancellation` - Request cancellation
-   **POST** `/leave/cancellations/{cancellationId}/approve` - Approve cancellation
-   **POST** `/leave/cancellations/{cancellationId}/reject` - Reject cancellation
-   **GET** `/leave/requests/{id}/download` - Download leave document
-   **POST** `/leave/requests/{id}/upload` - Upload leave document
-   **DELETE** `/leave/requests/{id}/delete-document` - Delete leave document

### Leave Entitlements

-   **GET** `/leave/entitlements` - List all entitlements
-   **POST** `/leave/entitlements` - Create new entitlement
-   **GET** `/leave/entitlements/{id}` - Get entitlement details
-   **PUT** `/leave/entitlements/{id}` - Update entitlement
-   **DELETE** `/leave/entitlements/{id}` - Delete entitlement
-   **POST** `/leave/entitlements/generate-project` - Generate project entitlements
-   **POST** `/leave/entitlements/clear-entitlements` - Clear all entitlements
-   **GET** `/leave/entitlements/employee/{employeeId}` - Get employee entitlements
-   **PUT** `/leave/entitlements/employee/{employeeId}` - Update employee entitlements

### Leave Types

-   **GET** `/leave/types` - List all leave types
-   **POST** `/leave/types` - Create new leave type
-   **GET** `/leave/types/{id}` - Get leave type details
-   **PUT** `/leave/types/{id}` - Update leave type
-   **DELETE** `/leave/types/{id}` - Delete leave type
-   **POST** `/leave/types/{id}/toggle-status` - Toggle leave type status

---

## 8. User Management API

### Users

-   **GET** `/users` - List all users
-   **POST** `/users` - Create new user
-   **GET** `/users/{id}` - Get user details
-   **PUT** `/users/{id}` - Update user
-   **DELETE** `/users/{id}` - Delete user

### Roles

-   **GET** `/roles` - List all roles
-   **POST** `/roles` - Create new role
-   **GET** `/roles/{id}` - Get role details
-   **PUT** `/roles/{id}` - Update role
-   **DELETE** `/roles/{id}` - Delete role

### Permissions

-   **GET** `/permissions` - List all permissions
-   **POST** `/permissions` - Create new permission
-   **GET** `/permissions/{id}` - Get permission details
-   **PUT** `/permissions/{id}` - Update permission
-   **DELETE** `/permissions/{id}` - Delete permission

---

## 9. Termination API

### Terminations

-   **GET** `/terminations` - List all terminations
-   **POST** `/terminations` - Create new termination
-   **GET** `/terminations/{id}` - Get termination details
-   **PUT** `/terminations/{id}` - Update termination
-   **DELETE** `/terminations/{id}` - Delete termination
-   **POST** `/terminations/mass-termination` - Mass termination

---

## 10. Bond Violation API

### Bond Violations

-   **GET** `/bond-violations` - List all bond violations
-   **POST** `/bond-violations` - Create new bond violation
-   **GET** `/bond-violations/{id}` - Get bond violation details
-   **PUT** `/bond-violations/{id}` - Update bond violation
-   **DELETE** `/bond-violations/{id}` - Delete bond violation
-   **POST** `/bond-violations/calculate-penalty` - Calculate penalty

---

## 11. Email API

### Emails

-   **GET** `/emails` - List all emails
-   **POST** `/emails/send` - Send email

---

## 12. Employee Registration Admin API

### Employee Registrations

-   **GET** `/employee-registrations` - List all registrations
-   **GET** `/employee-registrations/pending` - Get pending registrations
-   **GET** `/employee-registrations/tokens` - Get registration tokens
-   **POST** `/employee-registrations/invite` - Send invitation
-   **POST** `/employee-registrations/bulk-invite` - Bulk invite
-   **POST** `/employee-registrations/tokens/{tokenId}/resend` - Resend invitation
-   **DELETE** `/employee-registrations/tokens/{tokenId}` - Delete token
-   **GET** `/employee-registrations/stats` - Get registration stats
-   **POST** `/employee-registrations/cleanup` - Cleanup expired tokens
-   **GET** `/employee-registrations/{id}` - Get registration details
-   **POST** `/employee-registrations/{id}/approve` - Approve registration
-   **POST** `/employee-registrations/{id}/reject` - Reject registration

---

## Error Handling

### Common Error Responses

#### 401 Unauthorized

```json
{
    "status": "error",
    "message": "Unauthenticated."
}
```

#### 403 Forbidden

```json
{
    "status": "error",
    "message": "This action is unauthorized."
}
```

#### 404 Not Found

```json
{
    "status": "error",
    "message": "Resource not found."
}
```

#### 422 Validation Error

```json
{
    "status": "error",
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["The field name field is required."]
    }
}
```

#### 500 Server Error

```json
{
    "status": "error",
    "message": "Internal server error."
}
```

---

## Rate Limiting

API menggunakan rate limiting untuk mencegah abuse:

-   **Authentication endpoints**: 10 requests per minute
-   **Other endpoints**: 60 requests per minute

---

## Pagination

Untuk endpoint yang mengembalikan list data, gunakan parameter query:

-   `page`: Nomor halaman (default: 1)
-   `per_page`: Jumlah item per halaman (default: 15, max: 100)

**Response format dengan pagination:**

```json
{
    "status": "success",
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 15,
        "total": 150
    }
}
```

---

## Filtering & Searching

Banyak endpoint mendukung filtering dan searching menggunakan parameter query:

**Contoh untuk Employee API:**

```
GET /employees?search=john&department=IT&status=active&page=1&per_page=20
```

**Contoh untuk Leave Requests API:**

```
GET /leave/requests?employee_id=123&status=pending&leave_type_id=1&date_from=2024-01-01&date_to=2024-12-31
```

---

## File Upload

Untuk endpoint yang menerima file upload (seperti CV, dokumen), gunakan `multipart/form-data`:

**Contoh untuk Candidate CV Upload:**

```javascript
const formData = new FormData();
formData.append("cv", file);
formData.append("candidate_id", 123);

fetch("/api/v1/recruitment/candidates/123/cv", {
    method: "POST",
    headers: {
        Authorization: "Bearer " + token,
    },
    body: formData,
});
```

---

## Testing

### Using Postman

1. Import collection dengan base URL: `http://localhost/arka-hero/api/v1`
2. Set environment variable `token` dengan token dari login
3. Gunakan `{{token}}` di Authorization header

### Using cURL

```bash
# Login
curl -X POST http://localhost/arka-hero/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@arka.co.id","password":"admin"}'

# Get employees (with token)
curl -X GET http://localhost/arka-hero/api/v1/employees \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## Notes

1. **Authentication**: Semua endpoint kecuali login memerlukan token autentikasi
2. **Content-Type**: Gunakan `application/json` untuk request body
3. **Accept**: Gunakan `application/json` untuk response
4. **CORS**: API mendukung CORS untuk frontend applications
5. **Versioning**: API menggunakan versioning dengan prefix `/v1`
6. **Middleware**: Semua endpoint menggunakan middleware `auth:sanctum` kecuali yang dikecualikan

---

## Support

Untuk pertanyaan atau bantuan teknis, silakan hubungi tim development ARKA HERO.
