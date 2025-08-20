# Trainer APIs Documentation

## Overview
This document covers all API endpoints related to trainer management including availability, locations, specialties, sessions, and certifications for the Sports Club Pakistan platform.

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
- **Public**: Can view verified trainer data (limited access)
- **Member**: Can view trainer profiles and book sessions
- **Trainer**: Can manage their own data and availability
- **Admin/Owner**: Full access to all trainer operations

---

# Trainer Availability APIs

## 1. Get All Availability Slots
**GET** `/trainer-availability`

Returns a paginated list of trainer availability slots.

### Query Parameters:
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| trainer_profile_id | UUID | Filter by trainer | `trainer_profile_id=123e4567-e89b-12d3-a456-426614174000` |
| day_of_week | string | Filter by day | `day_of_week=Monday` |
| is_available | boolean | Filter by availability status | `is_available=true` |
| sort_by | string | Sort field | `sort_by=day_of_week` |
| sort_order | string | Sort direction | `sort_order=asc` |
| per_page | integer | Items per page | `per_page=15` |

### Response:
```json
{
    "status": "success",
    "data": {
        "availabilities": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
                "day_of_week": "Monday",
                "start_time": "09:00",
                "end_time": "12:00",
                "is_available": true,
                "created_at": "2025-08-18T10:00:00.000000Z",
                "updated_at": "2025-08-18T10:00:00.000000Z",
                "trainer_profile": {
                    "user": {
                        "id": "123e4567-e89b-12d3-a456-426614174002",
                        "name": "Ahmed Khan",
                        "email": "ahmed@example.com"
                    }
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 15,
            "total": 75
        }
    }
}
```

## 2. Create Availability Slot
**POST** `/trainer-availability`

Creates a new availability slot for a trainer.

### Request Body:
```json
{
    "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
    "day_of_week": "Monday",
    "start_time": "09:00",
    "end_time": "12:00",
    "is_available": true
}
```

### Validation Rules:
- `trainer_profile_id`: required, exists in trainer_profiles table
- `day_of_week`: required, enum: Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday
- `start_time`: required, format: H:i (24-hour format)
- `end_time`: required, format: H:i, must be after start_time
- `is_available`: optional, boolean

## 3. Get Availability Slot Details
**GET** `/trainer-availability/{availability_id}`

Returns details of a specific availability slot.

## 4. Update Availability Slot
**PUT** `/trainer-availability/{availability_id}`

Updates an existing availability slot.

## 5. Delete Availability Slot
**DELETE** `/trainer-availability/{availability_id}`

Deletes an availability slot.

## 6. Get Trainer's Availability
**GET** `/trainer-availability/trainer/{trainer_profile_id}`

Returns all availability slots for a specific trainer.

### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| day_of_week | string | Filter by specific day |
| is_available | boolean | Filter by availability status |

## 7. Get Weekly Schedule
**GET** `/trainer-availability/trainer/{trainer_profile_id}/weekly-schedule`

Returns a structured weekly schedule for a trainer.

### Response:
```json
{
    "status": "success",
    "data": {
        "trainer_profile": {
            "id": "123e4567-e89b-12d3-a456-426614174001",
            "user_id": "123e4567-e89b-12d3-a456-426614174002"
        },
        "weekly_schedule": {
            "Monday": [
                {
                    "id": "123e4567-e89b-12d3-a456-426614174000",
                    "start_time": "09:00",
                    "end_time": "12:00",
                    "is_available": true
                }
            ],
            "Tuesday": [],
            "Wednesday": [...],
            "Thursday": [...],
            "Friday": [...],
            "Saturday": [...],
            "Sunday": [...]
        }
    }
}
```

## 8. Bulk Update Availability Status
**POST** `/trainer-availability/bulk-update-status`

Updates availability status for multiple slots.

### Request Body:
```json
{
    "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
    "is_available": false,
    "availability_ids": ["id1", "id2", "id3"],
    "day_of_week": "Monday"
}
```

---

# Trainer Location APIs

## 1. Get All Locations
**GET** `/trainer-locations`

Returns a paginated list of trainer locations.

### Query Parameters:
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| trainer_profile_id | UUID | Filter by trainer | `trainer_profile_id=123e4567...` |
| location_type | enum | Filter by type | `location_type=gym` |
| city | string | Filter by city | `city=Karachi` |
| area | string | Filter by area | `area=DHA` |
| latitude | decimal | User latitude for distance | `latitude=24.8607` |
| longitude | decimal | User longitude for distance | `longitude=67.0011` |
| radius | integer | Search radius in km | `radius=10` |
| sort_by | string | Sort field | `sort_by=distance` |
| per_page | integer | Items per page | `per_page=15` |

### Response:
```json
{
    "status": "success",
    "data": {
        "locations": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
                "location_name": "Gold's Gym DHA",
                "location_type": "gym",
                "address": "Main Boulevard, DHA Phase 5",
                "city": "Karachi",
                "area": "DHA",
                "latitude": 24.8607,
                "longitude": 67.0011,
                "is_primary": true,
                "distance_km": 2.5,
                "created_at": "2025-08-18T10:00:00.000000Z",
                "trainer_profile": {
                    "user": {
                        "id": "123e4567-e89b-12d3-a456-426614174002",
                        "name": "Ahmed Khan",
                        "email": "ahmed@example.com"
                    }
                }
            }
        ],
        "pagination": {...}
    }
}
```

## 2. Create Location
**POST** `/trainer-locations`

Creates a new location for a trainer.

### Request Body:
```json
{
    "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
    "location_name": "Gold's Gym DHA",
    "location_type": "gym",
    "address": "Main Boulevard, DHA Phase 5",
    "city": "Karachi",
    "area": "DHA",
    "latitude": 24.8607,
    "longitude": 67.0011,
    "is_primary": true
}
```

### Validation Rules:
- `trainer_profile_id`: required, exists in trainer_profiles table
- `location_name`: required, string, max: 255
- `location_type`: required, enum: gym,outdoor,home,client_location,online
- `address`: optional, string, max: 500
- `city`: required, string, max: 100
- `area`: optional, string, max: 100
- `latitude`: required, numeric, between: -90,90
- `longitude`: required, numeric, between: -180,180
- `is_primary`: optional, boolean

## 3. Get Location Details
**GET** `/trainer-locations/{location_id}`

Returns details of a specific location with optional distance calculation.

### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| latitude | decimal | User latitude for distance calculation |
| longitude | decimal | User longitude for distance calculation |

## 4. Update Location
**PUT** `/trainer-locations/{location_id}`

Updates an existing location.

## 5. Delete Location
**DELETE** `/trainer-locations/{location_id}`

Deletes a location. If primary location is deleted, another location becomes primary automatically.

## 6. Get Trainer's Locations
**GET** `/trainer-locations/trainer/{trainer_profile_id}`

Returns all locations for a specific trainer.

### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| location_type | enum | Filter by location type |
| latitude | decimal | For distance calculation |
| longitude | decimal | For distance calculation |

## 7. Find Nearby Trainers
**GET** `/trainer-locations/nearby`

Finds trainer locations within a specified radius.

### Query Parameters:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| latitude | decimal | Yes | Search center latitude |
| longitude | decimal | Yes | Search center longitude |
| radius | integer | No | Search radius in km (default: 10) |
| location_type | enum | No | Filter by location type |
| limit | integer | No | Maximum results (default: 20) |

### Response:
```json
{
    "status": "success",
    "data": {
        "search_center": {
            "latitude": 24.8607,
            "longitude": 67.0011,
            "radius_km": 10
        },
        "nearby_locations": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "location_name": "Gold's Gym DHA",
                "distance_km": 2.5,
                "trainer_profile": {
                    "user": {
                        "name": "Ahmed Khan"
                    }
                }
            }
        ],
        "count": 15
    }
}
```

## 8. Set Primary Location
**POST** `/trainer-locations/{location_id}/set-primary`

Sets a location as the primary location for a trainer.

## 9. Get Location Statistics
**GET** `/trainer-locations/statistics`

Returns statistics about trainer locations.

### Response:
```json
{
    "status": "success",
    "data": {
        "statistics": {
            "total_locations": 150,
            "by_type": {
                "gym": 75,
                "outdoor": 30,
                "home": 25,
                "client_location": 15,
                "online": 5
            },
            "by_city": {
                "Karachi": 60,
                "Lahore": 45,
                "Islamabad": 30,
                "Rawalpindi": 15
            },
            "primary_locations": 50
        }
    }
}
```

---

# Trainer Specialty APIs

## 1. Get All Specialties
**GET** `/trainer-specialties`

Returns a paginated list of trainer specialties.

### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| trainer_profile_id | UUID | Filter by trainer |
| search | string | Search in specialty names |
| sort_by | string | Sort field (default: specialty) |
| sort_order | string | Sort direction |
| per_page | integer | Items per page |

## 2. Create Specialty
**POST** `/trainer-specialties`

Creates a new specialty for a trainer.

### Request Body:
```json
{
    "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
    "specialty": "Weight Loss"
}
```

### Validation Rules:
- `trainer_profile_id`: required, exists in trainer_profiles table
- `specialty`: required, string, max: 100

## 3. Get Specialty Details
**GET** `/trainer-specialties/{specialty_id}`

Returns details of a specific specialty.

## 4. Update Specialty
**PUT** `/trainer-specialties/{specialty_id}`

Updates an existing specialty.

## 5. Delete Specialty
**DELETE** `/trainer-specialties/{specialty_id}`

Deletes a specialty.

## 6. Get Trainer's Specialties
**GET** `/trainer-specialties/trainer/{trainer_profile_id}`

Returns all specialties for a specific trainer.

## 7. Get Popular Specialties
**GET** `/trainer-specialties/popular`

Returns most popular specialties across all trainers.

### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| limit | integer | Number of results (default: 10) |

### Response:
```json
{
    "status": "success",
    "data": {
        "popular_specialties": [
            {
                "specialty": "Weight Loss",
                "trainer_count": 25
            },
            {
                "specialty": "Strength Training",
                "trainer_count": 20
            }
        ]
    }
}
```

## 8. Bulk Create Specialties
**POST** `/trainer-specialties/bulk`

Creates multiple specialties for a trainer at once.

### Request Body:
```json
{
    "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
    "specialties": ["Weight Loss", "Strength Training", "Cardio"]
}
```

### Response:
```json
{
    "status": "success",
    "message": "Specialties processed successfully",
    "data": {
        "added_specialties": [...],
        "duplicates": ["Cardio"],
        "added_count": 2,
        "duplicate_count": 1
    }
}
```

---

# Trainer Session APIs

## 1. Get All Sessions
**GET** `/trainer-sessions`

Returns a paginated list of trainer sessions.

### Query Parameters:
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| trainer_profile_id | UUID | Filter by trainer | `trainer_profile_id=123e4567...` |
| client_user_id | UUID | Filter by client | `client_user_id=123e4567...` |
| status | enum | Filter by status | `status=completed` |
| start_date | date | Filter from date | `start_date=2025-01-01` |
| end_date | date | Filter to date | `end_date=2025-12-31` |
| sport_id | UUID | Filter by sport | `sport_id=123e4567...` |
| sort_by | string | Sort field | `sort_by=session_date` |
| sort_order | string | Sort direction | `sort_order=desc` |
| per_page | integer | Items per page | `per_page=15` |

### Response:
```json
{
    "status": "success",
    "data": {
        "sessions": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
                "client_user_id": "123e4567-e89b-12d3-a456-426614174002",
                "sport_id": "123e4567-e89b-12d3-a456-426614174003",
                "session_date": "2025-08-20",
                "start_time": "10:00",
                "end_time": "11:00",
                "session_fee": "75.00",
                "status": "scheduled",
                "notes": "Focus on upper body strength",
                "rating": null,
                "feedback": null,
                "created_at": "2025-08-18T10:00:00.000000Z",
                "trainer_profile": {
                    "user": {
                        "id": "123e4567-e89b-12d3-a456-426614174001",
                        "name": "Ahmed Khan",
                        "email": "ahmed@example.com"
                    }
                },
                "client_user": {
                    "id": "123e4567-e89b-12d3-a456-426614174002",
                    "name": "Ali Hassan",
                    "email": "ali@example.com"
                },
                "sport": {
                    "id": "123e4567-e89b-12d3-a456-426614174003",
                    "name": "fitness"
                }
            }
        ],
        "pagination": {...}
    }
}
```

## 2. Create Session
**POST** `/trainer-sessions`

Creates a new training session.

### Request Body:
```json
{
    "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
    "client_user_id": "123e4567-e89b-12d3-a456-426614174002",
    "sport_id": "123e4567-e89b-12d3-a456-426614174003",
    "session_date": "2025-08-20",
    "start_time": "10:00",
    "end_time": "11:00",
    "session_fee": 75.00,
    "notes": "Focus on upper body strength"
}
```

### Validation Rules:
- `trainer_profile_id`: required, exists in trainer_profiles table
- `client_user_id`: required, exists in users table
- `sport_id`: required, exists in sports table
- `session_date`: required, date, after: today
- `start_time`: required, format: H:i
- `end_time`: required, format: H:i, after: start_time
- `session_fee`: required, numeric, min: 0
- `notes`: optional, string, max: 1000

## 3. Get Session Details
**GET** `/trainer-sessions/{session_id}`

Returns details of a specific session.

## 4. Update Session
**PUT** `/trainer-sessions/{session_id}`

Updates an existing session.

### Request Body:
```json
{
    "session_date": "2025-08-21",
    "start_time": "11:00",
    "end_time": "12:00",
    "status": "completed",
    "rating": 5,
    "feedback": "Excellent session!"
}
```

### Authorization Notes:
- Trainers can update: session details, status
- Clients can update: rating, feedback
- Admins can update: all fields

## 5. Delete Session
**DELETE** `/trainer-sessions/{session_id}`

Deletes a session (admin/trainer only).

## 6. Get Trainer's Sessions
**GET** `/trainer-sessions/trainer/{trainer_profile_id}`

Returns all sessions for a specific trainer.

### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| status | enum | Filter by status |
| start_date | date | Filter from date |
| end_date | date | Filter to date |
| per_page | integer | Items per page |

## 7. Get Client's Sessions
**GET** `/trainer-sessions/my-sessions`

Returns sessions for the authenticated client.

### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| client_user_id | UUID | Specific client (admin only) |
| status | enum | Filter by status |
| start_date | date | Filter from date |
| end_date | date | Filter to date |

## 8. Cancel Session
**POST** `/trainer-sessions/{session_id}/cancel`

Cancels a session.

### Request Body:
```json
{
    "cancellation_reason": "Emergency came up"
}
```

### Response:
```json
{
    "status": "success",
    "message": "Session cancelled successfully",
    "data": {
        "session": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "status": "cancelled",
            "notes": "Previous notes\n\nCancellation reason: Emergency came up"
        }
    }
}
```

## 9. Complete Session
**POST** `/trainer-sessions/{session_id}/complete`

Marks a session as completed (trainer/admin only).

## 10. Get Session Statistics
**GET** `/trainer-sessions/statistics`

Returns session statistics.

### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| trainer_profile_id | UUID | Filter by specific trainer |

### Response:
```json
{
    "status": "success",
    "data": {
        "statistics": {
            "total_sessions": 500,
            "by_status": {
                "completed": 350,
                "scheduled": 100,
                "cancelled": 30,
                "no_show": 20
            },
            "completed_sessions": 350,
            "average_rating": 4.3,
            "total_revenue": "26250.00"
        }
    }
}
```

---

# Trainer Certification APIs

## 1. Get All Certifications
**GET** `/trainer-certifications`

Returns a paginated list of trainer certifications.

### Query Parameters:
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| trainer_profile_id | UUID | Filter by trainer | `trainer_profile_id=123e4567...` |
| verified | boolean | Filter by verification status | `verified=true` |
| expired_only | boolean | Show only expired | `expired_only=true` |
| valid_only | boolean | Show only valid | `valid_only=true` |
| expiring_soon | boolean | Expiring within 30 days | `expiring_soon=true` |
| search | string | Search in name/organization | `search=ACSM` |
| sort_by | string | Sort field | `sort_by=created_at` |
| sort_order | string | Sort direction | `sort_order=desc` |
| per_page | integer | Items per page | `per_page=15` |

### Response:
```json
{
    "status": "success",
    "data": {
        "certifications": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
                "certification_name": "Certified Personal Trainer",
                "issuing_organization": "ACSM",
                "issue_date": "2023-01-15",
                "expiry_date": "2026-01-15",
                "certificate_url": "https://example.com/cert.pdf",
                "is_verified": true,
                "created_at": "2025-08-18T10:00:00.000000Z",
                "trainer_profile": {
                    "user": {
                        "id": "123e4567-e89b-12d3-a456-426614174002",
                        "name": "Ahmed Khan",
                        "email": "ahmed@example.com"
                    }
                }
            }
        ],
        "pagination": {...}
    }
}
```

## 2. Create Certification
**POST** `/trainer-certifications`

Creates a new certification for a trainer.

### Request Body:
```json
{
    "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
    "certification_name": "Certified Personal Trainer",
    "issuing_organization": "ACSM",
    "issue_date": "2023-01-15",
    "expiry_date": "2026-01-15",
    "certificate_url": "https://example.com/cert.pdf"
}
```

### Validation Rules:
- `trainer_profile_id`: required, exists in trainer_profiles table
- `certification_name`: required, string, max: 200
- `issuing_organization`: optional, string, max: 200
- `issue_date`: optional, date, before_or_equal: today
- `expiry_date`: optional, date, after: issue_date
- `certificate_url`: optional, url, max: 500

## 3. Get Certification Details
**GET** `/trainer-certifications/{certification_id}`

Returns details of a specific certification.

## 4. Update Certification
**PUT** `/trainer-certifications/{certification_id}`

Updates an existing certification.

## 5. Delete Certification
**DELETE** `/trainer-certifications/{certification_id}`

Deletes a certification.

## 6. Verify Certification
**POST** `/trainer-certifications/{certification_id}/verify`

Verifies a certification (admin/owner only).

### Response:
```json
{
    "status": "success",
    "message": "Certification verified successfully",
    "data": {
        "certification": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "is_verified": true
        }
    }
}
```

## 7. Unverify Certification
**POST** `/trainer-certifications/{certification_id}/unverify`

Unverifies a certification (admin/owner only).

## 8. Get Trainer's Certifications
**GET** `/trainer-certifications/trainer/{trainer_profile_id}`

Returns all certifications for a specific trainer.

### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| verified | boolean | Filter by verification status |
| valid_only | boolean | Show only valid certifications |

### Response:
```json
{
    "status": "success",
    "data": {
        "trainer_profile": {
            "id": "123e4567-e89b-12d3-a456-426614174001",
            "user_id": "123e4567-e89b-12d3-a456-426614174002"
        },
        "certifications": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "certification_name": "Certified Personal Trainer",
                "issuing_organization": "ACSM",
                "issue_date": "2023-01-15",
                "expiry_date": "2026-01-15",
                "is_verified": true
            }
        ]
    }
}
```

---

# Common Response Formats

## Success Response
```json
{
    "status": "success",
    "message": "Operation completed successfully",
    "data": {
        // Response data
    }
}
```

## Error Response
```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

## Pagination Format
```json
{
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

---

# Common HTTP Status Codes

- **200**: OK - Request successful
- **201**: Created - Resource created successfully
- **400**: Bad Request - Invalid request data
- **401**: Unauthorized - Authentication required
- **403**: Forbidden - Insufficient permissions
- **404**: Not Found - Resource not found
- **422**: Unprocessable Entity - Validation errors
- **500**: Internal Server Error - Server error

---

# Enums and Constants

## Location Types
- `gym` - Commercial gym/fitness center
- `outdoor` - Outdoor locations (parks, fields)
- `home` - Trainer's home
- `client_location` - Client's location
- `online` - Virtual/online sessions

## Session Status
- `scheduled` - Session is scheduled
- `in_progress` - Session is currently happening
- `completed` - Session completed successfully
- `cancelled` - Session was cancelled
- `no_show` - Client didn't show up

## Days of Week
- `Monday`, `Tuesday`, `Wednesday`, `Thursday`, `Friday`, `Saturday`, `Sunday`

---

# Rate Limiting

All API endpoints are subject to rate limiting:
- **Public endpoints**: 100 requests per minute
- **Authenticated endpoints**: 300 requests per minute
- **Admin endpoints**: 500 requests per minute

---

# Best Practices

1. **Performance**: Use pagination for large result sets
2. **Filtering**: Combine multiple filters for precise searches
3. **Caching**: Public data is cached for better performance
4. **Security**: Always validate user permissions
5. **Geographic**: Use Haversine formula for location-based queries
6. **Time Zones**: All times are stored in UTC
7. **Validation**: Prevent overlapping availability/sessions

---

# Examples

## Book a Training Session
```bash
curl -X POST "https://api.sportsclub.pk/api/trainer-sessions" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
    "client_user_id": "123e4567-e89b-12d3-a456-426614174002",
    "sport_id": "123e4567-e89b-12d3-a456-426614174003",
    "session_date": "2025-08-20",
    "start_time": "10:00",
    "end_time": "11:00",
    "session_fee": 75.00
  }'
```

## Find Nearby Trainers
```bash
curl -X GET "https://api.sportsclub.pk/api/trainer-locations/nearby?latitude=24.8607&longitude=67.0011&radius=10&location_type=gym"
```

## Set Trainer Availability
```bash
curl -X POST "https://api.sportsclub.pk/api/trainer-availability" \
  -H "Authorization: Bearer {trainer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "trainer_profile_id": "123e4567-e89b-12d3-a456-426614174001",
    "day_of_week": "Monday",
    "start_time": "09:00",
    "end_time": "17:00",
    "is_available": true
  }'
```
