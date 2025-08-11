# Sports Club Backend - Postman Testing Guide

## Base Configuration

**Base URL:** `http://localhost:8000/api`

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

**⚠️ Save the token from login/register response for protected routes!**

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

## 5. Change Password

**Endpoint:** `POST /api/auth/change-password`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Request Body:**
```json
{
    "current_password": "password123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "Password changed successfully"
}
```

---

## 6. Forgot Password

**Endpoint:** `POST /api/auth/forgot-password`

**Request Body:**
```json
{
    "email": "john@example.com"
}
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "Password reset link sent to your email"
}
```

---

## 7. Reset Password

**Endpoint:** `POST /api/auth/reset-password`

**Request Body:**
```json
{
    "email": "john@example.com",
    "token": "reset-token-from-email",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "Password reset successful"
}
```

---

## 8. Logout from Current Device

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

---

## 9. Logout from All Devices

**Endpoint:** `POST /api/auth/logout-all`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "Logged out from all devices"
}
```

---

## 10. Deactivate Account

**Endpoint:** `POST /api/auth/deactivate-account`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Request Body:**
```json
{
    "password": "password123"
}
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "Account deactivated successfully"
}
```

---

## Error Response Examples

### Validation Error (422):
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

### Unauthorized (401):
```json
{
    "status": "error",
    "message": "Invalid credentials"
}
```

### Rate Limit Exceeded (429):
```json
{
    "status": "error",
    "message": "Too many authentication attempts. Please try again in 15 minutes.",
    "retry_after": 900
}
```

---

## Testing Workflow

### Step 1: Start Laravel Server
```bash
php artisan serve
```

### Step 2: Test Registration
1. Create a new request in Postman
2. Set method to POST
3. Set URL to `http://localhost:8000/api/auth/register`
4. Add headers and request body as shown above
5. Send request and save the token

### Step 3: Test Login
1. Use the same email/password from registration
2. Save the new token

### Step 4: Test Protected Routes
1. Use the token in Authorization header
2. Test profile endpoints, password change, etc.

### Step 5: Test Logout
1. Test single device logout
2. Test logout from all devices

---

## Security Features Implemented

1. **Rate Limiting**: 10 requests per minute on auth endpoints
2. **Token-based Authentication**: Using Laravel Sanctum
3. **Password Hashing**: Using bcrypt
4. **UUID Primary Keys**: For better security
5. **Input Validation**: Comprehensive validation rules
6. **Account Status Checks**: Active/inactive user validation

---

## Environment Setup

Make sure your `.env` file has:
```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
# or sqlite for testing
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

MAIL_MAILER=log
# For password reset emails
```

---

## Tips for Testing

1. **Create a Postman Collection** with all endpoints
2. **Use Environment Variables** for base URL and token
3. **Test Edge Cases** like invalid emails, weak passwords
4. **Test Rate Limiting** by making multiple rapid requests
5. **Verify Token Expiration** behavior
6. **Test with Different User Types** (trainer vs regular user)
