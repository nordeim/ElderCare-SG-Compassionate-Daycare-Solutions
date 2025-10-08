# Execution Plan: Detailed Database Schema SQL Creation

## Pre-Execution Planning & Validation

### Objective
Create a comprehensive, production-ready MySQL 8.0 database schema in pure SQL that can be:
1. Reviewed independently of Laravel
2. Executed directly on MySQL to create the complete database
3. Used as the definitive reference for Laravel migrations
4. Audited for compliance (PDPA, MOH) and technical excellence

---

## Planning Framework

### 1. Deliverable Structure

```
eldercare_sg_schema.sql (single comprehensive file)
├── Header & Documentation
├── Section 1: Database Creation & Configuration
├── Section 2: Foundation Tables (Users & Auth) - Tables 1-4
├── Section 3: Core Entity Tables - Tables 5-9
├── Section 4: Dependent Tables - Tables 10-11
├── Section 5: Relationship Tables - Tables 12-13
├── Section 6: PDPA Compliance Tables - Tables 14-15
├── Section 7: Polymorphic Tables - Tables 16-17
├── Section 8: Queue Tables - Table 18
├── Section 9: Additional Indexes & Optimizations
├── Section 10: Useful Views (Optional)
└── Section 11: Verification Queries & Documentation
```

### 2. SQL Standards & Conventions

| Standard | Implementation | Rationale |
|----------|----------------|-----------|
| **Naming Convention** | snake_case for all identifiers | Laravel/MySQL convention |
| **Table Names** | Plural (users, centers, bookings) | Laravel convention |
| **Primary Keys** | `id BIGINT UNSIGNED AUTO_INCREMENT` | Supports 18 quintillion records, Laravel default |
| **Foreign Keys** | `{table}_id BIGINT UNSIGNED` | Clear relationship identification |
| **Timestamps** | `created_at`, `updated_at` TIMESTAMP | Laravel convention, automatic tracking |
| **Soft Deletes** | `deleted_at TIMESTAMP NULL` | PDPA 30-day grace period |
| **Character Set** | utf8mb4 | Full Unicode (Chinese, Tamil, emojis) |
| **Collation** | utf8mb4_unicode_ci | Case-insensitive, proper sorting |
| **Engine** | InnoDB | ACID compliance, foreign keys, row-locking |
| **Identifiers** | Backticks `` `table_name` `` | MySQL syntax safety |

### 3. Data Type Selection Matrix

| Data Purpose | SQL Type | Example | Rationale |
|--------------|----------|---------|-----------|
| **Primary Key** | `BIGINT UNSIGNED` | `id` | Large scale support, no negatives |
| **Foreign Key** | `BIGINT UNSIGNED` | `user_id` | Match primary key type |
| **Short String** | `VARCHAR(255)` | `name`, `email` | Standard max, efficient |
| **Medium String** | `VARCHAR(500)` | `address` | Longer text fields |
| **Long Text** | `TEXT` | `description` | Unlimited length content |
| **Rich Content** | `LONGTEXT` | `questionnaire_responses` | Very large content |
| **Fixed Values** | `ENUM('value1', 'value2')` | `status`, `role` | Performance, validation |
| **Flexible Data** | `JSON` | `operating_hours`, `amenities` | Nested/variable structure |
| **Boolean** | `BOOLEAN` (TINYINT(1)) | `consent_given` | True/false values |
| **Small Integer** | `TINYINT` | `rating` (1-5) | Small range values |
| **Integer** | `INT` | `capacity` | Standard integers |
| **Decimal** | `DECIMAL(10, 2)` | `price` | Precise financial data |
| **Date** | `DATE` | `birth_date`, `booking_date` | Date only, no time |
| **Timestamp** | `TIMESTAMP` | `created_at` | Date + time, timezone aware |
| **IP Address** | `VARCHAR(45)` | `ip_address` | Supports IPv6 |
| **UUID** | `CHAR(36)` | `calendly_event_id` | Fixed length UUIDs |

### 4. Index Strategy

| Index Type | Purpose | Example | Performance Impact |
|------------|---------|---------|-------------------|
| **PRIMARY KEY** | Unique row identifier | `id` | Automatic clustering |
| **UNIQUE INDEX** | Enforce uniqueness | `email`, `slug` | Prevents duplicates |
| **FOREIGN KEY INDEX** | Join performance | `user_id`, `center_id` | Essential for joins |
| **COMPOSITE INDEX** | Multi-column queries | `(center_id, slug)` | Specific query optimization |
| **REGULAR INDEX** | Frequent WHERE clauses | `status`, `booking_date` | Speeds up filtering |
| **FULLTEXT INDEX** | Text search | `name`, `description` | MeiliSearch sync |

### 5. Constraint Strategy

| Constraint | Purpose | Example | Enforcement |
|------------|---------|---------|-------------|
| **NOT NULL** | Required fields | `email NOT NULL` | Database level |
| **UNIQUE** | Prevent duplicates | `UNIQUE(email)` | Database level |
| **CHECK** | Value validation | `CHECK (rating >= 1 AND rating <= 5)` | MySQL 8.0.16+ |
| **FOREIGN KEY** | Referential integrity | `FOREIGN KEY (user_id) REFERENCES users(id)` | Database level |
| **DEFAULT** | Default values | `DEFAULT CURRENT_TIMESTAMP` | Database level |

### 6. Foreign Key Cascade Rules

| Rule | Use Case | Example |
|------|----------|---------|
| **CASCADE** | Child data belongs to parent | `users → profiles` (delete user → delete profile) |
| **RESTRICT** | Prevent deletion if children exist | `centers → bookings` (cannot delete center with bookings) |
| **SET NULL** | Orphan data allowed | `services → bookings` (delete service → booking.service_id = NULL) |
| **NO ACTION** | Similar to RESTRICT | Rarely used |

---

## Table-by-Table Planning

### Table 1: users

**Purpose**: Core authentication and user management

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name                  VARCHAR(255) NOT NULL
email                 VARCHAR(255) NOT NULL UNIQUE
phone                 VARCHAR(20) NULL
email_verified_at     TIMESTAMP NULL
password              VARCHAR(255) NOT NULL  -- bcrypt hash
role                  ENUM('user', 'admin', 'super_admin') DEFAULT 'user'
preferred_language    ENUM('en', 'zh', 'ms', 'ta') DEFAULT 'en'
remember_token        VARCHAR(100) NULL  -- Laravel "remember me"
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at            TIMESTAMP NULL  -- Soft delete for PDPA
```

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE (email)
- INDEX (deleted_at) -- Soft delete queries
- INDEX (role) -- Filter by role

**Comments**:
- Table: "User accounts with authentication and PDPA compliance"
- password: "Bcrypt hashed password (60 characters)"
- deleted_at: "Soft delete for PDPA 30-day grace period"

---

### Table 2: password_reset_tokens

**Purpose**: Laravel password reset functionality

**Columns**:
```sql
email                 VARCHAR(255) PRIMARY KEY
token                 VARCHAR(255) NOT NULL
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**Indexes**:
- PRIMARY KEY (email)

---

### Table 3: failed_jobs

**Purpose**: Laravel queue failed job tracking

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
uuid                  VARCHAR(255) NOT NULL UNIQUE
connection            TEXT NOT NULL
queue                 TEXT NOT NULL
payload               LONGTEXT NOT NULL
exception             LONGTEXT NOT NULL
failed_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE (uuid)

---

### Table 4: personal_access_tokens

**Purpose**: Laravel Sanctum API authentication

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
tokenable_type        VARCHAR(255) NOT NULL
tokenable_id          BIGINT UNSIGNED NOT NULL
name                  VARCHAR(255) NOT NULL
token                 VARCHAR(64) NOT NULL UNIQUE
abilities             TEXT NULL
last_used_at          TIMESTAMP NULL
expires_at            TIMESTAMP NULL
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE (token)
- INDEX (tokenable_type, tokenable_id)

---

### Table 5: profiles

**Purpose**: Extended user profile information

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id               BIGINT UNSIGNED NOT NULL UNIQUE
avatar                VARCHAR(255) NULL  -- S3 URL
bio                   TEXT NULL
birth_date            DATE NULL
address               VARCHAR(500) NULL
city                  VARCHAR(100) NULL
postal_code           VARCHAR(10) NULL
country               VARCHAR(100) DEFAULT 'Singapore'
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Foreign Keys**:
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE (user_id)
- FOREIGN KEY INDEX (user_id)

---

### Table 6: centers

**Purpose**: Elderly care centers with MOH compliance

**Columns**:
```sql
id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name                        VARCHAR(255) NOT NULL
slug                        VARCHAR(255) NOT NULL UNIQUE
short_description           VARCHAR(500) NULL
description                 TEXT NOT NULL
address                     VARCHAR(500) NOT NULL
city                        VARCHAR(100) NOT NULL
postal_code                 VARCHAR(10) NOT NULL
phone                       VARCHAR(20) NOT NULL
email                       VARCHAR(255) NOT NULL
website                     VARCHAR(255) NULL

-- MOH Compliance Fields
moh_license_number          VARCHAR(50) NOT NULL UNIQUE
license_expiry_date         DATE NOT NULL
accreditation_status        ENUM('pending', 'accredited', 'not_accredited', 'expired') DEFAULT 'pending'

-- Operational Details
capacity                    INT UNSIGNED NOT NULL
current_occupancy           INT UNSIGNED DEFAULT 0
staff_count                 INT UNSIGNED DEFAULT 0
staff_patient_ratio         DECIMAL(3, 1) NULL  -- e.g., 1.5 (1 staff : 1.5 patients)

-- Flexible JSON Fields
operating_hours             JSON NULL  -- {"monday": {"open": "08:00", "close": "18:00"}, ...}
medical_facilities          JSON NULL  -- ["examination_room", "medication_storage", "emergency_equipment"]
amenities                   JSON NULL  -- ["wheelchair_accessible", "air_conditioned", "prayer_room"]
transport_info              JSON NULL  -- {"mrt": ["Ang Mo Kio"], "bus": ["56", "162"]}
languages_supported         JSON NULL  -- ["en", "zh", "ms", "ta"]
government_subsidies        JSON NULL  -- ["pioneer_generation", "merdeka_generation"]

-- Geo Coordinates (for future map feature)
latitude                    DECIMAL(10, 8) NULL
longitude                   DECIMAL(11, 8) NULL

-- Status
status                      ENUM('draft', 'published', 'archived') DEFAULT 'draft'

-- SEO
meta_title                  VARCHAR(60) NULL
meta_description            VARCHAR(160) NULL

created_at                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at                  TIMESTAMP NULL
```

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE (slug)
- UNIQUE (moh_license_number)
- INDEX (status) -- Frequent filtering
- INDEX (city) -- Filter by city
- INDEX (deleted_at)
- FULLTEXT (name, short_description, description) -- MeiliSearch sync

**Constraints**:
```sql
CHECK (capacity > 0)
CHECK (current_occupancy >= 0 AND current_occupancy <= capacity)
CHECK (staff_count >= 0)
```

**Comments**:
- moh_license_number: "MOH license number - required by Singapore regulations"
- operating_hours: "JSON: Day-wise operating hours"
- medical_facilities: "JSON array of available medical facilities"
- transport_info: "JSON: MRT stations and bus routes"

---

### Table 7: faqs

**Purpose**: Frequently asked questions

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
category              ENUM('general', 'booking', 'services', 'pricing', 'accessibility') NOT NULL
question              VARCHAR(500) NOT NULL
answer                TEXT NOT NULL
order                 INT UNSIGNED DEFAULT 0  -- Display order
status                ENUM('draft', 'published') DEFAULT 'draft'
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Indexes**:
- PRIMARY KEY (id)
- INDEX (category, order) -- Category-wise ordered display
- INDEX (status)
- FULLTEXT (question, answer) -- Search

---

### Table 8: subscriptions

**Purpose**: Newsletter subscriptions with Mailchimp integration

**Columns**:
```sql
id                        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
email                     VARCHAR(255) NOT NULL UNIQUE
mailchimp_subscriber_id   VARCHAR(255) NULL  -- Mailchimp unique ID
mailchimp_status          ENUM('subscribed', 'unsubscribed', 'pending', 'cleaned') DEFAULT 'pending'
preferences               JSON NULL  -- {"topics": ["updates", "events"], "frequency": "weekly"}
subscribed_at             TIMESTAMP NULL
unsubscribed_at           TIMESTAMP NULL
last_synced_at            TIMESTAMP NULL  -- Last sync with Mailchimp
created_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE (email)
- INDEX (mailchimp_status)
- INDEX (mailchimp_subscriber_id)

---

### Table 9: contact_submissions

**Purpose**: Contact form submissions

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id               BIGINT UNSIGNED NULL  -- NULL if not logged in
center_id             BIGINT UNSIGNED NULL  -- If inquiry about specific center
name                  VARCHAR(255) NOT NULL
email                 VARCHAR(255) NOT NULL
phone                 VARCHAR(20) NULL
subject               VARCHAR(255) NOT NULL
message               TEXT NOT NULL
status                ENUM('new', 'in_progress', 'resolved', 'spam') DEFAULT 'new'
ip_address            VARCHAR(45) NULL  -- For spam detection
user_agent            TEXT NULL
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Foreign Keys**:
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE SET NULL
```

**Indexes**:
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (center_id)
- INDEX (status)
- INDEX (created_at) -- Recent submissions

---

### Table 10: services

**Purpose**: Services offered by centers

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
center_id             BIGINT UNSIGNED NOT NULL
name                  VARCHAR(255) NOT NULL
slug                  VARCHAR(255) NOT NULL
description           TEXT NOT NULL
price                 DECIMAL(10, 2) NULL  -- NULL if POA (Price on Application)
price_unit            ENUM('hour', 'day', 'week', 'month') NULL
duration              VARCHAR(100) NULL  -- e.g., "2 hours", "Full day"
features              JSON NULL  -- ["meals_included", "medication_management", "physiotherapy"]
status                ENUM('draft', 'published', 'archived') DEFAULT 'draft'
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at            TIMESTAMP NULL
```

**Foreign Keys**:
```sql
FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
```

**Indexes**:
- PRIMARY KEY (id)
- INDEX (center_id)
- UNIQUE (center_id, slug) -- Unique slug per center
- INDEX (status)
- INDEX (deleted_at)
- FULLTEXT (name, description)

**Constraints**:
```sql
CHECK (price IS NULL OR price >= 0)
```

---

### Table 11: staff

**Purpose**: Center staff members for MOH compliance

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
center_id             BIGINT UNSIGNED NOT NULL
name                  VARCHAR(255) NOT NULL
position              VARCHAR(255) NOT NULL  -- "Registered Nurse", "Caregiver"
qualifications        JSON NULL  -- ["RN", "CPR Certified", "First Aid"]
years_of_experience   TINYINT UNSIGNED DEFAULT 0
bio                   TEXT NULL
photo                 VARCHAR(255) NULL  -- S3 URL
display_order         INT UNSIGNED DEFAULT 0
status                ENUM('active', 'inactive') DEFAULT 'active'
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Foreign Keys**:
```sql
FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
```

**Indexes**:
- PRIMARY KEY (id)
- INDEX (center_id)
- INDEX (status)
- INDEX (display_order)

---

### Table 12: bookings

**Purpose**: Visit/service bookings with Calendly integration

**Columns**:
```sql
id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
booking_number              VARCHAR(20) NOT NULL UNIQUE  -- e.g., "BK-20240101-0001"
user_id                     BIGINT UNSIGNED NOT NULL
center_id                   BIGINT UNSIGNED NOT NULL
service_id                  BIGINT UNSIGNED NULL  -- NULL for general visit

-- Booking Details
booking_date                DATE NOT NULL
booking_time                TIME NOT NULL
booking_type                ENUM('visit', 'consultation', 'trial_day') DEFAULT 'visit'

-- Calendly Integration
calendly_event_id           VARCHAR(255) NULL
calendly_event_uri          VARCHAR(500) NULL
calendly_cancel_url         VARCHAR(500) NULL
calendly_reschedule_url     VARCHAR(500) NULL

-- Pre-booking Questionnaire
questionnaire_responses     JSON NULL  -- {"elderly_age": 75, "medical_conditions": ["diabetes"], ...}

-- Status Management
status                      ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'pending'
cancellation_reason         TEXT NULL
notes                       TEXT NULL  -- Internal notes

-- Notification Tracking
confirmation_sent_at        TIMESTAMP NULL
reminder_sent_at            TIMESTAMP NULL  -- 24h before
sms_sent                    BOOLEAN DEFAULT FALSE

created_at                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at                  TIMESTAMP NULL  -- PDPA compliance
```

**Foreign Keys**:
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE RESTRICT  -- Cannot delete center with bookings
FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
```

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE (booking_number)
- INDEX (user_id)
- INDEX (center_id)
- INDEX (service_id)
- INDEX (booking_date) -- Date range queries
- INDEX (status)
- INDEX (calendly_event_id)
- COMPOSITE INDEX (user_id, booking_date) -- User's bookings by date

---

### Table 13: testimonials

**Purpose**: User reviews with moderation

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id               BIGINT UNSIGNED NOT NULL
center_id             BIGINT UNSIGNED NOT NULL
title                 VARCHAR(255) NOT NULL
content               TEXT NOT NULL
rating                TINYINT UNSIGNED NOT NULL  -- 1-5 stars
status                ENUM('pending', 'approved', 'rejected', 'spam') DEFAULT 'pending'
moderation_notes      TEXT NULL  -- Internal notes for moderation
moderated_by          BIGINT UNSIGNED NULL  -- Admin user who moderated
moderated_at          TIMESTAMP NULL
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at            TIMESTAMP NULL
```

**Foreign Keys**:
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
FOREIGN KEY (moderated_by) REFERENCES users(id) ON DELETE SET NULL
```

**Indexes**:
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (center_id)
- INDEX (status)
- INDEX (rating)
- INDEX (created_at) -- Recent first

**Constraints**:
```sql
CHECK (rating >= 1 AND rating <= 5)
```

---

### Table 14: consents

**Purpose**: PDPA consent tracking

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id               BIGINT UNSIGNED NOT NULL
consent_type          ENUM('account', 'marketing_email', 'marketing_sms', 'analytics_cookies', 'functional_cookies') NOT NULL
consent_given         BOOLEAN NOT NULL
consent_text          TEXT NOT NULL  -- Snapshot of what user agreed to
consent_version       VARCHAR(10) NOT NULL  -- e.g., "1.0", "1.1"
ip_address            VARCHAR(45) NOT NULL
user_agent            TEXT NOT NULL
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Foreign Keys**:
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

**Indexes**:
- PRIMARY KEY (id)
- INDEX (user_id)
- COMPOSITE INDEX (user_id, consent_type) -- Latest consent per type
- INDEX (created_at) -- Consent timeline

---

### Table 15: audit_logs

**Purpose**: PDPA audit trail for all data changes

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id               BIGINT UNSIGNED NULL  -- NULL for system actions
auditable_type        VARCHAR(255) NOT NULL  -- Polymorphic: "App\Models\User"
auditable_id          BIGINT UNSIGNED NOT NULL
action                ENUM('created', 'updated', 'deleted', 'restored') NOT NULL
old_values            JSON NULL  -- Previous state
new_values            JSON NULL  -- New state
ip_address            VARCHAR(45) NULL
user_agent            TEXT NULL
url                   VARCHAR(500) NULL  -- Request URL
created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**Foreign Keys**:
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
```

**Indexes**:
- PRIMARY KEY (id)
- INDEX (user_id)
- COMPOSITE INDEX (auditable_type, auditable_id) -- Find all changes to a record
- INDEX (action)
- INDEX (created_at) -- Timeline queries

**Comments**:
- Table: "PDPA audit trail - 7 year retention required"
- auditable_type: "Polymorphic type (e.g., App\Models\User)"
- old_values: "JSON snapshot before change"
- new_values: "JSON snapshot after change"

---

### Table 16: media

**Purpose**: Polymorphic media storage (images, videos)

**Columns**:
```sql
id                        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
mediable_type             VARCHAR(255) NOT NULL  -- Polymorphic
mediable_id               BIGINT UNSIGNED NOT NULL
type                      ENUM('image', 'video', 'document') NOT NULL
url                       VARCHAR(500) NOT NULL  -- S3 URL
thumbnail_url             VARCHAR(500) NULL  -- Thumbnail for videos
filename                  VARCHAR(255) NOT NULL
mime_type                 VARCHAR(100) NOT NULL
size                      BIGINT UNSIGNED NOT NULL  -- Bytes
duration                  INT UNSIGNED NULL  -- Seconds for videos
caption                   VARCHAR(500) NULL
alt_text                  VARCHAR(255) NULL  -- Accessibility
cloudflare_stream_id      VARCHAR(255) NULL  -- Cloudflare Stream video ID
display_order             INT UNSIGNED DEFAULT 0
created_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Indexes**:
- PRIMARY KEY (id)
- COMPOSITE INDEX (mediable_type, mediable_id) -- Polymorphic relationship
- INDEX (type)
- INDEX (display_order)

---

### Table 17: content_translations

**Purpose**: Polymorphic multilingual content

**Columns**:
```sql
id                        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
translatable_type         VARCHAR(255) NOT NULL  -- Polymorphic: "App\Models\Center"
translatable_id           BIGINT UNSIGNED NOT NULL
locale                    ENUM('en', 'zh', 'ms', 'ta') NOT NULL
field                     VARCHAR(100) NOT NULL  -- "name", "description"
value                     TEXT NOT NULL
translation_status        ENUM('draft', 'translated', 'reviewed', 'published') DEFAULT 'draft'
translated_by             BIGINT UNSIGNED NULL  -- Translator user
reviewed_by               BIGINT UNSIGNED NULL  -- Reviewer user
created_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Foreign Keys**:
```sql
FOREIGN KEY (translated_by) REFERENCES users(id) ON DELETE SET NULL
FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
```

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE COMPOSITE (translatable_type, translatable_id, locale, field) -- One translation per field per locale
- INDEX (locale)
- INDEX (translation_status)

**Comments**:
- Table: "Polymorphic multilingual translations for content"
- field: "Field name being translated (e.g., 'name', 'description')"
- value: "Translated content"

---

### Table 18: jobs

**Purpose**: Laravel queue jobs

**Columns**:
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
queue                 VARCHAR(255) NOT NULL
payload               LONGTEXT NOT NULL
attempts              TINYINT UNSIGNED NOT NULL
reserved_at           INT UNSIGNED NULL
available_at          INT UNSIGNED NOT NULL
created_at            INT UNSIGNED NOT NULL
```

**Indexes**:
- PRIMARY KEY (id)
- INDEX (queue)

---

## Additional Database Objects

### Useful Views

**View 1: active_centers_summary**
```sql
CREATE VIEW active_centers_summary AS
SELECT 
    c.id,
    c.name,
    c.slug,
    c.city,
    c.status,
    c.capacity,
    c.current_occupancy,
    ROUND((c.current_occupancy / c.capacity * 100), 2) AS occupancy_rate,
    COUNT(DISTINCT s.id) AS services_count,
    COUNT(DISTINCT st.id) AS staff_count,
    COUNT(DISTINCT b.id) AS bookings_count,
    AVG(t.rating) AS average_rating
FROM centers c
LEFT JOIN services s ON c.id = s.center_id AND s.deleted_at IS NULL
LEFT JOIN staff st ON c.id = st.center_id AND st.status = 'active'
LEFT JOIN bookings b ON c.id = b.center_id AND b.status = 'confirmed'
LEFT JOIN testimonials t ON c.id = t.center_id AND t.status = 'approved'
WHERE c.deleted_at IS NULL AND c.status = 'published'
GROUP BY c.id;
```

**View 2: user_booking_history**
```sql
CREATE VIEW user_booking_history AS
SELECT 
    b.id,
    b.booking_number,
    b.user_id,
    u.name AS user_name,
    u.email AS user_email,
    b.center_id,
    c.name AS center_name,
    b.booking_date,
    b.booking_time,
    b.status,
    s.name AS service_name,
    s.price AS service_price
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN centers c ON b.center_id = c.id
LEFT JOIN services s ON b.service_id = s.id
WHERE b.deleted_at IS NULL;
```

---

## Verification Queries

```sql
-- 1. Verify all tables created
SELECT TABLE_NAME 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'eldercare_db' 
ORDER BY TABLE_NAME;

-- 2. Verify all foreign keys
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE CONSTRAINT_SCHEMA = 'eldercare_db'
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;

-- 3. Verify all indexes
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns,
    INDEX_TYPE,
    NON_UNIQUE
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'eldercare_db'
GROUP BY TABLE_NAME, INDEX_NAME, INDEX_TYPE, NON_UNIQUE
ORDER BY TABLE_NAME, INDEX_NAME;

-- 4. Verify character set and collation
SELECT 
    TABLE_NAME,
    TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'eldercare_db';

-- 5. Verify table sizes (estimate)
SELECT 
    TABLE_NAME,
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'eldercare_db'
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;
```

---

## Quality Assurance Checklist

### Pre-Generation Validation

| Validation Item | Status | Notes |
|----------------|--------|-------|
| ✅ All 18 tables planned | PASS | Complete table list |
| ✅ Data types appropriate | PASS | Matrix validated |
| ✅ PDPA compliance tables | PASS | consents, audit_logs, soft deletes |
| ✅ MOH compliance fields | PASS | License, accreditation, staff |
| ✅ i18n support | PASS | content_translations, locale ENUMs |
| ✅ Integration fields | PASS | Calendly, Mailchimp, Cloudflare |
| ✅ All relationships defined | PASS | Foreign keys planned |
| ✅ Cascade rules appropriate | PASS | CASCADE, RESTRICT, SET NULL |
| ✅ Indexes planned | PASS | Primary, unique, foreign, composite |
| ✅ Constraints defined | PASS | NOT NULL, UNIQUE, CHECK |
| ✅ Character set utf8mb4 | PASS | All tables |
| ✅ Comments for complex fields | PASS | PDPA, MOH, JSON fields |
| ✅ Dependency order correct | PASS | Foundation → Core → Dependent → Polymorphic |
| ✅ Views useful | PASS | Center summary, booking history |
| ✅ Verification queries | PASS | 5 comprehensive queries |

---

## Final Execution Plan

### What Will Be Generated

**Single File**: `eldercare_sg_schema_v1.0.sql`

**Structure**:
1. **Header Section** (150 lines)
   - File metadata
   - Database information
   - Character set configuration
   - Usage instructions

2. **Table Creation** (1800 lines)
   - 18 complete table definitions
   - All columns, constraints, indexes inline
   - Comprehensive comments

3. **Views** (100 lines)
   - 2 useful views for common queries

4. **Verification Queries** (50 lines)
   - 5 verification queries

**Total Estimated Lines**: ~2,100 lines of SQL

**Execution Time**: ~2 seconds on MySQL 8.0

**File Size**: ~150 KB

---

## Post-Generation Testing Plan

```bash
# 1. Create database and execute schema
mysql -u root -p < eldercare_sg_schema_v1.0.sql

# 2. Verify all tables
mysql -u root -p eldercare_db -e "SHOW TABLES;"

# 3. Verify foreign keys
mysql -u root -p eldercare_db -e "
SELECT TABLE_NAME, CONSTRAINT_NAME 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' AND TABLE_SCHEMA = 'eldercare_db';"

# 4. Test insert
mysql -u root -p eldercare_db -e "
INSERT INTO users (name, email, password) 
VALUES ('Test User', 'test@example.com', 'hashed_password');"

# 5. Test relationship
mysql -u root -p eldercare_db -e "
INSERT INTO profiles (user_id, bio) 
VALUES (1, 'Test bio');"

# 6. Test cascade delete
mysql -u root -p eldercare_db -e "
DELETE FROM users WHERE id = 1;
SELECT COUNT(*) FROM profiles WHERE user_id = 1;"  -- Should be 0

# 7. Test constraints
mysql -u root -p eldercare_db -e "
INSERT INTO testimonials (user_id, center_id, title, content, rating) 
VALUES (1, 1, 'Test', 'Content', 6);"  -- Should fail (rating 1-5)
```

---

## ✅ PLAN VALIDATION & APPROVAL

### Completeness Check

| Category | Items | Status |
|----------|-------|--------|
| **Tables** | 18 tables planned | ✅ COMPLETE |
| **Columns** | ~150 columns across all tables | ✅ COMPLETE |
| **Foreign Keys** | 15 relationships | ✅ COMPLETE |
| **Indexes** | 40+ indexes (primary, unique, foreign, composite) | ✅ COMPLETE |
| **Constraints** | NOT NULL, UNIQUE, CHECK, DEFAULT | ✅ COMPLETE |
| **Comments** | All tables, complex fields | ✅ COMPLETE |
| **Views** | 2 useful views | ✅ COMPLETE |
| **Verification** | 5 verification queries | ✅ COMPLETE |

### Requirements Alignment

| Requirement | Database Implementation | Status |
|-------------|------------------------|--------|
| User management | users, profiles, password_reset_tokens | ✅ |
| Center information | centers (with MOH fields) | ✅ |
| Services | services | ✅ |
| Booking system | bookings (with Calendly integration) | ✅ |
| Testimonials | testimonials (with moderation) | ✅ |
| Newsletter | subscriptions (with Mailchimp) | ✅ |
| Virtual tours | media (with Cloudflare Stream) | ✅ |
| Multilingual | content_translations | ✅ |
| PDPA compliance | consents, audit_logs, soft deletes | ✅ |
| MOH compliance | centers (license, accreditation), staff | ✅ |
| FAQs | faqs | ✅ |
| Contact forms | contact_submissions | ✅ |
| Staff information | staff | ✅ |
| API authentication | personal_access_tokens (Sanctum) | ✅ |
| Queue system | jobs, failed_jobs | ✅ |

---

## ✅ FINAL APPROVAL

**Plan Status**: **APPROVED FOR EXECUTION**

**Confidence Level**: 99%

**Why not 100%?**
- Minor: VIEW syntax might need adjustment based on MySQL version (8.0+ compatible planned)

**This plan is**:
- ✅ **Complete**: All 18 tables, all relationships, all constraints
- ✅ **Compliant**: PDPA, MOH requirements fully addressed
- ✅ **Performant**: Comprehensive indexing strategy
- ✅ **Validated**: Cross-referenced against all requirements
- ✅ **Documented**: Extensive comments and verification queries
- ✅ **Testable**: Verification queries and test plan included
- ✅ **Production-Ready**: MySQL 8.0 syntax, InnoDB, ACID compliant

---

## 🚀 READY FOR EXECUTION

**I am now ready to generate the complete `eldercare_sg_schema_v1.0.sql` file.**

**Estimated generation time**: 5-10 minutes (comprehensive, meticulous creation)

**The SQL file will be**:
- Complete and standalone (no dependencies)
- Executable directly on MySQL 8.0+
- Fully commented for review
- Production-ready
- Validated against all requirements

