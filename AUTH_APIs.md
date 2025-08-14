# Sports Club Backend - Rest APIs Testing

## Base Configuration

**Base URL:** `https://sports-pass-app-backend.onrender.com/api/`

### Headers for All Requests
```
Content-Type: application/json
Accept: application/json
```

### Headers for Protected Routes
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 1. User Registration

**Endpoint:** `POST /api/auth/register`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+923001234567",
    "password": "password123",
    "password_confirmation": "password123",
    "date_of_birth": "1990-01-15",
    "gender": "male",
    "is_trainer": false
}
```

**Expected Response (201):**
```json
{
    "status": "success",
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": "uuid-here",
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+923001234567",
            "date_of_birth": "1990-01-15",
            "gender": "male",
            "profile_image_url": null,
            "is_trainer": false,
            "is_verified": false,
            "is_active": true,
            "join_date": "2025-08-11",
            "last_login": null,
            "created_at": "2025-08-11T12:00:00.000000Z",
            "updated_at": "2025-08-11T12:00:00.000000Z"
        },
        "token": "1|token-string-here",
        "token_type": "Bearer"
    }
}
```

---

## 2. User Login

**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123",
    "device_name": "John's iPhone"
}
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": "uuid-here",
            "name": "John Doe",
            "email": "john@example.com",
            // ... other user fields
            "last_login": "2025-08-11T12:05:00.000000Z"
        },
        "token": "2|new-token-string-here",
        "token_type": "Bearer"
    }
}
```

**Save the token from login/register response for protected routes!**

---

## 3. Get Current User Profile

**Endpoint:** `GET /api/auth/me`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Expected Response (200):**
```json
{
    "status": "success",
    "data": {
        "user": {
            "id": "uuid-here",
            "name": "John Doe",
            // ... complete user profile
        }
    }
}
```

---

## 4. Update User Profile

**Endpoint:** `PUT /api/auth/update-profile`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Request Body:**
```json
{
    "name": "John Smith",
    "phone": "+923001234568",
    "date_of_birth": "1990-01-15",
    "gender": "male",
    "profile_image_url": "https://example.com/profile.jpg"
}
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "Profile updated successfully",
    "data": {
        "user": {
            // ... updated user data
        }
    }
}
```


---

## 5. Logout from Current Device

**Endpoint:** `POST /api/auth/logout`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "Logged out successfully"
}
```


