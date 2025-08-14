# Sports Club Backend - Sports APIs Testing

## Base Configuration

**Base URL:** `https://sports-pass-app-backend.onrender.com/api/`

### Common Headers (Public Requests)
```
Content-Type: application/json
Accept: application/json
```

### Headers for Protected (Admin/Trainer) Routes
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 1. List Sports (Public)
**Endpoint:** `GET /sports`

**Query Parameters (optional):**
- `search` (string) – partial match on name or description (case-insensitive)
- `is_active` (true|false) – filter by active state
- `per_page` (int, default 15) – pagination size
- `page` (int) – page number

**Example:** `/api/sports?search=cricket&is_active=true&per_page=10`

**Sample Response (200):**
```json
{
  "status": "success",
  "data": {
    "sports": {
      "current_page": 1,
      "data": [
        {
          "id": "cricket",
          "name": "Cricket",
          "description": "Popular bat-and-ball sport.",
          "icon": "🏏",
          "color": "#3478f6",
          "is_active": true,
          "created_at": "2025-08-13T11:20:00.000000Z",
          "updated_at": "2025-08-13T11:20:00.000000Z"
        }
      ],
      "per_page": 10,
      "total": 1
    }
  }
}
```

---

## 2. List Active Sports (Public)
**Endpoint:** `GET /sports/active`

Returns all sports where `is_active = true` (no pagination).

**Sample Response (200):**
```json
{
  "status": "success",
  "data": {
    "sports": [
      { "id": "cricket", "name": "Cricket", "icon": "🏏", "color": "#3478f6", "is_active": true },
      { "id": "football", "name": "Football", "icon": "⚽", "color": "#34a853", "is_active": true }
    ]
  }
}
```

---

## 3. Show Single Sport (Public)
**Endpoint:** `GET /sports/{sport}`

`{sport}` resolves by the string primary key (slug-style ID) – e.g. `cricket`.

**Sample Response (200):**
```json
{
  "status": "success",
  "data": {
    "sport": {
      "id": "cricket",
      "name": "Cricket",
      "description": "Popular bat-and-ball sport.",
      "icon": "🏏",
      "color": "#3478f6",
      "is_active": true,
      "created_at": "2025-08-13T11:20:00.000000Z",
      "updated_at": "2025-08-13T11:20:00.000000Z"
    }
  }
}
```

**Not Found (404):**
```json
{ "status": "error", "message": "Sport not found" }
```

---

## 4. Create Sport (Protected)
**Endpoint:** `POST /admin/sports`

Requires valid Bearer token (intended for admin/trainer). Authorization middleware/role check should be added in code (not yet enforced in routes).

**Request Body:**
```json
{
  "name": "Table Tennis",
  "description": "Fast-paced indoor racket sport.",
  "icon": "🏓",
  "color": "#ff5722",
  "is_active": true
}
```

Notes:
- `id` (primary key) is auto-generated from the name (slugified) unless you explicitly provide a custom string id in implementation (current model auto-sets if missing).
- `icon` & `color` optional but recommended.

**Sample Response (201):**
```json
{
  "status": "success",
  "message": "Sport created successfully",
  "data": {
    "sport": {
      "id": "table-tennis",
      "name": "Table Tennis",
      "description": "Fast-paced indoor racket sport.",
      "icon": "🏓",
      "color": "#ff5722",
      "is_active": true,
      "created_at": "2025-08-14T07:10:00.000000Z",
      "updated_at": "2025-08-14T07:10:00.000000Z"
    }
  }
}
```

**Validation Error (422) Example:**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": { "name": ["The name field is required."] }
}
```

---

## 5. Update Sport (Protected)
**Endpoint:** `PUT /admin/sports/{sport}`

**Request Body (any subset of fields):**
```json
{
  "name": "Table Tennis",
  "description": "Indoor racket sport with a lightweight ball.",
  "icon": "🏓",
  "color": "#ff7043",
  "is_active": false
}
```

**Sample Response (200):**
```json
{
  "status": "success",
  "message": "Sport updated successfully",
  "data": { "sport": { "id": "table-tennis", "name": "Table Tennis", "is_active": false } }
}
```

---

## 6. Toggle Sport Status (Protected)
**Endpoint:** `POST /admin/sports/{sport}/toggle-status`

Toggles `is_active` boolean.

**Sample Response (200):**
```json
{
  "status": "success",
  "message": "Sport status updated",
  "data": { "sport": { "id": "table-tennis", "is_active": true } }
}
```

---

## 7. Delete Sport (Protected)
**Endpoint:** `DELETE /admin/sports/{sport}`

**Sample Response (200):**
```json
{ "status": "success", "message": "Sport deleted successfully" }
```

**If Sport In Use (Example Future Constraint):**
```json
{ "status": "error", "message": "Cannot delete sport currently assigned to sessions" }
```

---

## 8. Error Response Patterns
| Scenario | Status | Structure |
|----------|--------|-----------|
| Validation failure | 422 | `{ "status":"error", "message":"Validation failed", "errors": { ... } }` |
| Unauthorized (no / bad token) | 401 | `{ "message": "Unauthenticated." }` (Laravel default) |
| Forbidden (future role check) | 403 | `{ "status":"error", "message":"Forbidden" }` |
| Not Found | 404 | `{ "status":"error", "message":"Sport not found" }` |
| Server Error | 500 | `{ "status":"error", "message":"Something went wrong" }` |

---

## 9. Postman / Testing Tips
1. Import this file or copy endpoints manually.
2. Create an environment variable `base_url` = `https://sports-pass-app-backend.onrender.com/api`.
3. For protected routes: obtain token from Auth APIs (see `AUTH_APIs.md`) and set a Postman variable `auth_token`.
4. In Authorization tab for protected requests choose type `Bearer Token` and use `{{auth_token}}`.
5. To quickly test slug creation, send multiple create requests with different names and inspect returned `id`.
6. Use query params in the list endpoint to test search & filtering.

---

## 10. Future Enhancements (Recommended)
- Add role/ability middleware to restrict `/admin/sports/*` routes.
- Add caching layer for `/sports/active` (e.g. cache for 5–10 minutes).
- Add soft deletes if historical analytics needed.
- Add image upload endpoint for sport icons/logos (currently simple emoji/string).

---

## Quick Reference
| Purpose | Method | URL |
|---------|--------|-----|
| List (paginated) | GET | /sports |
| List active | GET | /sports/active |
| Show | GET | /sports/{sport} |
| Create | POST | /admin/sports |
| Update | PUT | /admin/sports/{sport} |
| Toggle status | POST | /admin/sports/{sport}/toggle-status |
| Delete | DELETE | /admin/sports/{sport} |

---

If anything needs to be adjusted to match new backend changes, update this document alongside code modifications.
