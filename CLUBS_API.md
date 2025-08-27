# Clubs API Documentation

## Overview
This document provides comprehensive API documentation for the Clubs management system in the Sports Club Backend. Clubs are the core entities that represent physical sports facilities where members can access various services, amenities, and participate in events.

## Table of Contents
1. [Public Club APIs](#public-club-apis)
2. [Club Management APIs](#club-management-apis)
3. [Club Administration APIs](#club-administration-apis)
4. [Authentication](#authentication)
5. [Error Handling](#error-handling)
6. [Rate Limiting](#rate-limiting)

## Public Club APIs

### List Clubs
**GET** `/api/clubs`

**Query Parameters:**
- `search` (optional): Search in club name, description, or address
- `sport_id` (optional): Filter by sport UUID
- `city` (optional): Filter by city
- `state` (optional): Filter by state
- `country` (optional): Filter by country
- `min_rating` (optional): Filter by minimum rating (1-5)
- `max_distance` (optional): Filter by maximum distance from coordinates (requires lat/lng)
- `lat` (optional): Latitude for distance calculation
- `lng` (optional): Longitude for distance calculation
- `verified_only` (optional): Show only verified clubs (true/false)
- `sort_by` (optional): Sort by (name, rating, distance, created_at)
- `sort_order` (optional): Sort order (asc, desc)

**Response:**
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid",
        "name": "Elite Fitness Center",
        "description": "Premium fitness facility with state-of-the-art equipment",
        "address": "123 Main Street",
        "city": "New York",
        "state": "NY",
        "country": "USA",
        "postal_code": "10001",
        "phone": "+1-555-0123",
        "email": "info@elitefitness.com",
        "website": "https://elitefitness.com",
        "latitude": 40.7128,
        "longitude": -74.0060,
        "operating_hours": {
          "monday": {"open": "06:00", "close": "22:00"},
          "tuesday": {"open": "06:00", "close": "22:00"},
          "wednesday": {"open": "06:00", "close": "22:00"},
          "thursday": {"open": "06:00", "close": "22:00"},
          "friday": {"open": "06:00", "close": "22:00"},
          "saturday": {"open": "08:00", "close": "20:00"},
          "sunday": {"open": "10:00", "close": "18:00"}
        },
        "rating": 4.5,
        "total_reviews": 128,
        "is_verified": true,
        "is_active": true,
        "owner_id": "uuid",
        "created_at": "2024-01-15T10:00:00Z",
        "updated_at": "2024-01-15T10:00:00Z",
        "sports_count": 8,
        "amenities_count": 12,
        "facilities_count": 15,
        "images_count": 25,
        "distance": 2.5
      }
    ],
    "per_page": 15,
    "total": 45
  }
}
```

### Search Clubs
**GET** `/api/clubs/search`

**Query Parameters:**
- `q` (required): Search query
- `limit` (optional): Number of results to return (default: 20)

**Response:** Same as list clubs but filtered by search query

### Find Nearby Clubs
**GET** `/api/clubs/nearby`

**Query Parameters:**
- `lat` (required): Latitude
- `lng` (required): Longitude
- `radius` (optional): Search radius in kilometers (default: 10)
- `limit` (optional): Number of results to return (default: 20)

**Response:** Same as list clubs but sorted by distance

### Filter Clubs
**GET** `/api/clubs/filter`

**Query Parameters:**
- `sport_ids` (optional): Comma-separated sport UUIDs
- `amenity_ids` (optional): Comma-separated amenity UUIDs
- `facility_ids` (optional): Comma-separated facility UUIDs
- `price_range` (optional): Price range (budget, moderate, premium)
- `rating_min` (optional): Minimum rating (1-5)
- `has_parking` (optional): Has parking (true/false)
- `has_locker_room` (optional): Has locker room (true/false)
- `accepts_membership` (optional): Accepts membership (true/false)

**Response:** Same as list clubs but filtered by specified criteria

### Get Club Details
**GET** `/api/clubs/{club}`

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": "uuid",
    "name": "Elite Fitness Center",
    "description": "Premium fitness facility with state-of-the-art equipment",
    "address": "123 Main Street",
    "city": "New York",
    "state": "NY",
    "country": "USA",
    "postal_code": "10001",
    "phone": "+1-555-0123",
    "email": "info@elitefitness.com",
    "website": "https://elitefitness.com",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "operating_hours": {
      "monday": {"open": "06:00", "close": "22:00"},
      "tuesday": {"open": "06:00", "close": "22:00"},
      "wednesday": {"open": "06:00", "close": "22:00"},
      "thursday": {"open": "06:00", "close": "22:00"},
      "friday": {"open": "06:00", "close": "22:00"},
      "saturday": {"open": "08:00", "close": "20:00"},
      "sunday": {"open": "10:00", "close": "18:00"}
    },
    "rating": 4.5,
    "total_reviews": 128,
    "is_verified": true,
    "is_active": true,
    "owner_id": "uuid",
    "created_at": "2024-01-15T10:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z",
    "sports": [
      {
        "id": "uuid",
        "name": "Basketball",
        "pivot": {
          "club_id": "uuid",
          "sport_id": "uuid",
          "created_at": "2024-01-15T10:00:00Z"
        }
      }
    ],
    "amenities": [
      {
        "id": "uuid",
        "name": "Free WiFi",
        "pivot": {
          "club_id": "uuid",
          "amenity_id": "uuid",
          "created_at": "2024-01-15T10:00:00Z"
        }
      }
    ],
    "facilities": [
      {
        "id": "uuid",
        "name": "Basketball Court",
        "pivot": {
          "club_id": "uuid",
          "facility_id": "uuid",
          "created_at": "2024-01-15T10:00:00Z"
        }
      }
    ],
    "images": [
      {
        "id": "uuid",
        "image_path": "club_images/image.jpg",
        "type": "gallery",
        "caption": "Main facility",
        "is_primary": true,
        "sort_order": 1
      }
    ]
  }
}
```

### Get Club Sports
**GET** `/api/clubs/{club}/sports`

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": "uuid",
      "name": "Basketball",
      "description": "Basketball sport",
      "icon": "basketball-icon.png",
      "is_active": true,
      "pivot": {
        "club_id": "uuid",
        "sport_id": "uuid",
        "created_at": "2024-01-15T10:00:00Z"
      }
    }
  ]
}
```

### Get Club Amenities
**GET** `/api/clubs/{club}/amenities`

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": "uuid",
      "name": "Free WiFi",
      "description": "High-speed wireless internet",
      "icon": "wifi-icon.png",
      "is_active": true,
      "pivot": {
        "club_id": "uuid",
        "amenity_id": "uuid",
        "created_at": "2024-01-15T10:00:00Z"
      }
    }
  ]
}
```

### Get Club Facilities
**GET** `/api/clubs/{club}/facilities`

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": "uuid",
      "name": "Basketball Court",
      "description": "Full-size basketball court",
      "capacity": 20,
      "is_active": true,
      "pivot": {
        "club_id": "uuid",
        "facility_id": "uuid",
        "created_at": "2024-01-15T10:00:00Z"
      }
    }
  ]
}
```

### Get Club Images
**GET** `/api/clubs/{club}/images`

**Query Parameters:**
- `type` (optional): Filter by image type (gallery, logo, banner, interior, exterior)

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": "uuid",
      "image_path": "club_images/image.jpg",
      "image_name": "image.jpg",
      "type": "gallery",
      "caption": "Main facility",
      "alt_text": "Elite Fitness Center main facility",
      "is_primary": true,
      "sort_order": 1,
      "file_size": 2048576,
      "mime_type": "image/jpeg",
      "created_at": "2024-01-15T10:00:00Z"
    }
  ]
}
```

### Get Club Check-ins
**GET** `/api/clubs/{club}/check-ins`

**Query Parameters:**
- `date` (optional): Filter by specific date
- `limit` (optional): Number of results to return

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": "uuid",
      "user_id": "uuid",
      "club_id": "uuid",
      "membership_id": "uuid",
      "check_in_time": "2024-01-15T14:30:00Z",
      "check_out_time": "2024-01-15T16:45:00Z",
      "check_in_method": "qr_code",
      "location": "Main Entrance",
      "notes": "Regular workout",
      "user": {
        "id": "uuid",
        "name": "John Doe"
      },
      "membership": {
        "id": "uuid",
        "membership_type": "Premium"
      }
    }
  ]
}
```

### Get Club Events
**GET** `/api/clubs/{club}/events`

**Query Parameters:**
- `status` (optional): Filter by status (active, upcoming)
- `limit` (optional): Number of results to return

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": "uuid",
      "title": "Summer Basketball Tournament",
      "description": "Annual basketball tournament",
      "event_date": "2024-07-15",
      "event_time": "2024-07-15T14:00:00Z",
      "type": "tournament",
      "fee": 50.00,
      "max_participants": 32,
      "current_participants": 15,
      "is_active": true,
      "sport": {
        "id": "uuid",
        "name": "Basketball"
      }
    }
  ]
}
```

### Get Club Statistics
**GET** `/api/clubs/{club}/statistics`

**Response:**
```json
{
  "status": "success",
  "data": {
    "total_members": 250,
    "active_memberships": 220,
    "total_check_ins": 15420,
    "check_ins_this_month": 1250,
    "total_events": 15,
    "upcoming_events": 5,
    "total_event_registrations": 180,
    "average_rating": 4.5,
    "total_reviews": 128,
    "revenue_this_month": 25000.00,
    "popular_sports": [
      {"sport": "Basketball", "check_ins": 4500},
      {"sport": "Swimming", "check_ins": 3200}
    ],
    "peak_hours": [
      {"hour": "18", "check_ins": 450},
      {"hour": "19", "check_ins": 380}
    ]
  }
}
```

### Generate Club QR Code
**GET** `/api/clubs/{club}/qr-code`

**Response:**
```json
{
  "status": "success",
  "data": {
    "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
    "club_id": "uuid",
    "expires_at": "2024-01-15T16:00:00Z"
  }
}
```

## Club Management APIs

### Create Club
**POST** `/api/clubs`

**Request Body:**
```json
{
  "name": "Elite Fitness Center",
  "description": "Premium fitness facility with state-of-the-art equipment",
  "address": "123 Main Street",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "postal_code": "10001",
  "phone": "+1-555-0123",
  "email": "info@elitefitness.com",
  "website": "https://elitefitness.com",
  "latitude": 40.7128,
  "longitude": -74.0060,
  "operating_hours": {
    "monday": {"open": "06:00", "close": "22:00"},
    "tuesday": {"open": "06:00", "close": "22:00"},
    "wednesday": {"open": "06:00", "close": "22:00"},
    "thursday": {"open": "06:00", "close": "22:00"},
    "friday": {"open": "06:00", "close": "22:00"},
    "saturday": {"open": "08:00", "close": "20:00"},
    "sunday": {"open": "10:00", "close": "18:00"}
  }
}
```

**Response:** Same as get club details

### Update Club
**PUT** `/api/clubs/{club}`

**Request Body:** Same as create club (all fields optional)

**Response:** Same as get club details

### Delete Club
**DELETE** `/api/clubs/{club}`

**Response:**
```json
{
  "status": "success",
  "message": "Club deleted successfully"
}
```

### Toggle Club Status
**POST** `/api/clubs/{club}/toggle-status`

**Response:**
```json
{
  "status": "success",
  "message": "Club status updated successfully",
  "data": {
    "club": {
      "id": "uuid",
      "name": "Elite Fitness Center",
      "is_active": true
    }
  }
}
```

### Add Sports to Club
**POST** `/api/clubs/{club}/sports`

**Request Body:**
```json
{
  "sport_ids": ["uuid1", "uuid2", "uuid3"]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Sports added to club successfully",
  "data": {
    "added_sports": [
      {
        "id": "uuid",
        "name": "Basketball",
        "pivot": {
          "club_id": "uuid",
          "sport_id": "uuid",
          "created_at": "2024-01-15T10:00:00Z"
        }
      }
    ]
  }
}
```

### Remove Sport from Club
**DELETE** `/api/clubs/{club}/sports/{sport}`

**Response:**
```json
{
  "status": "success",
  "message": "Sport removed from club successfully"
}
```

### Add Amenities to Club
**POST** `/api/clubs/{club}/amenities`

**Request Body:**
```json
{
  "amenity_ids": ["uuid1", "uuid2", "uuid3"]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Amenities added to club successfully",
  "data": {
    "added_amenities": [
      {
        "id": "uuid",
        "name": "Free WiFi",
        "pivot": {
          "club_id": "uuid",
          "amenity_id": "uuid",
          "created_at": "2024-01-15T10:00:00Z"
        }
      }
    ]
  }
}
```

### Remove Amenity from Club
**DELETE** `/api/clubs/{club}/amenities/{amenity}`

**Response:**
```json
{
  "status": "success",
  "message": "Amenity removed from club successfully"
}
```

### Add Facilities to Club
**POST** `/api/clubs/{club}/facilities`

**Request Body:**
```json
{
  "facility_ids": ["uuid1", "uuid2", "uuid3"]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Facilities added to club successfully",
  "data": {
    "added_facilities": [
      {
        "id": "uuid",
        "name": "Basketball Court",
        "pivot": {
          "club_id": "uuid",
          "facility_id": "uuid",
          "created_at": "2024-01-15T10:00:00Z"
        }
      }
    ]
  }
}
```

### Remove Facility from Club
**DELETE** `/api/clubs/{club}/facilities/{facility}`

**Response:**
```json
{
  "status": "success",
  "message": "Facility removed from club successfully"
}
```

### Add Image to Club
**POST** `/api/clubs/{club}/images`

**Request Body (Form Data):**
- `image`: Image file (JPEG, PNG, JPG, GIF, WebP, max 5MB)
- `type` (optional): Image type (gallery, logo, banner, interior, exterior)
- `caption` (optional): Image caption
- `alt_text` (optional): Alt text for accessibility
- `is_primary` (optional): Set as primary image

**Response:**
```json
{
  "status": "success",
  "message": "Image added to club successfully",
  "data": {
    "image": {
      "id": "uuid",
      "image_path": "club_images/image.jpg",
      "type": "gallery",
      "caption": "Main facility",
      "is_primary": true,
      "sort_order": 1
    }
  }
}
```

### Remove Image from Club
**DELETE** `/api/clubs/{club}/images/{image}`

**Response:**
```json
{
  "status": "success",
  "message": "Image removed from club successfully"
}
```

### Check In to Club
**POST** `/api/clubs/{club}/check-in`

**Request Body:**
```json
{
  "membership_id": "uuid",
  "check_in_method": "manual",
  "location": "Main Entrance",
  "notes": "Regular workout"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Check-in successful",
  "data": {
    "check_in": {
      "id": "uuid",
      "user_id": "uuid",
      "club_id": "uuid",
      "membership_id": "uuid",
      "check_in_time": "2024-01-15T14:30:00Z",
      "check_in_method": "manual",
      "location": "Main Entrance",
      "notes": "Regular workout"
    }
  }
}
```

## Club Administration APIs

### Admin Club Index
**GET** `/api/clubs/admin`

**Query Parameters:**
- `verification_status` (optional): Filter by verification status (verified, unverified, pending)
- `status` (optional): Filter by status (active, inactive)
- `owner_id` (optional): Filter by owner UUID

**Response:** Same as list clubs with additional admin fields

### Verify Club
**POST** `/api/clubs/admin/{club}/verify`

**Response:**
```json
{
  "status": "success",
  "message": "Club verified successfully",
  "data": {
    "club": {
      "id": "uuid",
      "name": "Elite Fitness Center",
      "is_verified": true,
      "verified_at": "2024-01-15T10:00:00Z"
    }
  }
}
```

### Unverify Club
**POST** `/api/clubs/admin/{club}/unverify`

**Request Body:**
```json
{
  "reason": "Documentation incomplete"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Club unverified successfully",
  "data": {
    "club": {
      "id": "uuid",
      "name": "Elite Fitness Center",
      "is_verified": false,
      "verified_at": null
    }
  }
}
```

### Admin Club Statistics
**GET** `/api/clubs/admin/statistics`

**Response:**
```json
{
  "status": "success",
  "data": {
    "total_clubs": 150,
    "verified_clubs": 120,
    "unverified_clubs": 25,
    "pending_verification": 5,
    "active_clubs": 140,
    "inactive_clubs": 10,
    "clubs_by_city": {
      "New York": 25,
      "Los Angeles": 20,
      "Chicago": 15
    },
    "clubs_by_state": {
      "NY": 30,
      "CA": 25,
      "IL": 18
    },
    "total_members": 25000,
    "total_revenue": 1250000.00,
    "average_rating": 4.2,
    "top_performing_clubs": [
      {
        "club_id": "uuid",
        "name": "Elite Fitness Center",
        "revenue": 50000.00,
        "members": 500,
        "rating": 4.8
      }
    ],
    "recent_registrations": [
      {
        "date": "2024-01-15",
        "count": 25
      }
    ]
  }
}
```

## Authentication
All club management and administration APIs require authentication using Sanctum tokens. Include the token in the Authorization header:
```
Authorization: Bearer your_token_here
```

Club management APIs require `club_owner` or `admin` role.
Club administration APIs require `admin` role.

## Error Handling
All APIs return errors in the following format:
```json
{
  "status": "error",
  "message": "Error message",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

Common HTTP status codes:
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Failed
- `500`: Internal Server Error

## Rate Limiting
Club API endpoints are rate limited based on the following rules:
- Public endpoints: 60 requests per minute
- Management endpoints: 30 requests per minute
- File upload endpoints: 10 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1638360000
```</content>
<parameter name="filePath">d:\vscode\temp\Sports Club Backend\sports-club-backend\CLUBS_API.md
