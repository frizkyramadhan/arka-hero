# Approval System API Documentation

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Base URL and Endpoints](#base-url-and-endpoints)
4. [Approval Flows API](#approval-flows-api)
5. [Document Approvals API](#document-approvals-api)
6. [Delegation API](#delegation-api)
7. [Escalation API](#escalation-api)
8. [Analytics API](#analytics-api)
9. [Notifications API](#notifications-api)
10. [Error Handling](#error-handling)
11. [Rate Limiting](#rate-limiting)
12. [Usage Examples](#usage-examples)

## Overview

The Approval System API provides RESTful endpoints for managing approval workflows, document approvals, delegation, escalation, and analytics. The API follows REST principles and returns JSON responses.

### API Versioning

-   Current Version: `v1`
-   Base URL: `/api/v1/approvals`

### Response Format

All API responses follow this structure:

```json
{
    "success": true,
    "data": {},
    "message": "Operation completed successfully",
    "meta": {
        "timestamp": "2024-01-15T10:30:00Z",
        "version": "v1"
    }
}
```

## Authentication

### Bearer Token Authentication

All API requests require authentication using Bearer tokens.

```bash
Authorization: Bearer {your-access-token}
```

### Obtaining Access Token

```bash
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

Response:

```json
{
    "success": true,
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "Bearer",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "roles": ["approver", "hr_manager"]
        }
    }
}
```

### Token Refresh

```bash
POST /api/auth/refresh
Authorization: Bearer {current-token}
```

## Base URL and Endpoints

### Base URL

```
https://your-domain.com/api/v1/approvals
```

### Common Headers

```bash
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

## Approval Flows API

### Get All Approval Flows

```bash
GET /api/v1/approvals/flows
```

**Query Parameters:**

-   `status` (optional): Filter by status (active, inactive)
-   `document_type` (optional): Filter by document type
-   `page` (optional): Page number for pagination
-   `per_page` (optional): Items per page (default: 15)

**Response:**

```json
{
    "success": true,
    "data": {
        "flows": [
            {
                "id": 1,
                "name": "Employee Registration Approval",
                "document_type": "employee_registration",
                "status": "active",
                "description": "Standard approval process for new employee registrations",
                "stages": [
                    {
                        "id": 1,
                        "name": "HR Review",
                        "order": 1,
                        "approvers": ["hr_manager", "hr_supervisor"],
                        "time_limit": 48,
                        "escalation_enabled": true
                    }
                ],
                "created_at": "2024-01-15T10:30:00Z",
                "updated_at": "2024-01-15T10:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 1,
            "last_page": 1
        }
    }
}
```

### Get Specific Approval Flow

```bash
GET /api/v1/approvals/flows/{id}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Employee Registration Approval",
        "document_type": "employee_registration",
        "status": "active",
        "description": "Standard approval process for new employee registrations",
        "stages": [
            {
                "id": 1,
                "name": "HR Review",
                "order": 1,
                "approvers": ["hr_manager", "hr_supervisor"],
                "time_limit": 48,
                "escalation_enabled": true,
                "escalation_to": "supervisor"
            }
        ],
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T10:30:00Z"
    }
}
```

### Create Approval Flow

```bash
POST /api/v1/approvals/flows
Content-Type: application/json

{
    "name": "New Approval Flow",
    "document_type": "employee_registration",
    "description": "Custom approval process",
    "stages": [
        {
            "name": "Initial Review",
            "order": 1,
            "approvers": ["hr_manager"],
            "time_limit": 24,
            "escalation_enabled": true
        },
        {
            "name": "Final Approval",
            "order": 2,
            "approvers": ["department_head"],
            "time_limit": 48,
            "escalation_enabled": false
        }
    ]
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 2,
        "name": "New Approval Flow",
        "document_type": "employee_registration",
        "status": "active",
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T10:30:00Z"
    },
    "message": "Approval flow created successfully"
}
```

### Update Approval Flow

```bash
PUT /api/v1/approvals/flows/{id}
Content-Type: application/json

{
    "name": "Updated Approval Flow",
    "description": "Updated description",
    "stages": [
        {
            "id": 3,
            "name": "Updated Stage",
            "order": 1,
            "approvers": ["hr_manager", "hr_supervisor"],
            "time_limit": 36,
            "escalation_enabled": true
        }
    ]
}
```

### Delete Approval Flow

```bash
DELETE /api/v1/approvals/flows/{id}
```

**Response:**

```json
{
    "success": true,
    "message": "Approval flow deleted successfully"
}
```

## Document Approvals API

### Get Pending Approvals

```bash
GET /api/v1/approvals/pending
```

**Query Parameters:**

-   `document_type` (optional): Filter by document type
-   `stage` (optional): Filter by approval stage
-   `page` (optional): Page number
-   `per_page` (optional): Items per page

**Response:**

```json
{
    "success": true,
    "data": {
        "approvals": [
            {
                "id": 1,
                "document_id": 123,
                "document_type": "employee_registration",
                "document_title": "Employee Registration - John Doe",
                "stage": {
                    "id": 1,
                    "name": "HR Review",
                    "order": 1
                },
                "status": "pending",
                "submitted_at": "2024-01-15T09:00:00Z",
                "due_at": "2024-01-17T09:00:00Z",
                "is_overdue": false,
                "approver": {
                    "id": 1,
                    "name": "HR Manager",
                    "email": "hr@example.com"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 1,
            "last_page": 1
        }
    }
}
```

### Get Approval Details

```bash
GET /api/v1/approvals/{id}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "document_id": 123,
        "document_type": "employee_registration",
        "document_data": {
            "employee_name": "John Doe",
            "email": "john.doe@example.com",
            "position": "Software Developer",
            "department": "IT"
        },
        "flow": {
            "id": 1,
            "name": "Employee Registration Approval"
        },
        "stages": [
            {
                "id": 1,
                "name": "HR Review",
                "order": 1,
                "status": "pending",
                "approver": {
                    "id": 1,
                    "name": "HR Manager"
                },
                "due_at": "2024-01-17T09:00:00Z",
                "is_overdue": false
            },
            {
                "id": 2,
                "name": "Department Head Approval",
                "order": 2,
                "status": "waiting",
                "approver": null,
                "due_at": null,
                "is_overdue": false
            }
        ],
        "history": [
            {
                "id": 1,
                "action": "submitted",
                "user": {
                    "id": 5,
                    "name": "John Doe"
                },
                "timestamp": "2024-01-15T09:00:00Z",
                "comments": "Initial submission"
            }
        ],
        "overall_status": "pending",
        "created_at": "2024-01-15T09:00:00Z",
        "updated_at": "2024-01-15T09:00:00Z"
    }
}
```

### Approve Document

```bash
POST /api/v1/approvals/{id}/approve
Content-Type: application/json

{
    "comments": "Approved with minor changes",
    "notify_creator": true
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "status": "approved",
        "approved_by": {
            "id": 1,
            "name": "HR Manager"
        },
        "approved_at": "2024-01-15T10:30:00Z",
        "comments": "Approved with minor changes",
        "next_stage": {
            "id": 2,
            "name": "Department Head Approval"
        }
    },
    "message": "Document approved successfully"
}
```

### Reject Document

```bash
POST /api/v1/approvals/{id}/reject
Content-Type: application/json

{
    "comments": "Incomplete information provided",
    "notify_creator": true
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "status": "rejected",
        "rejected_by": {
            "id": 1,
            "name": "HR Manager"
        },
        "rejected_at": "2024-01-15T10:30:00Z",
        "comments": "Incomplete information provided"
    },
    "message": "Document rejected successfully"
}
```

### Add Comments

```bash
POST /api/v1/approvals/{id}/comments
Content-Type: application/json

{
    "comment": "Please provide additional documentation",
    "type": "request_info"
}
```

### Submit Document for Approval

```bash
POST /api/v1/approvals/submit
Content-Type: application/json

{
    "document_type": "employee_registration",
    "document_id": 123,
    "flow_id": 1,
    "metadata": {
        "employee_name": "John Doe",
        "position": "Software Developer"
    }
}
```

## Delegation API

### Get Active Delegations

```bash
GET /api/v1/approvals/delegations
```

**Response:**

```json
{
    "success": true,
    "data": {
        "delegations": [
            {
                "id": 1,
                "original_approver": {
                    "id": 1,
                    "name": "HR Manager"
                },
                "delegated_to": {
                    "id": 2,
                    "name": "HR Supervisor"
                },
                "start_date": "2024-01-15T00:00:00Z",
                "end_date": "2024-01-22T00:00:00Z",
                "reason": "Out of office",
                "status": "active",
                "created_at": "2024-01-15T08:00:00Z"
            }
        ]
    }
}
```

### Create Delegation

```bash
POST /api/v1/approvals/delegations
Content-Type: application/json

{
    "delegated_to_id": 2,
    "start_date": "2024-01-15T00:00:00Z",
    "end_date": "2024-01-22T00:00:00Z",
    "reason": "Out of office",
    "approval_types": ["employee_registration", "official_travel"]
}
```

### Update Delegation

```bash
PUT /api/v1/approvals/delegations/{id}
Content-Type: application/json

{
    "end_date": "2024-01-25T00:00:00Z",
    "reason": "Extended vacation"
}
```

### Cancel Delegation

```bash
DELETE /api/v1/approvals/delegations/{id}
```

### Get Delegated Approvals

```bash
GET /api/v1/approvals/delegated
```

## Escalation API

### Get Active Escalations

```bash
GET /api/v1/approvals/escalations
```

**Response:**

```json
{
    "success": true,
    "data": {
        "escalations": [
            {
                "id": 1,
                "approval_id": 1,
                "original_approver": {
                    "id": 1,
                    "name": "HR Manager"
                },
                "escalated_to": {
                    "id": 3,
                    "name": "Supervisor"
                },
                "reason": "Time limit exceeded",
                "escalated_at": "2024-01-17T09:00:00Z",
                "status": "active"
            }
        ]
    }
}
```

### Manually Escalate

```bash
POST /api/v1/approvals/{id}/escalate
Content-Type: application/json

{
    "escalated_to_id": 3,
    "reason": "Urgent approval required"
}
```

### Get Escalation History

```bash
GET /api/v1/approvals/{id}/escalations
```

## Analytics API

### Get Approval Statistics

```bash
GET /api/v1/approvals/analytics/stats
```

**Query Parameters:**

-   `date_range` (optional): last_7_days, last_30_days, last_90_days
-   `document_type` (optional): Filter by document type
-   `approver_id` (optional): Filter by specific approver

**Response:**

```json
{
    "success": true,
    "data": {
        "total_approvals": 150,
        "pending_approvals": 25,
        "approved_count": 120,
        "rejected_count": 5,
        "approval_rate": 96.0,
        "average_processing_time": 2.5,
        "escalation_rate": 8.0,
        "delegation_rate": 12.0,
        "by_document_type": {
            "employee_registration": {
                "total": 80,
                "approved": 75,
                "rejected": 3,
                "pending": 2
            },
            "official_travel": {
                "total": 70,
                "approved": 45,
                "rejected": 2,
                "pending": 23
            }
        },
        "by_approver": {
            "1": {
                "name": "HR Manager",
                "total": 50,
                "approved": 48,
                "rejected": 2,
                "average_time": 1.8
            }
        }
    }
}
```

### Get Bottleneck Analysis

```bash
GET /api/v1/approvals/analytics/bottlenecks
```

**Response:**

```json
{
    "success": true,
    "data": {
        "bottlenecks": [
            {
                "stage_id": 2,
                "stage_name": "Department Head Approval",
                "average_time": 4.2,
                "escalation_rate": 15.0,
                "pending_count": 12,
                "recommendations": [
                    "Add backup approvers",
                    "Reduce time limit",
                    "Provide additional training"
                ]
            }
        ]
    }
}
```

### Get Performance Trends

```bash
GET /api/v1/approvals/analytics/trends
```

**Query Parameters:**

-   `period` (optional): daily, weekly, monthly
-   `date_range` (optional): Custom date range

**Response:**

```json
{
    "success": true,
    "data": {
        "trends": [
            {
                "date": "2024-01-15",
                "submissions": 5,
                "approvals": 4,
                "rejections": 1,
                "average_time": 2.1
            }
        ]
    }
}
```

## Notifications API

### Get User Notifications

```bash
GET /api/v1/approvals/notifications
```

**Query Parameters:**

-   `type` (optional): approval, delegation, escalation
-   `read` (optional): true, false
-   `page` (optional): Page number

**Response:**

```json
{
    "success": true,
    "data": {
        "notifications": [
            {
                "id": 1,
                "type": "approval",
                "title": "New approval assigned",
                "message": "You have a new employee registration to approve",
                "data": {
                    "approval_id": 1,
                    "document_type": "employee_registration"
                },
                "read": false,
                "created_at": "2024-01-15T10:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 1,
            "last_page": 1
        }
    }
}
```

### Mark Notification as Read

```bash
PUT /api/v1/approvals/notifications/{id}/read
```

### Mark All Notifications as Read

```bash
PUT /api/v1/approvals/notifications/read-all
```

### Get Notification Settings

```bash
GET /api/v1/approvals/notifications/settings
```

### Update Notification Settings

```bash
PUT /api/v1/approvals/notifications/settings
Content-Type: application/json

{
    "email_notifications": true,
    "dashboard_notifications": true,
    "sms_notifications": false,
    "notification_types": {
        "approval": true,
        "delegation": true,
        "escalation": true
    }
}
```

## Error Handling

### Error Response Format

```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": {
            "field": ["The field is required."]
        }
    },
    "meta": {
        "timestamp": "2024-01-15T10:30:00Z",
        "request_id": "req_123456"
    }
}
```

### Common Error Codes

| Code                  | HTTP Status | Description              |
| --------------------- | ----------- | ------------------------ |
| `UNAUTHORIZED`        | 401         | Authentication required  |
| `FORBIDDEN`           | 403         | Insufficient permissions |
| `NOT_FOUND`           | 404         | Resource not found       |
| `VALIDATION_ERROR`    | 422         | Validation failed        |
| `RATE_LIMIT_EXCEEDED` | 429         | Too many requests        |
| `INTERNAL_ERROR`      | 500         | Server error             |

### Error Handling Examples

#### Validation Error

```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": {
            "document_type": ["The document type field is required."],
            "approvers": ["At least one approver must be specified."]
        }
    }
}
```

#### Permission Error

```json
{
    "success": false,
    "error": {
        "code": "FORBIDDEN",
        "message": "You do not have permission to perform this action."
    }
}
```

#### Resource Not Found

```json
{
    "success": false,
    "error": {
        "code": "NOT_FOUND",
        "message": "Approval flow not found."
    }
}
```

## Rate Limiting

### Rate Limit Headers

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1642233600
```

### Rate Limit Exceeded Response

```json
{
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "Too many requests. Please try again later."
    }
}
```

### Rate Limits by Endpoint

| Endpoint             | Limit | Window |
| -------------------- | ----- | ------ |
| GET endpoints        | 1000  | 1 hour |
| POST endpoints       | 100   | 1 hour |
| PUT/DELETE endpoints | 50    | 1 hour |
| Analytics endpoints  | 200   | 1 hour |

## Usage Examples

### Complete Approval Workflow

#### 1. Submit Document for Approval

```bash
curl -X POST https://your-domain.com/api/v1/approvals/submit \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "document_type": "employee_registration",
    "document_id": 123,
    "flow_id": 1,
    "metadata": {
      "employee_name": "John Doe",
      "position": "Software Developer"
    }
  }'
```

#### 2. Get Pending Approvals

```bash
curl -X GET "https://your-domain.com/api/v1/approvals/pending?document_type=employee_registration" \
  -H "Authorization: Bearer {token}"
```

#### 3. Approve Document

```bash
curl -X POST https://your-domain.com/api/v1/approvals/1/approve \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "comments": "Approved with minor changes",
    "notify_creator": true
  }'
```

### Delegation Workflow

#### 1. Create Delegation

```bash
curl -X POST https://your-domain.com/api/v1/approvals/delegations \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "delegated_to_id": 2,
    "start_date": "2024-01-15T00:00:00Z",
    "end_date": "2024-01-22T00:00:00Z",
    "reason": "Out of office",
    "approval_types": ["employee_registration"]
  }'
```

#### 2. Get Delegated Approvals

```bash
curl -X GET https://your-domain.com/api/v1/approvals/delegated \
  -H "Authorization: Bearer {token}"
```

### Analytics Usage

#### Get Statistics

```bash
curl -X GET "https://your-domain.com/api/v1/approvals/analytics/stats?date_range=last_30_days" \
  -H "Authorization: Bearer {token}"
```

#### Get Bottlenecks

```bash
curl -X GET https://your-domain.com/api/v1/approvals/analytics/bottlenecks \
  -H "Authorization: Bearer {token}"
```

### Error Handling Example

```javascript
// JavaScript example with error handling
async function approveDocument(approvalId, comments) {
    try {
        const response = await fetch(
            `/api/v1/approvals/${approvalId}/approve`,
            {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${token}`,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ comments }),
            }
        );

        const data = await response.json();

        if (!response.ok) {
            if (data.error.code === "FORBIDDEN") {
                throw new Error(
                    "You do not have permission to approve this document"
                );
            } else if (data.error.code === "VALIDATION_ERROR") {
                throw new Error("Invalid data provided");
            } else {
                throw new Error(data.error.message);
            }
        }

        return data.data;
    } catch (error) {
        console.error("Approval failed:", error.message);
        throw error;
    }
}
```

### PHP Example

```php
// PHP example with error handling
function approveDocument($approvalId, $comments) {
    $url = "https://your-domain.com/api/v1/approvals/{$approvalId}/approve";

    $data = [
        'comments' => $comments,
        'notify_creator' => true
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode !== 200) {
        if (isset($result['error']['code'])) {
            switch ($result['error']['code']) {
                case 'FORBIDDEN':
                    throw new Exception('Permission denied');
                case 'VALIDATION_ERROR':
                    throw new Exception('Invalid data provided');
                default:
                    throw new Exception($result['error']['message']);
            }
        }
        throw new Exception('Request failed');
    }

    return $result['data'];
}
```

---

_This API documentation is regularly updated. Check for the latest version online._
