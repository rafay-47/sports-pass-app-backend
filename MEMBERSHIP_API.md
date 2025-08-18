# Membership API Documentation

## Overview
The Membership API provides endpoints for managing user memberships to different sports within the Sports Club Pakistan system. Each membership represents a paid subscription that grants access to specific sports facilities and services.

## Base URL
```
/api/memberships
```

## Authentication
All membership endpoints require authentication using Sanctum tokens.
```
Authorization: Bearer {token}
```

## Endpoints

### 1. List Memberships
**GET** `/api/memberships`

Lists memberships based on user role:
- **Regular users**: Can only see their own memberships
- **Admins/Owners**: Can see all memberships

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `user_id` | UUID | Filter by specific user (admin/owner only) |
| `sport_id` | UUID | Filter by specific sport |
| `status` | string | Filter by status: `active`, `paused`, `expired`, `cancelled` |
| `active` | boolean | Show only active memberships |
| `expired` | boolean | Show only expired memberships |
| `expiring_soon` | boolean | Show memberships expiring soon |
| `expiring_days` | integer | Days threshold for expiring soon (default: 30) |
| `search` | string | Search by membership number, user name/email, sport name |
| `sort_by` | string | Sort field: `created_at`, `expiry_date`, `user_name`, `sport_name` |
| `sort_order` | string | Sort direction: `asc`, `desc` |
| `per_page` | integer | Items per page (default: 15) |

#### Response
```json
{
    "status": "success",
    "data": {
        "memberships": [
            {
                "id": "uuid",
                "membership_number": "MEM123ABC",
                "user_id": "uuid",
                "sport_id": "uuid",
                "tier_id": "uuid",
                "status": "active",
                "purchase_date": "2025-01-15",
                "start_date": "2025-01-15",
                "expiry_date": "2026-01-15",
                "auto_renew": true,
                "purchase_amount": "5000.00",
                "monthly_check_ins": 8,
                "total_spent": "1200.00",
                "monthly_spent": "300.00",
                "total_earnings": "0.00",
                "monthly_earnings": "0.00",
                "is_active": true,
                "is_expired": false,
                "days_remaining": 152,
                "usage_percentage": 26.67,
                "created_at": "2025-01-15T10:00:00Z",
                "updated_at": "2025-01-15T10:00:00Z",
                "user": {
                    "id": "uuid",
                    "name": "Ahmed Khan",
                    "email": "ahmed@example.com"
                },
                "sport": {
                    "id": "uuid",
                    "name": "Gym",
                    "display_name": "GYM CARD"
                },
                "tier": {
                    "id": "uuid",
                    "tier_name": "premium",
                    "display_name": "Premium Plan",
                    "price": "5000.00"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 3,
            "per_page": 15,
            "total": 42
        }
    }
}
```

### 2. Create Membership
**POST** `/api/memberships`

Creates a new membership for a user.

#### Request Body
```json
{
    "user_id": "uuid", // Required
    "sport_id": "uuid", // Required
    "tier_id": "uuid", // Required
    "start_date": "2025-01-20", // Optional (defaults to today)
    "auto_renew": true, // Optional (defaults to true)
    "purchase_amount": 5000.00 // Optional (defaults to tier price)
}
```

#### Validation Rules
- `user_id`: Must exist in users table. Non-admin users can only create for themselves
- `sport_id`: Must exist and be active
- `tier_id`: Must exist, be active, available, and belong to the selected sport
- No existing active membership for the same sport allowed
- `start_date`: Must be today or future date
- `purchase_amount`: Must be positive number if provided

#### Response
```json
{
    "status": "success",
    "message": "Membership created successfully",
    "data": {
        "membership": { /* membership object */ }
    }
}
```

### 3. Get Membership Details
**GET** `/api/memberships/{id}`

Retrieves detailed information about a specific membership.

#### Authorization
- Users can only view their own memberships
- Admins/owners can view any membership

#### Response
```json
{
    "status": "success",
    "data": {
        "membership": {
            /* Full membership object with related data */
            "tier": {
                "id": "uuid",
                "tier_name": "premium",
                "display_name": "Premium Plan",
                "price": "5000.00",
                "features": ["Feature 1", "Feature 2"]
            }
        }
    }
}
```

### 4. Update Membership
**PUT** `/api/memberships/{id}`

Updates membership details.

#### Authorization
- Users can update limited fields of their own memberships (`auto_renew`)
- Admins/owners can update all fields

#### Request Body (Admin/Owner)
```json
{
    "status": "paused",
    "expiry_date": "2026-02-15",
    "purchase_amount": 4500.00,
    "monthly_check_ins": 12,
    "total_spent": 1500.00,
    "monthly_spent": 200.00,
    "auto_renew": false
}
```

#### Request Body (Regular User)
```json
{
    "auto_renew": false
}
```

### 5. Delete Membership
**DELETE** `/api/memberships/{id}`

Deletes a membership (admin/owner only).

#### Response
```json
{
    "status": "success",
    "message": "Membership deleted successfully"
}
```

### 6. Renew Membership
**POST** `/api/memberships/{id}/renew`

Renews an expired or expiring membership.

#### Authorization
- Users can renew their own memberships
- Admins/owners can renew any membership

#### Response
```json
{
    "status": "success",
    "message": "Membership renewed successfully",
    "data": {
        "membership": { /* updated membership object */ }
    }
}
```

### 7. Pause Membership
**POST** `/api/memberships/{id}/pause`

Pauses an active membership.

#### Response
```json
{
    "status": "success",
    "message": "Membership paused successfully",
    "data": {
        "membership": { /* updated membership object */ }
    }
}
```

### 8. Resume Membership
**POST** `/api/memberships/{id}/resume`

Resumes a paused membership.

### 9. Cancel Membership
**POST** `/api/memberships/{id}/cancel`

Cancels a membership.

### 10. Membership Statistics
**GET** `/api/memberships/statistics`

Retrieves membership statistics.

#### Response (Admin/Owner)
```json
{
    "status": "success",
    "data": {
        "statistics": {
            "total_memberships": 150,
            "active_memberships": 120,
            "expired_memberships": 20,
            "paused_memberships": 8,
            "cancelled_memberships": 2,
            "expiring_soon": 15,
            "total_revenue": 750000.00,
            "monthly_revenue": 45000.00,
            "sports_breakdown": [
                {
                    "name": "Gym",
                    "display_name": "GYM CARD",
                    "count": 80,
                    "revenue": 400000.00
                }
            ],
            "tier_breakdown": [
                {
                    "tier_name": "premium",
                    "display_name": "Premium Plan",
                    "count": 45,
                    "revenue": 225000.00
                }
            ]
        }
    }
}
```

#### Response (Regular User)
```json
{
    "status": "success",
    "data": {
        "statistics": {
            "total_memberships": 3,
            "active_memberships": 2,
            "expired_memberships": 1,
            "paused_memberships": 0,
            "cancelled_memberships": 0,
            "expiring_soon": 1,
            "total_revenue": 15000.00,
            "monthly_revenue": 5000.00
        }
    }
}
```

### 11. My Memberships
**GET** `/api/member/memberships`

Retrieves the authenticated user's active memberships.

#### Response
```json
{
    "status": "success",
    "data": {
        "memberships": [
            {
                /* membership objects with sport and tier details */
                "sport": {
                    "id": "uuid",
                    "name": "Gym",
                    "display_name": "GYM CARD",
                    "icon": "🏋️",
                    "color": "#FFB948"
                },
                "tier": {
                    "id": "uuid",
                    "tier_name": "premium",
                    "display_name": "Premium Plan",
                    "price": "5000.00",
                    "features": ["24/7 Access", "Personal Trainer", "Diet Plan"]
                }
            }
        ]
    }
}
```

## Error Responses

### Validation Error (422)
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "tier_id": ["Selected tier does not belong to the selected sport."]
    }
}
```

### Unauthorized (403)
```json
{
    "status": "error",
    "message": "Unauthorized to view this membership"
}
```

### Not Found (404)
```json
{
    "status": "error",
    "message": "Membership not found"
}
```

### Server Error (500)
```json
{
    "status": "error",
    "message": "Failed to create membership",
    "error": "Detailed error message"
}
```

## Business Rules

1. **One Active Membership Per Sport**: Users cannot have multiple active memberships for the same sport
2. **Tier-Sport Validation**: Selected tier must belong to the selected sport
3. **Active Sport/Tier**: Both sport and tier must be active and available
4. **Date Validation**: Start date cannot be in the past, expiry date must be after start date
5. **Role-based Access**: Users can only manage their own memberships unless they're admin/owner
6. **Monthly Limits**: Check-ins are limited to 30 per month per membership
7. **Auto-renewal**: Memberships can be set for automatic renewal before expiry

## Related Models

- **User**: Membership owner
- **Sport**: The sport the membership provides access to
- **Tier**: The pricing tier and feature set
- **CheckIn**: Facility visits linked to membership (future implementation)
- **Payment**: Payment transactions for membership purchases (future implementation)
