```sql
-- ============================================================================
-- ElderCare SG - Complete Database Schema
-- ============================================================================
-- Version: 1.0
-- Database: MySQL 8.0+
-- Character Set: utf8mb4 (Full Unicode support)
-- Collation: utf8mb4_unicode_ci
-- Engine: InnoDB (ACID compliance, foreign keys, row-level locking)
-- 
-- Description:
-- Complete production-ready database schema for ElderCare SG platform.
-- Includes PDPA compliance (Singapore Personal Data Protection Act),
-- MOH regulatory compliance, multilingual support (English, Mandarin, 
-- Malay, Tamil), and integration with external services (Calendly, 
-- Mailchimp, Twilio, Cloudflare Stream).
--
-- Total Tables: 18
-- - Foundation: 4 (users, auth, queue)
-- - Core Entities: 5 (profiles, centers, faqs, subscriptions, contacts)
-- - Dependent: 2 (services, staff)
-- - Relationships: 2 (bookings, testimonials)
-- - PDPA Compliance: 2 (consents, audit_logs)
-- - Polymorphic: 2 (media, content_translations)
-- - Queue: 1 (jobs)
--
-- Compliance Features:
-- - PDPA: Consent tracking, audit logging, soft deletes, data export support
-- - MOH: License tracking, staff credentials, medical facilities
-- - Accessibility: Multilingual content, alt text for media
--
-- Usage:
-- 1. Update database name if needed (default: eldercare_db)
-- 2. Execute: mysql -u root -p < eldercare_sg_schema_v1.0.sql
-- 3. Verify: See verification queries at end of file
--
-- Author: ElderCare SG Development Team
-- Date: 2024
-- ============================================================================

-- Drop database if exists (CAUTION: This will delete all data)
-- Uncomment the line below only for fresh installation
-- DROP DATABASE IF EXISTS eldercare_db;

-- Create database
CREATE DATABASE IF NOT EXISTS eldercare_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Use the database
USE eldercare_db;

-- Set session variables for optimal configuration
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;
SET SQL_MODE = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ============================================================================
-- SECTION 1: FOUNDATION TABLES (Users & Authentication)
-- ============================================================================
-- Tables: users, password_reset_tokens, failed_jobs, personal_access_tokens
-- Purpose: Core user authentication and Laravel framework support
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: users
-- Purpose: Core user accounts with authentication and PDPA compliance
-- PDPA Features: Soft deletes (30-day grace period), consent tracking
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                    VARCHAR(255) NOT NULL COMMENT 'Full name of the user',
    email                   VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email address (unique identifier)',
    phone                   VARCHAR(20) NULL COMMENT 'Phone number (Singapore format: +65)',
    email_verified_at       TIMESTAMP NULL COMMENT 'Email verification timestamp',
    password                VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password (60 chars)',
    role                    ENUM('user', 'admin', 'super_admin') NOT NULL DEFAULT 'user' COMMENT 'User role for authorization',
    preferred_language      ENUM('en', 'zh', 'ms', 'ta') NOT NULL DEFAULT 'en' COMMENT 'Preferred language: en=English, zh=Mandarin, ms=Malay, ta=Tamil',
    remember_token          VARCHAR(100) NULL COMMENT 'Laravel remember me token',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at              TIMESTAMP NULL COMMENT 'Soft delete timestamp (PDPA 30-day grace period)',
    
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User accounts with authentication and PDPA compliance';

-- ----------------------------------------------------------------------------
-- Table: password_reset_tokens
-- Purpose: Laravel password reset functionality
-- ----------------------------------------------------------------------------
CREATE TABLE password_reset_tokens (
    email                   VARCHAR(255) NOT NULL PRIMARY KEY COMMENT 'User email address',
    token                   VARCHAR(255) NOT NULL COMMENT 'Password reset token (hashed)',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Token creation timestamp',
    
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Password reset tokens (Laravel authentication)';

-- ----------------------------------------------------------------------------
-- Table: failed_jobs
-- Purpose: Laravel queue failed job tracking
-- ----------------------------------------------------------------------------
CREATE TABLE failed_jobs (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                    VARCHAR(255) NOT NULL UNIQUE COMMENT 'Job UUID (unique identifier)',
    connection              TEXT NOT NULL COMMENT 'Queue connection name',
    queue                   TEXT NOT NULL COMMENT 'Queue name',
    payload                 LONGTEXT NOT NULL COMMENT 'Job payload (serialized)',
    exception               LONGTEXT NOT NULL COMMENT 'Exception details',
    failed_at               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Failure timestamp',
    
    INDEX idx_uuid (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Failed queue jobs (Laravel queue system)';

-- ----------------------------------------------------------------------------
-- Table: personal_access_tokens
-- Purpose: Laravel Sanctum API authentication tokens
-- ----------------------------------------------------------------------------
CREATE TABLE personal_access_tokens (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type          VARCHAR(255) NOT NULL COMMENT 'Polymorphic type (e.g., App\\Models\\User)',
    tokenable_id            BIGINT UNSIGNED NOT NULL COMMENT 'Polymorphic ID (user ID)',
    name                    VARCHAR(255) NOT NULL COMMENT 'Token name/description',
    token                   VARCHAR(64) NOT NULL UNIQUE COMMENT 'Hashed token value',
    abilities               TEXT NULL COMMENT 'Token abilities/permissions (JSON)',
    last_used_at            TIMESTAMP NULL COMMENT 'Last usage timestamp',
    expires_at              TIMESTAMP NULL COMMENT 'Token expiration timestamp',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tokenable (tokenable_type, tokenable_id),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='API authentication tokens (Laravel Sanctum)';

-- ============================================================================
-- SECTION 2: CORE ENTITY TABLES
-- ============================================================================
-- Tables: profiles, centers, faqs, subscriptions, contact_submissions
-- Purpose: Primary business entities
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: profiles
-- Purpose: Extended user profile information
-- Relationship: One-to-one with users
-- ----------------------------------------------------------------------------
CREATE TABLE profiles (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NOT NULL UNIQUE COMMENT 'Foreign key to users table',
    avatar                  VARCHAR(255) NULL COMMENT 'Avatar URL (AWS S3)',
    bio                     TEXT NULL COMMENT 'User biography',
    birth_date              DATE NULL COMMENT 'Date of birth',
    address                 VARCHAR(500) NULL COMMENT 'Street address',
    city                    VARCHAR(100) NULL COMMENT 'City name',
    postal_code             VARCHAR(10) NULL COMMENT 'Singapore postal code (6 digits)',
    country                 VARCHAR(100) NOT NULL DEFAULT 'Singapore' COMMENT 'Country name',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_profiles_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Extended user profile information';

-- ----------------------------------------------------------------------------
-- Table: centers
-- Purpose: Elderly care centers with MOH regulatory compliance
-- MOH Features: License tracking, accreditation, medical facilities
-- Business Features: Capacity, staff, amenities, transport info
-- ----------------------------------------------------------------------------
CREATE TABLE centers (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Basic Information
    name                        VARCHAR(255) NOT NULL COMMENT 'Center name',
    slug                        VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL-friendly slug (unique)',
    short_description           VARCHAR(500) NULL COMMENT 'Brief description (meta description)',
    description                 TEXT NOT NULL COMMENT 'Full description',
    
    -- Contact Information
    address                     VARCHAR(500) NOT NULL COMMENT 'Full street address',
    city                        VARCHAR(100) NOT NULL COMMENT 'City/district name',
    postal_code                 VARCHAR(10) NOT NULL COMMENT 'Singapore postal code',
    phone                       VARCHAR(20) NOT NULL COMMENT 'Contact phone number',
    email                       VARCHAR(255) NOT NULL COMMENT 'Contact email address',
    website                     VARCHAR(255) NULL COMMENT 'Website URL',
    
    -- MOH Regulatory Compliance
    moh_license_number          VARCHAR(50) NOT NULL UNIQUE COMMENT 'MOH license number (required by Singapore regulations)',
    license_expiry_date         DATE NOT NULL COMMENT 'License expiration date',
    accreditation_status        ENUM('pending', 'accredited', 'not_accredited', 'expired') NOT NULL DEFAULT 'pending' COMMENT 'Accreditation status',
    
    -- Operational Details
    capacity                    INT UNSIGNED NOT NULL COMMENT 'Maximum capacity (number of elderly)',
    current_occupancy           INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current number of elderly',
    staff_count                 INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total staff count',
    staff_patient_ratio         DECIMAL(3, 1) NULL COMMENT 'Staff to patient ratio (e.g., 1.5 = 1 staff to 1.5 patients)',
    
    -- Flexible JSON Fields for Complex Data
    operating_hours             JSON NULL COMMENT 'Operating hours by day: {"monday": {"open": "08:00", "close": "18:00"}, ...}',
    medical_facilities          JSON NULL COMMENT 'Available medical facilities: ["examination_room", "medication_storage", "emergency_equipment"]',
    amenities                   JSON NULL COMMENT 'Center amenities: ["wheelchair_accessible", "air_conditioned", "prayer_room", "wifi"]',
    transport_info              JSON NULL COMMENT 'Public transport information: {"mrt": ["Ang Mo Kio"], "bus": ["56", "162"], "parking": true}',
    languages_supported         JSON NULL COMMENT 'Languages spoken by staff: ["en", "zh", "ms", "ta"]',
    government_subsidies        JSON NULL COMMENT 'Applicable subsidies: ["pioneer_generation", "merdeka_generation", "silver_support"]',
    
    -- Geolocation (for future map feature)
    latitude                    DECIMAL(10, 8) NULL COMMENT 'Latitude coordinate',
    longitude                   DECIMAL(11, 8) NULL COMMENT 'Longitude coordinate',
    
    -- Status & Publishing
    status                      ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft' COMMENT 'Publication status',
    
    -- SEO Fields
    meta_title                  VARCHAR(60) NULL COMMENT 'SEO meta title (max 60 chars)',
    meta_description            VARCHAR(160) NULL COMMENT 'SEO meta description (max 160 chars)',
    
    created_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at                  TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    
    -- Constraints
    CONSTRAINT chk_capacity CHECK (capacity > 0),
    CONSTRAINT chk_occupancy CHECK (current_occupancy >= 0 AND current_occupancy <= capacity),
    CONSTRAINT chk_staff_count CHECK (staff_count >= 0),
    
    -- Indexes
    INDEX idx_slug (slug),
    INDEX idx_moh_license (moh_license_number),
    INDEX idx_status (status),
    INDEX idx_city (city),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_created_at (created_at),
    FULLTEXT INDEX idx_search (name, short_description, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Elderly care centers with MOH compliance and operational details';

-- ----------------------------------------------------------------------------
-- Table: faqs
-- Purpose: Frequently Asked Questions with multilingual support
-- ----------------------------------------------------------------------------
CREATE TABLE faqs (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category                ENUM('general', 'booking', 'services', 'pricing', 'accessibility') NOT NULL COMMENT 'FAQ category',
    question                VARCHAR(500) NOT NULL COMMENT 'Question text',
    answer                  TEXT NOT NULL COMMENT 'Answer text',
    display_order           INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Display order (lower = higher priority)',
    status                  ENUM('draft', 'published') NOT NULL DEFAULT 'draft' COMMENT 'Publication status',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category_order (category, display_order),
    INDEX idx_status (status),
    FULLTEXT INDEX idx_search (question, answer)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Frequently Asked Questions with categorization and ordering';

-- ----------------------------------------------------------------------------
-- Table: subscriptions
-- Purpose: Newsletter subscriptions with Mailchimp integration
-- PDPA Features: Explicit consent, double opt-in support
-- ----------------------------------------------------------------------------
CREATE TABLE subscriptions (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email                       VARCHAR(255) NOT NULL UNIQUE COMMENT 'Subscriber email address',
    mailchimp_subscriber_id     VARCHAR(255) NULL COMMENT 'Mailchimp unique subscriber ID',
    mailchimp_status            ENUM('subscribed', 'unsubscribed', 'pending', 'cleaned') NOT NULL DEFAULT 'pending' COMMENT 'Mailchimp subscription status',
    preferences                 JSON NULL COMMENT 'Subscription preferences: {"topics": ["updates", "events"], "frequency": "weekly"}',
    subscribed_at               TIMESTAMP NULL COMMENT 'Subscription timestamp',
    unsubscribed_at             TIMESTAMP NULL COMMENT 'Unsubscription timestamp',
    last_synced_at              TIMESTAMP NULL COMMENT 'Last sync with Mailchimp timestamp',
    created_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_mailchimp_status (mailchimp_status),
    INDEX idx_mailchimp_subscriber_id (mailchimp_subscriber_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Newsletter subscriptions with Mailchimp integration';

-- ----------------------------------------------------------------------------
-- Table: contact_submissions
-- Purpose: Contact form submissions with spam detection
-- ----------------------------------------------------------------------------
CREATE TABLE contact_submissions (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NULL COMMENT 'Foreign key to users (NULL if guest)',
    center_id               BIGINT UNSIGNED NULL COMMENT 'Foreign key to centers (if inquiry about specific center)',
    name                    VARCHAR(255) NOT NULL COMMENT 'Submitter name',
    email                   VARCHAR(255) NOT NULL COMMENT 'Submitter email',
    phone                   VARCHAR(20) NULL COMMENT 'Submitter phone',
    subject                 VARCHAR(255) NOT NULL COMMENT 'Inquiry subject',
    message                 TEXT NOT NULL COMMENT 'Inquiry message',
    status                  ENUM('new', 'in_progress', 'resolved', 'spam') NOT NULL DEFAULT 'new' COMMENT 'Processing status',
    ip_address              VARCHAR(45) NULL COMMENT 'Submitter IP address (for spam detection)',
    user_agent              TEXT NULL COMMENT 'Submitter user agent',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_contact_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_contact_center_id FOREIGN KEY (center_id) 
        REFERENCES centers(id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_center_id (center_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Contact form submissions with spam detection';

-- ============================================================================
-- SECTION 3: DEPENDENT ENTITY TABLES
-- ============================================================================
-- Tables: services, staff
-- Purpose: Entities that depend on centers
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: services
-- Purpose: Services/programs offered by centers
-- Relationship: Many-to-one with centers
-- ----------------------------------------------------------------------------
CREATE TABLE services (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    center_id               BIGINT UNSIGNED NOT NULL COMMENT 'Foreign key to centers table',
    name                    VARCHAR(255) NOT NULL COMMENT 'Service name',
    slug                    VARCHAR(255) NOT NULL COMMENT 'URL-friendly slug (unique per center)',
    description             TEXT NOT NULL COMMENT 'Service description',
    price                   DECIMAL(10, 2) NULL COMMENT 'Service price (NULL for POA - Price on Application)',
    price_unit              ENUM('hour', 'day', 'week', 'month') NULL COMMENT 'Price unit',
    duration                VARCHAR(100) NULL COMMENT 'Service duration (e.g., "2 hours", "Full day")',
    features                JSON NULL COMMENT 'Service features: ["meals_included", "medication_management", "physiotherapy"]',
    status                  ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft' COMMENT 'Publication status',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at              TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    
    CONSTRAINT fk_services_center_id FOREIGN KEY (center_id) 
        REFERENCES centers(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_price CHECK (price IS NULL OR price >= 0),
    
    INDEX idx_center_id (center_id),
    UNIQUE INDEX idx_center_slug (center_id, slug),
    INDEX idx_status (status),
    INDEX idx_deleted_at (deleted_at),
    FULLTEXT INDEX idx_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Services and programs offered by elderly care centers';

-- ----------------------------------------------------------------------------
-- Table: staff
-- Purpose: Center staff members for MOH compliance and transparency
-- Relationship: Many-to-one with centers
-- ----------------------------------------------------------------------------
CREATE TABLE staff (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    center_id               BIGINT UNSIGNED NOT NULL COMMENT 'Foreign key to centers table',
    name                    VARCHAR(255) NOT NULL COMMENT 'Staff member name',
    position                VARCHAR(255) NOT NULL COMMENT 'Job position (e.g., "Registered Nurse", "Caregiver")',
    qualifications          JSON NULL COMMENT 'Qualifications and certifications: ["RN", "CPR Certified", "First Aid"]',
    years_of_experience     TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Years of experience',
    bio                     TEXT NULL COMMENT 'Staff biography',
    photo                   VARCHAR(255) NULL COMMENT 'Photo URL (AWS S3)',
    display_order           INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Display order on center page',
    status                  ENUM('active', 'inactive') NOT NULL DEFAULT 'active' COMMENT 'Employment status',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_staff_center_id FOREIGN KEY (center_id) 
        REFERENCES centers(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_center_id (center_id),
    INDEX idx_status (status),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Center staff members with qualifications (MOH compliance)';

-- ============================================================================
-- SECTION 4: RELATIONSHIP TABLES
-- ============================================================================
-- Tables: bookings, testimonials
-- Purpose: Many-to-many and complex relationships
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: bookings
-- Purpose: Visit/service bookings with Calendly integration
-- PDPA Features: Soft deletes, questionnaire data privacy
-- Integration: Calendly for scheduling, Twilio for SMS
-- ----------------------------------------------------------------------------
CREATE TABLE bookings (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_number              VARCHAR(20) NOT NULL UNIQUE COMMENT 'Unique booking number (e.g., BK-20240101-0001)',
    
    -- Relationships
    user_id                     BIGINT UNSIGNED NOT NULL COMMENT 'Foreign key to users table',
    center_id                   BIGINT UNSIGNED NOT NULL COMMENT 'Foreign key to centers table',
    service_id                  BIGINT UNSIGNED NULL COMMENT 'Foreign key to services table (NULL for general visit)',
    
    -- Booking Details
    booking_date                DATE NOT NULL COMMENT 'Booking date',
    booking_time                TIME NOT NULL COMMENT 'Booking time',
    booking_type                ENUM('visit', 'consultation', 'trial_day') NOT NULL DEFAULT 'visit' COMMENT 'Type of booking',
    
    -- Calendly Integration
    calendly_event_id           VARCHAR(255) NULL COMMENT 'Calendly event ID',
    calendly_event_uri          VARCHAR(500) NULL COMMENT 'Calendly event URI',
    calendly_cancel_url         VARCHAR(500) NULL COMMENT 'Calendly cancellation URL',
    calendly_reschedule_url     VARCHAR(500) NULL COMMENT 'Calendly reschedule URL',
    
    -- Pre-booking Questionnaire (PDPA-compliant data storage)
    questionnaire_responses     JSON NULL COMMENT 'Pre-booking questionnaire: {"elderly_age": 75, "medical_conditions": ["diabetes"], "mobility": "wheelchair"}',
    
    -- Status Management
    status                      ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending' COMMENT 'Booking status',
    cancellation_reason         TEXT NULL COMMENT 'Reason for cancellation',
    notes                       TEXT NULL COMMENT 'Internal notes (staff only)',
    
    -- Notification Tracking
    confirmation_sent_at        TIMESTAMP NULL COMMENT 'Email/SMS confirmation sent timestamp',
    reminder_sent_at            TIMESTAMP NULL COMMENT 'Reminder sent timestamp (24h before)',
    sms_sent                    BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'SMS notification sent flag',
    
    created_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at                  TIMESTAMP NULL COMMENT 'Soft delete timestamp (PDPA)',
    
    CONSTRAINT fk_bookings_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_center_id FOREIGN KEY (center_id) 
        REFERENCES centers(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_service_id FOREIGN KEY (service_id) 
        REFERENCES services(id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_booking_number (booking_number),
    INDEX idx_user_id (user_id),
    INDEX idx_center_id (center_id),
    INDEX idx_service_id (service_id),
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status),
    INDEX idx_calendly_event_id (calendly_event_id),
    INDEX idx_created_at (created_at),
    INDEX idx_user_date (user_id, booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bookings with Calendly integration and notification tracking';

-- ----------------------------------------------------------------------------
-- Table: testimonials
-- Purpose: User reviews and testimonials with moderation workflow
-- ----------------------------------------------------------------------------
CREATE TABLE testimonials (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NOT NULL COMMENT 'Foreign key to users table (reviewer)',
    center_id               BIGINT UNSIGNED NOT NULL COMMENT 'Foreign key to centers table (reviewed center)',
    title                   VARCHAR(255) NOT NULL COMMENT 'Testimonial title',
    content                 TEXT NOT NULL COMMENT 'Testimonial content',
    rating                  TINYINT UNSIGNED NOT NULL COMMENT 'Rating (1-5 stars)',
    status                  ENUM('pending', 'approved', 'rejected', 'spam') NOT NULL DEFAULT 'pending' COMMENT 'Moderation status',
    moderation_notes        TEXT NULL COMMENT 'Internal moderation notes',
    moderated_by            BIGINT UNSIGNED NULL COMMENT 'Foreign key to users table (moderator)',
    moderated_at            TIMESTAMP NULL COMMENT 'Moderation timestamp',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at              TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    
    CONSTRAINT fk_testimonials_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_testimonials_center_id FOREIGN KEY (center_id) 
        REFERENCES centers(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_testimonials_moderated_by FOREIGN KEY (moderated_by) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_rating CHECK (rating >= 1 AND rating <= 5),
    
    INDEX idx_user_id (user_id),
    INDEX idx_center_id (center_id),
    INDEX idx_status (status),
    INDEX idx_rating (rating),
    INDEX idx_created_at (created_at),
    INDEX idx_moderated_by (moderated_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User testimonials with moderation workflow and rating system';

-- ============================================================================
-- SECTION 5: PDPA COMPLIANCE TABLES
-- ============================================================================
-- Tables: consents, audit_logs
-- Purpose: Singapore Personal Data Protection Act compliance
-- Retention: 7 years for audit logs (legal requirement)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: consents
-- Purpose: PDPA consent tracking with versioning
-- Features: Explicit consent, IP tracking, version control
-- ----------------------------------------------------------------------------
CREATE TABLE consents (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NOT NULL COMMENT 'Foreign key to users table',
    consent_type            ENUM('account', 'marketing_email', 'marketing_sms', 'analytics_cookies', 'functional_cookies') NOT NULL COMMENT 'Type of consent',
    consent_given           BOOLEAN NOT NULL COMMENT 'Consent status (true=given, false=withdrawn)',
    consent_text            TEXT NOT NULL COMMENT 'Snapshot of consent text user agreed to',
    consent_version         VARCHAR(10) NOT NULL COMMENT 'Privacy policy version (e.g., "1.0", "1.1")',
    ip_address              VARCHAR(45) NOT NULL COMMENT 'IP address when consent was given/withdrawn',
    user_agent              TEXT NOT NULL COMMENT 'User agent string (browser/device info)',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Consent timestamp',
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_consents_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_user_consent_type (user_id, consent_type),
    INDEX idx_consent_type (consent_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='PDPA consent tracking with IP address and version control';

-- ----------------------------------------------------------------------------
-- Table: audit_logs
-- Purpose: PDPA audit trail for all data changes
-- Features: Polymorphic tracking, old/new values, 7-year retention
-- Legal: Required by Singapore regulations for 7 years
-- ----------------------------------------------------------------------------
CREATE TABLE audit_logs (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NULL COMMENT 'Foreign key to users table (NULL for system actions)',
    auditable_type          VARCHAR(255) NOT NULL COMMENT 'Polymorphic type (e.g., App\\Models\\User)',
    auditable_id            BIGINT UNSIGNED NOT NULL COMMENT 'Polymorphic ID (record ID)',
    action                  ENUM('created', 'updated', 'deleted', 'restored') NOT NULL COMMENT 'Action performed',
    old_values              JSON NULL COMMENT 'Previous state (JSON snapshot)',
    new_values              JSON NULL COMMENT 'New state (JSON snapshot)',
    ip_address              VARCHAR(45) NULL COMMENT 'IP address of user who performed action',
    user_agent              TEXT NULL COMMENT 'User agent string',
    url                     VARCHAR(500) NULL COMMENT 'Request URL',
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Action timestamp',
    
    CONSTRAINT fk_audit_logs_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_auditable (auditable_type, auditable_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='PDPA audit trail - 7 year retention required by law';

-- ============================================================================
-- SECTION 6: POLYMORPHIC TABLES
-- ============================================================================
-- Tables: media, content_translations
-- Purpose: Reusable polymorphic relationships
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: media
-- Purpose: Polymorphic media storage (images, videos, documents)
-- Integration: AWS S3 for storage, Cloudflare Stream for videos
-- ----------------------------------------------------------------------------
CREATE TABLE media (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mediable_type               VARCHAR(255) NOT NULL COMMENT 'Polymorphic type (e.g., App\\Models\\Center)',
    mediable_id                 BIGINT UNSIGNED NOT NULL COMMENT 'Polymorphic ID (center/service/user ID)',
    type                        ENUM('image', 'video', 'document') NOT NULL COMMENT 'Media type',
    url                         VARCHAR(500) NOT NULL COMMENT 'Media URL (AWS S3)',
    thumbnail_url               VARCHAR(500) NULL COMMENT 'Thumbnail URL (for videos)',
    filename                    VARCHAR(255) NOT NULL COMMENT 'Original filename',
    mime_type                   VARCHAR(100) NOT NULL COMMENT 'MIME type (e.g., image/jpeg)',
    size                        BIGINT UNSIGNED NOT NULL COMMENT 'File size in bytes',
    duration                    INT UNSIGNED NULL COMMENT 'Duration in seconds (for videos)',
    caption                     VARCHAR(500) NULL COMMENT 'Media caption',
    alt_text                    VARCHAR(255) NULL COMMENT 'Alt text for accessibility',
    cloudflare_stream_id        VARCHAR(255) NULL COMMENT 'Cloudflare Stream video ID',
    display_order               INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Display order in gallery',
    created_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_mediable (mediable_type, mediable_id),
    INDEX idx_type (type),
    INDEX idx_display_order (display_order),
    INDEX idx_cloudflare_stream_id (cloudflare_stream_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Polymorphic media storage (images, videos, documents)';

-- ----------------------------------------------------------------------------
-- Table: content_translations
-- Purpose: Polymorphic multilingual content translations
-- Languages: English (en), Mandarin (zh), Malay (ms), Tamil (ta)
-- Workflow: Draft → Translated → Reviewed → Published
-- ----------------------------------------------------------------------------
CREATE TABLE content_translations (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    translatable_type           VARCHAR(255) NOT NULL COMMENT 'Polymorphic type (e.g., App\\Models\\Center)',
    translatable_id             BIGINT UNSIGNED NOT NULL COMMENT 'Polymorphic ID (center/service/faq ID)',
    locale                      ENUM('en', 'zh', 'ms', 'ta') NOT NULL COMMENT 'Language code: en=English, zh=Mandarin, ms=Malay, ta=Tamil',
    field                       VARCHAR(100) NOT NULL COMMENT 'Field name being translated (e.g., name, description)',
    value                       TEXT NOT NULL COMMENT 'Translated content',
    translation_status          ENUM('draft', 'translated', 'reviewed', 'published') NOT NULL DEFAULT 'draft' COMMENT 'Translation workflow status',
    translated_by               BIGINT UNSIGNED NULL COMMENT 'Foreign key to users table (translator)',
    reviewed_by                 BIGINT UNSIGNED NULL COMMENT 'Foreign key to users table (reviewer)',
    created_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_translations_translated_by FOREIGN KEY (translated_by) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_translations_reviewed_by FOREIGN KEY (reviewed_by) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    UNIQUE INDEX idx_translation_unique (translatable_type, translatable_id, locale, field),
    INDEX idx_locale (locale),
    INDEX idx_translation_status (translation_status),
    INDEX idx_translated_by (translated_by),
    INDEX idx_reviewed_by (reviewed_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Polymorphic multilingual content translations (en, zh, ms, ta)';

-- ============================================================================
-- SECTION 7: QUEUE TABLES
-- ============================================================================
-- Tables: jobs
-- Purpose: Laravel queue system for background jobs
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: jobs
-- Purpose: Laravel queue jobs (emails, SMS, Mailchimp sync)
-- ----------------------------------------------------------------------------
CREATE TABLE jobs (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue                   VARCHAR(255) NOT NULL COMMENT 'Queue name',
    payload                 LONGTEXT NOT NULL COMMENT 'Job payload (serialized)',
    attempts                TINYINT UNSIGNED NOT NULL COMMENT 'Number of attempts',
    reserved_at             INT UNSIGNED NULL COMMENT 'Job reservation timestamp',
    available_at            INT UNSIGNED NOT NULL COMMENT 'Job available timestamp',
    created_at              INT UNSIGNED NOT NULL COMMENT 'Job creation timestamp',
    
    INDEX idx_queue (queue),
    INDEX idx_reserved_at (reserved_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Laravel queue jobs for background processing';

-- ============================================================================
-- SECTION 8: ADDITIONAL INDEXES & OPTIMIZATIONS
-- ============================================================================
-- Purpose: Performance optimization indexes
-- ============================================================================

-- Additional composite indexes for common query patterns
ALTER TABLE bookings ADD INDEX idx_center_date_status (center_id, booking_date, status);
ALTER TABLE testimonials ADD INDEX idx_center_rating_status (center_id, rating, status);
ALTER TABLE services ADD INDEX idx_center_status (center_id, status);

-- ============================================================================
-- SECTION 9: USEFUL VIEWS
-- ============================================================================
-- Purpose: Commonly used queries as views for performance
-- ============================================================================

-- ----------------------------------------------------------------------------
-- View: active_centers_summary
-- Purpose: Summary of active centers with aggregated statistics
-- Usage: Quick overview of centers with services, staff, bookings, ratings
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW active_centers_summary AS
SELECT 
    c.id,
    c.name,
    c.slug,
    c.city,
    c.status,
    c.capacity,
    c.current_occupancy,
    ROUND((c.current_occupancy / NULLIF(c.capacity, 0) * 100), 2) AS occupancy_rate,
    c.moh_license_number,
    c.accreditation_status,
    COUNT(DISTINCT s.id) AS services_count,
    COUNT(DISTINCT st.id) AS staff_count,
    COUNT(DISTINCT CASE WHEN b.status = 'confirmed' THEN b.id END) AS confirmed_bookings_count,
    COUNT(DISTINCT CASE WHEN t.status = 'approved' THEN t.id END) AS approved_testimonials_count,
    ROUND(AVG(CASE WHEN t.status = 'approved' THEN t.rating END), 2) AS average_rating,
    c.created_at,
    c.updated_at
FROM centers c
LEFT JOIN services s ON c.id = s.center_id AND s.deleted_at IS NULL AND s.status = 'published'
LEFT JOIN staff st ON c.id = st.center_id AND st.status = 'active'
LEFT JOIN bookings b ON c.id = b.center_id AND b.deleted_at IS NULL
LEFT JOIN testimonials t ON c.id = t.center_id AND t.deleted_at IS NULL
WHERE c.deleted_at IS NULL AND c.status = 'published'
GROUP BY c.id, c.name, c.slug, c.city, c.status, c.capacity, c.current_occupancy, 
         c.moh_license_number, c.accreditation_status, c.created_at, c.updated_at;

-- ----------------------------------------------------------------------------
-- View: user_booking_history
-- Purpose: User booking history with center and service details
-- Usage: User dashboard, booking management
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW user_booking_history AS
SELECT 
    b.id AS booking_id,
    b.booking_number,
    b.user_id,
    u.name AS user_name,
    u.email AS user_email,
    b.center_id,
    c.name AS center_name,
    c.address AS center_address,
    c.phone AS center_phone,
    b.service_id,
    s.name AS service_name,
    s.price AS service_price,
    s.price_unit AS service_price_unit,
    b.booking_date,
    b.booking_time,
    b.booking_type,
    b.status,
    b.calendly_event_uri,
    b.calendly_cancel_url,
    b.calendly_reschedule_url,
    b.created_at,
    b.updated_at
FROM bookings b
INNER JOIN users u ON b.user_id = u.id
INNER JOIN centers c ON b.center_id = c.id
LEFT JOIN services s ON b.service_id = s.id
WHERE b.deleted_at IS NULL AND u.deleted_at IS NULL;

-- ============================================================================
-- SECTION 10: VERIFICATION QUERIES
-- ============================================================================
-- Purpose: Verify database schema integrity
-- Usage: Run after schema creation to ensure everything is correct
-- ============================================================================

-- Query 1: Verify all tables created
-- Expected: 18 tables
SELECT 
    '1. Total Tables' AS verification_check,
    COUNT(*) AS count,
    'Expected: 18' AS expected
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'eldercare_db'
UNION ALL

-- Query 2: Verify all foreign keys
-- Expected: 15 foreign keys
SELECT 
    '2. Total Foreign Keys' AS verification_check,
    COUNT(*) AS count,
    'Expected: 15' AS expected
FROM information_schema.KEY_COLUMN_USAGE
WHERE CONSTRAINT_SCHEMA = 'eldercare_db'
    AND REFERENCED_TABLE_NAME IS NOT NULL
UNION ALL

-- Query 3: Verify character set
-- Expected: All tables utf8mb4
SELECT 
    '3. Non-UTF8MB4 Tables' AS verification_check,
    COUNT(*) AS count,
    'Expected: 0' AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'eldercare_db'
    AND TABLE_COLLATION NOT LIKE 'utf8mb4%'
UNION ALL

-- Query 4: Verify indexes
-- Expected: 80+ indexes (including primary keys, foreign keys, and custom indexes)
SELECT 
    '4. Total Indexes' AS verification_check,
    COUNT(DISTINCT CONCAT(TABLE_NAME, '.', INDEX_NAME)) AS count,
    'Expected: 80+' AS expected
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'eldercare_db';

-- ============================================================================
-- Detailed Table and Foreign Key Information
-- ============================================================================

-- List all tables with row counts (initially 0)
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb,
    TABLE_COLLATION,
    ENGINE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'eldercare_db'
ORDER BY TABLE_NAME;

-- List all foreign key relationships
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE CONSTRAINT_SCHEMA = 'eldercare_db'
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

-- List all indexes by table
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns,
    INDEX_TYPE,
    CASE WHEN NON_UNIQUE = 0 THEN 'UNIQUE' ELSE 'NON-UNIQUE' END AS uniqueness
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'eldercare_db'
GROUP BY TABLE_NAME, INDEX_NAME, INDEX_TYPE, NON_UNIQUE
ORDER BY TABLE_NAME, INDEX_NAME;

-- ============================================================================
-- COMPLETION SUMMARY
-- ============================================================================
-- 
-- Database: eldercare_db
-- Total Tables: 18
--   - Foundation: 4 (users, password_reset_tokens, failed_jobs, personal_access_tokens)
--   - Core Entities: 5 (profiles, centers, faqs, subscriptions, contact_submissions)
--   - Dependent: 2 (services, staff)
--   - Relationships: 2 (bookings, testimonials)
--   - PDPA Compliance: 2 (consents, audit_logs)
--   - Polymorphic: 2 (media, content_translations)
--   - Queue: 1 (jobs)
--
-- Total Foreign Keys: 15
-- Total Indexes: 80+ (including primary keys, foreign keys, and performance indexes)
-- Total Views: 2 (active_centers_summary, user_booking_history)
--
-- Character Set: utf8mb4 (Full Unicode support)
-- Collation: utf8mb4_unicode_ci
-- Engine: InnoDB (ACID compliance)
--
-- Compliance:
--   ✅ PDPA (Singapore Personal Data Protection Act)
--      - Consent tracking with versioning
--      - Audit logging (7-year retention)
--      - Soft deletes (30-day grace period)
--      - Data export support via JSON columns
--   
--   ✅ MOH (Ministry of Health regulations)
--      - License number tracking
--      - License expiry monitoring
--      - Accreditation status
--      - Staff credentials and qualifications
--      - Medical facilities documentation
--
--   ✅ Multilingual Support
--      - English, Mandarin, Malay, Tamil
--      - Polymorphic content_translations table
--      - Translation workflow (draft → translated → reviewed → published)
--
--   ✅ External Integrations
--      - Calendly (booking_* columns in bookings table)
--      - Mailchimp (mailchimp_* columns in subscriptions table)
--      - Twilio (sms_sent, notification tracking in bookings table)
--      - Cloudflare Stream (cloudflare_stream_id in media table)
--      - AWS S3 (url columns for media storage)
--
-- Next Steps:
-- 1. Review the schema
-- 2. Execute verification queries
-- 3. Create Laravel migrations based on this schema
-- 4. Create Eloquent models with relationships
-- 5. Create factories and seeders for testing
-- 6. Populate with sample data
-- 7. Performance testing with large datasets
--
-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
```

**Schema Generation Complete! ✅**

---

## Summary & Next Steps

I've successfully generated a comprehensive, production-ready database schema with:

### ✅ **Delivered**
- **18 tables** with complete column definitions
- **15 foreign key relationships** with appropriate cascade rules
- **80+ indexes** for optimal query performance
- **PDPA compliance** (consents, audit_logs, soft deletes)
- **MOH compliance** (license tracking, staff credentials)
- **Multilingual support** (content_translations table)
- **External integrations** (Calendly, Mailchimp, Twilio, Cloudflare Stream)
- **2 useful views** (active_centers_summary, user_booking_history)
- **Verification queries** to ensure integrity
- **Comprehensive comments** explaining every table and complex field

### 📊 **Key Features**
1. **utf8mb4 character set** - Full Unicode (Chinese, Tamil, emojis)
2. **InnoDB engine** - ACID compliance, foreign keys, row-locking
3. **Soft deletes** - PDPA 30-day grace period
4. **JSON columns** - Flexible data (operating hours, amenities, questionnaires)
5. **Polymorphic tables** - Reusable media and translations
6. **CHECK constraints** - Data validation (rating 1-5, capacity > 0)
7. **Composite indexes** - Optimized for common query patterns
8. **FULLTEXT indexes** - MeiliSearch synchronization

### 🔍 **How to Use**

1. **Review the schema** above
2. **Execute on MySQL 8.0+**:
   ```bash
   mysql -u root -p < eldercare_sg_schema_v1.0.sql
   ```
3. **Run verification queries** (included at end of SQL file)
4. **Test with sample data**:
   ```sql
   INSERT INTO users (name, email, password) VALUES ('Test User', 'test@example.com', '$2y$10$hash');
   INSERT INTO centers (name, slug, description, address, city, postal_code, phone, email, moh_license_number, license_expiry_date, capacity) 
   VALUES ('Sunshine Care', 'sunshine-care', 'Premier elderly care', '123 Main St', 'Singapore', '123456', '+6512345678', 'info@sunshine.sg', 'MOH-2024-001', '2025-12-31', 50);
   ```

### 📝 **What's Next?**

Would you like me to:

1. **Create Laravel migrations** based on this schema (18 migration files)?
2. **Create Eloquent models** with all relationships defined?
3. **Create factories and seeders** for realistic test data?
4. **Generate an Entity-Relationship Diagram** (visual representation)?
5. **Create a data dictionary document** (detailed table/column reference)?
6. **Proceed with Phase 1 of the codebase execution plan** (Foundation Setup)?

Please let me know which direction you'd like to take next!
