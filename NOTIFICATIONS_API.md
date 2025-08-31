# Notifications API Documentation

## Overview
The Notifications API provides endpoints for managing user notifications within the Sports Club Pakistan system. This system allows users to receive various types of notifications including membership updates, event reminders, trainer notifications, check-in confirmations, and payment confirmations.

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
- **Member**: Can create, view, update, and delete their own notifications
- **Owner**: Can create, view, update, and delete their own notifications, plus manage any notification
- **Admin**: Can create, view, update, and delete their own notifications, plus manage any notification

---

## Endpoints

### 1. List Notifications
**GET** `/api/notifications`

Returns a list of notifications for the authenticated user.

#### Query Parameters:
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `is_read` | boolean | Filter by read status | `is_read=false` |
| `type` | string | Filter by notification type | `type=membership` |
| `sort_by` | string | Sort field | `sort_by=created_at` |
| `sort_order` | string | Sort direction | `sort_order=desc` |

#### Response:
```json
{
    "status": "success",
    "data": {
        "notifications": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "user_id": "123e4567-e89b-12d3-a456-426614174001",
                "title": "Membership Activated",
                "message": "Your membership has been successfully activated.",
                "type": "membership",
                "is_read": false,
                "action_url": "/memberships",
                "metadata": {
                    "membership_id": "123e4567-e89b-12d3-a456-426614174002",
                    "membership_number": "MEM123ABC"
                },
                "expires_at": null,
                "created_at": "2025-08-31T10:00:00.000000Z",
                "updated_at": "2025-08-31T10:00:00.000000Z",
                "user": {
                    "id": "123e4567-e89b-12d3-a456-426614174001",
                    "name": "Ahmed Khan",
                    "email": "ahmed@example.com"
                }
            }
        ]
    }
}
```

### 2. Create Notification
**POST** `/api/notifications`

Creates a new notification.

#### Request Body:
```json
{
    "user_id": "123e4567-e89b-12d3-a456-426614174001",
    "title": "Welcome to Sports Club",
    "message": "Welcome! Your account has been created successfully.",
    "type": "info",
    "action_url": "/dashboard",
    "metadata": {
        "welcome_bonus": true
    },
    "expires_at": "2025-09-30T23:59:59.000000Z"
}
```

#### Response:
```json
{
    "status": "success",
    "message": "Notification created successfully",
    "data": {
        "notification": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "user_id": "123e4567-e89b-12d3-a456-426614174001",
            "title": "Welcome to Sports Club",
            "message": "Welcome! Your account has been created successfully.",
            "type": "info",
            "is_read": false,
            "action_url": "/dashboard",
            "metadata": {
                "welcome_bonus": true
            },
            "expires_at": "2025-09-30T23:59:59.000000Z",
            "created_at": "2025-08-31T10:00:00.000000Z",
            "updated_at": "2025-08-31T10:00:00.000000Z"
        }
    }
}
```

### 3. Get Notification
**GET** `/api/notifications/{notification}`

Returns a specific notification.

#### Response:
```json
{
    "status": "success",
    "data": {
        "notification": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "user_id": "123e4567-e89b-12d3-a456-426614174001",
            "title": "Membership Activated",
            "message": "Your membership has been successfully activated.",
            "type": "membership",
            "is_read": false,
            "action_url": "/memberships",
            "metadata": {
                "membership_id": "123e4567-e89b-12d3-a456-426614174002"
            },
            "expires_at": null,
            "created_at": "2025-08-31T10:00:00.000000Z",
            "updated_at": "2025-08-31T10:00:00.000000Z",
            "user": {
                "id": "123e4567-e89b-12d3-a456-426614174001",
                "name": "Ahmed Khan",
                "email": "ahmed@example.com"
            }
        }
    }
}
```

### 4. Update Notification
**PUT** `/api/notifications/{notification}`

Updates a notification (primarily for marking as read/unread).

#### Request Body:
```json
{
    "is_read": true
}
```

#### Response:
```json
{
    "status": "success",
    "message": "Notification updated successfully",
    "data": {
        "notification": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "user_id": "123e4567-e89b-12d3-a456-426614174001",
            "title": "Membership Activated",
            "message": "Your membership has been successfully activated.",
            "type": "membership",
            "is_read": true,
            "action_url": "/memberships",
            "metadata": {
                "membership_id": "123e4567-e89b-12d3-a456-426614174002"
            },
            "expires_at": null,
            "created_at": "2025-08-31T10:00:00.000000Z",
            "updated_at": "2025-08-31T10:00:00.000000Z"
        }
    }
}
```

### 5. Delete Notification
**DELETE** `/api/notifications/{notification}`

Deletes a notification.

#### Response:
```json
{
    "status": "success",
    "message": "Notification deleted successfully"
}
```

### 6. Mark Notification as Read
**PATCH** `/api/notifications/{notification}/read`

Marks a specific notification as read.

#### Response:
```json
{
    "status": "success",
    "message": "Notification marked as read",
    "data": {
        "notification": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "is_read": true,
            "updated_at": "2025-08-31T10:00:00.000000Z"
        }
    }
}
```

### 7. Mark Notification as Unread
**PATCH** `/api/notifications/{notification}/unread`

Marks a specific notification as unread.

#### Response:
```json
{
    "status": "success",
    "message": "Notification marked as unread",
    "data": {
        "notification": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "is_read": false,
            "updated_at": "2025-08-31T10:00:00.000000Z"
        }
    }
}
```

### 8. Mark All Notifications as Read
**POST** `/api/notifications/mark-all-read`

Marks all notifications for the authenticated user as read.

#### Response:
```json
{
    "status": "success",
    "message": "All notifications marked as read"
}
```

### 9. Get Notification Statistics
**GET** `/api/notifications/statistics`

Returns notification statistics for the authenticated user.

#### Response:
```json
{
    "status": "success",
    "data": {
        "statistics": {
            "total_notifications": 25,
            "unread_notifications": 5,
            "read_notifications": 20
        }
    }
}
```

---

## Notification Types

The system supports the following notification types:

| Type | Description | Example Use Case |
|------|-------------|------------------|
| `info` | General information | Welcome messages, system announcements |
| `success` | Success confirmations | Membership activation, payment success |
| `warning` | Warning messages | Expiring memberships, payment reminders |
| `error` | Error notifications | Failed payments, system errors |
| `membership` | Membership-related | Activation, renewal, expiry notifications |
| `event` | Event-related | Event registration, reminders, updates |
| `trainer` | Trainer-related | Session bookings, trainer availability |
| `checkin` | Check-in related | Check-in confirmations, facility access |
| `payment` | Payment-related | Payment confirmations, refunds, failures |

---

## Data Structures

### Notification Object
```json
{
    "id": "UUID",
    "user_id": "UUID",
    "title": "string (max 200 chars)",
    "message": "text",
    "type": "enum: info, success, warning, error, membership, event, trainer, checkin, payment",
    "is_read": "boolean",
    "action_url": "string (max 500 chars, nullable)",
    "metadata": "JSON object (nullable)",
    "expires_at": "datetime (nullable)",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Validation Rules

#### Store Notification Request:
- `user_id`: required, exists:users,id
- `title`: required, string, max:200
- `message`: required, string
- `type`: required, in:info,success,warning,error,membership,event,trainer,checkin,payment
- `action_url`: nullable, string, max:500
- `metadata`: nullable, json
- `expires_at`: nullable, date

#### Update Notification Request:
- `is_read`: sometimes, boolean

---

## Error Responses

### 403 Unauthorized
```json
{
    "status": "error",
    "message": "Unauthorized to create notifications"
}
```

### 404 Not Found
```json
{
    "status": "error",
    "message": "Notification not found"
}
```

### 422 Validation Error
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "title": ["The title field is required."],
        "type": ["The selected type is invalid."]
    }
}
```

---

## Rate Limiting
- All notification endpoints are subject to standard API rate limiting
- Authentication endpoints have stricter limits (10 requests per minute)

---

## Best Practices

1. **Use appropriate notification types** for better user experience and filtering
2. **Include action URLs** when notifications require user action
3. **Set expiration dates** for time-sensitive notifications
4. **Use metadata** to store additional context or related IDs
5. **Keep titles concise** (under 200 characters) and messages clear
6. **Test notifications** in different scenarios to ensure proper delivery

---

## Integration Examples

### JavaScript (Fetch API)
```javascript
// Create a notification
const createNotification = async (notificationData) => {
    const response = await fetch('/api/notifications', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(notificationData)
    });
    return response.json();
};

// Mark notification as read
const markAsRead = async (notificationId) => {
    const response = await fetch(`/api/notifications/${notificationId}/read`, {
        method: 'PATCH',
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    return response.json();
};
```

### cURL Examples
```bash
# Create notification
curl -X POST /api/notifications \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "user_id": "123e4567-e89b-12d3-a456-426614174001",
    "title": "Welcome!",
    "message": "Welcome to Sports Club Pakistan",
    "type": "info"
  }'

# Get user notifications
curl -X GET /api/notifications \
  -H "Authorization: Bearer {token}"

# Mark all as read
curl -X POST /api/notifications/mark-all-read \
  -H "Authorization: Bearer {token}"
```
