# Sport Services API Documentation

This document describes the API endpoints for managing sport services in the Sports Club Backend.

## Overview

Sport Services represent the different services offered for each sport (e.g., Personal Training, Equipment Rental, Court Booking). Only administrators can manage sport services through CRUD operations, while all users can view active services.

## Authentication

All admin operations require authentication with an admin role. Use the `Authorization: Bearer {token}` header with a valid admin token.

## Endpoints

### Public Endpoints (No Authentication Required)

#### 1. Get All Sport Services
```
GET /api/sport-services
```

**Query Parameters:**
- `sport_id` (optional): Filter by specific sport ID
- `active` (optional): Filter by active status (true/false)
- `search` (optional): Search in service name and description
- `sort_by` (optional, default: 'service_name'): Field to sort by
- `sort_order` (optional, default: 'asc'): Sort order (asc/desc)
- `per_page` (optional, default: 15): Number of items per page

**Example:**
```
GET /api/sport-services?sport_id=123&active=true&search=training&per_page=10
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "services": [
      {
        "id": "uuid",
        "sport_id": "uuid",
        "service_name": "Personal Training",
        "description": "One-on-one basketball training to improve your skills",
        "base_price": "50.00",
        "duration_minutes": 60,
        "discount_percentage": "0.00",
        "discounted_price": "50.00",
        "is_active": true,
        "created_at": "2025-08-14T10:00:00.000000Z",
        "updated_at": "2025-08-14T10:00:00.000000Z",
        "sport": {
          "id": "uuid",
          "name": "Basketball",
          "display_name": "Basketball"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 65
    }
  }
}
```

#### 2. Get Sport Service by ID
```
GET /api/sport-services/{id}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "service": {
      "id": "uuid",
      "sport_id": "uuid",
      "service_name": "Personal Training",
      "description": "One-on-one basketball training to improve your skills",
      "base_price": "50.00",
      "duration_minutes": 60,
      "discount_percentage": "0.00",
      "discounted_price": "50.00",
      "is_active": true,
      "created_at": "2025-08-14T10:00:00.000000Z",
      "updated_at": "2025-08-14T10:00:00.000000Z",
      "sport": {
        "id": "uuid",
        "name": "Basketball",
        "display_name": "Basketball"
      }
    }
  }
}
```

#### 3. Get Services by Sport
```
GET /api/sports/{sport_id}/services
```

**Query Parameters:**
- `active` (optional): Filter by active status (true/false)
- `search` (optional): Search in service name and description
- `sort_by` (optional, default: 'service_name'): Field to sort by
- `sort_order` (optional, default: 'asc'): Sort order (asc/desc)
- `per_page` (optional, default: 15): Number of items per page

**Response:**
```json
{
  "status": "success",
  "data": {
    "sport": {
      "id": "uuid",
      "name": "Basketball",
      "display_name": "Basketball"
    },
    "services": [
      {
        "id": "uuid",
        "sport_id": "uuid",
        "service_name": "Personal Training",
        "description": "One-on-one basketball training to improve your skills",
        "base_price": "50.00",
        "duration_minutes": 60,
        "discount_percentage": "0.00",
        "discounted_price": "50.00",
        "is_active": true,
        "created_at": "2025-08-14T10:00:00.000000Z",
        "updated_at": "2025-08-14T10:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 15,
      "total": 20
    }
  }
}
```

### Admin Endpoints (Requires Admin Authentication)

#### 1. Get All Sport Services (Admin View)
```
GET /api/admin/sport-services
```

Same as public endpoint but includes inactive services and additional admin data.

#### 2. Create Sport Service
```
POST /api/admin/sport-services
```

**Headers:**
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "sport_id": "uuid",
  "service_name": "Personal Training",
  "description": "One-on-one basketball training to improve your skills",
  "base_price": 50.00,
  "duration_minutes": 60,
  "discount_percentage": 0.00,
  "is_active": true
}
```

**Validation Rules:**
- `sport_id`: Required, must exist in sports table
- `service_name`: Required, max 100 characters, unique per sport
- `description`: Optional, text
- `base_price`: Optional, numeric, min 0, max 99,999,999.99
- `duration_minutes`: Optional, integer, min 1, max 1440 (24 hours)
- `discount_percentage`: Optional, numeric, min 0, max 100
- `is_active`: Optional, boolean, default true

**Response:**
```json
{
  "status": "success",
  "message": "Sport service created successfully",
  "data": {
    "service": {
      "id": "uuid",
      "sport_id": "uuid",
      "service_name": "Personal Training",
      "description": "One-on-one basketball training to improve your skills",
      "base_price": "50.00",
      "duration_minutes": 60,
      "discount_percentage": "0.00",
      "discounted_price": "50.00",
      "is_active": true,
      "created_at": "2025-08-14T10:00:00.000000Z",
      "updated_at": "2025-08-14T10:00:00.000000Z",
      "sport": {
        "id": "uuid",
        "name": "Basketball",
        "display_name": "Basketball"
      }
    }
  }
}
```

#### 3. Update Sport Service
```
PUT /api/admin/sport-services/{id}
```

**Headers:**
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "service_name": "Advanced Personal Training",
  "description": "Advanced one-on-one basketball training for experienced players",
  "base_price": 75.00,
  "discount_percentage": 10.00
}
```

All fields are optional in update requests. Same validation rules apply as create.

**Response:**
```json
{
  "status": "success",
  "message": "Sport service updated successfully",
  "data": {
    "service": {
      "id": "uuid",
      "sport_id": "uuid",
      "service_name": "Advanced Personal Training",
      "description": "Advanced one-on-one basketball training for experienced players",
      "base_price": "75.00",
      "duration_minutes": 60,
      "discount_percentage": "10.00",
      "discounted_price": "67.50",
      "is_active": true,
      "created_at": "2025-08-14T10:00:00.000000Z",
      "updated_at": "2025-08-14T10:30:00.000000Z",
      "sport": {
        "id": "uuid",
        "name": "Basketball",
        "display_name": "Basketball"
      }
    }
  }
}
```

#### 4. Delete Sport Service
```
DELETE /api/admin/sport-services/{id}
```

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
  "status": "success",
  "message": "Sport service deleted successfully"
}
```

#### 5. Toggle Sport Service Status
```
POST /api/admin/sport-services/{id}/toggle-status
```

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
  "status": "success",
  "message": "Sport service status updated successfully",
  "data": {
    "service": {
      "id": "uuid",
      "sport_id": "uuid",
      "service_name": "Personal Training",
      "description": "One-on-one basketball training to improve your skills",
      "base_price": "50.00",
      "duration_minutes": 60,
      "discount_percentage": "0.00",
      "discounted_price": "50.00",
      "is_active": false,
      "created_at": "2025-08-14T10:00:00.000000Z",
      "updated_at": "2025-08-14T10:45:00.000000Z",
      "sport": {
        "id": "uuid",
        "name": "Basketball",
        "display_name": "Basketball"
      }
    }
  }
}
```

#### 6. Get Sport Service by ID (Admin View)
```
GET /api/admin/sport-services/{id}
```

Same as public endpoint but includes additional admin data.

## Error Responses

### Validation Error (422)
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "service_name": ["The service name is required."],
    "base_price": ["The base price must be a valid number."]
  }
}
```

### Unauthorized (401)
```json
{
  "status": "error",
  "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
  "status": "error",
  "message": "Access denied. Admin role required."
}
```

### Not Found (404)
```json
{
  "status": "error",
  "message": "Sport service not found."
}
```

### Duplicate Service Name (422)
```json
{
  "status": "error",
  "message": "A service with this name already exists for this sport"
}
```

## Features

### Automatic Price Calculation
The API automatically calculates the discounted price based on the base price and discount percentage. This is returned as a `discounted_price` field in responses.

### Flexible Filtering and Searching
- Filter by sport, active status
- Search across service names and descriptions
- Sort by any field in ascending or descending order
- Paginated results

### Data Validation
- Comprehensive validation for all fields
- Business logic validation (e.g., unique service names per sport)
- Proper error messages

### Security
- Role-based access control
- Admin-only write operations
- Public read access for active services only

## Usage Examples

### Creating a New Service
```bash
curl -X POST http://localhost:8000/api/admin/sport-services \
  -H "Authorization: Bearer your_admin_token" \
  -H "Content-Type: application/json" \
  -d '{
    "sport_id": "your_sport_id",
    "service_name": "Advanced Training",
    "description": "Advanced training for experienced players",
    "base_price": 75.00,
    "duration_minutes": 90,
    "discount_percentage": 15.00
  }'
```

### Searching Services
```bash
curl "http://localhost:8000/api/sport-services?search=training&active=true&per_page=5"
```

### Getting Services for a Specific Sport
```bash
curl "http://localhost:8000/api/sports/your_sport_id/services?active=true"
```
