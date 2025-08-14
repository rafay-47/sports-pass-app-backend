# Sports API with Tiers - Updated Documentation

This document describes the updated API endpoints for fetching sports with their associated tiers and services.

## Key Changes

1. **Performance Optimization**: Fixed the timeout issue caused by complex eager loading
2. **Conditional Loading**: Relationships are now loaded conditionally based on query parameters
3. **New Endpoints**: Added specialized endpoints for different use cases

## Endpoints

### 1. Get All Sports (Basic)
```
GET /api/sports
```

**Query Parameters:**
- `active` (optional): Filter by active status (true/false)
- `search` (optional): Search in name, display_name, and description
- `per_page` (optional, default: 15): Number of items per page
- `include_services` (optional, default: false): Include active services
- `include_tiers` (optional, default: false): Include active tiers

**Example:**
```bash
# Basic request (fastest)
GET /api/sports

# With tiers included
GET /api/sports?include_tiers=true

# With both tiers and services
GET /api/sports?include_tiers=true&include_services=true

# Active sports with tiers
GET /api/sports?active=true&include_tiers=true
```

### 2. Get Active Sports Only
```
GET /api/sports/active
```

**Query Parameters:**
- `include_services` (optional, default: false): Include active services
- `include_tiers` (optional, default: false): Include active tiers

**Example:**
```bash
# Active sports only (fastest)
GET /api/sports/active

# Active sports with tiers
GET /api/sports/active?include_tiers=true
```

### 3. Get Sports with Available Tiers (Recommended for Memberships)
```
GET /api/sports/with-available-tiers
```

This endpoint automatically filters tiers by current date and includes only tiers that are:
- Active (`is_active = true`)
- Available now (within start_date and end_date range)

**Query Parameters:**
- `active` (optional, default: true): Filter sports by active status
- `search` (optional): Search in sport names and descriptions

**Example:**
```bash
# Get all active sports with currently available tiers
GET /api/sports/with-available-tiers

# Search for specific sports with available tiers
GET /api/sports/with-available-tiers?search=basketball
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "sports": [
      {
        "id": "uuid",
        "name": "Basketball",
        "display_name": "Basketball",
        "description": "...",
        "number_of_services": 4,
        "active_services_count": 3,
        "is_active": true,
        "active_tiers": [
          {
            "id": "uuid",
            "tier_name": "basic",
            "display_name": "Basketball Basic",
            "description": "...",
            "price": "45.00",
            "discounted_price": "45.00",
            "duration_days": 30,
            "features": ["Court access", "Group training"],
            "is_available": true
          }
        ]
      }
    ],
    "filtered_date": "2025-08-14"
  }
}
```

### 4. Get Single Sport
```
GET /api/sports/{sport_id}
```

**Query Parameters:**
- `include_services` (optional, default: true): Include active services
- `include_tiers` (optional, default: true): Include active tiers

**Example:**
```bash
# Get sport with default inclusions (services and tiers)
GET /api/sports/uuid-here

# Get sport without relationships (fastest)
GET /api/sports/uuid-here?include_services=false&include_tiers=false

# Get sport with only tiers
GET /api/sports/uuid-here?include_services=false&include_tiers=true
```

### 5. Get Sport Tiers (Dedicated Endpoint)
```
GET /api/sports/{sport_id}/tiers
```

**Query Parameters:**
- `active` (optional): Filter by active status
- `available` (optional): Filter by availability (date range)
- `min_price` / `max_price` (optional): Filter by price range
- `search` (optional): Search in tier names and descriptions

### 6. Get Available Sport Tiers (Public)
```
GET /api/sports/{sport_id}/tiers/available
```

Returns only tiers that are active and currently available (within date range).

## Performance Guidelines

### For Best Performance:

1. **Basic Listing**: Use `/api/sports` without includes for fastest response
2. **With Tiers**: Use `/api/sports/with-available-tiers` for membership selection
3. **Single Sport**: Use `/api/sports/{id}` for detailed view (includes relationships by default)

### Query Parameter Combinations:

```bash
# Fastest - No relationships
GET /api/sports?include_tiers=false&include_services=false

# Moderate - Only tiers
GET /api/sports?include_tiers=true&include_services=false

# Full - All relationships (use sparingly)
GET /api/sports?include_tiers=true&include_services=true
```

## Frontend Usage Examples

### Sports Listing Page
```javascript
// Fast loading for sports list
const response = await fetch('/api/sports?active=true');
```

### Membership Selection Page
```javascript
// Get sports with available tiers for purchase
const response = await fetch('/api/sports/with-available-tiers');
```

### Sport Detail Page
```javascript
// Get full sport details with tiers and services
const response = await fetch(`/api/sports/${sportId}`);
```

### Tier Selection Component
```javascript
// Get available tiers for a specific sport
const response = await fetch(`/api/sports/${sportId}/tiers/available`);
```

## Migration Notes

If you were previously using:
- `/api/sports` and experiencing timeouts → Use `/api/sports?include_tiers=true` or `/api/sports/with-available-tiers`
- Complex tier filtering → Use the dedicated tier endpoints
- Membership selection → Use `/api/sports/with-available-tiers`

## Error Handling

All endpoints return consistent error responses:

```json
{
  "status": "error",
  "message": "Error description",
  "error": "Technical details (in debug mode)"
}
```

Common HTTP status codes:
- `200`: Success
- `422`: Validation Error
- `404`: Resource Not Found
- `500`: Server Error
