# Service Purchases API Documentation

This document outlines the REST API endpoints for managing service purchases in the Sports Club Pakistan application.

## Overview

Service purchases represent user purchases of sport services offered by the club. Each purchase is linked to a user, their membership, and a specific sport service.

## Base URL
```
/api/service-purchases
```

## Authentication
All endpoints require authentication using Laravel Sanctum tokens.

## Models

### ServicePurchase Model Structure
```json
{
    "id": "uuid",
    "user_id": "uuid",
    "membership_id": "uuid", 
    "sport_service_id": "uuid",
    "amount": "decimal(10,2)",
    "status": "enum(completed,cancelled,upcoming,expired)",
    "service_date": "date|nullable",
    "service_time": "time|nullable", 
    "provider": "string|nullable",
    "location": "string|nullable",
    "notes": "text|nullable",
    "created_at": "timestamp",
    "updated_at": "timestamp",
    
    // Computed attributes
    "is_upcoming": "boolean",
    "is_expired": "boolean", 
    "is_completed": "boolean",
    "formatted_status": "string",
    "service_datetime": "datetime|nullable"
}
```

## Endpoints

### 1. Get All Service Purchases
```http
GET /api/service-purchases
```

**Query Parameters:**
- `user_id` (uuid, optional) - Filter by user ID
- `membership_id` (uuid, optional) - Filter by membership ID
- `sport_service_id` (uuid, optional) - Filter by sport service ID
- `status` (string|array, optional) - Filter by status (completed, cancelled, upcoming, expired)
- `date_from` (date, optional) - Filter by service date from
- `date_to` (date, optional) - Filter by service date to
- `amount_min` (numeric, optional) - Filter by minimum amount
- `amount_max` (numeric, optional) - Filter by maximum amount
- `active` (boolean, optional) - Filter active purchases (completed + upcoming)
- `completed` (boolean, optional) - Filter completed purchases only
- `upcoming` (boolean, optional) - Filter upcoming purchases only
- `expired` (boolean, optional) - Filter expired purchases only
- `cancelled` (boolean, optional) - Filter cancelled purchases only
- `this_month` (boolean, optional) - Filter this month's purchases
- `search` (string, optional) - Search in provider, location, notes, user name, service name
- `sort_by` (string, optional) - Sort field (default: created_at)
- `sort_order` (string, optional) - Sort order (asc, desc, default: desc)
- `per_page` (integer, optional) - Items per page (default: 15)

**Response:**
```json
{
    "status": "success",
    "data": {
        "service_purchases": [
            {
                "id": "uuid",
                "user_id": "uuid",
                "membership_id": "uuid",
                "sport_service_id": "uuid",
                "amount": "1500.00",
                "status": "completed",
                "service_date": "2025-01-15",
                "service_time": "14:30",
                "provider": "Expert Trainer",
                "location": "Main Gym",
                "notes": "Personal training session",
                "created_at": "2025-01-10T10:00:00.000000Z",
                "updated_at": "2025-01-15T14:30:00.000000Z",
                "is_upcoming": false,
                "is_expired": false,
                "is_completed": true,
                "formatted_status": "Completed",
                "service_datetime": "2025-01-15T14:30:00.000000Z",
                "user": {
                    "id": "uuid",
                    "name": "John Doe",
                    "email": "john@example.com"
                },
                "membership": {
                    "id": "uuid",
                    "membership_number": "MEM123456",
                    "sport_id": "uuid",
                    "tier_id": "uuid",
                    "sport": {
                        "id": "uuid",
                        "name": "Gym",
                        "display_name": "GYM CARD"
                    },
                    "tier": {
                        "id": "uuid",
                        "tier_name": "premium",
                        "display_name": "Premium Plan"
                    }
                },
                "sport_service": {
                    "id": "uuid",
                    "service_name": "Personal Training",
                    "base_price": "2000.00",
                    "type": "trainer",
                    "rating": "4.5"
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

### 2. Create Service Purchase
```http
POST /api/service-purchases
```

**Request Body:**
```json
{
    "user_id": "uuid", // Required (auto-set for non-admin users)
    "membership_id": "uuid", // Required
    "sport_service_id": "uuid", // Required
    "amount": "1500.00", // Optional (defaults to service price)
    "status": "completed", // Optional (default: completed)
    "service_date": "2025-01-20", // Optional
    "service_time": "15:00", // Optional (HH:MM format)
    "provider": "Expert Trainer", // Optional
    "location": "Main Gym", // Optional
    "notes": "Personal training session" // Optional
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Service purchase created successfully",
    "data": {
        "service_purchase": {
            // ServicePurchase object with relationships
        }
    }
}
```

### 3. Get Single Service Purchase
```http
GET /api/service-purchases/{id}
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "service_purchase": {
            // ServicePurchase object with full relationships
        }
    }
}
```

### 4. Update Service Purchase
```http
PUT /api/service-purchases/{id}
```

**Request Body:**
```json
{
    "amount": "1800.00", // Optional
    "status": "completed", // Optional
    "service_date": "2025-01-22", // Optional
    "service_time": "16:00", // Optional
    "provider": "Senior Trainer", // Optional
    "location": "Private Room", // Optional
    "notes": "Updated session notes" // Optional
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Service purchase updated successfully",
    "data": {
        "service_purchase": {
            // Updated ServicePurchase object
        }
    }
}
```

### 5. Delete Service Purchase
```http
DELETE /api/service-purchases/{id}
```

**Authorization:** Admin/Owner only

**Response:**
```json
{
    "status": "success",
    "message": "Service purchase deleted successfully"
}
```

### 6. Mark Service Purchase as Completed
```http
POST /api/service-purchases/{id}/complete
```

**Response:**
```json
{
    "status": "success",
    "message": "Service purchase marked as completed",
    "data": {
        "service_purchase": {
            // Updated ServicePurchase object
        }
    }
}
```

### 7. Cancel Service Purchase
```http
POST /api/service-purchases/{id}/cancel
```

**Response:**
```json
{
    "status": "success",
    "message": "Service purchase cancelled successfully",
    "data": {
        "service_purchase": {
            // Updated ServicePurchase object
        }
    }
}
```

## Member-Specific Endpoints

### 8. Get My Service Purchases
```http
GET /api/member/service-purchases
```

**Query Parameters:**
- `per_page` (integer, optional) - Items per page (default: 15)

**Response:**
```json
{
    "status": "success",
    "data": {
        "service_purchases": [
            {
                // ServicePurchase objects for authenticated user
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

## Membership-Related Endpoints

### 9. Get Service Purchases by Membership
```http
GET /api/memberships/{membership_id}/service-purchases
```

**Query Parameters:**
- `per_page` (integer, optional) - Items per page (default: 15)

**Response:**
```json
{
    "status": "success",
    "data": {
        "membership": {
            "id": "uuid",
            "membership_number": "MEM123456",
            // Other membership details with sport and tier
        },
        "service_purchases": [
            {
                // ServicePurchase objects for this membership
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 2,
            "per_page": 15,
            "total": 28
        }
    }
}
```

## Error Responses

### Validation Error (422)
```json
{
    "status": "error",
    "message": "The given data was invalid.",
    "errors": {
        "membership_id": [
            "Selected membership does not belong to the specified user."
        ],
        "sport_service_id": [
            "Selected sport service does not belong to the membership's sport."
        ]
    }
}
```

### Unauthorized (403)
```json
{
    "status": "error",
    "message": "Unauthorized to view this service purchase"
}
```

### Not Found (404)
```json
{
    "status": "error",
    "message": "Service purchase not found"
}
```

### Server Error (500)
```json
{
    "status": "error",
    "message": "Failed to create service purchase",
    "error": "Detailed error message"
}
```

## Authorization Rules

1. **Regular Users:**
   - Can view, create, and update their own service purchases only
   - Can mark their own purchases as completed or cancel them
   - Cannot delete service purchases

2. **Admin/Owner:**
   - Can view, create, update, and delete any service purchase
   - Can perform all actions on behalf of any user

## Business Rules

1. **Service Purchase Creation:**
   - User must have an active membership
   - Membership must not be expired
   - Sport service must be active
   - Sport service must belong to the membership's sport

2. **Status Transitions:**
   - `upcoming` → `completed` (when service is delivered)
   - `upcoming` → `cancelled` (when cancelled before delivery)
   - `upcoming` → `expired` (when service date passes without completion)
   - `completed` purchases cannot be changed
   - `cancelled` purchases cannot be reactivated

3. **Financial Impact:**
   - Completed purchases automatically update membership spending totals
   - Cancelled purchases don't affect spending calculations

## Data Relationships

- **User**: Each service purchase belongs to a user
- **Membership**: Each service purchase is linked to a specific membership
- **Sport Service**: Each service purchase is for a specific sport service
- **Sport**: Indirectly linked through membership and sport service (must match)

## Status Definitions

- **completed**: Service has been delivered and payment processed
- **upcoming**: Service is scheduled for future delivery
- **cancelled**: Service was cancelled before delivery
- **expired**: Service date passed without completion (auto-updated by system)

## Best Practices

1. **Filtering**: Use appropriate filters to reduce response size
2. **Pagination**: Always implement pagination for list endpoints
3. **Authorization**: Check user permissions before allowing actions
4. **Validation**: Ensure data integrity through proper validation
5. **Error Handling**: Provide meaningful error messages
6. **Performance**: Use eager loading for related models to avoid N+1 queries
