# Trainer Request APIs Documentation

## Overview
This document covers all API endpoints related to trainer requests in the Sports Club Pakistan platform. Trainer requests allow members to request training sessions with trainers, either generally or specifically.

## Base URL
```
/api
```

## Authentication
All protected endpoints require authentication using Laravel Sanctum tokens:
```
Authorization: Bearer {token}
```

## Role-Based Access Control

### Roles and Permissions:
- **Member**: Can create trainer requests for their memberships and view their own requests
- **Trainer**: Can view incoming trainer requests and accept/decline them
- **Admin/Owner**: Full access to all trainer request operations

## Public Endpoints
None - All trainer request endpoints require authentication.

## Protected Endpoints

### 1. Get User's Trainer Requests
**GET** `/trainer-requests`

Retrieves all trainer requests for the authenticated user. Requires member role.

#### Query Parameters:
- `status`: Filter by status (pending, accepted, declined, cancelled, expired)
- `page`: Page number for pagination
- `per_page`: Items per page (default 15)

#### Response:
```json
{
    "status": "success",
    "data": {
        "trainer_requests": [
            {
                "id": "0198feca-9216-72a4-a15e-1cb5bc5c233a",
                "user": {
                    "id": "0198fec6-96de-719f-a7c3-c21d51d8c827",
                    "name": "John Doe",
                    "email": "john@example.com"
                },
                "membership": {
                    "id": "0198fec6-96de-719f-a7c3-c21d51d8c826",
                    "membership_number": "SC-2025-001",
                    "status": "active"
                },
                "sport": {
                    "id": "0198fec6-96de-719f-a7c3-c21d51d8c828",
                    "name": "Fitness"
                },
                "tier": {
                    "id": "0198fec6-96de-719f-a7c3-c21d51d8c829",
                    "name": "Beginner"
                },
                "service": {
                    "id": "0198fec6-96de-719f-a7c3-c21d51d8c830",
                    "name": "Personal Training"
                },
                "request_type": "specific_trainer",
                "trainer_profile": {
                    "id": "0198fec6-96de-719f-a7c3-c21d51d8c831",
                    "user": {
                        "id": "0198fec6-96de-719f-a7c3-c21d51d8c832",
                        "name": "Trainer Ahmed"
                    },
                    "rating": 4.5
                },
                "club": {
                    "id": "0198fec6-96de-719f-a7c3-c21d51d8c833",
                    "name": "Elite Fitness Center",
                    "address": "123 Main St, Karachi"
                },
                "preferred_time_slots": [
                    {
                        "start": "10:00",
                        "end": "11:00"
                    }
                ],
                "message": "Focus on strength training",
                "status": "pending",
                "accepted_by_trainer": null,
                "accepted_at": null,
                "expires_at": "2025-09-14T00:00:00.000000Z",
                "created_at": "2025-09-07T12:00:00.000000Z",
                "updated_at": "2025-09-07T12:00:00.000000Z"
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

### 2. Create Trainer Request
**POST** `/trainer-requests`

Creates a new trainer request. Requires member role.

#### Request Body:
```json
{
    "membership_id": "0198fec6-96de-719f-a7c3-c21d51d8c826",
    "service_id": "0198fec6-96de-719f-a7c3-c21d51d8c830",
    "request_type": "specific_trainer",
    "trainer_profile_id": "0198fec6-96de-719f-a7c3-c21d51d8c831",
    "club_id": "0198fec6-96de-719f-a7c3-c21d51d8c833",
    "preferred_time_slots": [
        {
            "start": "10:00",
            "end": "11:00"
        }
    ],
    "message": "Focus on strength training"
}
```

#### Request Body for Open Request:
```json
2
```

#### Validation Rules:
- `membership_id`: Required, must exist and belong to the authenticated user
- `service_id`: Required, must exist
- `request_type`: Required, must be 'specific_trainer' or 'open_request'
- `trainer_profile_id`: Required if request_type is 'specific_trainer', must exist and match membership sport/tier
- `club_id`: Required if request_type is 'open_request', must exist
- `preferred_time_slots`: Required, array of time slots with start and end times
- `message`: Optional, string, max 1000 characters

#### Response:
```json
{
    "data": {
        "id": "0198feca-9216-72a4-a15e-1cb5bc5c233a",
        "user": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c827",
            "name": "John Doe",
            "email": "john@example.com"
        },
        "membership": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c826",
            "membership_number": "SC-2025-001",
            "status": "active"
        },
        "sport": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c828",
            "name": "Fitness"
        },
        "tier": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c829",
            "name": "Beginner"
        },
        "service": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c830",
            "name": "Personal Training"
        },
        "request_type": "specific_trainer",
        "trainer_profile": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c831",
            "user": {
                "id": "0198fec6-96de-719f-a7c3-c21d51d8c832",
                "name": "Trainer Ahmed"
            },
            "rating": 4.5
        },
        "club": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c833",
            "name": "Elite Fitness Center",
            "address": "123 Main St, Karachi"
        },
        "preferred_time_slots": [
            {
                "start": "10:00",
                "end": "11:00"
            }
        ],
        "message": "Focus on strength training",
        "status": "pending",
        "accepted_by_trainer": null,
        "accepted_at": null,
        "expires_at": "2025-09-14T00:00:00.000000Z",
        "created_at": "2025-09-07T12:00:00.000000Z",
        "updated_at": "2025-09-07T12:00:00.000000Z"
    }
}
```

#### Error Responses:
- `400 Bad Request`: Trainer sport/tier mismatch or invalid request data
- `404 Not Found`: Membership, service, trainer, or club not found
- `422 Unprocessable Entity`: Validation errors

### 3. Get Trainer Request Details
**GET** `/trainer-requests/{trainerRequest}`

Retrieves details of a specific trainer request. Access depends on role:
- Members can view their own requests
- Trainers can view requests assigned to them or open requests they can accept

#### Response:
```json
{
    "data": {
        "id": "0198feca-9216-72a4-a15e-1cb5bc5c233a",
        "user": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c827",
            "name": "John Doe",
            "email": "john@example.com"
        },
        "membership": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c826",
            "membership_number": "SC-2025-001",
            "status": "active"
        },
        "sport": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c828",
            "name": "Fitness"
        },
        "tier": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c829",
            "name": "Beginner"
        },
        "service": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c830",
            "name": "Personal Training"
        },
        "request_type": "specific_trainer",
        "trainer_profile": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c831",
            "user": {
                "id": "0198fec6-96de-719f-a7c3-c21d51d8c832",
                "name": "Trainer Ahmed"
            },
            "rating": 4.5
        },
        "club": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c833",
            "name": "Elite Fitness Center",
            "address": "123 Main St, Karachi"
        },
        "preferred_time_slots": [
            {
                "start": "10:00",
                "end": "11:00"
            }
        ],
        "message": "Focus on strength training",
        "status": "pending",
        "accepted_by_trainer": null,
        "accepted_at": null,
        "expires_at": "2025-09-14T00:00:00.000000Z",
        "created_at": "2025-09-07T12:00:00.000000Z",
        "updated_at": "2025-09-07T12:00:00.000000Z"
    }
}
```

### 4. Cancel Trainer Request
**PATCH** `/trainer-requests/{trainerRequest}/cancel`

Cancels a trainer request. Only the owner can cancel their pending requests.

#### Response:
```json
{
    "data": {
        "id": "0198feca-9216-72a4-a15e-1cb5bc5c233a",
        "status": "cancelled",
        "updated_at": "2025-09-07T12:30:00.000000Z"
    }
}
```

#### Error Responses:
- `400 Bad Request`: Request is not pending
- `403 Forbidden`: User is not the owner of the request

### 5. Get Incoming Trainer Requests (Trainer)
**GET** `/trainer/requests`

Retrieves incoming trainer requests for the authenticated trainer. Requires trainer role.

#### Query Parameters:
- `sport_id`: Filter by sport
- `page`: Page number for pagination
- `per_page`: Items per page (default 15)

#### Response:
```json
{
    "data": [
        {
            "id": "0198feca-9216-72a4-a15e-1cb5bc5c233a",
            "user": {
                "id": "0198fec6-96de-719f-a7c3-c21d51d8c827",
                "name": "John Doe",
                "email": "john@example.com"
            },
            "membership": {
                "id": "0198fec6-96de-719f-a7c3-c21d51d8c826",
                "membership_number": "SC-2025-001",
                "status": "active"
            },
            "sport": {
                "id": "0198fec6-96de-719f-a7c3-c21d51d8c828",
                "name": "Fitness"
            },
            "tier": {
                "id": "0198fec6-96de-719f-a7c3-c21d51d8c829",
                "name": "Beginner"
            },
            "service": {
                "id": "0198fec6-96de-719f-a7c3-c21d51d8c830",
                "name": "Personal Training"
            },
            "request_type": "open_request",
            "trainer_profile": null,
            "club": {
                "id": "0198fec6-96de-719f-a7c3-c21d51d8c833",
                "name": "Elite Fitness Center",
                "address": "123 Main St, Karachi"
            },
            "preferred_time_slots": [
                {
                    "start": "10:00",
                    "end": "11:00"
                }
            ],
            "message": "Looking for a fitness trainer",
            "status": "pending",
            "accepted_by_trainer": null,
            "accepted_at": null,
            "expires_at": "2025-09-14T00:00:00.000000Z",
            "created_at": "2025-09-07T12:00:00.000000Z",
            "updated_at": "2025-09-07T12:00:00.000000Z"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/trainer/requests?page=1",
        "last": "http://localhost:8000/api/trainer/requests?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/trainer/requests?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "path": "http://localhost:8000/api/trainer/requests",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

### 6. Accept Trainer Request (Trainer)
**PATCH** `/trainer/requests/{trainerRequest}/accept`

Accepts a trainer request. Requires trainer role. Uses database locking to prevent race conditions.

#### Response:
```json
{
    "data": {
        "id": "0198feca-9216-72a4-a15e-1cb5bc5c233a",
        "user": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c827",
            "name": "John Doe",
            "email": "john@example.com"
        },
        "membership": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c826",
            "membership_number": "SC-2025-001",
            "status": "active"
        },
        "sport": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c828",
            "name": "Fitness"
        },
        "tier": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c829",
            "name": "Beginner"
        },
        "service": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c830",
            "name": "Personal Training"
        },
        "request_type": "open_request",
        "trainer_profile": null,
        "club": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c833",
            "name": "Elite Fitness Center",
            "address": "123 Main St, Karachi"
        },
        "preferred_time_slots": [
            {
                "start": "10:00",
                "end": "11:00"
            }
        ],
        "message": "Looking for a fitness trainer",
        "status": "accepted",
        "accepted_by_trainer": {
            "id": "0198fec6-96de-719f-a7c3-c21d51d8c831",
            "user": {
                "id": "0198fec6-96de-719f-a7c3-c21d51d8c832",
                "name": "Trainer Ahmed"
            }
        },
        "accepted_at": "2025-09-07T12:30:00.000000Z",
        "expires_at": "2025-09-14T00:00:00.000000Z",
        "created_at": "2025-09-07T12:00:00.000000Z",
        "updated_at": "2025-09-07T12:30:00.000000Z"
    }
}
```

#### Error Responses:
- `400 Bad Request`: Request is no longer available or trainer not qualified
- `403 Forbidden`: Trainer cannot accept this request
- `404 Not Found`: Trainer profile not found

### 7. Decline Trainer Request (Trainer)
**PATCH** `/trainer/requests/{trainerRequest}/decline`

Declines a trainer request. Requires trainer role.

#### Request Body (Optional):
```json
{
    "reason": "Schedule conflict"
}
```

#### Response:
```json
{
    "data": {
        "id": "0198feca-9216-72a4-a15e-1cb5bc5c233a",
        "status": "declined",
        "updated_at": "2025-09-07T12:30:00.000000Z"
    }
}
```

#### Error Responses:
- `400 Bad Request`: Request is not pending
- `403 Forbidden`: Trainer cannot decline this request
- `404 Not Found`: Trainer profile not found

## Error Responses

### Common Error Format:
```json
{
    "message": "Error description"
}
```

### HTTP Status Codes:
- `200 OK`: Success
- `201 Created`: Resource created
- `400 Bad Request`: Invalid request or business logic error
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

## Request Types

### Specific Trainer Request
- Request is directed to a particular trainer
- `request_type`: "specific_trainer"
- Requires `trainer_profile_id`
- Trainer must match the membership's sport and tier

### Open Request
- Request is open to any qualified trainer
- `request_type`: "open_request"
- Requires `club_id`
- Any trainer with matching sport and tier can accept

## Status Flow

```
pending → accepted | declined | cancelled | expired
```

- **pending**: Initial status, waiting for trainer response
- **accepted**: Trainer has accepted the request
- **declined**: Trainer has declined the request
- **cancelled**: Member has cancelled the request
- **expired**: Request has expired without response (default 7 days)

## Best Practices

1. **Validation**: Always validate membership ownership and trainer qualifications
2. **Authorization**: Implement proper role-based access control
3. **Race Conditions**: Use database locking for accept operations
4. **Expiration**: Set reasonable expiration times for requests
5. **Notifications**: Send notifications when request status changes
6. **Audit Trail**: Log all status changes for accountability

## Examples

### Example 1: Create a specific trainer request
```bash
curl -X POST "https://api.sportsclub.pk/api/trainer-requests" \
  -H "Authorization: Bearer {member_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "membership_id": "0198fec6-96de-719f-a7c3-c21d51d8c826",
    "service_id": "0198fec6-96de-719f-a7c3-c21d51d8c830",
    "request_type": "specific_trainer",
    "trainer_profile_id": "0198fec6-96de-719f-a7c3-c21d51d8c831",
    "preferred_time_slots": [
      {
        "start": "10:00",
        "end": "11:00"
      }
    ],
    "message": "Focus on strength training"
  }'
```

### Example 2: Create an open trainer request
```bash
curl -X POST "https://api.sportsclub.pk/api/trainer-requests" \
  -H "Authorization: Bearer {member_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "membership_id": "0198fec6-96de-719f-a7c3-c21d51d8c826",
    "service_id": "0198fec6-96de-719f-a7c3-c21d51d8c830",
    "request_type": "open_request",
    "club_id": "0198fec6-96de-719f-a7c3-c21d51d8c833",
    "preferred_time_slots": [
      {
        "start": "14:00",
        "end": "15:00"
      }
    ],
    "message": "Looking for cardio training"
  }'
```

### Example 3: Get user's trainer requests
```bash
curl -X GET "https://api.sportsclub.pk/api/trainer-requests?status=pending" \
  -H "Authorization: Bearer {member_token}"
```

### Example 4: Accept trainer request (trainer)
```bash
curl -X PATCH "https://api.sportsclub.pk/api/trainer/requests/0198feca-9216-72a4-a15e-1cb5bc5c233a/accept" \
  -H "Authorization: Bearer {trainer_token}"
```

### Example 5: Get incoming requests (trainer)
```bash
curl -X GET "https://api.sportsclub.pk/api/trainer/requests" \
  -H "Authorization: Bearer {trainer_token}"
```

---

*This documentation is based on the TrainerRequestController implementation and follows the same structure as other API documentation files in the project.*
