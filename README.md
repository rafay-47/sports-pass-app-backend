# Sports Club Backend API

A comprehensive Laravel-based REST API for managing sports clubs, memberships, and services.

## Features

### 🏆 Sports Management
- **Sports CRUD**: Complete management of sports categories
- **Dynamic Sports**: Support for any type of sport
- **Status Management**: Enable/disable sports dynamically

### 🎯 Services System
- **Sport Services**: Multiple services per sport (training, equipment rental, etc.)
- **Pricing Management**: Base prices with discount support
- **Duration Tracking**: Service duration in minutes
- **Automatic Counting**: Auto-update service counts per sport

### 🎪 Tier System
- **Membership Tiers**: Basic, Pro, Elite, and custom tiers
- **Time-based Availability**: Start and end dates for promotional offers
- **Feature Lists**: JSON-based feature management
- **Smart Pricing**: Automatic discount calculations
- **Flexible Duration**: Customizable membership periods

### 👥 User Management
- **Role-based Access**: Member, Admin, Owner roles
- **Trainer Support**: Special trainer role and permissions
- **Account Management**: Profile updates, password changes
- **Email Verification**: Secure account activation

### 🔐 Authentication & Security
- **Sanctum Authentication**: Token-based API security
- **Rate Limiting**: Protection against abuse
- **Role Middleware**: Endpoint-level access control
- **Password Reset**: Secure password recovery

## Quick Start

### Prerequisites
- PHP 8.1+
- PostgreSQL 13+
- Composer
- Laravel 11.x

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/rafay-47/sports-pass-app-backend.git
   cd sports-club-backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   Update your `.env` file with PostgreSQL credentials:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=sports_club
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed --class=UserSeeder
   php artisan db:seed --class=SportSeeder
   php artisan db:seed --class=SportServiceSeeder
   php artisan db:seed --class=TierSeeder
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api`

## API Documentation

### Core Endpoints

#### Sports
```
GET    /api/sports              # List all sports (with tiers & services)
GET    /api/sports/active       # List active sports only
GET    /api/sports/{id}         # Get specific sport with tiers & services
POST   /api/admin/sports        # Create sport (admin only)
PUT    /api/admin/sports/{id}   # Update sport (admin only)
DELETE /api/admin/sports/{id}   # Delete sport (admin only)
```

#### Sport Services
```
GET    /api/sport-services           # List all services
GET    /api/sports/{id}/services     # Get services for specific sport
POST   /api/admin/sport-services     # Create service (admin only)
PUT    /api/admin/sport-services/{id} # Update service (admin only)
DELETE /api/admin/sport-services/{id} # Delete service (admin only)
```

#### Tiers
```
GET    /api/tiers                        # List all tiers
GET    /api/sports/{id}/tiers            # Get tiers for specific sport
GET    /api/sports/{id}/tiers/available  # Get available tiers only
POST   /api/admin/tiers                  # Create tier (admin only)
PUT    /api/admin/tiers/{id}             # Update tier (admin only)
DELETE /api/admin/tiers/{id}             # Delete tier (admin only)
```

#### Authentication
```
POST   /api/auth/register        # User registration
POST   /api/auth/login           # User login
POST   /api/auth/logout          # User logout
GET    /api/auth/me              # Get current user
PUT    /api/auth/update-profile  # Update profile
POST   /api/auth/change-password # Change password
```

### Sample Response: Sport with Tiers & Services

```json
{
  "status": "success",
  "data": {
    "sport": {
      "id": "uuid",
      "name": "Basketball",
      "display_name": "Basketball",
      "description": "Professional basketball training and facilities",
      "number_of_services": 4,
      "active_services_count": 4,
      "active_tiers_count": 3,
      "is_active": true,
      "available_tiers": [
        {
          "id": "uuid",
          "tier_name": "basic",
          "display_name": "Basketball Basic",
          "price": "45.00",
          "discounted_price": "45.00",
          "duration_days": 30,
          "features": ["Court access", "Group training"],
          "is_available": true
        }
      ],
      "active_services": [
        {
          "id": "uuid",
          "service_name": "Personal Training",
          "base_price": "50.00",
          "duration_minutes": 60
        }
      ]
    }
  }
}
```

## Database Schema

### Key Tables
- **sports**: Main sports categories
- **sport_services**: Services offered for each sport
- **tiers**: Membership tiers for each sport
- **users**: User accounts with role-based permissions

### Features
- **UUID Primary Keys**: For better security and scalability
- **JSON Fields**: For flexible feature lists in tiers
- **Automatic Triggers**: Database-level count updates
- **Comprehensive Indexing**: Optimized for performance

## Development Tools

### Artisan Commands
```bash
# Sync service counts
php artisan sports:sync-service-counts

# Seed sample data
php artisan db:seed --class=SportSeeder
php artisan db:seed --class=SportServiceSeeder
php artisan db:seed --class=TierSeeder
```

### Testing
```bash
php artisan test
```

## Documentation Files
- `AUTH_APIs.md` - Authentication endpoints
- `SPORTS_APIs.md` - Sports management endpoints
- `SPORT_SERVICES_API.md` - Sport services endpoints
- `TIERS_API.md` - Tier management endpoints
- `ROLE_BASED_ACCESS.md` - Permission system documentation

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
