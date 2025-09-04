# Trainer Profile APIs Documentation

## Overview
The Trainer Profile system manages trainer profiles, qualifications, specializations, and availability for the Sports Club Pakistan platform. This document covers all API endpoints related to trainer profile management.

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
- **Public**: Can view verified and available trainer profiles
- **Member**: Can view trainer profiles and book sessions
- **Trainer**: Can manage their own profile and availability
- **Admin/Owner**: Full access to all trainer profile operations

---

## Public Endpoints

### 1. Get All Trainer Profiles (Public)
**GET** `/trainers`

Returns a paginated list of verified and available trainer profiles.

#### Query Parameters:
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| sport_id | UUID | Filter by sport | `sport_id=123e4567-e89b-12d3-a456-426614174000` |
| min_rating | decimal | Minimum rating filter | `min_rating=4.0` |
| max_rating | decimal | Maximum rating filter | `max_rating=5.0` |
| min_experience | integer | Minimum years of experience | `min_experience=2` |
| max_experience | integer | Maximum years of experience | `max_experience=10` |
| min_rate | decimal | Minimum hourly rate | `min_rate=30.00` |
| max_rate | decimal | Maximum hourly rate | `max_rate=100.00` |
| gender_preference | enum | Gender preference | `gender_preference=female` |
| search | string | Search in trainer names, emails, sports, bio | `search=Ahmed` |
| sort_by | string | Sort field | `sort_by=rating` |
| sort_order | string | Sort direction | `sort_order=desc` |
| per_page | integer | Items per page (max 50) | `per_page=15` |

#### Response:
```json
{
    "status": "success",
    "data": {
        "trainers": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "user_id": "123e4567-e89b-12d3-a456-426614174001",
                "sport_id": "123e4567-e89b-12d3-a456-426614174002",
                "tier_id": "123e4567-e89b-12d3-a456-426614174003",
                "experience_years": 5,
                "bio": "Experienced fitness trainer specializing in strength training and weight loss.",
                "hourly_rate": "75.00",
                "rating": "4.50",
                "total_sessions": 150,
                "total_earnings": "11250.00",
                "monthly_earnings": "1500.00",
                "is_verified": true,
                "is_available": true,
                "gender_preference": "both",
                "created_at": "2025-08-18T10:00:00.000000Z",
                "updated_at": "2025-08-18T10:00:00.000000Z",
                "user": {
                    "id": "123e4567-e89b-12d3-a456-426614174001",
                    "name": "Ahmed Khan",
                    "email": "ahmed@example.com",
                    "phone": "+92-300-1234567",
                    "gender": "male"
                },
                "sport": {
                    "id": "123e4567-e89b-12d3-a456-426614174002",
                    "name": "fitness",
                    "display_name": "Fitness Training",
                    "icon": "💪",
                    "color": "#FF6B35"
                },
                "tier": {
                    "id": "123e4567-e89b-12d3-a456-426614174003",
                    "tier_name": "premium",
                    "display_name": "Premium",
                    "price": "150.00"
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

### 2. Get Trainer Profile Details (Public)
**GET** `/trainers/{trainer_profile_id}`

Returns detailed information about a specific trainer profile.

#### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| include_availability | boolean | Include trainer availability schedule |

#### Response:
```json
{
    "status": "success",
    "data": {
        "trainer_profile": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "user_id": "123e4567-e89b-12d3-a456-426614174001",
            "sport_id": "123e4567-e89b-12d3-a456-426614174002",
            "tier_id": "123e4567-e89b-12d3-a456-426614174003",
            "experience_years": 5,
            "bio": "Experienced fitness trainer with certifications in personal training and nutrition.",
            "hourly_rate": "75.00",
            "rating": "4.50",
            "total_sessions": 150,
            "is_verified": true,
            "is_available": true,
            "gender_preference": "both",
            "created_at": "2025-08-18T10:00:00.000000Z",
            "updated_at": "2025-08-18T10:00:00.000000Z",
            "user": {
                "id": "123e4567-e89b-12d3-a456-426614174001",
                "name": "Ahmed Khan",
                "email": "ahmed@example.com",
                "phone": "+92-300-1234567",
                "gender": "male",
                "profile_image_url": "https://example.com/images/ahmed.jpg",
                "join_date": "2024-01-15"
            },
            "sport": {
                "id": "123e4567-e89b-12d3-a456-426614174002",
                "name": "fitness",
                "display_name": "Fitness Training",
                "icon": "💪",
                "color": "#FF6B35"
            },
            "tier": {
                "id": "123e4567-e89b-12d3-a456-426614174003",
                "tier_name": "premium",
                "display_name": "Premium",
                "price": "150.00",
                "features": ["Personal Training", "Nutrition Consultation", "Progress Tracking"]
            },
            "specialties": [
                {
                    "id": "123e4567-e89b-12d3-a456-426614174004",
                    "name": "Weight Loss",
                    "description": "Specialized in weight loss programs"
                }
            ],
            "certifications": [
                {
                    "id": "123e4567-e89b-12d3-a456-426614174005",
                    "name": "Certified Personal Trainer",
                    "issuing_organization": "ACSM",
                    "issue_date": "2023-01-15",
                    "expiry_date": "2026-01-15",
                    "is_verified": true
                }
            ]
        }
    }
}
```

### 3. Get Trainers by Sport (Public)
**GET** `/trainers/sport/{sport_id}`

Returns trainers specialized in a specific sport.

#### Query Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| verified | boolean | Filter by verification status (default: true) |
| available | boolean | Filter by availability (default: true) |
| min_rating | decimal | Minimum rating filter |
| sort_by | string | Sort field (default: rating) |
| sort_order | string | Sort direction (default: desc) |

#### Response:
```json
{
    "status": "success",
    "data": {
        "sport": {
            "id": "123e4567-e89b-12d3-a456-426614174002",
            "name": "fitness",
            "display_name": "Fitness Training"
        },
        "trainers": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "experience_years": 5,
                "bio": "Experienced fitness trainer...",
                "hourly_rate": "75.00",
                "rating": "4.50",
                "is_verified": true,
                "is_available": true,
                "user": {
                    "id": "123e4567-e89b-12d3-a456-426614174001",
                    "name": "Ahmed Khan",
                    "email": "ahmed@example.com",
                    "phone": "+92-300-1234567",
                    "gender": "male"
                },
                "tier": {
                    "id": "123e4567-e89b-12d3-a456-426614174003",
                    "tier_name": "premium",
                    "display_name": "Premium",
                    "price": "150.00"
                }
            }
        ]
    }
}
```

---

## Protected Endpoints

### 4. Create Trainer Profile
**POST** `/trainer-profiles`

Creates a new trainer profile. Requires authentication.

#### Request Body:
```json
{
    "user_id": "123e4567-e89b-12d3-a456-426614174001",
    "sport_id": "123e4567-e89b-12d3-a456-426614174002",
    "tier_id": "123e4567-e89b-12d3-a456-426614174003",
    "experience_years": 5,
    "bio": "Experienced fitness trainer with a passion for helping clients achieve their goals.",
    "hourly_rate": 75.00,
    "is_available": true,
    "gender_preference": "both"
}
```

#### Validation Rules:
- `user_id`: required, exists in users table, user must have active membership for the selected sport
- `sport_id`: required, exists in active sports
- `tier_id`: required, exists in active tiers
- `experience_years`: required, integer, min: 0, max: 50
- `bio`: required, string, min: 50 characters, max: 1000 characters
- `hourly_rate`: optional, numeric, min: 10.00, max: 500.00
- `is_available`: optional, boolean
- `gender_preference`: optional, enum: male, female, both

#### Response:
```json
{
    "status": "success",
    "message": "Trainer profile created successfully",
    "data": {
        "trainer_profile": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "user_id": "123e4567-e89b-12d3-a456-426614174001",
            "sport_id": "123e4567-e89b-12d3-a456-426614174002",
            "tier_id": "123e4567-e89b-12d3-a456-426614174003",
            "experience_years": 5,
            "bio": "Experienced fitness trainer...",
            "hourly_rate": "75.00",
            "rating": "0.00",
            "total_sessions": 0,
            "total_earnings": "0.00",
            "monthly_earnings": "0.00",
            "is_verified": false,
            "is_available": true,
            "gender_preference": "both",
            "created_at": "2025-08-18T10:00:00.000000Z",
            "updated_at": "2025-08-18T10:00:00.000000Z"
        }
    }
}
```

### 5. Update Trainer Profile
**PUT** `/trainer-profiles/{trainer_profile_id}`

Updates an existing trainer profile. Trainers can only update their own profiles.

#### Request Body:
```json
{
    "experience_years": 6,
    "bio": "Updated bio with new achievements and certifications.",
    "hourly_rate": 80.00,
    "is_available": true,
    "gender_preference": "both"
}
```

#### Response:
```json
{
    "status": "success",
    "message": "Trainer profile updated successfully",
    "data": {
        "trainer_profile": {
            // Updated trainer profile data
        }
    }
}
```

### 6. Delete Trainer Profile
**DELETE** `/trainer-profiles/{trainer_profile_id}`

Deletes a trainer profile. If the user has no other verified trainer profiles after deletion, their `is_trainer` status is set to `false`. Only admins and owners can delete profiles.

#### Response:
```json
{
    "status": "success",
    "message": "Trainer profile deleted successfully"
}
```

### 7. Verify Trainer Profile
**POST** `/trainer-profiles/{trainer_profile_id}/verify`

Verifies a trainer profile and sets the user's `is_trainer` status to `true`. Only admins and owners can verify profiles.

#### Response:
```json
{
    "status": "success",
    "message": "Trainer profile verified successfully",
    "data": {
        "trainer_profile": {
            // Updated trainer profile with is_verified: true
        }
    }
}
```

### 8. Unverify Trainer Profile
**POST** `/trainer-profiles/{trainer_profile_id}/unverify`

Unverifies a trainer profile. If the user has no other verified trainer profiles, their `is_trainer` status is set to `false`. Only admins and owners can unverify profiles.

#### Response:
```json
{
    "status": "success",
    "message": "Trainer profile unverified successfully",
    "data": {
        "trainer_profile": {
            // Updated trainer profile with is_verified: false
        }
    }
}
```

### 9. Toggle Trainer Availability
**POST** `/trainer-profiles/{trainer_profile_id}/toggle-availability`

Toggles trainer availability. Trainers can toggle their own availability, admins/owners can toggle any trainer's availability.

#### Response:
```json
{
    "status": "success",
    "message": "Trainer availability updated successfully",
    "data": {
        "trainer_profile": {
            // Updated trainer profile with toggled is_available status
        }
    }
}
```

### 10. Get Trainer Statistics
**GET** `/trainer-profiles/statistics`

Returns comprehensive trainer statistics. Only admins and owners can access.

#### Response:
```json
{
    "status": "success",
    "data": {
        "statistics": {
            "total_trainers": 150,
            "verified_trainers": 120,
            "available_trainers": 110,
            "active_trainers": 100,
            "average_rating": 4.35,
            "total_sessions": 15000,
            "total_earnings": "1125000.00",
            "average_hourly_rate": 65.50,
            "sports_breakdown": [
                {
                    "name": "fitness",
                    "display_name": "Fitness Training",
                    "trainer_count": 45,
                    "avg_rating": "4.40"
                },
                {
                    "name": "swimming",
                    "display_name": "Swimming",
                    "trainer_count": 25,
                    "avg_rating": "4.30"
                }
            ],
            "experience_breakdown": {
                "beginner": 25,
                "intermediate": 50,
                "senior": 45,
                "expert": 30
            }
        }
    }
}
```

### 11. Get My Trainer Profile
**GET** `/trainer/profile`

Returns the authenticated trainer's profile. Only accessible by trainers.

#### Response:
```json
{
    "status": "success",
    "data": {
        "trainer_profile": {
            // Complete trainer profile with all relationships
        }
    }
}
```

### 12. Update Trainer Statistics
**POST** `/trainer-profiles/{trainer_profile_id}/update-statistics`

Updates trainer statistics (used internally or by admins). Trainers can update their own statistics.

#### Response:
```json
{
    "status": "success",
    "message": "Trainer statistics updated successfully",
    "data": {
        "trainer_profile": {
            // Updated trainer profile with refreshed statistics
        }
    }
}
```

---

## Error Responses

### Common Error Codes:
- **400**: Bad Request - Invalid input data
- **401**: Unauthorized - Missing or invalid authentication
- **403**: Forbidden - Insufficient permissions
- **404**: Not Found - Resource not found
- **422**: Unprocessable Entity - Validation errors
- **500**: Internal Server Error - Server error

### Error Response Format:
```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

---

## Advanced Features

### Filtering Combinations:
You can combine multiple filters for precise trainer searches:
```
GET /trainers?sport_id=123&min_rating=4.0&max_rate=100&gender_preference=female&verified=true
```

### Sorting Options:
- `rating` (default)
- `experience_years`
- `hourly_rate`
- `total_sessions`
- `user_name`
- `sport_name`
- `created_at`

### Search Functionality:
The search parameter performs a fuzzy search across:
- Trainer name
- Trainer email
- Sport name and display name
- Trainer bio

---

## Best Practices

1. **Performance**: Use pagination for large result sets
2. **Caching**: Public trainer lists are cached for better performance
3. **Security**: Always validate user permissions before operations
4. **Data Integrity**: Use foreign key constraints and validation
5. **Monitoring**: Track API usage and performance metrics

---

## Rate Limiting

All API endpoints are subject to rate limiting:
- **Public endpoints**: 100 requests per minute
- **Authenticated endpoints**: 300 requests per minute
- **Admin endpoints**: 500 requests per minute

---

## Examples

### Example 1: Find experienced fitness trainers
```bash
curl -X GET "https://api.sportsclub.pk/api/trainers?sport_id=123&min_experience=5&sort_by=rating&sort_order=desc"
```

### Example 2: Create a trainer profile
```bash
curl -X POST "https://api.sportsclub.pk/api/trainer-profiles" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": "123e4567-e89b-12d3-a456-426614174001",
    "sport_id": "123e4567-e89b-12d3-a456-426614174002",
    "tier_id": "123e4567-e89b-12d3-a456-426614174003",
    "experience_years": 5,
    "bio": "Passionate fitness trainer with 5 years of experience...",
    "hourly_rate": 75.00
  }'
```

### Example 3: Verify a trainer profile (admin only)
```bash
curl -X POST "https://api.sportsclub.pk/api/trainer-profiles/123/verify" \
  -H "Authorization: Bearer {admin_token}"
```
