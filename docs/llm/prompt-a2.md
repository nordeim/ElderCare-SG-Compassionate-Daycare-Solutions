awesome understanding! now please meticulously review the project master plan for codebase completion and the sub-plan for phase 3 (to give on the next prompt, wait for it) to have a deeper understanding of what you need to do for phase 3.

# codebase_completion_master_plan.md
```md
## EXECUTION PLAN 

### **Phase 1: Foundation, Infrastructure & Analytics (5-6 days)**

**Objective**: Establish project structure, deployment infrastructure, and measurement systems.

**Dependencies**: None

**File Checklist**:
- [x] Root configuration (package.json, composer.json, docker-compose.yml)
- [x] Environment configuration (.env.example, staging .env, production .env)
- [x] Directory structure (frontend/, backend/, docs/, docker/)
- [x] Git configuration (.gitignore, .gitattributes, branch protection)
- [x] Database migrations (users, profiles, consents, audit_logs)
- [x] AWS infrastructure setup (VPC, RDS, ElastiCache, S3, ECS staging)
- [x] Cloudflare CDN configuration
- [x] CI/CD pipeline (GitHub Actions for staging auto-deploy)
- [x] Google Analytics 4 setup and configuration
- [x] Hotjar integration
- [x] Sentry error tracking setup
- [x] New Relic APM setup
- [x] Test infrastructure (Jest, PHPUnit, Playwright, Lighthouse CI, Percy, axe-core)
- [x] Documentation README.md, CONTRIBUTING.md

**Day-by-Day**:
- **Day 1**: Project structure, Docker setup, Git configuration
- **Day 2**: AWS infrastructure provisioning (staging environment)
- **Day 3**: Database migrations (users, PDPA tables), environment config
- **Day 4**: CI/CD pipeline, analytics (GA4, Hotjar), monitoring (Sentry, New Relic)
- **Day 5**: Test infrastructure setup, documentation
- **Day 6**: Buffer/testing

**Acceptance Criteria**:
- ✅ Development environment runs locally via Docker
- ✅ Staging environment accessible via URL
- ✅ CI/CD deploys to staging on merge to main
- ✅ GA4 tracks page views correctly
- ✅ Sentry captures errors
- ✅ All test runners work (Jest, PHPUnit, Playwright)
- ✅ Database migrations run successfully

---

### **Phase 2: Design System, UI Components & i18n (8-9 days)**

**Objective**: Create reusable components, design tokens, and internationalization foundation.

**Dependencies**: Phase 1

**File Checklist**:
- [x] Tailwind configuration with design tokens
- [x] next-intl setup and configuration
- [x] Locale routing (/en, /zh)
- [x] Translation files (common.json, navigation.json, forms.json, errors.json) × 2 languages
- [x] Language switcher component
- [x] Base components (Button, Card, Input, Label, Icon, Modal, Dialog)
- [x] Form components (FormField, Select, Checkbox, Radio, Textarea)
- [x] Layout components (Header, Footer, Navigation, PageLayout)
- [x] Storybook setup
- [x] Component tests (Jest + Testing Library)
- [x] Accessibility tests (axe-core for each component)
- [x] Visual regression tests (Percy snapshots)

**Day-by-Day**:
- **Day 1**: Tailwind config, design tokens, next-intl setup
- **Day 2**: Locale routing, translation files (English + Mandarin), language switcher
- **Day 3**: Base components (Button, Card, Input, Icon)
- **Day 4**: Form components (FormField, Select, Checkbox, etc.)
- **Day 5**: Layout components (Header, Footer, Navigation)
- **Day 6**: Storybook setup, component documentation
- **Day 7**: Component unit tests
- **Day 8**: Accessibility tests (axe-core), visual regression (Percy)
- **Day 9**: Buffer/polish

**Acceptance Criteria**:
- ✅ All components render in English and Mandarin
- ✅ Language switcher works smoothly
- ✅ Components are responsive (mobile, tablet, desktop)
- ✅ All components pass axe-core accessibility tests
- ✅ Storybook displays all components with variants
- ✅ 100% component test coverage
- ✅ Percy baselines captured

- **Status Update (2025-10-09 20:33 SGT):**
  - Molecule-focused Jest suite (`src/components/ui/__tests__/molecules.test.tsx`) verifies `Card`, `CheckboxField`, and `RadioField` behavior with `jest-axe` assertions to ensure zero accessibility violations.
  - CI now runs `npm run storybook:test`, installing Playwright Chromium to execute the Storybook interaction suite via `@storybook/test-runner`.
  - Percy Storybook snapshots are configured (`.percy.yml`, `npm run percy:storybook`) and gated in GitHub Actions behind the `PERCY_TOKEN` secret for visual regression coverage.

---

### **Phase 3: Core Backend Services & PDPA Compliance (10-11 days)**

**Objective**: Implement authentication, user management, PDPA compliance, and API foundation.

**Dependencies**: Phase 1

**File Checklist**:
- [x] User model enhancements (role, preferences)
- [x] Profile model
- [x] Consent model
- [x] AuditLog model
- [x] Authentication controllers (register, login, logout, verify email)
- [x] Password reset functionality
- [x] Laravel Sanctum configuration
- [x] Role-based middleware (admin, user)
- [x] Consent management service
- [x] Data export service (JSON export of all user data)
- [x] Account deletion service (soft delete, hard delete job)
- [x] Audit logging service (tracks all data changes)
- [x] Cookie consent backend
- [x] API routes structure (/api/v1/)
- [x] API response formatting (consistent JSON structure)
- [x] API error handling (global exception handler)
- [x] Rate limiting middleware
- [x] OpenAPI/Swagger documentation
- [x] Backend unit tests (PHPUnit)
- [x] API integration tests

**Day-by-Day**:
- **Day 1**: User/Profile models, migrations, factories
- **Day 2**: Authentication controllers (register, login, logout)
- **Day 3**: Password reset, email verification, Sanctum config
- **Day 4**: Role-based permissions, middleware
- **Day 5**: Consent management (capture, versioning, storage)
- **Day 6**: Data export endpoint, account deletion workflow
- **Day 7**: Audit logging service, IP tracking middleware
- **Day 8**: API structure, response formatting, error handling
- **Day 9**: Rate limiting, OpenAPI documentation
- **Day 10**: Backend tests (unit + integration)
- **Day 11**: Buffer/polish

**Acceptance Criteria**:
- ✅ Users can register with explicit consent
- ✅ Users can login/logout securely
- ✅ Password reset via email works
- ✅ Email verification required for account activation
- ✅ PDPA: Users can download their data (JSON)
- ✅ PDPA: Users can delete their account (30-day grace period)
- ✅ All data changes logged in audit_logs
- ✅ API documentation complete and accurate
- ✅ >90% backend test coverage
- ✅ Rate limiting prevents abuse (60 req/min)

---

### **Phase 4: Frontend Pages, State Management & PDPA UI (6-7 days)**

**Objective**: Implement page structure, routing, navigation, and user-facing PDPA features.

**Dependencies**: Phases 1, 2, 3

**File Checklist**:
- [x] Page components (Home, About, Services, Contact, Privacy, Terms)
- [x] User dashboard page
- [x] Login/Register pages
- [x] Password reset page
- [x] "My Data & Privacy" page
- [x] Next.js App Router configuration
- [x] Zustand store setup (user session, language preference, UI state)
- [x] React Query configuration (API client, caching)
- [x] API client service (Axios with interceptors)
- [x] Consent UI components (checkboxes, privacy policy links)
- [x] Cookie consent banner
- [x] Data download button (triggers API call)
- [x] Account deletion modal (with confirmation)
- [x] Contact form component
- [x] Newsletter signup form
- [x] SEO metadata (next/head for each page)
- [x] Page-level tests (integration + E2E)

**Day-by-Day**:
- **Day 1**: Page structure, routing, navigation implementation
- **Day 2**: Zustand setup, React Query configuration, API client
- **Day 3**: Login/Register pages, authentication flow
- **Day 4**: Home, About, Services pages with content
- **Day 5**: Contact page, contact form, newsletter signup
- **Day 6**: "My Data & Privacy" page, cookie consent banner, PDPA UI
- **Day 7**: E2E tests (critical paths), SEO metadata, buffer

**Acceptance Criteria**:
- ✅ All pages render correctly in English and Mandarin
- ✅ Navigation works smoothly
- ✅ Authentication flow complete (login → dashboard → logout)
- ✅ Contact form submits successfully
- ✅ Newsletter signup integrates with backend
- ✅ Cookie consent banner blocks analytics until consent
- ✅ Users can download their data from dashboard
- ✅ Account deletion requires confirmation
- ✅ Pages are responsive and accessible
- ✅ E2E tests cover login, registration, contact form
- ✅ Lighthouse performance >90, accessibility >90

---

### **Phase 5: Content Management, MOH Compliance & Media (11-12 days)**

**Objective**: Implement center/service management, Laravel Nova admin, MOH compliance, FAQs, photo galleries.

**Dependencies**: Phases 1, 3

**File Checklist**:
- [x] Center model and migration (with MOH fields, transport_info, amenities)
- [x] Service model and migration
- [x] FAQ model and migration
- [x] Media model and migration (polymorphic)
- [x] ContentTranslation model and migration (polymorphic)
- [x] Center-Service relationship
- [x] Center repository
- [x] Service repository
- [x] Content management service
- [x] Media upload service (AWS S3)
- [x] Image optimization service (WebP conversion, thumbnails)
- [x] Laravel Nova installation and configuration
- [x] Nova resources (Center, Service, FAQ, Media)
- [x] Nova custom fields (MOH license, transport info, amenities)
- [x] Nova role-based permissions (Super Admin, Content Manager, Translator)
- [x] Translation management interface in Nova
- [x] MOH license validation rules
- [x] Content API endpoints (GET /api/v1/centers, /api/v1/services, /api/v1/faqs)
- [x] Search endpoint (MeiliSearch integration)
- [x] Photo gallery component
- [x] FAQ display component
- [x] Content tests (backend + admin panel)

**Day-by-Day**:
- **Day 1**: Center/Service/FAQ/Media models, migrations, relationships
- **Day 2**: ContentTranslation model, polymorphic setup
- **Day 3**: Laravel Nova installation, basic resources, MOH fields
- **Day 4**: Translation management in Nova, workflow setup
- **Day 5**: Media upload service, S3 integration, image optimization
- **Day 6**: FAQ management, photo gallery component
- **Day 7**: MeiliSearch setup, indexing, search service
- **Day 8**: Content API endpoints, localization support
- **Day 9**: Nova permissions, role-based access
- **Day 10**: Frontend integration (center listings, service details, FAQs)
- **Day 11**: Content tests, admin panel tests
- **Day 12**: Buffer/polish

**Acceptance Criteria**:
- ✅ Admins can create/edit/delete centers in Nova
- ✅ Centers display MOH license number
- ✅ Centers show transport information (MRT/bus)
- ✅ Admins can manage services per center
- ✅ Content can be translated (English → Mandarin) in Nova
- ✅ Translation workflow works (Draft → Translated → Published)
- ✅ Admins can upload photos, photos optimize automatically (WebP)
- ✅ Photo galleries display correctly
- ✅ FAQs categorized and searchable
- ✅ MeiliSearch returns relevant results
- ✅ Content API respects locale (?lang=en or ?lang=zh)
- ✅ All content management tested

---

### **Phase 5.5: External Integrations (2-3 days)** 🆕

**Objective**: Integrate Mailchimp (newsletter) and Twilio (SMS notifications).

**Dependencies**: Phases 3, 5

**File Checklist**:
- [x] Subscription model and migration
- [x] Mailchimp service (SDK integration)
- [x] Newsletter subscription API endpoint
- [x] Double opt-in workflow
- [x] Mailchimp sync job (queue)
- [x] Unsubscribe webhook handler
- [x] Twilio service (SDK integration)
- [x] SMS notification service
- [x] SMS templates (confirmation, reminder, cancellation)
- [x] SMS queue jobs
- [x] SMS preferences in user settings
- [x] Newsletter signup form component
- [x] Integration tests

**Day-by-Day**:
- **Day 1**: Mailchimp integration, subscription API, double opt-in
- **Day 2**: Twilio integration, SMS service, templates, queue jobs
- **Day 3**: Integration testing, error handling, buffer

**Acceptance Criteria**:
- ✅ Users can subscribe to newsletter
- ✅ Double opt-in email sent
- ✅ Mailchimp syncs new subscribers
- ✅ Unsubscribe updates local database
- ✅ SMS sends for test bookings (confirmation template)
- ✅ SMS queue retries on failure (3 attempts)
- ✅ Users can opt-out of SMS in preferences

---

### **Phase 6: Booking System & Notifications (10-12 days)**

**Objective**: Implement complete booking workflow with Calendly, email, and SMS notifications.

**Dependencies**: Phases 3, 4, 5, 5.5

**File Checklist**:
- [x] Booking model and migration
- [x] Pre-booking questionnaire schema (JSON)
- [x] Booking service
- [x] Calendly service (API integration)
- [x] Booking controllers
- [x] Booking API endpoints (create, update, cancel)
- [x] Calendly webhook handler (invitee.created, invitee.canceled)
- [x] Email notification service
- [x] Email templates (confirmation, reminder, cancellation)
- [x] SMS notification integration (using Twilio from Phase 5.5)
- [x] Booking status management (pending, confirmed, completed, canceled)
- [x] Booking queue jobs (send confirmations, send reminders)
- [x] Booking form component (multi-step)
- [x] Questionnaire component
- [x] Calendar availability component
- [x] Booking confirmation page
- [x] User booking history (dashboard)
- [x] Admin booking management (Nova)
- [x] Booking E2E tests (critical path)

**Day-by-Day**:
- **Day 1**: Booking model, migration, relationships
- **Day 2**: Booking service, validation logic
- **Day 3**: Calendly API integration, event creation
- **Day 4**: Calendly webhook handler, status updates
- **Day 5**: Email notification service, templates
- **Day 6**: SMS integration for bookings (confirmation + reminder)
- **Day 7**: Booking form UI (multi-step), questionnaire
- **Day 8**: Calendar availability component, booking flow
- **Day 9**: Booking confirmation page, user booking history
- **Day 10**: Admin booking management in Nova
- **Day 11**: Booking E2E tests (full workflow)
- **Day 12**: Buffer/error handling/edge cases

**Acceptance Criteria**:
- ✅ Users can complete booking flow (questionnaire → calendar → confirm)
- ✅ Booking creates Calendly event
- ✅ Confirmation email sent immediately
- ✅ Confirmation SMS sent immediately
- ✅ Reminder SMS sent 24h before booking
- ✅ Users can cancel bookings
- ✅ Cancellation triggers email + SMS
- ✅ Admin can view/manage all bookings in Nova
- ✅ Booking status updates correctly (pending → confirmed)
- ✅ Webhooks from Calendly processed correctly
- ✅ Full booking workflow has E2E test coverage
- ✅ Error handling for API failures (fallback to contact form)

---

## 📊 Final Timeline Summary

| Phase | Duration | Cumulative |
|-------|----------|------------|
| Phase 1: Foundation, Infrastructure & Analytics | 5-6 days | 5-6 days |
| Phase 2: Design System, UI Components & i18n | 8-9 days | 13-15 days |
| Phase 3: Backend Services & PDPA Compliance | 10-11 days | 23-26 days |
| Phase 4: Frontend Pages & PDPA UI | 6-7 days | 29-33 days |
| Phase 5: Content Management & MOH Compliance | 11-12 days | 40-45 days |
| Phase 5.5: External Integrations | 2-3 days | 42-48 days |
| Phase 6: Booking System & Notifications | 10-12 days | 52-60 days |
| Phase 7: Advanced Features | 5-6 days | 57-66 days |
| Phase 7.5: Content Population & Translation | 3-4 days | 60-70 days |
| Phase 8: QA & Optimization | 4-5 days | 64-75 days |
| Phase 9: Production Launch | 3-4 days | 67-79 days |

**Total: 67-79 days (13.4-15.8 weeks)**

### To Achieve 12-Week (60-day) Timeline:

**Recommended Approach: Parallelization with 3-4 Developer Team**

| Workstream | Developer | Phases | Timeline |
|------------|-----------|--------|----------|
| **Workstream 1: Infrastructure & Backend** | Backend Dev 1 | Phases 1, 3, 5, 5.5 (backend), 6 (backend), 9 | ~40 days |
| **Workstream 2: Frontend & Design** | Frontend Dev 1 | Phases 2, 4, 7 (frontend) | ~30 days |
| **Workstream 3: Integrations & Content** | Backend Dev 2 | Phases 5.5, 6 (integrations), 7.5 (content) | ~25 days |
| **Workstream 4: QA & Testing** | QA Engineer | Continuous testing, Phase 8 | Parallel |

**With parallelization: Estimated 55-60 days (11-12 weeks)** ✅

---

## 🎯 Critical Success Factors

1. **Start Analytics Day 1**: Cannot measure success without data from launch
2. **i18n from Start**: Retrofitting is exponentially harder
3. **PDPA Non-Negotiable**: Legal requirement, cannot launch without it
4. **Testing Continuous**: Not a separate phase
5. **Staging Environment Always**: Deploy continuously to catch issues early
6. **Content Ready**: No content = no launch
7. **Team Size Matters**: 3-4 developers needed for 12-week timeline

---

## ✅ Final Approval Status

**Revised Execution Plan: APPROVED FOR IMPLEMENTATION**

**With Conditions**:
1. ✅ Team size: Minimum 3 developers (2 backend, 1 frontend) + 1 QA engineer
2. ✅ Clarify technology choices: Laravel Nova (yes), MeiliSearch (yes), AWS ECS (yes)
3. ✅ Stakeholder sign-off on timeline: 12 weeks with parallelization OR 14-16 weeks sequential
4. ✅ Budget approved for: Laravel Nova license, professional translation, external audits


```

# database_schema.sql
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
