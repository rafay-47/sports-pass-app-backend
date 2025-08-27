{\rtf1\ansi\ansicpg1252\cocoartf2822
\cocoatextscaling0\cocoaplatform0{\fonttbl\f0\fswiss\fcharset0 Helvetica;}
{\colortbl;\red255\green255\blue255;}
{\*\expandedcolortbl;;}
\paperw11900\paperh16840\margl1440\margr1440\vieww11520\viewh8400\viewkind0
\pard\tx720\tx1440\tx2160\tx2880\tx3600\tx4320\tx5040\tx5760\tx6480\tx7200\tx7920\tx8640\pardirnatural\partightenfactor0

\f0\fs24 \cf0 # Sports Club Pakistan - Database Architecture\
\
## Overview\
This document outlines the complete database architecture for the Sports Club Pakistan mobile application, supporting multiple sports memberships, trainer management, club check-ins, events, and payment processing.\
\
## Database Technology Recommendations\
- **Primary Database**: PostgreSQL (for ACID compliance and complex relationships)\
- **Cache Layer**: Redis (for session management and real-time features)\
- **File Storage**: AWS S3 or similar (for images, documents, QR codes)\
- **Search Engine**: Elasticsearch (for club and trainer search)\
\
## Core Tables\
\
### 1. Users Table\
```sql
CREATE TABLE users (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    email VARCHAR(255) UNIQUE NOT NULL,\
    password_hash VARCHAR(255) NOT NULL,\
    name VARCHAR(255) NOT NULL,\
    phone VARCHAR(20) UNIQUE NOT NULL,\
    date_of_birth DATE,\
    gender VARCHAR(10) CHECK (gender IN ('male', 'female', 'other')),\
    profile_image_url VARCHAR(500),\
    user_role VARCHAR(50),\
    is_trainer BOOLEAN DEFAULT FALSE,\
    is_verified BOOLEAN DEFAULT FALSE,\
    is_active BOOLEAN DEFAULT TRUE,\
    join_date DATE NOT NULL DEFAULT CURRENT_DATE,\
    last_login TIMESTAMP,\
    remember_token VARCHAR(100),\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    -- Indexes\
    INDEX idx_users_email (email),\
    INDEX idx_users_phone (phone),\
    INDEX idx_users_join_date (join_date),\
    INDEX idx_users_is_trainer (is_trainer),\
    INDEX idx_users_user_role (user_role)\

);\

```
\
### 2. Sports Table\
```sql
CREATE TABLE sports (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    name VARCHAR(100) NOT NULL,\
    display_name VARCHAR(100) NOT NULL,\
    icon VARCHAR(10) NOT NULL,\
    color VARCHAR(7) NOT NULL, -- Hex color code\
    description TEXT,\
    number_of_services INTEGER DEFAULT 0,\
    is_active BOOLEAN DEFAULT TRUE,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    -- Indexes\
    INDEX idx_sports_active (is_active),\
    INDEX idx_sports_name (name)\
);\
```

\
### 3. Sport Services Table\
```sql
CREATE TABLE sport_services (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    sport_id UUID NOT NULL,\
    service_name VARCHAR(100) NOT NULL,\
    description TEXT,\
    icon VARCHAR(10),\
    base_price DECIMAL(10,2),\
    duration_minutes INTEGER,\
    discount_percentage DECIMAL(5,2) DEFAULT 0,\
    is_active BOOLEAN DEFAULT TRUE,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE,\
    INDEX idx_sport_services_sport (sport_id),\
    INDEX idx_sport_services_active (is_active)\
);\
```
\
### 4. Sport Tiers Table\
```sql
CREATE TABLE tiers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    sport_id UUID NOT NULL,
    tier_name VARCHAR(50) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(10),
    color VARCHAR(7), -- Hex color code
    price DECIMAL(10,2) NOT NULL,
    duration_days INTEGER DEFAULT 365,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    start_date DATE, -- Added for time-limited offers
    end_date DATE, -- Added for time-limited offers
    features JSON, -- Array of features for this tier
    is_active BOOLEAN DEFAULT TRUE,
    is_popular BOOLEAN DEFAULT FALSE, -- Added from model
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE,
    UNIQUE(sport_id, tier_name),
    INDEX idx_tiers_sport_tier (sport_id, tier_name),
    INDEX idx_tiers_active (is_active),
    INDEX idx_tiers_popular (is_popular),
    INDEX idx_tiers_dates (start_date, end_date)
);\
```
\
### 5. Memberships Table\
```sql
CREATE TABLE memberships (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    membership_number VARCHAR(50) UNIQUE NOT NULL,\
    user_id UUID NOT NULL,\
    sport_id UUID NOT NULL,\
    tier_id UUID NOT NULL, -- References the tiers table\
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'paused', 'expired', 'cancelled')),\
    purchase_date DATE NOT NULL,\
    start_date DATE NOT NULL,\
    expiry_date DATE NOT NULL,\
    auto_renew BOOLEAN DEFAULT TRUE,\
    purchase_amount DECIMAL(10,2) NOT NULL,\
    monthly_check_ins INTEGER DEFAULT 0,\
    total_spent DECIMAL(10,2) DEFAULT 0,\
    monthly_spent DECIMAL(10,2) DEFAULT 0,\
    total_earnings DECIMAL(10,2) DEFAULT 0, -- For trainers\
    monthly_earnings DECIMAL(10,2) DEFAULT 0, -- For trainers\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE RESTRICT,\
    FOREIGN KEY (tier_id) REFERENCES tiers(id) ON DELETE RESTRICT,\
    INDEX idx_memberships_user (user_id),\
    INDEX idx_memberships_sport (sport_id),\
    INDEX idx_memberships_tier (tier_id),\
    INDEX idx_memberships_number (membership_number),\
    INDEX idx_memberships_status (status),\
    INDEX idx_memberships_expiry (expiry_date)\
);\
```
### 6. Trainer Profiles Table\
```sql
CREATE TABLE trainer_profiles (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    user_id UUID UNIQUE NOT NULL,\
    sport_id UUID NOT NULL, -- Single sport per trainer\
    tier_id UUID NOT NULL, -- References the membership tier\
    experience_years INTEGER NOT NULL,\
    bio TEXT,\
    hourly_rate DECIMAL(10,2), -- Fixed based on tier\
    rating DECIMAL(3,2) DEFAULT 0.0,\
    total_sessions INTEGER DEFAULT 0,\
    total_earnings DECIMAL(10,2) DEFAULT 0,\
    monthly_earnings DECIMAL(10,2) DEFAULT 0,\
    is_verified BOOLEAN DEFAULT FALSE,\
    is_available BOOLEAN DEFAULT TRUE,\
    gender_preference VARCHAR(10) CHECK (gender_preference IN ('male', 'female', 'both')),\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE RESTRICT,\
    FOREIGN KEY (tier_id) REFERENCES tiers(id) ON DELETE RESTRICT,\
    INDEX idx_trainer_profiles_user (user_id),\
    INDEX idx_trainer_profiles_sport (sport_id),\
    INDEX idx_trainer_profiles_tier (tier_id),\
    INDEX idx_trainer_profiles_verified (is_verified),\
    INDEX idx_trainer_profiles_available (is_available),\
    INDEX idx_trainer_profiles_rating (rating)\
);\
```

### 7. Trainer Certifications Table
```sql
CREATE TABLE trainer_certifications (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    trainer_profile_id UUID NOT NULL,\
    certification_name VARCHAR(200) NOT NULL,\
    issuing_organization VARCHAR(200),\
    issue_date DATE,\
    expiry_date DATE,\
    certificate_url VARCHAR(500),\
    is_verified BOOLEAN DEFAULT FALSE,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (trainer_profile_id) REFERENCES trainer_profiles(id) ON DELETE CASCADE,\
    INDEX idx_trainer_certs_trainer (trainer_profile_id),\
    INDEX idx_trainer_certs_verified (is_verified)\
);\
```
### 8. Trainer Specialties Table
```sql
CREATE TABLE trainer_specialties (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    trainer_profile_id UUID NOT NULL,\
    specialty VARCHAR(100) NOT NULL,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (trainer_profile_id) REFERENCES trainer_profiles(id) ON DELETE CASCADE,\
    UNIQUE(trainer_profile_id, specialty),\
    INDEX idx_trainer_specialties_trainer (trainer_profile_id)\
);\
```

### 9. Trainer Availability Table
```sql
CREATE TABLE trainer_availability (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    trainer_profile_id UUID NOT NULL,\
    day_of_week INTEGER NOT NULL CHECK (day_of_week BETWEEN 0 AND 6), -- 0=Sunday\
    start_time TIME NOT NULL,\
    end_time TIME NOT NULL,\
    is_available BOOLEAN DEFAULT TRUE,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (trainer_profile_id) REFERENCES trainer_profiles(id) ON DELETE CASCADE,\
    UNIQUE(trainer_profile_id, day_of_week, start_time, end_time),\
    INDEX idx_trainer_availability_trainer (trainer_profile_id),\
    INDEX idx_trainer_availability_day (day_of_week)\
);\
```

### 10. Trainer Locations Table
```sql
CREATE TABLE trainer_locations (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    trainer_profile_id UUID NOT NULL,\
    location_name VARCHAR(200) NOT NULL,\
    address TEXT,\
    latitude DECIMAL(10, 8),\
    longitude DECIMAL(11, 8),\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (trainer_profile_id) REFERENCES trainer_profiles(id) ON DELETE CASCADE,\
    INDEX idx_trainer_locations_trainer (trainer_profile_id),\
    INDEX idx_trainer_locations_coords (latitude, longitude)\
);\

```
### 11. Clubs Table
```sql
CREATE TABLE clubs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owner_id UUID, -- FK to users (club owner)
    name VARCHAR(200) NOT NULL,
    type VARCHAR(100) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    city VARCHAR(100),
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0.0,
    price_range VARCHAR(20), -- e.g., "$$", "$$$"
    category VARCHAR(20) CHECK (category IN ('male', 'female', 'mixed')),
    qr_code VARCHAR(100) UNIQUE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','pending','suspended')),
    verification_status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (verification_status IN ('pending','verified','rejected')),
    timings JSONB,  -- e.g. { "monday": {"open":"06:00","close":"22:00","isOpen":true}, ... }
    pricing JSONB,  -- e.g. { "basic":1000, "standard":2000, "premium":3000 }
    is_active BOOLEAN DEFAULT TRUE, -- kept for compatibility
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_clubs_owner (owner_id),
    INDEX idx_clubs_qr_code (qr_code),
    INDEX idx_clubs_location (latitude, longitude),
    INDEX idx_clubs_category (category),
    INDEX idx_clubs_status (status),
    INDEX idx_clubs_verification (verification_status),
    INDEX idx_clubs_active (is_active),
    INDEX idx_clubs_rating (rating)
);

```
### 12. Club Sports Table
```sql
CREATE TABLE club_sports (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    club_id UUID NOT NULL,
    sport_id UUID NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE,
    UNIQUE (club_id, sport_id),
    INDEX idx_club_sports_club (club_id),
    INDEX idx_club_sports_sport (sport_id)
);

-- Master lists (admin can CRUD these)
CREATE TABLE amenities (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_by UUID, -- admin user id (optional)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_amenities_name (name),
    INDEX idx_amenities_active (is_active)
);

```
```sql
CREATE TABLE facilities (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_by UUID, -- admin user id (optional)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_facilities_name (name),
    INDEX idx_facilities_active (is_active)
);

-- Club -> amenity mapping (references master list)

CREATE TABLE club_amenities (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    club_id UUID NOT NULL,
    amenity_id UUID NOT NULL,
    custom_name VARCHAR(200), -- optional override/display name per club
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by UUID, -- club owner/admin who added it
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE (club_id, amenity_id),
    INDEX idx_club_amenities_club (club_id),
    INDEX idx_club_amenities_amenity (amenity_id)
);

-- Club -> facility mapping (references master list)
CREATE TABLE club_facilities (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    club_id UUID NOT NULL,
    facility_id UUID NOT NULL,
    custom_name VARCHAR(200), -- optional override/display name per club
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by UUID, -- club owner/admin who added it
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE (club_id, facility_id),
    INDEX idx_club_facilities_club (club_id),
    INDEX idx_club_facilities_facility (facility_id)
);
```
### 15. Club Images Table
```sql
CREATE TABLE club_images (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    club_id UUID NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(200),
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    INDEX idx_club_images_club (club_id),
    INDEX idx_club_images_primary (club_id, is_primary)
);


```
\
### 15. Check-ins Table
```sql
CREATE TABLE check_ins (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    user_id UUID NOT NULL,\
    membership_id UUID NOT NULL,\
    club_id UUID NOT NULL,\
    check_in_date DATE NOT NULL,\
    check_in_time TIME NOT NULL,\
    sport_type VARCHAR(50) NOT NULL,\
    qr_code_used VARCHAR(100),\
    duration_minutes INTEGER,\
    notes TEXT,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE CASCADE,\
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,\
    INDEX idx_check_ins_user (user_id),\
    INDEX idx_check_ins_membership (membership_id),\
    INDEX idx_check_ins_clubs (club_id),\
    INDEX idx_check_ins_date (check_in_date),\
    INDEX idx_check_ins_user_date (user_id, check_in_date)\
);\
```
\
### 16. Events Table
```sql
CREATE TABLE events (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    title VARCHAR(200) NOT NULL,\
    description TEXT,\
    sport_id UUID NOT NULL,\
    event_date DATE NOT NULL,\
    event_time TIME NOT NULL,\
    end_date DATE,\
    end_time TIME,\
    type VARCHAR(50) NOT NULL,\
    category VARCHAR(50) CHECK (category IN ('tournament', 'workshop', 'league', 'social')),\
    difficulty VARCHAR(20) CHECK (difficulty IN ('beginner', 'intermediate', 'advanced')),\
    fee DECIMAL(10,2) DEFAULT 0,\
    max_participants INTEGER,\
    current_participants INTEGER DEFAULT 0,\
    location VARCHAR(200) NOT NULL,\
    organizer VARCHAR(200),\
    requirements JSONB, -- Array of requirements\
    prizes JSONB, -- Array of prizes\
    is_active BOOLEAN DEFAULT TRUE,\
    registration_deadline TIMESTAMP,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE RESTRICT,\
    INDEX idx_events_sport (sport_id),\
    INDEX idx_events_date (event_date),\
    INDEX idx_events_category (category),\
    INDEX idx_events_difficulty (difficulty),\
    INDEX idx_events_active (is_active)\
);\
```
\
### 17. Event Registrations Table
```sql
CREATE TABLE event_registrations (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    event_id UUID NOT NULL,\
    user_id UUID NOT NULL,\
    membership_id UUID NOT NULL,\
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    payment_amount DECIMAL(10,2) NOT NULL,\
    payment_status VARCHAR(20) DEFAULT 'pending' CHECK (payment_status IN ('pending', 'completed', 'failed', 'refunded')),\
    attendance_status VARCHAR(20) DEFAULT 'registered' CHECK (attendance_status IN ('registered', 'attended', 'no_show', 'cancelled')),\
    notes TEXT,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,\
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE CASCADE,\
    UNIQUE(event_id, user_id),\
    INDEX idx_event_registrations_event (event_id),\
    INDEX idx_event_registrations_user (user_id),\
    INDEX idx_event_registrations_payment_status (payment_status)\
);\
```
\
### 18. Trainer Sessions Table
```sql
CREATE TABLE trainer_sessions (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    trainer_profile_id UUID NOT NULL,\
    trainee_user_id UUID NOT NULL,\
    trainee_membership_id UUID NOT NULL,\
    session_date DATE NOT NULL,\
    session_time TIME NOT NULL,\
    duration_minutes INTEGER NOT NULL DEFAULT 60,\
    status VARCHAR(20) DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'completed', 'cancelled', 'no_show')),\
    fee_amount DECIMAL(10,2) NOT NULL,\
    payment_status VARCHAR(20) DEFAULT 'pending' CHECK (payment_status IN ('pending', 'completed', 'failed', 'refunded')),\
    location VARCHAR(200),\
    notes TEXT,\
    trainee_rating INTEGER CHECK (trainee_rating BETWEEN 1 AND 5),\
    trainee_feedback TEXT,\
    trainer_notes TEXT,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (trainer_profile_id) REFERENCES trainer_profiles(id) ON DELETE CASCADE,\
    FOREIGN KEY (trainee_user_id) REFERENCES users(id) ON DELETE CASCADE,\
    FOREIGN KEY (trainee_membership_id) REFERENCES memberships(id) ON DELETE CASCADE,\
    INDEX idx_trainer_sessions_trainer (trainer_profile_id),\
    INDEX idx_trainer_sessions_trainee (trainee_user_id),\
    INDEX idx_trainer_sessions_date (session_date),\
    INDEX idx_trainer_sessions_status (status)\
);\
```
\
### 19. Service Purchases Table
```sql
CREATE TABLE service_purchases (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL,
    membership_id UUID NOT NULL,
    sport_service_id UUID NOT NULL, -- References sport_services(id)
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'completed' CHECK (status IN ('completed', 'cancelled', 'upcoming', 'expired')),
    service_date DATE,
    service_time TIME,
    provider VARCHAR(200),
    location VARCHAR(200),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE CASCADE,
    FOREIGN KEY (sport_service_id) REFERENCES sport_services(id) ON DELETE RESTRICT,
    INDEX idx_service_purchases_user (user_id),
    INDEX idx_service_purchases_membership (membership_id),
    INDEX idx_service_purchases_service (sport_service_id),
    INDEX idx_service_purchases_status (status),
    INDEX idx_service_purchases_date (service_date)
);\
```
\


### 20. Payments Table\
```sql
CREATE TABLE payments (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    user_id UUID NOT NULL,\
    transaction_id VARCHAR(100) UNIQUE NOT NULL,\
    amount DECIMAL(10,2) NOT NULL,\
    currency VARCHAR(3) DEFAULT 'PKR',\
    payment_method VARCHAR(50) NOT NULL, -- 'easypaisa', 'jazzcash', 'sadapay', 'bank', 'mastercard'\
    payment_type VARCHAR(50) NOT NULL, -- 'membership', 'service', 'event', 'trainer_session'\
    reference_id UUID, -- ID of the related record (membership, service, etc.)\
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded')),\
    payment_gateway_response JSONB,\
    failure_reason TEXT,\
    refund_amount DECIMAL(10,2),\
    refund_date TIMESTAMP,\
    payment_date TIMESTAMP,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\
    INDEX idx_payments_user (user_id),\
    INDEX idx_payments_transaction_id (transaction_id),\
    INDEX idx_payments_status (status),\
    INDEX idx_payments_type (payment_type),\
    INDEX idx_payments_date (payment_date),\
    INDEX idx_payments_reference (reference_id)\
);\
```
\
### 21. Notifications Table
```sql
CREATE TABLE notifications (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    user_id UUID NOT NULL,\
    title VARCHAR(200) NOT NULL,\
    message TEXT NOT NULL,\
    type VARCHAR(50) NOT NULL CHECK (type IN ('info', 'success', 'warning', 'error', 'membership', 'event', 'trainer', 'checkin', 'payment')),\
    is_read BOOLEAN DEFAULT FALSE,\
    action_url VARCHAR(500),\
    metadata JSONB, -- Additional data for the notification\
    expires_at TIMESTAMP,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\
    INDEX idx_notifications_user (user_id),\
    INDEX idx_notifications_read (user_id, is_read),\
    INDEX idx_notifications_type (type),\
    INDEX idx_notifications_created (created_at)\
);\
```
\
### 22. User Sessions Table (for authentication)
```sql
CREATE TABLE user_sessions (\
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),\
    user_id UUID NOT NULL,\
    session_token VARCHAR(255) UNIQUE NOT NULL,\
    device_info JSONB,\
    ip_address INET,\
    expires_at TIMESTAMP NOT NULL,\
    is_active BOOLEAN DEFAULT TRUE,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    \
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\
    INDEX idx_user_sessions_user (user_id),\
    INDEX idx_user_sessions_token (session_token),\
    INDEX idx_user_sessions_expires (expires_at)\
);\
```
\
## Views for Analytics and Reporting\
\
### 1. User Statistics View\
```sql
CREATE VIEW user_statistics AS\
SELECT \
    u.id,\
    u.name,\
    u.email,\
    u.join_date,\
    COUNT(DISTINCT m.id) as total_memberships,\
    COUNT(DISTINCT ci.id) as total_check_ins,\
    COUNT(DISTINCT ep.id) as events_participated,\
    COUNT(DISTINCT ts.id) as trainer_sessions_taken,\
    COALESCE(SUM(p.amount), 0) as total_spent\
FROM users u\
LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'\
LEFT JOIN check_ins ci ON u.id = ci.user_id\
LEFT JOIN event_registrations ep ON u.id = ep.user_id\
LEFT JOIN trainer_sessions ts ON u.id = ts.trainee_user_id AND ts.status = 'completed'\
LEFT JOIN payments p ON u.id = p.user_id AND p.status = 'completed'\
GROUP BY u.id, u.name, u.email, u.join_date;\
```
\
### 2. Trainer Performance View
```sql
CREATE VIEW trainer_performance AS
SELECT 
    tp.id as trainer_id,\
    u.name as trainer_name,\
    tp.sport_id,\
    tp.rating,\
    tp.total_sessions,\
    tp.total_earnings,\
    COUNT(DISTINCT ts.trainee_user_id) as unique_trainees,\
    AVG(ts.trainee_rating) as average_session_rating,\
    COUNT(CASE WHEN ts.status = 'completed' THEN 1 END) as completed_sessions,\
    COUNT(CASE WHEN ts.status = 'cancelled' THEN 1 END) as cancelled_sessions\
FROM trainer_profiles tp\
JOIN users u ON tp.user_id = u.id\
LEFT JOIN trainer_sessions ts ON tp.id = ts.trainer_profile_id\
GROUP BY tp.id, u.name, tp.sport_id, tp.rating, tp.total_sessions, tp.total_earnings;\
```
\
### 3. club Usage View
```sql
CREATE VIEW club_usage AS\
SELECT \
    f.id as club_id,\
    f.name as club_name,\
    f.category,\
    COUNT(DISTINCT ci.user_id) as unique_visitors,\
    COUNT(ci.id) as total_check_ins,\
    COUNT(CASE WHEN ci.check_in_date >= CURRENT_DATE - INTERVAL '30 days' THEN 1 END) as check_ins_last_30_days,\
    AVG(f.rating) as average_rating\
FROM clubs f\
LEFT JOIN check_ins ci ON f.id = ci.club_id\
GROUP BY f.id, f.name, f.category, f.rating;\
```
\
## Indexes for Performance\
\
```sql
-- Composite indexes for common queries\
CREATE INDEX idx_memberships_user_sport_status ON memberships(user_id, sport_id, status);\
CREATE INDEX idx_memberships_user_tier_status ON memberships(user_id, tier_id, status);\
CREATE INDEX idx_check_ins_user_month ON check_ins(user_id, date_trunc('month', check_in_date));\
CREATE INDEX idx_trainer_sessions_trainer_date ON trainer_sessions(trainer_profile_id, session_date);\
CREATE INDEX idx_payments_user_status_type ON payments(user_id, status, payment_type);\
CREATE INDEX idx_notifications_user_read_created ON notifications(user_id, is_read, created_at DESC);\
\
-- Full-text search indexes\
CREATE INDEX idx_clubs_search ON clubs USING gin(to_tsvector('english', name || ' ' || coalesce(address, '')));\
CREATE INDEX idx_events_search ON events USING gin(to_tsvector('english', title || ' ' || coalesce(description, '')));\
```
\
## Constraints and Business Rules\
\
```sql
-- Ensure trainer can only have one active profile per sport\
ALTER TABLE trainer_profiles ADD CONSTRAINT unique_active_trainer_per_sport \
EXCLUDE (user_id WITH =, sport_id WITH =) WHERE (is_verified = true);\
\
-- Ensure membership expiry is after start date\
ALTER TABLE memberships ADD CONSTRAINT check_membership_dates \
CHECK (expiry_date > start_date);\
\
-- Ensure trainer and membership belong to the same sport\
ALTER TABLE trainer_profiles ADD CONSTRAINT check_trainer_sport_tier_match \
CHECK (sport_id = (SELECT sport_id FROM tiers WHERE id = tier_id));\
\
ALTER TABLE memberships ADD CONSTRAINT check_membership_sport_tier_match \
CHECK (sport_id = (SELECT sport_id FROM tiers WHERE id = tier_id));\
\
-- Ensure trainer session is in the future when created\
ALTER TABLE trainer_sessions ADD CONSTRAINT check_session_future \
CHECK (session_date >= CURRENT_DATE OR status != 'scheduled');\
\
-- Ensure check-in limit per membership per month\
CREATE OR REPLACE FUNCTION check_monthly_checkin_limit()\
RETURNS TRIGGER AS $$\
BEGIN\
    IF (SELECT COUNT(*) FROM check_ins \
        WHERE membership_id = NEW.membership_id \
        AND date_trunc('month', check_in_date) = date_trunc('month', NEW.check_in_date)) >= 30 \
    THEN\
        RAISE EXCEPTION 'Monthly check-in limit of 30 exceeded for this membership';\
    END IF;\
    RETURN NEW;\
END;\
$$ LANGUAGE plpgsql;\
\
CREATE TRIGGER trigger_check_monthly_checkin_limit\
    BEFORE INSERT ON check_ins\
    FOR EACH ROW EXECUTE FUNCTION check_monthly_checkin_limit();\
```
\
## Sample Data Population\
\
```sql
-- Insert sports data\
INSERT INTO sports (name, display_name, icon, color) VALUES\
('Gym', 'GYM CARD', '\uc0\u55357 \u56490 ', '#FFB948'),\
('Cricket', 'CRICKET CARD', '\uc0\u55356 \u57295 ', '#A148FF'),\
('Tennis', 'TABLE TENNIS CARD', '\uc0\u55356 \u57299 ', '#FF6B6B'),\
('Snooker', 'SNOOKER CARD', '\uc0\u55356 \u57265 ', '#4ECDC4'),\
('Badminton', 'BADMINTON CARD', '\uc0\u55356 \u57336 ', '#95E1D3');\
\
-- Note: For tiers and sport services, use the actual UUID values from the sports table\
-- Example tier insertion (replace with actual sport UUIDs):\
-- INSERT INTO tiers (sport_id, tier_name, display_name, price, duration_days) VALUES\
-- ('actual-uuid-here', 'basic', 'Basic Plan', 2000, 365),\
-- ('actual-uuid-here', 'standard', 'Standard Plan', 4000, 365),\
-- ('actual-uuid-here', 'premium', 'Premium Plan', 6000, 365);\
\
-- Example sport services insertion (replace with actual sport UUIDs):\
-- INSERT INTO sport_services (sport_id, service_name, base_price) VALUES\
-- ('actual-uuid-here', 'Personal Trainer', 5000),\
-- ('actual-uuid-here', 'Diet Plan', 2000),\
-- ('actual-uuid-here', 'Expert Consultation', 1500);\
```
\
## Security Considerations\
\
1. **Data Encryption**: Encrypt sensitive fields like payment information and personal data\
2. **Access Control**: Implement row-level security for user data\
3. **Audit Trail**: Add audit tables for sensitive operations\
4. **API Rate Limiting**: Implement rate limiting at the database level\
5. **Data Retention**: Implement policies for data cleanup and archival\
\
## Backup and Recovery Strategy\
\
1. **Daily Backups**: Automated daily backups with point-in-time recovery\
2. **Replication**: Master-slave replication for read scalability\
3. **Disaster Recovery**: Cross-region backup strategy\
4. **Data Archival**: Archive old records to reduce main database size\
\
This database architecture supports all features of the Sports Club Pakistan app including user management, multi-sport memberships, trainer system, clubs check-ins, events, payments, and comprehensive analytics.}