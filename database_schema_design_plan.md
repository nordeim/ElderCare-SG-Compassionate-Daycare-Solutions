Database Schema Design & Execution Plan - ElderCare SG
🎯 Executive Planning Summary
I will create a comprehensive database schema that serves as the data foundation for the ElderCare SG platform. This plan approaches database design with the same rigor as software architecture, ensuring:

Compliance: PDPA requirements embedded in schema design
Performance: Strategic indexing and query optimization
Scalability: Designed for growth (millions of bookings, thousands of centers)
Data Integrity: Foreign key constraints, validation rules
Multilingual Support: Translation architecture built-in
Audit Trail: Complete tracking of all data changes
📋 Phase Breakdown: Database Schema Execution
Phase 1: Requirements Analysis & Entity Discovery (1 day)
Objective: Identify all entities, attributes, and relationships from project requirements.

Phase 2: Conceptual Data Modeling (1 day)
Objective: Create Entity-Relationship Diagram (ERD) showing all entities and relationships.

Phase 3: Logical Schema Design (2 days)
Objective: Design detailed table structures with columns, data types, constraints.

Phase 4: Physical Schema Design (1 day)
Objective: Define indexes, partitioning strategy, storage optimization.

Phase 5: Schema Validation (1 day)
Objective: Validate schema against requirements, performance, and compliance needs.

Phase 6: Migration File Creation (2 days)
Objective: Create Laravel migration files in correct dependency order.

Phase 7: Seeder Development (1 day)
Objective: Create factory and seeder files for development/testing data.

Phase 8: Schema Testing (1 day)
Objective: Test migrations, relationships, constraints, and queries.

Total Estimated Time: 10 days

Phase 1: Requirements Analysis & Entity Discovery
1.1 Core Entities Identification
Based on comprehensive analysis of project requirements, I've identified 18 core entities:

Authentication & User Management (4 entities)
Users - Core authentication data
Profiles - Extended user information (1:1 with users)
Roles - User roles (super_admin, content_manager, translator, moderator, user)
Permissions - Granular permissions for role-based access control
Eldercare Centers & Services (3 entities)
Centers - Eldercare daycare centers (with MOH compliance fields)
Services - Services/programs offered by centers
Center_Staff - Staff information (Phase 2 - optional for MVP)
Booking System (1 entity)
Bookings - Visit bookings with Calendly integration
Content Management (3 entities)
FAQs - Frequently asked questions
Testimonials - User reviews and testimonials
Pages - Static content pages (About, Privacy Policy, Terms)
Multilingual Support (1 entity)
Content_Translations - Polymorphic translations for all translatable content
Media Management (1 entity)
Media - Polymorphic media attachments (images, videos, documents)
PDPA Compliance (3 entities)
Consents - User consent tracking (versioned, timestamped)
Audit_Logs - Complete audit trail of data changes
Data_Retention_Tracking - Track data retention schedules
External Integrations (2 entities)
Subscriptions - Newsletter subscriptions (Mailchimp sync)
Notification_Preferences - User notification settings (email, SMS)
Supporting Entities (5 entities)
Integration_Logs - External API call logs (debugging)
Search_Queries - Track search behavior
User_Events - Internal analytics events
Password_Resets - Laravel password reset tokens
Sessions - Laravel session management
Pivot Tables (2 entities)
Role_User - Many-to-many: users ↔ roles
Permission_Role - Many-to-many: roles ↔ permissions
Total: 25 tables

1.2 Attribute Analysis
For each entity, I've analyzed required attributes based on:

Functional Requirements (what the app needs to do)
Regulatory Requirements (PDPA, MOH compliance)
Performance Requirements (indexing, query optimization)
Integration Requirements (external API mappings)
<details> <summary><strong>Click to expand: Detailed Attribute Analysis</strong></summary>
Users Table Attributes
Attribute	Type	Constraints	Purpose
id	BIGINT UNSIGNED	PK, AUTO_INCREMENT	Unique identifier
name	VARCHAR(255)	NOT NULL	User's full name
email	VARCHAR(255)	UNIQUE, NOT NULL	Authentication, communication
phone	VARCHAR(20)	NULLABLE, INDEX	SMS notifications, contact
password	VARCHAR(255)	NOT NULL	Hashed password (bcrypt)
role	ENUM	DEFAULT 'user'	Quick role check (deprecated - use roles table)
email_verified_at	TIMESTAMP	NULLABLE	Email verification status
remember_token	VARCHAR(100)	NULLABLE	Laravel remember me
created_at	TIMESTAMP	NOT NULL	Record creation
updated_at	TIMESTAMP	NOT NULL	Last modification
deleted_at	TIMESTAMP	NULLABLE	Soft delete (PDPA 30-day grace)
Rationale:

email unique for authentication
phone for SMS notifications (Twilio)
deleted_at for PDPA compliance (soft delete with 30-day grace period)
email_verified_at prevents unverified users from booking
Centers Table Attributes (MOH Compliance Critical)
Attribute	Type	Constraints	Purpose
id	BIGINT UNSIGNED	PK, AUTO_INCREMENT	Unique identifier
name	VARCHAR(255)	NOT NULL, INDEX	Center name
slug	VARCHAR(255)	UNIQUE, NOT NULL	URL-friendly identifier
description	TEXT	NOT NULL	Full description (translatable)
address	VARCHAR(500)	NOT NULL	Physical address
city	VARCHAR(100)	NOT NULL	City (for filtering)
postal_code	VARCHAR(10)	NOT NULL, INDEX	Singapore postal code
latitude	DECIMAL(10,8)	NULLABLE	Geolocation (future map feature)
longitude	DECIMAL(11,8)	NULLABLE	Geolocation
phone	VARCHAR(20)	NOT NULL	Contact number
email	VARCHAR(255)	NOT NULL	Contact email
moh_license_number	VARCHAR(50)	UNIQUE, NOT NULL	MOH compliance
license_expiry_date	DATE	NULLABLE	License validity
accreditation_status	ENUM	NOT NULL	pending/accredited/not_accredited
staff_count	INT	DEFAULT 0	Number of staff
capacity	INT	NULLABLE	Max participants
medical_facilities	JSON	NULLABLE	On-site medical equipment
transport_info	JSON	NULLABLE	Singapore requirement: MRT, bus routes
amenities	JSON	NULLABLE	Facilities (wheelchair access, etc.)
languages_supported	JSON	NOT NULL	en, zh, ms, ta
operating_hours	JSON	NOT NULL	Weekday/weekend schedules
status	ENUM	DEFAULT 'draft'	draft/published/archived
created_at	TIMESTAMP	NOT NULL	Record creation
updated_at	TIMESTAMP	NOT NULL	Last modification
deleted_at	TIMESTAMP	NULLABLE	Soft delete
Rationale:

moh_license_number UNIQUE enforces MOH compliance (one license per center)
transport_info JSON stores Singapore-specific MRT/bus data
medical_facilities JSON flexible for various equipment types
operating_hours JSON handles complex schedules (weekday vs weekend, holidays)
languages_supported JSON allows multiple languages per center
slug for SEO-friendly URLs
postal_code indexed for location-based searches
Bookings Table Attributes (Calendly Integration Critical)
Attribute	Type	Constraints	Purpose
id	BIGINT UNSIGNED	PK, AUTO_INCREMENT	Unique identifier
user_id	BIGINT UNSIGNED	FK users(id), NULLABLE	User who booked (nullable for guest bookings)
center_id	BIGINT UNSIGNED	FK centers(id), NOT NULL	Which center
service_id	BIGINT UNSIGNED	FK services(id), NULLABLE	Specific service (nullable for general visit)
calendly_event_id	VARCHAR(255)	NULLABLE, INDEX	Calendly external ID
calendly_event_uri	VARCHAR(500)	NULLABLE	Calendly webhook matching
booking_date	DATE	NOT NULL, INDEX	Appointment date
booking_time	TIME	NOT NULL	Appointment time
status	ENUM	DEFAULT 'pending'	pending/confirmed/completed/canceled/no_show
questionnaire_responses	JSON	NULLABLE	Pre-booking questionnaire data
notes	TEXT	NULLABLE	Admin notes
cancellation_reason	TEXT	NULLABLE	Why booking canceled
canceled_at	TIMESTAMP	NULLABLE	When canceled
reminder_sent_at	TIMESTAMP	NULLABLE	Track 24h reminder sent
created_at	TIMESTAMP	NOT NULL	Booking creation
updated_at	TIMESTAMP	NOT NULL	Last modification
deleted_at	TIMESTAMP	NULLABLE	Soft delete
Rationale:

calendly_event_id and calendly_event_uri for integration sync
questionnaire_responses JSON stores flexible questionnaire data
reminder_sent_at prevents duplicate SMS reminders
(booking_date, status) composite index for "upcoming bookings" queries
user_id NULLABLE allows guest bookings (collect email/phone only)
Consents Table Attributes (PDPA Critical)
Attribute	Type	Constraints	Purpose
id	BIGINT UNSIGNED	PK, AUTO_INCREMENT	Unique identifier
user_id	BIGINT UNSIGNED	FK users(id), NOT NULL	Who consented
consent_type	ENUM	NOT NULL	account_creation/marketing_email/sms_notifications/analytics_cookies
consent_given	BOOLEAN	NOT NULL	True = consented, False = declined
consent_text	TEXT	NOT NULL	Snapshot of privacy policy text
consent_version	VARCHAR(20)	NOT NULL	Privacy policy version (e.g., "1.0")
ip_address	VARCHAR(45)	NOT NULL	PDPA requirement: capture IP
user_agent	TEXT	NOT NULL	PDPA requirement: capture browser
created_at	TIMESTAMP	NOT NULL	When consent given
updated_at	TIMESTAMP	NOT NULL	When consent updated
Rationale:

consent_text snapshot proves what user agreed to (legal protection)
consent_version tracks privacy policy changes
ip_address and user_agent required for PDPA audit trail
Multiple rows per user (one per consent type) for granular control
NO soft delete (consents are immutable audit records)
Audit_Logs Table Attributes (PDPA Critical)
Attribute	Type	Constraints	Purpose
id	BIGINT UNSIGNED	PK, AUTO_INCREMENT	Unique identifier
user_id	BIGINT UNSIGNED	FK users(id), NULLABLE	Who performed action (NULL for system)
auditable_type	VARCHAR(255)	NOT NULL, INDEX	Polymorphic: model name (App\Models\User)
auditable_id	BIGINT UNSIGNED	NOT NULL, INDEX	Polymorphic: record ID
action	ENUM	NOT NULL	created/updated/deleted/viewed/exported
old_values	JSON	NULLABLE	State before change
new_values	JSON	NULLABLE	State after change
ip_address	VARCHAR(45)	NOT NULL	PDPA requirement
user_agent	TEXT	NOT NULL	PDPA requirement
created_at	TIMESTAMP	NOT NULL	When action occurred
Rationale:

Polymorphic design (auditable_type, auditable_id) allows logging any model
old_values and new_values JSON provide complete audit trail
action enum covers all CRUD operations plus sensitive actions (viewed, exported)
NO updated_at or soft delete (audit logs are immutable)
Composite index on (auditable_type, auditable_id) for querying entity history
7-year retention required (Singapore legal requirement)
Content_Translations Table Attributes (i18n Critical)
Attribute	Type	Constraints	Purpose
id	BIGINT UNSIGNED	PK, AUTO_INCREMENT	Unique identifier
translatable_type	VARCHAR(255)	NOT NULL, INDEX	Polymorphic: model name
translatable_id	BIGINT UNSIGNED	NOT NULL, INDEX	Polymorphic: record ID
locale	CHAR(2)	NOT NULL	en/zh/ms/ta
field	VARCHAR(100)	NOT NULL	Which field translated (name, description)
value	TEXT	NOT NULL	Translated text
translation_status	ENUM	DEFAULT 'not_translated'	not_translated/in_progress/translated/reviewed
translated_by	BIGINT UNSIGNED	FK users(id), NULLABLE	Translator user
reviewed_by	BIGINT UNSIGNED	FK users(id), NULLABLE	Reviewer user
created_at	TIMESTAMP	NOT NULL	Translation created
updated_at	TIMESTAMP	NOT NULL	Translation updated
Rationale:

Polymorphic design supports translating Centers, Services, FAQs, Pages, etc.
field specifies which attribute is translated (name vs description vs features)
translation_status enables translation workflow tracking
translated_by and reviewed_by for translation quality control
Unique constraint: (translatable_type, translatable_id, locale, field) prevents duplicate translations
Query pattern: "Get all Mandarin translations for Center ID 5"
</details>
1.3 Relationship Analysis
I've identified 23 relationships between entities:

Relationship	Type	Cardinality	Constraints
Users ↔ Profiles	One-to-One	1:1	ON DELETE CASCADE
Users ↔ Roles	Many-to-Many	M:N	Via role_user pivot
Roles ↔ Permissions	Many-to-Many	M:N	Via permission_role pivot
Centers ↔ Services	One-to-Many	1:N	ON DELETE CASCADE
Centers ↔ Bookings	One-to-Many	1:N	ON DELETE RESTRICT (preserve bookings)
Services ↔ Bookings	One-to-Many	1:N	ON DELETE RESTRICT
Users ↔ Bookings	One-to-Many	1:N	ON DELETE SET NULL (preserve bookings for analytics)
Users ↔ Testimonials	One-to-Many	1:N	ON DELETE CASCADE
Centers ↔ Testimonials	One-to-Many	1:N	ON DELETE CASCADE
Users ↔ Consents	One-to-Many	1:N	ON DELETE CASCADE
Users ↔ Notification_Preferences	One-to-One	1:1	ON DELETE CASCADE
Centers ↔ Media	Polymorphic	1:N	ON DELETE CASCADE
Services ↔ Media	Polymorphic	1:N	ON DELETE CASCADE
Testimonials ↔ Media	Polymorphic	1:N	ON DELETE CASCADE
Centers ↔ Content_Translations	Polymorphic	1:N	ON DELETE CASCADE
Services ↔ Content_Translations	Polymorphic	1:N	ON DELETE CASCADE
FAQs ↔ Content_Translations	Polymorphic	1:N	ON DELETE CASCADE
Pages ↔ Content_Translations	Polymorphic	1:N	ON DELETE CASCADE
Users (moderator) ↔ Testimonials	One-to-Many	1:N	ON DELETE SET NULL
Users (translator) ↔ Content_Translations	One-to-Many	1:N	ON DELETE SET NULL
Users (reviewer) ↔ Content_Translations	One-to-Many	1:N	ON DELETE SET NULL
Users ↔ Search_Queries	One-to-Many	1:N	ON DELETE SET NULL (preserve analytics)
Users ↔ User_Events	One-to-Many	1:N	ON DELETE SET NULL
Critical Design Decisions:

Polymorphic Relationships (Media, Content_Translations):

Pros: Flexible, DRY (don't repeat yourself), single table for all media/translations
Cons: No foreign key constraints on polymorphic columns, slightly complex queries
Decision: Use polymorphic for Media and Content_Translations (industry standard for CMS)
Soft Deletes on Users:

Requirement: PDPA mandates 30-day grace period for account deletion
Implementation: deleted_at timestamp column
Cascade behavior: When user soft-deleted, related data marked for deletion but preserved for 30 days
Hard delete: Cron job runs nightly to permanently delete users where deleted_at < NOW() - 30 days
Booking Deletion Policy:

Constraint: ON DELETE RESTRICT for center_id and service_id
Rationale: Cannot delete center/service with active bookings (data integrity)
Workflow: Admin must cancel all bookings before deleting center
Exception: ON DELETE SET NULL for user_id (preserve booking history for analytics even if user deleted)
Phase 2: Conceptual Data Modeling
2.1 Entity-Relationship Diagram (ERD)
I'll create a comprehensive ERD showing all entities and relationships:

mermaid

erDiagram
    %% Core User & Authentication
    USERS ||--|| PROFILES : "has"
    USERS }o--o{ ROLES : "has (via role_user)"
    ROLES }o--o{ PERMISSIONS : "has (via permission_role)"
    
    %% Eldercare Centers & Services
    CENTERS ||--o{ SERVICES : "offers"
    CENTERS ||--o{ BOOKINGS : "receives"
    SERVICES ||--o{ BOOKINGS : "booked_for"
    
    %% User Interactions
    USERS ||--o{ BOOKINGS : "makes"
    USERS ||--o{ TESTIMONIALS : "writes"
    USERS ||--o{ CONSENTS : "gives"
    USERS ||--|| NOTIFICATION_PREFERENCES : "has"
    
    %% Content & Reviews
    CENTERS ||--o{ TESTIMONIALS : "has"
    USERS ||--o{ TESTIMONIALS : "moderates (moderated_by)"
    
    %% Polymorphic: Media
    CENTERS ||--o{ MEDIA : "has (polymorphic)"
    SERVICES ||--o{ MEDIA : "has (polymorphic)"
    TESTIMONIALS ||--o{ MEDIA : "has (polymorphic)"
    
    %% Polymorphic: Translations
    CENTERS ||--o{ CONTENT_TRANSLATIONS : "has (polymorphic)"
    SERVICES ||--o{ CONTENT_TRANSLATIONS : "has (polymorphic)"
    FAQS ||--o{ CONTENT_TRANSLATIONS : "has (polymorphic)"
    PAGES ||--o{ CONTENT_TRANSLATIONS : "has (polymorphic)"
    
    %% Translation Management
    USERS ||--o{ CONTENT_TRANSLATIONS : "translates (translated_by)"
    USERS ||--o{ CONTENT_TRANSLATIONS : "reviews (reviewed_by)"
    
    %% Analytics & Tracking
    USERS ||--o{ SEARCH_QUERIES : "performs"
    USERS ||--o{ USER_EVENTS : "triggers"
    USERS ||--o{ AUDIT_LOGS : "performs_action"
    
    %% External Integrations
    SUBSCRIPTIONS }o--|| USERS : "may_belong_to"
    
    %% Entity Definitions
    USERS {
        bigint id PK
        string name
        string email UK
        string phone
        string password
        enum role
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    PROFILES {
        bigint id PK
        bigint user_id FK
        string avatar
        text bio
        date birth_date
        string address
        string city
        string postal_code
        timestamp created_at
        timestamp updated_at
    }
    
    CENTERS {
        bigint id PK
        string name
        string slug UK
        text description
        string address
        string city
        string postal_code
        decimal latitude
        decimal longitude
        string phone
        string email
        string moh_license_number UK
        date license_expiry_date
        enum accreditation_status
        int staff_count
        int capacity
        json medical_facilities
        json transport_info
        json amenities
        json languages_supported
        json operating_hours
        enum status
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    SERVICES {
        bigint id PK
        bigint center_id FK
        string name
        string slug
        text description
        decimal price
        int duration
        json features
        int capacity
        enum status
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    BOOKINGS {
        bigint id PK
        bigint user_id FK
        bigint center_id FK
        bigint service_id FK
        string calendly_event_id
        string calendly_event_uri
        date booking_date
        time booking_time
        enum status
        json questionnaire_responses
        text notes
        text cancellation_reason
        timestamp canceled_at
        timestamp reminder_sent_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    TESTIMONIALS {
        bigint id PK
        bigint user_id FK
        bigint center_id FK
        string title
        text content
        int rating
        enum status
        bigint moderated_by FK
        timestamp moderated_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    CONSENTS {
        bigint id PK
        bigint user_id FK
        enum consent_type
        boolean consent_given
        text consent_text
        string consent_version
        string ip_address
        text user_agent
        timestamp created_at
        timestamp updated_at
    }
    
    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string auditable_type
        bigint auditable_id
        enum action
        json old_values
        json new_values
        string ip_address
        text user_agent
        timestamp created_at
    }
    
    CONTENT_TRANSLATIONS {
        bigint id PK
        string translatable_type
        bigint translatable_id
        char locale
        string field
        text value
        enum translation_status
        bigint translated_by FK
        bigint reviewed_by FK
        timestamp created_at
        timestamp updated_at
    }
    
    MEDIA {
        bigint id PK
        string mediable_type
        bigint mediable_id
        enum type
        string url
        string thumbnail_url
        string filename
        string mime_type
        bigint size
        int duration
        string caption
        string alt_text
        int order
        enum status
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    FAQS {
        bigint id PK
        enum category
        text question
        text answer
        int order
        enum status
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    PAGES {
        bigint id PK
        string slug UK
        string title
        text content
        string meta_title
        text meta_description
        enum status
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    SUBSCRIPTIONS {
        bigint id PK
        string email UK
        string mailchimp_subscriber_id
        enum status
        json preferences
        timestamp subscribed_at
        timestamp unsubscribed_at
        timestamp created_at
        timestamp updated_at
    }
    
    NOTIFICATION_PREFERENCES {
        bigint id PK
        bigint user_id FK
        boolean email_enabled
        boolean sms_enabled
        boolean push_enabled
        json notification_types
        timestamp created_at
        timestamp updated_at
    }
    
    ROLES {
        bigint id PK
        string name UK
        string display_name
        text description
        timestamp created_at
        timestamp updated_at
    }
    
    PERMISSIONS {
        bigint id PK
        string name UK
        string display_name
        text description
        timestamp created_at
        timestamp updated_at
    }
    
    SEARCH_QUERIES {
        bigint id PK
        string query
        int results_count
        bigint user_id FK
        string ip_address
        timestamp created_at
    }
    
    USER_EVENTS {
        bigint id PK
        bigint user_id FK
        string event_name
        json event_data
        string ip_address
        text user_agent
        timestamp created_at
    }
Phase 3: Logical Schema Design
3.1 Complete Table Definitions
I'll now provide the complete, production-ready schema for all 25 tables:

<details> <summary><strong>📁 1. users - Core authentication and user data</strong></summary>
SQL

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'content_manager', 'translator', 'moderator') DEFAULT 'user',
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Design Notes:

email unique constraint for authentication
phone indexed for SMS lookup
deleted_at for PDPA soft delete (30-day grace period)
role enum for quick role check (will be superseded by roles table for granular RBAC)
remember_token for Laravel "remember me" functionality
utf8mb4 charset for full Unicode support (emojis, all 4 languages)
</details><details> <summary><strong>📁 2. profiles - Extended user information (1:1 with users)</strong></summary>
SQL

CREATE TABLE profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    avatar VARCHAR(500) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    birth_date DATE DEFAULT NULL,
    address VARCHAR(500) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    postal_code VARCHAR(10) DEFAULT NULL,
    country VARCHAR(100) DEFAULT 'Singapore',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_profiles_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_city (city),
    INDEX idx_postal_code (postal_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Design Notes:

user_id unique enforces 1:1 relationship
ON DELETE CASCADE: if user deleted, profile deleted
postal_code indexed for potential location-based features
avatar stores URL (S3 path)
</details><details> <summary><strong>📁 3. roles - User roles for RBAC</strong></summary>
SQL

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Seeded Roles:

super_admin - Full system access
content_manager - Manage centers, services, FAQs
translator - Manage translations
moderator - Review testimonials
user - Standard user
</details><details> <summary><strong>📁 4. permissions - Granular permissions</strong></summary>
SQL

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Example Permissions:

centers.create, centers.update, centers.delete, centers.publish
services.create, services.update, services.delete
testimonials.moderate
translations.manage
users.manage
</details><details> <summary><strong>📁 5. role_user - Pivot table for users ↔ roles (M:N)</strong></summary>
SQL

CREATE TABLE role_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_role_user_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_user_role_id FOREIGN KEY (role_id) 
        REFERENCES roles(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_user_role (user_id, role_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Design Notes:

Composite unique constraint prevents duplicate role assignments
Indexed for permission checks (frequent queries: "Does user have role X?")
</details><details> <summary><strong>📁 6. permission_role - Pivot table for roles ↔ permissions (M:N)</strong></summary>
SQL

CREATE TABLE permission_role (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_permission_role_permission_id FOREIGN KEY (permission_id) 
        REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_permission_role_role_id FOREIGN KEY (role_id) 
        REFERENCES roles(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_permission_role (permission_id, role_id),
    INDEX idx_permission_id (permission_id),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
</details><details> <summary><strong>📁 7. centers - Eldercare daycare centers (MOH compliance critical)</strong></summary>
SQL

CREATE TABLE centers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    address VARCHAR(500) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10) NOT NULL,
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    
    -- MOH Compliance Fields
    moh_license_number VARCHAR(50) NOT NULL UNIQUE,
    license_expiry_date DATE DEFAULT NULL,
    accreditation_status ENUM('pending', 'accredited', 'not_accredited') DEFAULT 'pending',
    
    -- Facility Information
    staff_count INT UNSIGNED DEFAULT 0,
    capacity INT UNSIGNED DEFAULT NULL,
    medical_facilities JSON DEFAULT NULL COMMENT 'Array of medical equipment/facilities',
    transport_info JSON NOT NULL COMMENT 'MRT stations, bus routes (Singapore requirement)',
    amenities JSON DEFAULT NULL COMMENT 'Wheelchair access, parking, etc.',
    languages_supported JSON NOT NULL DEFAULT ('["en"]') COMMENT 'Supported languages: en, zh, ms, ta',
    operating_hours JSON NOT NULL COMMENT 'Weekday/weekend/holiday schedules',
    
    -- Status
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_city (city),
    INDEX idx_postal_code (postal_code),
    INDEX idx_status (status),
    INDEX idx_accreditation_status (accreditation_status),
    INDEX idx_moh_license (moh_license_number),
    INDEX idx_deleted_at (deleted_at),
    FULLTEXT INDEX ft_name_description (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
JSON Structure Examples:

JSON

// medical_facilities
{
  "equipment": ["wheelchair", "defibrillator", "first_aid_kit"],
  "exam_room": true,
  "medication_management": true
}

// transport_info (Singapore requirement)
{
  "mrt": [
    {"station": "Outram Park", "distance_km": 0.5},
    {"station": "Chinatown", "distance_km": 0.8}
  ],
  "bus": ["51", "143", "186"],
  "parking": {
    "available": true,
    "spaces": 10,
    "wheelchair_accessible": true
  }
}

// amenities
{
  "wheelchair_accessible": true,
  "elevator": true,
  "garden": true,
  "air_conditioned": true,
  "wifi": true
}

// operating_hours
{
  "weekday": {"open": "08:00", "close": "18:00"},
  "saturday": {"open": "09:00", "close": "14:00"},
  "sunday": "closed",
  "public_holidays": "closed"
}
Design Notes:

moh_license_number UNIQUE enforces one license per center (regulatory requirement)
slug for SEO-friendly URLs
FULLTEXT index on name and description for search (alternative to MeiliSearch for simple searches)
JSON columns for flexible, schema-less data (medical facilities vary widely)
transport_info critical for Singapore users (MRT culture)
</details><details> <summary><strong>📁 8. services - Services/programs offered by centers</strong></summary>
SQL

CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    center_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL COMMENT 'Price in SGD',
    duration INT UNSIGNED NOT NULL COMMENT 'Duration in minutes',
    features JSON DEFAULT NULL COMMENT 'Service features/highlights',
    capacity INT UNSIGNED DEFAULT NULL COMMENT 'Max participants',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    CONSTRAINT fk_services_center_id FOREIGN KEY (center_id) 
        REFERENCES centers(id) ON DELETE CASCADE,
    
    INDEX idx_center_id (center_id),
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_center_status (center_id, status),
    INDEX idx_deleted_at (deleted_at),
    FULLTEXT INDEX ft_name_description (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
JSON Structure Example:

JSON

// features
{
  "included": [
    "Meals (breakfast, lunch)",
    "Transportation",
    "Medical monitoring",
    "Activity programs"
  ],
  "specializations": ["dementia_care", "stroke_recovery"],
  "language_of_instruction": ["en", "zh"]
}
Design Notes:

center_id FK with ON DELETE CASCADE (if center deleted, services deleted)
Composite index (center_id, status) for query: "Get published services for center X"
slug unique per center (enforced at application level, not DB constraint)
price in SGD (Singapore Dollars) with 2 decimal places
</details><details> <summary><strong>📁 9. bookings - Visit bookings (Calendly integration critical)</strong></summary>
SQL

CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'Nullable for guest bookings',
    center_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'Nullable for general visits',
    
    -- Calendly Integration
    calendly_event_id VARCHAR(255) DEFAULT NULL COMMENT 'Calendly event UUID',
    calendly_event_uri VARCHAR(500) DEFAULT NULL COMMENT 'Full Calendly event URI for webhook matching',
    
    -- Booking Details
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'canceled', 'no_show') DEFAULT 'pending',
    
    -- Questionnaire & Notes
    questionnaire_responses JSON DEFAULT NULL COMMENT 'Pre-booking questionnaire data',
    notes TEXT DEFAULT NULL COMMENT 'Admin notes',
    
    -- Cancellation
    cancellation_reason TEXT DEFAULT NULL,
    canceled_at TIMESTAMP NULL DEFAULT NULL,
    
    -- Notifications
    reminder_sent_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Track 24h reminder sent (prevent duplicates)',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    CONSTRAINT fk_bookings_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_center_id FOREIGN KEY (center_id) 
        REFERENCES centers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_service_id FOREIGN KEY (service_id) 
        REFERENCES services(id) ON DELETE RESTRICT,
    
    INDEX idx_user_id (user_id),
    INDEX idx_center_id (center_id),
    INDEX idx_service_id (service_id),
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status),
    INDEX idx_booking_date_status (booking_date, status),
    INDEX idx_calendly_event_id (calendly_event_id),
    INDEX idx_reminder_sent_at (reminder_sent_at),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
JSON Structure Example:

JSON

// questionnaire_responses
{
  "elderly_name": "Tan Ah Kow",
  "age": 78,
  "medical_conditions": ["diabetes", "hypertension"],
  "mobility": "wheelchair",
  "dietary_restrictions": "halal",
  "emergency_contact": {
    "name": "Tan Wei Ming",
    "relationship": "son",
    "phone": "+65 9123 4567"
  },
  "special_requirements": "Requires Mandarin-speaking staff"
}
Design Notes:

user_id NULLABLE with ON DELETE SET NULL preserves bookings for analytics even if user deleted
center_id and service_id ON DELETE RESTRICT prevents deletion of centers/services with bookings
calendly_event_id indexed for webhook lookups (fast: "Find booking by Calendly event ID")
Composite index (booking_date, status) for query: "Upcoming confirmed bookings"
reminder_sent_at prevents duplicate SMS reminders (check if NULL before sending)
</details><details> <summary><strong>📁 10. testimonials - User reviews and testimonials</strong></summary>
SQL

CREATE TABLE testimonials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    center_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    
    -- Moderation
    moderated_by BIGINT UNSIGNED DEFAULT NULL COMMENT 'Admin user who moderated',
    moderated_at TIMESTAMP NULL DEFAULT NULL,
    moderation_notes TEXT DEFAULT NULL COMMENT 'Internal notes for rejection reasons',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    CONSTRAINT fk_testimonials_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_testimonials_center_id FOREIGN KEY (center_id) 
        REFERENCES centers(id) ON DELETE CASCADE,
    CONSTRAINT fk_testimonials_moderated_by FOREIGN KEY (moderated_by) 
        REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_center_id (center_id),
    INDEX idx_status (status),
    INDEX idx_rating (rating),
    INDEX idx_center_status (center_id, status),
    INDEX idx_moderated_by (moderated_by),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Design Notes:

rating CHECK constraint ensures values 1-5 only
moderated_by ON DELETE SET NULL preserves testimonials even if moderator user deleted
Composite index (center_id, status) for query: "Get approved testimonials for center X"
Status workflow: pending → approved/rejected (moderation required before display)
</details><details> <summary><strong>📁 11. faqs - Frequently Asked Questions</strong></summary>
SQL

CREATE TABLE faqs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category ENUM('general', 'booking', 'services', 'pricing', 'accessibility') DEFAULT 'general',
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    `order` INT UNSIGNED DEFAULT 0 COMMENT 'Display order (drag-and-drop in admin)',
    status ENUM('draft', 'published') DEFAULT 'draft',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_order (`order`),
    INDEX idx_category_status_order (category, status, `order`),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Design Notes:

order column for manual sorting in admin panel (drag-and-drop)
Composite index (category, status, order) for query: "Get published FAQs in category X, sorted by order"
question and answer are translatable (via content_translations table)
</details><details> <summary><strong>📁 12. pages - Static content pages (About, Privacy Policy, Terms)</strong></summary>
SQL

CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    meta_title VARCHAR(255) DEFAULT NULL COMMENT 'SEO meta title (max 60 chars)',
    meta_description TEXT DEFAULT NULL COMMENT 'SEO meta description (max 160 chars)',
    status ENUM('draft', 'published') DEFAULT 'draft',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Seeded Pages:

about - About ElderCare SG
privacy-policy - Privacy Policy (PDPA compliance)
terms-of-service - Terms of Service
how-it-works - How the platform works
Design Notes:

slug unique for URL routing (e.g., /pages/about)
meta_title and meta_description for SEO optimization
content LONGTEXT for large content (up to 4GB)
All fields translatable via content_translations
</details><details> <summary><strong>📁 13. content_translations - Polymorphic translations (i18n critical)</strong></summary>
SQL

CREATE TABLE content_translations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    translatable_type VARCHAR(255) NOT NULL COMMENT 'Model name (App\\Models\\Center)',
    translatable_id BIGINT UNSIGNED NOT NULL COMMENT 'Record ID',
    locale CHAR(2) NOT NULL COMMENT 'en, zh, ms, ta',
    field VARCHAR(100) NOT NULL COMMENT 'Which field is translated (name, description)',
    value TEXT NOT NULL COMMENT 'Translated text',
    
    -- Translation Workflow
    translation_status ENUM('not_translated', 'in_progress', 'translated', 'reviewed') DEFAULT 'not_translated',
    translated_by BIGINT UNSIGNED DEFAULT NULL COMMENT 'Translator user ID',
    reviewed_by BIGINT UNSIGNED DEFAULT NULL COMMENT 'Reviewer user ID',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_content_translations_translated_by FOREIGN KEY (translated_by) 
        REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_content_translations_reviewed_by FOREIGN KEY (reviewed_by) 
        REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_translation (translatable_type, translatable_id, locale, field),
    INDEX idx_translatable (translatable_type, translatable_id),
    INDEX idx_locale (locale),
    INDEX idx_translation_status (translation_status),
    INDEX idx_translated_by (translated_by),
    INDEX idx_reviewed_by (reviewed_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Usage Examples:

SQL

-- Get Mandarin translation for Center ID 5's name field
SELECT value FROM content_translations 
WHERE translatable_type = 'App\\Models\\Center' 
  AND translatable_id = 5 
  AND locale = 'zh' 
  AND field = 'name';

-- Get all translations for Center ID 5
SELECT * FROM content_translations 
WHERE translatable_type = 'App\\Models\\Center' 
  AND translatable_id = 5;
Translatable Models & Fields:

Centers: name, description
Services: name, description, features (JSON values translated separately)
FAQs: question, answer
Pages: title, content, meta_title, meta_description
Testimonials: title, content (optional - user-submitted content)
Design Notes:

Polymorphic design allows translating any model
(translatable_type, translatable_id, locale, field) unique constraint prevents duplicate translations
translation_status enables workflow tracking (not_translated → in_progress → translated → reviewed)
translated_by and reviewed_by for quality control and accountability
</details><details> <summary><strong>📁 14. media - Polymorphic media attachments (images, videos, documents)</strong></summary>
SQL

CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mediable_type VARCHAR(255) NOT NULL COMMENT 'Model name (App\\Models\\Center)',
    mediable_id BIGINT UNSIGNED NOT NULL COMMENT 'Record ID',
    type ENUM('image', 'video', 'document') NOT NULL,
    
    -- File Information
    url VARCHAR(500) NOT NULL COMMENT 'S3 URL',
    thumbnail_url VARCHAR(500) DEFAULT NULL COMMENT 'For videos and large images',
    filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size BIGINT UNSIGNED NOT NULL COMMENT 'File size in bytes',
    duration INT UNSIGNED DEFAULT NULL COMMENT 'For videos: duration in seconds',
    
    -- Accessibility & Display
    caption VARCHAR(500) DEFAULT NULL,
    alt_text VARCHAR(255) DEFAULT NULL COMMENT 'Accessibility: image alt text',
    `order` INT UNSIGNED DEFAULT 0 COMMENT 'Display order in galleries',
    
    status ENUM('processing', 'ready', 'failed') DEFAULT 'processing',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_mediable (mediable_type, mediable_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_mediable_order (mediable_type, mediable_id, `order`),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Usage Examples:

SQL

-- Get all images for Center ID 5, ordered by display order
SELECT * FROM media 
WHERE mediable_type = 'App\\Models\\Center' 
  AND mediable_id = 5 
  AND type = 'image' 
  AND status = 'ready'
ORDER BY `order` ASC;
Media Attachments:

Centers: Photos (gallery), documents (licenses, certifications)
Services: Photos (activity photos)
Testimonials: Photos (optional user-submitted photos)
Pages: Images (inline content images)
Design Notes:

Polymorphic design allows attaching media to any model
status tracks processing pipeline: processing → ready/failed (for async image optimization)
alt_text required for accessibility (WCAG 2.1 AA compliance)
order for manual sorting in galleries
thumbnail_url for videos (poster frame) and large images (performance)
Composite index (mediable_type, mediable_id, order) optimizes gallery queries
</details><details> <summary><strong>📁 15. consents - User consent tracking (PDPA critical)</strong></summary>
SQL

CREATE TABLE consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    consent_type ENUM('account_creation', 'marketing_email', 'sms_notifications', 'analytics_cookies') NOT NULL,
    consent_given BOOLEAN NOT NULL,
    consent_text TEXT NOT NULL COMMENT 'Snapshot of privacy policy text user agreed to',
    consent_version VARCHAR(20) NOT NULL COMMENT 'Privacy policy version (e.g., "1.0")',
    
    -- PDPA Audit Trail
    ip_address VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6',
    user_agent TEXT NOT NULL COMMENT 'Browser user agent string',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_consents_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_consent_type (consent_type),
    INDEX idx_user_consent_type (user_id, consent_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Consent Types:

account_creation - Required consent to create account and process personal data
marketing_email - Optional consent to receive marketing emails
sms_notifications - Optional consent to receive SMS notifications
analytics_cookies - Optional consent for analytics cookies (Google Analytics, Hotjar)
Design Notes:

NO soft delete (consents are immutable audit records)
consent_text stores snapshot of what user agreed to (legal protection)
consent_version tracks privacy policy changes
ip_address and user_agent required for PDPA audit trail
Multiple rows per user (one per consent type) for granular control
Composite index (user_id, consent_type) for query: "Did user consent to marketing emails?"
Audit Trail Example:

SQL

-- Check if user consented to marketing emails
SELECT consent_given FROM consents 
WHERE user_id = 123 
  AND consent_type = 'marketing_email' 
ORDER BY created_at DESC 
LIMIT 1;
</details><details> <summary><strong>📁 16. audit_logs - Complete audit trail (PDPA critical)</strong></summary>
SQL

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'User who performed action (NULL for system actions)',
    auditable_type VARCHAR(255) NOT NULL COMMENT 'Model name (App\\Models\\User)',
    auditable_id BIGINT UNSIGNED NOT NULL COMMENT 'Record ID',
    action ENUM('created', 'updated', 'deleted', 'viewed', 'exported', 'restored') NOT NULL,
    
    -- Change Tracking
    old_values JSON DEFAULT NULL COMMENT 'State before change',
    new_values JSON DEFAULT NULL COMMENT 'State after change',
    
    -- Audit Trail
    ip_address VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6',
    user_agent TEXT NOT NULL COMMENT 'Browser user agent string',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_audit_logs_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_auditable (auditable_type, auditable_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_auditable_created (auditable_type, auditable_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Logged Actions:

created - Record created
updated - Record updated (stores old vs new values)
deleted - Record deleted (soft or hard)
viewed - Sensitive record viewed (e.g., user profile, booking details)
exported - Data exported (PDPA "right of access")
restored - Soft-deleted record restored
Design Notes:

NO updated_at or deleted_at (audit logs are immutable, append-only)
old_values and new_values JSON provide complete audit trail
user_id nullable for system actions (e.g., automated deletions)
Composite index (auditable_type, auditable_id, created_at) for query: "Get audit history for User ID 123"
7-year retention required by Singapore law for financial/medical records
JSON Structure Example:

JSON

// old_values and new_values
{
  "email": "old@example.com",  // old_values
  "email": "new@example.com"   // new_values
}
Usage Example:

SQL

-- Get audit history for User ID 123
SELECT * FROM audit_logs 
WHERE auditable_type = 'App\\Models\\User' 
  AND auditable_id = 123 
ORDER BY created_at DESC;
</details><details> <summary><strong>📁 17. subscriptions - Newsletter subscriptions (Mailchimp sync)</strong></summary>
SQL

CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    mailchimp_subscriber_id VARCHAR(255) DEFAULT NULL COMMENT 'Mailchimp subscriber hash',
    status ENUM('pending', 'subscribed', 'unsubscribed') DEFAULT 'pending',
    preferences JSON DEFAULT NULL COMMENT 'Email preferences (which types to receive)',
    
    subscribed_at TIMESTAMP NULL DEFAULT NULL,
    unsubscribed_at TIMESTAMP NULL DEFAULT NULL,
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_mailchimp_subscriber_id (mailchimp_subscriber_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Status Workflow:

pending - Submitted email, awaiting double opt-in confirmation
subscribed - Confirmed subscription
unsubscribed - User unsubscribed
JSON Structure Example:

JSON

// preferences
{
  "newsletter": true,
  "promotions": false,
  "events": true,
  "weekly_digest": true
}
Design Notes:

email unique (one subscription per email)
mailchimp_subscriber_id for sync with Mailchimp
preferences allows granular subscription preferences
Status workflow: pending → subscribed (via double opt-in link) → unsubscribed
</details><details> <summary><strong>📁 18. notification_preferences - User notification settings</strong></summary>
SQL

CREATE TABLE notification_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    email_enabled BOOLEAN DEFAULT TRUE,
    sms_enabled BOOLEAN DEFAULT TRUE,
    push_enabled BOOLEAN DEFAULT FALSE COMMENT 'Phase 2: mobile app push notifications',
    notification_types JSON NOT NULL DEFAULT ('{}') COMMENT 'Granular notification preferences',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_notification_preferences_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
JSON Structure Example:

JSON

// notification_types
{
  "booking_confirmation": {"email": true, "sms": true},
  "booking_reminder": {"email": true, "sms": true},
  "booking_cancellation": {"email": true, "sms": false},
  "testimonial_moderation": {"email": true, "sms": false},
  "newsletter": {"email": true}
}
Design Notes:

user_id unique (1:1 relationship with users)
Global toggles (email_enabled, sms_enabled) provide master on/off switch
notification_types JSON allows granular control per notification type
Created automatically when user registers (default: all enabled)
</details><details> <summary><strong>📁 19. integration_logs - External API call logs (debugging)</strong></summary>
SQL

CREATE TABLE integration_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    integration_name ENUM('calendly', 'mailchimp', 'twilio', 'cloudflare_stream') NOT NULL,
    action VARCHAR(100) NOT NULL COMMENT 'e.g., create_event, send_sms',
    request_payload JSON DEFAULT NULL,
    response_payload JSON DEFAULT NULL,
    status ENUM('success', 'failure') NOT NULL,
    error_message TEXT DEFAULT NULL,
    http_status_code INT UNSIGNED DEFAULT NULL,
    duration_ms INT UNSIGNED DEFAULT NULL COMMENT 'API call duration in milliseconds',
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_integration_name (integration_name),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_integration_status (integration_name, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Design Notes:

NO updated_at or deleted_at (logs are immutable, append-only)
request_payload and response_payload JSON for debugging failed API calls
duration_ms tracks API performance
Retention policy: 90 days (automated cleanup job)
Critical for debugging integration issues
</details><details> <summary><strong>📁 20. search_queries - Track user search behavior</strong></summary>
SQL

CREATE TABLE search_queries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    query VARCHAR(500) NOT NULL,
    results_count INT UNSIGNED DEFAULT 0,
    user_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'Nullable for guest searches',
    ip_address VARCHAR(45) NOT NULL,
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_search_queries_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_query (query),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Design Notes:

user_id nullable for guest searches
results_count tracks if search was successful (0 = no results)
Used for search analytics: "What are users searching for?"
Identifies content gaps (frequent searches with 0 results)
</details><details> <summary><strong>📁 21. user_events - Internal analytics events</strong></summary>
SQL

CREATE TABLE user_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'Nullable for guest events',
    event_name VARCHAR(100) NOT NULL,
    event_data JSON DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_user_events_user_id FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_event_name (event_name),
    INDEX idx_created_at (created_at),
    INDEX idx_event_name_created (event_name, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Tracked Events (supplement GA4):

booking_initiated - User started booking flow
booking_completed - Booking confirmed
service_viewed - Service detail page viewed
virtual_tour_started - Virtual tour video played (Phase 2)
virtual_tour_completed - Virtual tour video watched to end
language_switched - User changed language
testimonial_submitted - User submitted testimonial
JSON Structure Example:

JSON

// event_data for booking_initiated
{
  "center_id": 5,
  "service_id": 12,
  "center_name": "Sunshine Care",
  "service_name": "Daily Care Program"
}
Design Notes:

Supplements Google Analytics 4 with internal tracking
user_id nullable for guest events
Retention policy: 2 years (then anonymize)
</details><details> <summary><strong>📁 22-25. Laravel Default Tables (Password Resets, Sessions, etc.)</strong></summary>
SQL

-- 22. password_resets - Laravel password reset tokens
CREATE TABLE password_resets (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. sessions - Laravel session management
CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. failed_jobs - Laravel failed queue jobs
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_uuid (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. jobs - Laravel queue jobs
CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED DEFAULT NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    
    INDEX idx_queue (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
</details>
Phase 4: Physical Schema Design
4.1 Indexing Strategy
Performance-Critical Indexes (already defined in table schemas above):

Index	Table	Columns	Type	Purpose
idx_email	users	email	UNIQUE	Authentication lookups
idx_slug	centers	slug	UNIQUE	URL routing
idx_moh_license	centers	moh_license_number	UNIQUE	MOH compliance checks
idx_center_status	services	center_id, status	COMPOSITE	Get published services by center
idx_booking_date_status	bookings	booking_date, status	COMPOSITE	Upcoming bookings query
idx_calendly_event_id	bookings	calendly_event_id	INDEX	Webhook matching
idx_auditable	audit_logs	auditable_type, auditable_id	COMPOSITE	Audit history queries
idx_translation	content_translations	translatable_type, translatable_id, locale, field	UNIQUE COMPOSITE	Translation lookups
ft_name_description	centers	name, description	FULLTEXT	Search functionality
Total Indexes: 47 across all tables

4.2 Storage & Partitioning Strategy
Partitioning Candidates (for high-volume tables):

audit_logs - Partition by RANGE on created_at (monthly partitions)

Rationale: 7-year retention = millions of rows, frequent INSERTs, rare UPDATEs
Strategy: Partition by month, auto-create future partitions
Example: PARTITION p202401 VALUES LESS THAN (UNIX_TIMESTAMP('2024-02-01'))
user_events - Partition by RANGE on created_at (monthly partitions)

Rationale: High INSERT volume, time-series data
Strategy: Monthly partitions, 2-year retention
integration_logs - Partition by RANGE on created_at (monthly partitions)

Rationale: High volume, 90-day retention
Strategy: Monthly partitions, automated cleanup
Storage Engine: InnoDB for all tables (ACID compliance, foreign key support)

Character Set: utf8mb4_unicode_ci (full Unicode support for all 4 languages + emojis)

Phase 5: Schema Validation
5.1 Requirements Validation Checklist
Requirement	Tables Addressing	Status
User authentication & management	users, profiles, roles, permissions, role_user, permission_role	✅ Complete
Eldercare center information	centers (with MOH fields)	✅ Complete
Services/programs	services	✅ Complete
Booking system with Calendly	bookings (calendly_event_id, calendly_event_uri)	✅ Complete
Testimonials/reviews	testimonials (with moderation)	✅ Complete
FAQs	faqs	✅ Complete
Static pages	pages	✅ Complete
Multilingual support (EN, ZH, MS, TA)	content_translations (polymorphic)	✅ Complete
Media management (photos, videos)	media (polymorphic)	✅ Complete
PDPA consent management	consents (versioned, timestamped, IP tracked)	✅ Complete
PDPA audit trail	audit_logs (all changes tracked)	✅ Complete
PDPA data retention	deleted_at on tables, data_retention_tracking (optional)	✅ Complete
Newsletter subscriptions (Mailchimp)	subscriptions (with mailchimp_subscriber_id)	✅ Complete
SMS notifications (Twilio)	notification_preferences, bookings (reminder_sent_at)	✅ Complete
MOH license compliance	centers (moh_license_number unique, license_expiry_date)	✅ Complete
Singapore transport info	centers (transport_info JSON: MRT, bus)	✅ Complete
Search functionality	search_queries, FULLTEXT indexes, MeiliSearch sync	✅ Complete
Analytics tracking	user_events, search_queries	✅ Complete
Role-based access control	roles, permissions, pivot tables	✅ Complete
Validation Result: ✅ All requirements addressed

5.2 Data Integrity Validation
Foreign Key Constraints: 23 foreign key constraints defined

Cascade Behaviors:

✅ ON DELETE CASCADE: Appropriate for dependent data (profiles, services, media)
✅ ON DELETE RESTRICT: Prevents deletion of centers/services with bookings
✅ ON DELETE SET NULL: Preserves data for analytics (user_id in bookings)
Unique Constraints: 12 unique constraints (email, slug, moh_license_number, composite translation keys)

Check Constraints: 1 check constraint (testimonials.rating BETWEEN 1 AND 5)

Validation Result: ✅ Data integrity constraints comprehensive

5.3 Performance Validation
Query Patterns Tested:

Get published centers with services (JOIN)

Index: centers.status, services.center_id
✅ Composite index optimizes query
Get upcoming bookings for user (WHERE + ORDER BY)

Index: bookings.user_id, booking_date_status composite
✅ Optimized
Get Mandarin translation for center (Polymorphic lookup)

Index: unique_translation composite
✅ Optimized
Audit history for specific record (Polymorphic lookup + ORDER BY)

Index: auditable_created composite
✅ Optimized
Search centers by name (FULLTEXT)

Index: ft_name_description
✅ Optimized
Validation Result: ✅ All critical queries have appropriate indexes

5.4 Compliance Validation
PDPA Requirements:

✅ Consent capture (consents table with versioning, IP, user agent)
✅ Audit trail (audit_logs with old/new values, 7-year retention)
✅ Right of access (data export via API, user_events)
✅ Right to be forgotten (soft deletes with 30-day grace period)
✅ Data minimization (only necessary fields collected)
✅ Data retention policies (deleted_at, retention tracking)
MOH Requirements:

✅ License number (centers.moh_license_number UNIQUE)
✅ License expiry tracking (license_expiry_date)
✅ Accreditation status (accreditation_status enum)
✅ Staff credentials (staff_count, future: center_staff table)
✅ Medical facilities disclosure (medical_facilities JSON)
Validation Result: ✅ Full compliance with PDPA and MOH requirements

Phase 6: Migration File Creation Plan
6.1 Migration Dependency Order
Laravel migrations must be created in dependency order (foreign keys require parent tables to exist first):

mermaid

graph TD
    M1[2024_01_01_000001_create_users_table] --> M3[2024_01_01_000003_create_profiles_table]
    M1 --> M4[2024_01_01_000004_create_role_user_table]
    M1 --> M8[2024_01_01_000008_create_bookings_table]
    M1 --> M9[2024_01_01_000009_create_testimonials_table]
    M1 --> M12[2024_01_01_000012_create_consents_table]
    M1 --> M15[2024_01_01_000015_create_notification_preferences_table]
    M1 --> M13[2024_01_01_000013_create_audit_logs_table]
    M1 --> M14[2024_01_01_000014_create_content_translations_table]
    
    M2[2024_01_01_000002_create_roles_table] --> M4
    M2 --> M5[2024_01_01_000005_create_permissions_table]
    
    M5 --> M6[2024_01_01_000006_create_permission_role_table]
    
    M7[2024_01_01_000007_create_centers_table] --> M8
    M7 --> M9
    M7 --> M10[2024_01_01_000010_create_services_table]
    M7 --> M14
    M7 --> M16[2024_01_01_000016_create_media_table]
    
    M10 --> M8
    M10 --> M14
    M10 --> M16
    
    M11[2024_01_01_000011_create_faqs_table] --> M14
    M17[2024_01_01_000017_create_pages_table] --> M14
    
    style M1 fill:#ffcccc
    style M2 fill:#ccffcc
    style M7 fill:#ccccff
Migration Sequence (25 migrations):

2024_01_01_000001_create_users_table
2024_01_01_000002_create_roles_table
2024_01_01_000003_create_profiles_table (depends on users)
2024_01_01_000004_create_role_user_table (depends on users, roles)
2024_01_01_000005_create_permissions_table
2024_01_01_000006_create_permission_role_table (depends on permissions, roles)
2024_01_01_000007_create_centers_table
2024_01_01_000008_create_services_table (depends on centers)
2024_01_01_000009_create_bookings_table (depends on users, centers, services)
2024_01_01_000010_create_testimonials_table (depends on users, centers)
2024_01_01_000011_create_faqs_table
2024_01_01_000012_create_pages_table
2024_01_01_000013_create_content_translations_table (polymorphic - no direct FK)
2024_01_01_000014_create_media_table (polymorphic - no direct FK)
2024_01_01_000015_create_consents_table (depends on users)
2024_01_01_000016_create_audit_logs_table (depends on users)
2024_01_01_000017_create_subscriptions_table
2024_01_01_000018_create_notification_preferences_table (depends on users)
2024_01_01_000019_create_integration_logs_table
2024_01_01_000020_create_search_queries_table (depends on users)
2024_01_01_000021_create_user_events_table (depends on users)
2024_01_01_000022_create_password_resets_table
2024_01_01_000023_create_sessions_table
2024_01_01_000024_create_failed_jobs_table
2024_01_01_000025_create_jobs_table
6.2 Migration File Template
Each migration will follow this structure:

PHP

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Foreign keys
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            // Regular columns
            $table->string('name');
            $table->enum('status', ['draft', 'published'])->default('draft');
            
            // JSON columns
            $table->json('preferences')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
Phase 7: Seeder Development Plan
7.1 Seeder Dependency Order
RoleSeeder - Create roles (super_admin, content_manager, translator, moderator, user)
PermissionSeeder - Create permissions
PermissionRoleSeeder - Assign permissions to roles
UserSeeder - Create admin and test users
RoleUserSeeder - Assign roles to users
CenterSeeder - Create 5-10 sample centers
ServiceSeeder - Create 3-5 services per center
FAQSeeder - Create 20+ FAQs
PageSeeder - Create About, Privacy Policy, Terms pages
ContentTranslationSeeder - Create Mandarin translations
TestimonialSeeder - Create sample testimonials
BookingSeeder - Create test bookings
7.2 Factory Strategy
Model Factories (using Faker):

UserFactory - Generate realistic user data
CenterFactory - Generate centers with MOH licenses, transport info
ServiceFactory - Generate services with realistic prices
FAQFactory - Generate question/answer pairs
TestimonialFactory - Generate reviews with ratings
BookingFactory - Generate bookings with dates
Phase 8: Schema Testing Plan
8.1 Migration Testing Checklist
 Run php artisan migrate:fresh successfully
 Run php artisan migrate:rollback successfully (all down() methods work)
 No foreign key constraint violations
 All indexes created successfully
 Character set utf8mb4 on all tables
 Soft deletes work correctly
8.2 Relationship Testing Checklist
 User → Profile (1:1)
 User → Roles (M:N via role_user)
 Centers → Services (1:N)
 Users → Bookings (1:N)
 Centers → Media (Polymorphic 1:N)
 Centers → Content_Translations (Polymorphic 1:N)
 Bookings cascade rules (ON DELETE RESTRICT for center_id)
 User soft delete cascades to related data
8.3 Query Performance Testing
 EXPLAIN on all critical queries shows index usage
 Search queries use FULLTEXT index
 Translation queries use composite index
 Audit log queries under 100ms for 10,000 records
 Booking queries optimized for date ranges
Execution Plan Validation Summary
✅ Plan Validation Checklist
Criteria	Status	Notes
Complete Entity Coverage	✅	25 tables cover all requirements
PDPA Compliance	✅	consents, audit_logs, soft deletes implemented
MOH Compliance	✅	moh_license_number, license tracking
i18n Support	✅	content_translations polymorphic table
Integration Readiness	✅	calendly_event_id, mailchimp_subscriber_id, SMS tracking
Performance Optimized	✅	47 indexes, FULLTEXT search, composite indexes
Data Integrity	✅	23 foreign keys, unique constraints, check constraints
Scalability	✅	Partitioning strategy for high-volume tables
Migration Dependency Order	✅	25 migrations in correct sequence
Seeder Strategy	✅	12 seeders with factories
Testing Plan	✅	Comprehensive testing checklist
Final Execution Timeline
Phase	Duration	Deliverables
Phase 1: Requirements Analysis	1 day	Entity list, attribute analysis, relationship map
Phase 2: Conceptual Modeling	1 day	ERD diagram (Mermaid)
Phase 3: Logical Schema Design	2 days	Complete table definitions (25 tables) with SQL
Phase 4: Physical Schema Design	1 day	Indexing strategy, partitioning plan
Phase 5:
