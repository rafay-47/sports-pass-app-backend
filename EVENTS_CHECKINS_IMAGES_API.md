# Events, Check-ins, and Club Images API Documentation

## Overview
This document provides comprehensive API documentation for the Events, Event Registrations, Check-ins, and Club Images management system in the Sports Club Backend.

## Table of Contents
1. [Events API](#events-api)
2. [Event Registrations API](#event-registrations-api)
3. [Check-ins API](#check-ins-api)
4. [Club Images API](#club-images-api)
5. [Authentication](#authentication)
6. [Error Handling](#error-handling)
7. [Rate Limiting](#rate-limiting)

## Events API

### List Events
**GET** `/api/events`

**Query Parameters:**
- `sport_id` (optional): Filter by sport UUID
- `category` (optional): Filter by category (beginner, intermediate, advanced)
- `type` (optional): Filter by type (tournament, workshop, class, competition)
- `start_date` (optional): Filter by start date (YYYY-MM-DD)
- `end_date` (optional): Filter by end date (YYYY-MM-DD)
- `status` (optional): Filter by status (active, upcoming)
- `search` (optional): Search in title and description

**Response:**
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid",
        "title": "Summer Basketball Tournament",
        "description": "Annual basketball tournament",
        "sport_id": "uuid",
        "event_date": "2024-07-15",
        "event_time": "2024-07-15 14:00:00",
        "end_date": "2024-07-15",
        "end_time": "2024-07-15 18:00:00",
        "type": "tournament",
        "category": "intermediate",
        "difficulty": "medium",
        "fee": 50.00,
        "max_participants": 32,
        "current_participants": 15,
        "location": "Main Court",
        "organizer": "Sports Club",
        "requirements": ["Valid membership", "Basketball shoes"],
        "prizes": ["1st: $500", "2nd: $300", "3rd: $100"],
        "registration_deadline": "2024-07-10",
        "is_active": true,
        "created_at": "2024-06-01T10:00:00Z",
        "updated_at": "2024-06-01T10:00:00Z",
        "sport": {
          "id": "uuid",
          "name": "Basketball",
          "description": "Basketball sport"
        },
        "registrations_count": 15
      }
    ],
    "per_page": 15,
    "total": 25
  }
}
```

### Create Event
**POST** `/api/events`

**Request Body:**
```json
{
  "title": "Summer Basketball Tournament",
  "description": "Annual basketball tournament for all skill levels",
  "sport_id": "uuid",
  "event_date": "2024-07-15",
  "event_time": "2024-07-15 14:00:00",
  "end_date": "2024-07-15",
  "end_time": "2024-07-15 18:00:00",
  "type": "tournament",
  "category": "intermediate",
  "difficulty": "medium",
  "fee": 50.00,
  "max_participants": 32,
  "location": "Main Court",
  "organizer": "Sports Club",
  "requirements": ["Valid membership", "Basketball shoes"],
  "prizes": ["1st: $500", "2nd: $300", "3rd: $100"],
  "registration_deadline": "2024-07-10"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Event created successfully",
  "data": {
    "id": "uuid",
    "title": "Summer Basketball Tournament",
    "description": "Annual basketball tournament for all skill levels",
    "sport_id": "uuid",
    "event_date": "2024-07-15",
    "event_time": "2024-07-15 14:00:00",
    "end_date": "2024-07-15",
    "end_time": "2024-07-15 18:00:00",
    "type": "tournament",
    "category": "intermediate",
    "difficulty": "medium",
    "fee": 50.00,
    "max_participants": 32,
    "current_participants": 0,
    "location": "Main Court",
    "organizer": "Sports Club",
    "requirements": ["Valid membership", "Basketball shoes"],
    "prizes": ["1st: $500", "2nd: $300", "3rd: $100"],
    "registration_deadline": "2024-07-10",
    "is_active": true,
    "created_at": "2024-06-01T10:00:00Z",
    "updated_at": "2024-06-01T10:00:00Z",
    "sport": {
      "id": "uuid",
      "name": "Basketball",
      "description": "Basketball sport"
    }
  }
}
```

### Get Event
**GET** `/api/events/{event}`

**Response:** Same as create event response

### Update Event
**PUT** `/api/events/{event}`

**Request Body:** Same as create event (all fields optional)

**Response:** Same as create event response

### Delete Event
**DELETE** `/api/events/{event}`

**Response:**
```json
{
  "status": "success",
  "message": "Event deleted successfully"
}
```

### Get Events by Sport
**GET** `/api/events/sport/{sport}`

**Response:** Paginated list of events for the specified sport

### Register for Event
**POST** `/api/events/{event}/register`

**Request Body:**
```json
{
  "payment_status": "pending",
  "notes": "Looking forward to the tournament"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Successfully registered for event",
  "data": {
    "id": "uuid",
    "event_id": "uuid",
    "user_id": "uuid",
    "registration_date": "2024-06-01T10:00:00Z",
    "status": "confirmed",
    "payment_status": "pending",
    "payment_amount": 50.00,
    "notes": "Looking forward to the tournament",
    "event": {
      "id": "uuid",
      "title": "Summer Basketball Tournament"
    },
    "user": {
      "id": "uuid",
      "name": "John Doe"
    }
  }
}
```

### Get User's Event Registrations
**GET** `/api/events/my-registrations`

**Response:** Paginated list of user's event registrations

### Get Event Statistics
**GET** `/api/events/statistics`

**Response:**
```json
{
  "status": "success",
  "data": {
    "total_events": 25,
    "active_events": 20,
    "upcoming_events": 15,
    "total_registrations": 150,
    "events_by_type": {
      "tournament": 10,
      "workshop": 8,
      "class": 5,
      "competition": 2
    },
    "events_by_category": {
      "beginner": 8,
      "intermediate": 10,
      "advanced": 7
    }
  }
}
```

## Event Registrations API

### List Event Registrations
**GET** `/api/event-registrations`

**Query Parameters:**
- `event_id` (optional): Filter by event UUID
- `user_id` (optional): Filter by user UUID
- `status` (optional): Filter by status (pending, confirmed, cancelled)
- `payment_status` (optional): Filter by payment status (pending, paid, refunded)
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date

### Create Event Registration
**POST** `/api/event-registrations`

**Request Body:**
```json
{
  "event_id": "uuid",
  "user_id": "uuid",
  "status": "confirmed",
  "payment_status": "pending",
  "payment_amount": 50.00,
  "payment_method": "credit_card",
  "notes": "Registration notes"
}
```

### Get Event Registration
**GET** `/api/event-registrations/{eventRegistration}`

### Update Event Registration
**PUT** `/api/event-registrations/{eventRegistration}`

### Delete Event Registration
**DELETE** `/api/event-registrations/{eventRegistration}`

### Get Registrations by Event
**GET** `/api/event-registrations/event/{event}`

### Get Registrations by User
**GET** `/api/event-registrations/user/{user}`

### Cancel Registration
**POST** `/api/event-registrations/{eventRegistration}/cancel`

### Confirm Registration
**POST** `/api/event-registrations/{eventRegistration}/confirm`

### Process Payment
**POST** `/api/event-registrations/{eventRegistration}/process-payment`

**Request Body:**
```json
{
  "payment_method": "credit_card",
  "transaction_id": "txn_1234567890"
}
```

### Get Registration Statistics
**GET** `/api/event-registrations/statistics`

## Check-ins API

### List Check-ins
**GET** `/api/check-ins`

**Query Parameters:**
- `club_id` (optional): Filter by club UUID
- `user_id` (optional): Filter by user UUID
- `membership_id` (optional): Filter by membership UUID
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date
- `today` (optional): Get today's check-ins only
- `checked_out` (optional): Filter by check-out status

### Create Check-in
**POST** `/api/check-ins`

**Request Body:**
```json
{
  "club_id": "uuid",
  "user_id": "uuid",
  "membership_id": "uuid",
  "check_in_method": "manual",
  "location": "Main Entrance",
  "notes": "Regular check-in"
}
```

### Get Check-in
**GET** `/api/check-ins/{checkIn}`

### Update Check-in
**PUT** `/api/check-ins/{checkIn}`

### Delete Check-in
**DELETE** `/api/check-ins/{checkIn}`

### Check Out
**POST** `/api/check-ins/{checkIn}/check-out`

**Request Body:**
```json
{
  "location": "Main Entrance",
  "notes": "Check-out notes"
}
```

### Get Check-ins by Club
**GET** `/api/check-ins/club/{club}`

### Get Check-ins by User
**GET** `/api/check-ins/user/{user}`

### Get Current Check-ins
**GET** `/api/check-ins/current`

### QR Code Check-in
**POST** `/api/check-ins/qr-check-in`

**Request Body:**
```json
{
  "qr_code": "base64_encoded_qr_data",
  "club_id": "uuid"
}
```

### Get Check-in Statistics
**GET** `/api/check-ins/statistics`

**Query Parameters:**
- `start_date` (optional): Start date for statistics
- `end_date` (optional): End date for statistics

## Club Images API

### List Club Images
**GET** `/api/club-images`

**Query Parameters:**
- `club_id` (optional): Filter by club UUID
- `type` (optional): Filter by type (gallery, logo, banner, interior, exterior)
- `is_primary` (optional): Filter by primary status

### Upload Club Image
**POST** `/api/club-images`

**Request Body (Form Data):**
- `club_id`: Club UUID
- `image`: Image file (JPEG, PNG, JPG, GIF, WebP, max 5MB)
- `type` (optional): Image type
- `caption` (optional): Image caption
- `alt_text` (optional): Alt text for accessibility
- `is_primary` (optional): Set as primary image
- `sort_order` (optional): Sort order

### Get Club Image
**GET** `/api/club-images/{clubImage}`

### Update Club Image
**PUT** `/api/club-images/{clubImage}`

### Delete Club Image
**DELETE** `/api/club-images/{clubImage}`

### Get Images by Club
**GET** `/api/club-images/club/{club}`

**Query Parameters:**
- `type` (optional): Filter by type
- `primary_only` (optional): Get only primary image

### Set Primary Image
**POST** `/api/club-images/{clubImage}/set-primary`

### Update Sort Order
**POST** `/api/club-images/update-sort-order`

**Request Body:**
```json
{
  "images": [
    {
      "id": "uuid",
      "sort_order": 1
    },
    {
      "id": "uuid",
      "sort_order": 2
    }
  ]
}
```

### Bulk Upload Images
**POST** `/api/club-images/bulk-upload`

**Request Body (Form Data):**
- `club_id`: Club UUID
- `images`: Array of image files
- `type` (optional): Image type for all images

### Get Image Statistics
**GET** `/api/club-images/statistics`

## Authentication
All API endpoints require authentication using Sanctum tokens. Include the token in the Authorization header:
```
Authorization: Bearer your_token_here
```

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
API endpoints are rate limited based on the following rules:
- Authentication endpoints: 10 requests per minute
- General API endpoints: 60 requests per minute
- File upload endpoints: 10 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1638360000
```</content>
<parameter name="filePath">d:\vscode\temp\Sports Club Backend\sports-club-backend\EVENTS_CHECKINS_IMAGES_API.md
