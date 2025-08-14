# Role-Based Access Control API Documentation

## User Roles

The system supports three user roles with different access levels:

1. **member** - Basic users (default role)
2. **admin** - Administrative users with sports management privileges
3. **owner** - Highest privilege level with full system access

## Additional Privileges

- **is_trainer** - Boolean flag that grants trainer-specific access (independent of role)

## Access Control Overview

### Public Endpoints (No Authentication Required)
- `GET /api/sports` - List all sports
- `GET /api/sports/active` - List active sports only
- `GET /api/sports/{id}` - Get specific sport details
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/forgot-password` - Forgot password
- `POST /api/auth/reset-password` - Reset password

### Member-Level Access (All Authenticated Users)
- `GET /api/auth/me` - Get current user profile
- `PUT /api/auth/update-profile` - Update own profile (limited fields)
- `POST /api/auth/logout` - Logout current session
- `POST /api/auth/logout-all` - Logout all sessions
- `POST /api/auth/change-password` - Change password
- `GET /api/member/dashboard` - Member dashboard

### Trainer-Level Access (is_trainer=true OR admin/owner role)
- `GET /api/trainer/dashboard` - Trainer dashboard
- All member-level endpoints

### Admin-Level Access (admin role only)
- `POST /api/admin/sports` - Create new sport
- `PUT /api/admin/sports/{id}` - Update sport
- `DELETE /api/admin/sports/{id}` - Delete sport
- `POST /api/admin/sports/{id}/toggle-status` - Toggle sport active status
- All trainer and member-level endpoints

### Owner-Level Access (owner role only)
- `GET /api/owner/users` - List all users
- `PUT /api/owner/users/{id}/role` - Change user role
- `PUT /api/owner/users/{id}/toggle-status` - Activate/deactivate user
- `PUT /api/auth/update-profile` - Update profile with role changes (own profile only)
- All trainer and member-level endpoints (but NOT sports CRUD operations)

## Authentication Headers

For all protected endpoints, include:
```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
Accept: application/json
```

## Error Responses

### 401 Unauthorized
```json
{
    "status": "error",
    "message": "Unauthenticated"
}
```

### 403 Forbidden (Insufficient Role)
```json
{
    "status": "error",
    "message": "Insufficient permissions. Required roles: admin, owner",
    "user_role": "member"
}
```

### 403 Forbidden (Trainer Access Required)
```json
{
    "status": "error",
    "message": "Access denied. Trainer privileges or admin/owner role required.",
    "user_role": "member",
    "is_trainer": false
}
```

### 403 Forbidden (Account Deactivated)
```json
{
    "status": "error",
    "message": "Account is deactivated"
}
```

## Example API Usage

### Admin Creating a Sport
```bash
curl -X POST https://your-api.com/api/admin/sports \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tennis",
    "display_name": "Tennis",
    "icon": "🎾",
    "color": "#32CD32",
    "description": "Tennis sport",
    "number_of_services": 4
  }'
```

### Owner Changing User Role
```bash
curl -X PUT https://your-api.com/api/owner/users/123/role \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_role": "admin",
    "is_trainer": true
  }'
```

### Trainer Dashboard Access
```bash
curl -X GET https://your-api.com/api/trainer/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Role Middleware Usage in Routes

```php
// Admin or Owner only
Route::middleware('role:admin,owner')->group(function () {
    // Routes here
});

// Trainer privileges (trainers OR admin/owner)
Route::middleware('trainer')->group(function () {
    // Routes here
});

// Owner only
Route::middleware('role:owner')->group(function () {
    // Routes here
});
```
