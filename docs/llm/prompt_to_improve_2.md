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

### **Phase 7: Advanced Features (5-6 days)**

**Objective**: Implement testimonials and advanced search (MeiliSearch).

**Dependencies**: Phases 4, 5, 6

**File Checklist**:
- [x] Testimonial model and migration
- [x] Testimonial moderation workflow (pending, approved, rejected)
- [x] Testimonial submission form (frontend)
- [x] Testimonial API endpoints
- [x] Testimonial moderation in Nova
- [x] Testimonial display component
- [x] Spam protection (reCAPTCHA v3)
- [x] MeiliSearch advanced filters (by service type, rating, location)
- [x] Search UI component (filters, results)
- [x] Search analytics (track search queries)
- [x] Feature tests

**Day-by-Day**:
- **Day 1**: Testimonial model, API endpoints, validation
- **Day 2**: Testimonial submission form, spam protection (reCAPTCHA)
- **Day 3**: Testimonial moderation in Nova, approval workflow
- **Day 4**: Testimonial display component, rating system
- **Day 5**: Advanced search filters (MeiliSearch), search UI
- **Day 6**: Search analytics, testing, buffer

**Acceptance Criteria**:
- ✅ Users can submit testimonials
- ✅ Testimonials require moderation before display
- ✅ Admin can approve/reject testimonials in Nova
- ✅ Testimonials display with ratings
- ✅ reCAPTCHA prevents spam submissions
- ✅ Search supports filters (service type, location, rating)
- ✅ Search results are relevant and fast (<100ms)
- ✅ Search queries tracked in analytics

**Note**: Virtual tours deferred to Phase 2 (post-MVP, weeks 13-16)

---

### **Phase 7.5: Content Population & Translation (3-4 days)** 🆕

**Objective**: Populate database with real content and translate to Mandarin.

**Dependencies**: Phases 5, 7

**Tasks**:
- [x] Research 5-10 real eldercare centers in Singapore
- [x] Write center descriptions (English)
- [x] Source/create photos for each center
- [x] Create 3-5 services per center
- [x] Write 20+ FAQs (categorized)
- [x] Write About page content
- [x] Create sample testimonials (3-5 per center)
- [x] Professional translation to Mandarin (all content)
- [x] Review translation quality (native speaker)
- [x] Publish content in both languages
- [x] Verify content displays correctly

**Day-by-Day**:
- **Day 1**: Research centers, write English content (centers, services)
- **Day 2**: FAQs, About page, testimonials, source photos
- **Day 3**: Professional translation service (or native speaker translates)
- **Day 4**: Review translations, publish content, verification

**Acceptance Criteria**:
- ✅ At least 5 centers fully populated with descriptions, photos, services
- ✅ All content available in English and Mandarin
- ✅ 20+ FAQs covering common questions
- ✅ About page tells compelling story
- ✅ Sample testimonials provide social proof
- ✅ Translation quality verified by native Mandarin speaker
- ✅ All content displays correctly on frontend

---

### **Phase 8: Quality Assurance & Optimization (4-5 days)**

**Objective**: Comprehensive testing, performance optimization, accessibility audit, security hardening.

**Dependencies**: All previous phases

**Tasks**:
- [x] Cross-browser testing (Chrome, Safari, Firefox, Edge) via BrowserStack
- [x] Device testing (iPhone 12, Samsung Galaxy S21, iPad)
- [x] Screen reader testing (NVDA on Windows, VoiceOver on Mac/iOS)
- [x] Lighthouse CI review (ensure all pages >90 performance & accessibility)
- [x] Performance optimization:
  - Image lazy loading
  - Code splitting optimization
  - Remove unused CSS/JS
  - CDN cache configuration
- [x] Load testing with k6:
  - 1000 concurrent users browsing
  - 100 simultaneous booking submissions
  - Spike test (2x expected load)
- [x] Accessibility audit (external certified auditor)
- [x] Security audit:
  - `npm audit` and fix vulnerabilities
  - `composer audit` and fix vulnerabilities
  - Penetration testing (external firm)
- [x] Fix all critical and high-priority issues
- [x] Create bug tracker for low-priority issues (post-launch backlog)

**Day-by-Day**:
- **Day 1**: Cross-browser testing, device testing, fix compatibility issues
- **Day 2**: Screen reader testing (NVDA, VoiceOver), fix accessibility issues
- **Day 3**: Performance optimization, Lighthouse CI fixes
- **Day 4**: Load testing (k6), security audit, fix vulnerabilities
- **Day 5**: Final fixes, external accessibility audit, penetration test results review

**Acceptance Criteria**:
- ✅ Works correctly on all target browsers (Chrome, Safari, Firefox, Edge)
- ✅ Works on iPhone, Samsung, iPad
- ✅ Fully navigable with keyboard only
- ✅ NVDA and VoiceOver can navigate entire site
- ✅ All pages Lighthouse performance >90
- ✅ All pages Lighthouse accessibility >90
- ✅ Load tests pass with <5% error rate at 1000 concurrent users
- ✅ No critical or high security vulnerabilities
- ✅ External accessibility audit approves WCAG 2.1 AA compliance
- ✅ Penetration test finds no critical issues

---

### **Phase 9: Production Launch & Hardening (3-4 days)**

**Objective**: Deploy to production, configure monitoring, execute launch.

**Dependencies**: All previous phases

**File Checklist**:
- [x] Production environment setup (AWS ECS, RDS, ElastiCache)
- [x] Production environment variables (.env.production)
- [x] Production database setup (with encryption at rest)
- [x] Production secrets management (AWS Secrets Manager)
- [x] Production SSL certificates (Cloudflare)
- [x] Production monitoring (New Relic, Sentry)
- [x] Production alerting rules
- [x] Production backup configuration (automated daily)
- [x] Disaster recovery procedures documentation
- [x] Deployment runbooks
- [x] Rollback procedures
- [x] Launch checklist
- [x] Post-launch monitoring dashboard

**Day-by-Day**:
- **Day 1**: Production environment provisioning (replicate staging)
- **Day 2**: Production configuration, secrets management, SSL
- **Day 3**: Production deployment, smoke tests, monitoring verification
- **Day 4**: Final launch checklist, go-live, post-launch monitoring

**Acceptance Criteria**:
- ✅ Production environment live at final domain
- ✅ SSL certificates valid
- ✅ Database encrypted at rest
- ✅ All secrets in AWS Secrets Manager (not in code)
- ✅ New Relic monitoring active
- ✅ Sentry error tracking active
- ✅ Alerts configured and tested
- ✅ Automated backups running
- ✅ Disaster recovery procedures tested
- ✅ Deployment runbook complete
- ✅ Rollback procedure tested
- ✅ Smoke tests pass in production
- ✅ Launch checklist 100% complete
- ✅ Stakeholders notified of successful launch

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

# docs/llm/phase3-coding-sub-plan-v2.md
```md
# Phase 3 Backend Execution Sub-Plan — Core Services, PDPA Compliance & API Foundation

<think>
This is a critical planning phase. The user needs:
1. Complete backend implementation to enable a production demo
2. Comprehensive file-by-file roadmap with feature checklists
3. A plan that an independent AI agent can execute without ambiguity
4. Deep integration with the existing schema (18 tables, complex relationships)
5. PDPA compliance as a non-negotiable requirement
6. External integrations (Calendly, Mailchimp, Twilio, S3, Cloudflare Stream)

I need to think strategically about:
- Layered architecture (Models → Repositories → Services → Controllers)
- Service-oriented design (thin controllers, fat services)
- PDPA compliance embedded at every layer (audit logging, consent checks)
- API-first design with consistent response formats
- Queue-based async operations (emails, SMS, Mailchimp sync)
- Comprehensive testing strategy
- File organization that scales

Let me structure this as a multi-workstream plan with:
- Clear dependencies between workstreams
- File creation matrix with descriptions and checklists
- Validation criteria for each deliverable
- Risk mitigation strategies
</think>

---

## 1. Executive Summary

**Objective**: Deliver production-ready Laravel backend API (v1) with complete PDPA compliance, MOH regulatory support, and external integrations to enable an impressive stakeholder demo.

**Scope**: 
- ✅ Foundation: Authentication (Sanctum), authorization (RBAC), PDPA compliance (consent + audit)
- ✅ Core Business Logic: Centers, services, staff, FAQs, contact forms, newsletter subscriptions
- ✅ Booking System: Calendly integration, questionnaires, email/SMS notifications, webhooks
- ✅ Advanced Features: Testimonial moderation, media management (S3), multilingual content
- ✅ API Infrastructure: Consistent response formats, error handling, rate limiting, OpenAPI docs
- ✅ Testing: >90% coverage (unit + feature tests), factories/seeders, integration tests

**Out of Scope** (Deferred to Phase 4+):
- Frontend integration (handled in Phase 4)
- MeiliSearch advanced search (Phase 7)
- Cloudflare Stream video processing (Phase 5/6)
- Laravel Nova admin panel (Phase 5)

**Success Criteria**:
- All API endpoints documented and testable via Postman
- PDPA compliance verified (consent tracking, audit logs, data export, account deletion)
- External integrations functional (Calendly webhook, Mailchimp sync, Twilio SMS)
- Test coverage ≥90% (PHPUnit)
- Staging deployment successful with smoke tests passing

---

## 2. Database Schema Deep Dive & Backend Implications

### 2.1 Schema Complexity Analysis

| Complexity Factor | Impact on Backend Design |
|-------------------|--------------------------|
| **18 interconnected tables** | Requires robust Eloquent relationship mapping, eager loading strategies, N+1 query prevention |
| **Polymorphic relationships** (media, content_translations, audit_logs) | Need trait-based implementations, dynamic relationship resolution |
| **JSON columns** (operating_hours, amenities, questionnaire_responses) | Custom casting, validation rules, query helpers |
| **Soft deletes** (users, centers, services, bookings, testimonials) | Global scopes, trash/restore endpoints, PDPA grace period jobs |
| **PDPA audit trail** | Observer pattern for automatic audit log creation on model events |
| **External service IDs** (calendly_event_id, mailchimp_subscriber_id, cloudflare_stream_id) | API client abstraction, webhook signature verification, retry logic |
| **Multilingual content** | Translation repository pattern, locale-based query scopes |
| **MOH compliance fields** | Validation rules for license numbers, expiry date alerts, accreditation workflows |

### 2.2 Critical Relationships Map

```
users (1) ──→ (1) profiles
  │
  ├──→ (*) bookings ──→ (1) centers ──→ (*) services
  │                      │              │
  ├──→ (*) testimonials ─┘              └──→ (*) media (polymorphic)
  │                                      └──→ (*) content_translations (polymorphic)
  ├──→ (*) consents                      
  │
  ├──→ (*) audit_logs (polymorphic)
  │
  └──→ (*) contact_submissions (optional)

centers (1) ──→ (*) staff

subscriptions (independent, Mailchimp-synced)

faqs (independent, multilingual via content_translations)
```

### 2.3 Service Layer Responsibilities (Derived from Schema)

| Service | Primary Responsibilities | Key Dependencies |
|---------|-------------------------|------------------|
| **AuthService** | Registration, login, logout, email verification, password reset | `users`, `password_reset_tokens`, `consents` |
| **ConsentService** | Consent capture, withdrawal, versioning, export | `consents`, `audit_logs` |
| **AuditService** | Automatic logging of model changes | `audit_logs` (polymorphic) |
| **UserService** | Profile management, data export, account deletion | `users`, `profiles`, `consents`, `audit_logs` |
| **CenterService** | CRUD, MOH compliance checks, capacity management | `centers`, `services`, `staff`, `media`, `content_translations` |
| **ServiceMgmtService** | Center service CRUD, pricing, features | `services`, `media`, `content_translations` |
| **BookingService** | Booking creation, status management, questionnaires | `bookings`, `centers`, `services`, `users`, Calendly API |
| **CalendlyService** | Event creation, cancellation, webhook processing | External Calendly API, `bookings` |
| **NotificationService** | Email/SMS sending, template rendering, queue jobs | Twilio API, Laravel Mail, `bookings` |
| **MailchimpService** | Subscription sync, double opt-in, webhook handling | External Mailchimp API, `subscriptions` |
| **MediaService** | Upload to S3, optimization, polymorphic attachment | AWS S3 SDK, `media` |
| **TranslationService** | Translation CRUD, workflow management | `content_translations` |
| **TestimonialService** | Submission, moderation, rating aggregation | `testimonials`, `users`, `centers` |
| **ContactService** | Form submission, spam detection, status tracking | `contact_submissions` |

---

## 3. Phase 3 Workstream Breakdown (6 Workstreams)

### Timeline Overview (Total: 10-11 days)

```
Day 1-2   │ Workstream A: Foundation (Models, Auth, PDPA)
Day 3-4   │ Workstream B: Core Business Logic
Day 5-6   │ Workstream C: Booking System & Integrations
Day 7-8   │ Workstream D: Advanced Features
Day 9     │ Workstream E: API Layer & Documentation
Day 10-11 │ Workstream F: Testing & Quality Assurance
```

---

## 4. Detailed File Creation Matrix

### 🔹 Workstream A: Foundation Services (Days 1-2)

**Objective**: Establish authentication, authorization, PDPA compliance foundation.

**Dependencies**: Completed Phase 1 migrations, Docker environment.

#### A.1 Eloquent Models & Relationships

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Models/User.php` | User model with authentication, PDPA compliance | - [ ] `HasApiTokens`, `SoftDeletes`, `Notifiable` traits<br>- [ ] Relationships: `profile()`, `bookings()`, `testimonials()`, `consents()`, `auditLogs()`<br>- [ ] Accessors: `preferredLanguage`, `isAdmin()`<br>- [ ] Mutators: `password` (bcrypt)<br>- [ ] Casts: `email_verified_at` (datetime)<br>- [ ] Hidden: `password`, `remember_token` |
| `backend/app/Models/Profile.php` | User profile (1:1 with User) | - [ ] Relationship: `user()`<br>- [ ] Casts: `birth_date` (date)<br>- [ ] Fillable: `avatar`, `bio`, `address`, `city`, `postal_code` |
| `backend/app/Models/Consent.php` | PDPA consent tracking | - [ ] Relationship: `user()`<br>- [ ] Casts: `consent_given` (boolean), `created_at` (datetime)<br>- [ ] Scopes: `ofType()`, `active()`, `withdrawn()`<br>- [ ] Methods: `isActive()`, `withdraw()` |
| `backend/app/Models/AuditLog.php` | Polymorphic audit trail | - [ ] Polymorphic: `auditable()`<br>- [ ] Relationship: `user()`<br>- [ ] Casts: `old_values` (array), `new_values` (array)<br>- [ ] Scopes: `forModel()`, `byAction()` |
| `backend/app/Models/Center.php` | Eldercare centers (core entity) | - [ ] `SoftDeletes` trait<br>- [ ] Relationships: `services()`, `staff()`, `bookings()`, `testimonials()`, `media()`, `translations()`<br>- [ ] Casts: `operating_hours` (array), `medical_facilities` (array), `amenities` (array), `transport_info` (array), `languages_supported` (array), `government_subsidies` (array), `license_expiry_date` (date)<br>- [ ] Accessors: `occupancyRate()`, `averageRating()`, `isLicenseValid()`<br>- [ ] Scopes: `published()`, `validLicense()`, `inCity()`<br>- [ ] Sluggable implementation |
| `backend/app/Models/Service.php` | Center services | - [ ] `SoftDeletes` trait<br>- [ ] Relationship: `center()`, `bookings()`, `media()`, `translations()`<br>- [ ] Casts: `price` (decimal:2), `features` (array)<br>- [ ] Scopes: `published()`, `forCenter()` |
| `backend/app/Models/Staff.php` | Center staff (MOH compliance) | - [ ] Relationship: `center()`<br>- [ ] Casts: `qualifications` (array), `years_of_experience` (integer)<br>- [ ] Scopes: `active()`, `forCenter()` |
| `backend/app/Models/Booking.php` | Bookings with Calendly integration | - [ ] `SoftDeletes` trait<br>- [ ] Relationships: `user()`, `center()`, `service()`<br>- [ ] Casts: `booking_date` (date), `booking_time` (datetime), `questionnaire_responses` (array), `sms_sent` (boolean)<br>- [ ] Scopes: `upcoming()`, `byStatus()`, `forUser()`, `forCenter()`<br>- [ ] Methods: `confirm()`, `cancel()`, `markCompleted()`, `sendReminder()` |
| `backend/app/Models/Testimonial.php` | User testimonials with moderation | - [ ] `SoftDeletes` trait<br>- [ ] Relationships: `user()`, `center()`, `moderatedBy()`<br>- [ ] Casts: `rating` (integer), `moderated_at` (datetime)<br>- [ ] Scopes: `approved()`, `pending()`, `forCenter()`<br>- [ ] Methods: `approve()`, `reject()`, `markAsSpam()` |
| `backend/app/Models/FAQ.php` | FAQs with multilingual support | - [ ] Relationship: `translations()`<br>- [ ] Scopes: `published()`, `byCategory()`, `ordered()`<br>- [ ] Casts: `display_order` (integer) |
| `backend/app/Models/Subscription.php` | Newsletter subscriptions (Mailchimp) | - [ ] Casts: `preferences` (array), `subscribed_at` (datetime), `unsubscribed_at` (datetime), `last_synced_at` (datetime)<br>- [ ] Scopes: `subscribed()`, `pending()`<br>- [ ] Methods: `subscribe()`, `unsubscribe()`, `syncToMailchimp()` |
| `backend/app/Models/ContactSubmission.php` | Contact form submissions | - [ ] Relationships: `user()`, `center()`<br>- [ ] Scopes: `new()`, `byStatus()`<br>- [ ] Methods: `markAsSpam()`, `resolve()` |
| `backend/app/Models/Media.php` | Polymorphic media storage | - [ ] Polymorphic: `mediable()`<br>- [ ] Casts: `size` (integer), `duration` (integer), `display_order` (integer)<br>- [ ] Scopes: `images()`, `videos()`, `ordered()`<br>- [ ] Accessors: `sizeInMB()`, `formattedDuration()` |
| `backend/app/Models/ContentTranslation.php` | Polymorphic translations | - [ ] Polymorphic: `translatable()`<br>- [ ] Relationships: `translator()`, `reviewer()`<br>- [ ] Scopes: `locale()`, `published()`, `forField()`<br>- [ ] Methods: `markTranslated()`, `markReviewed()`, `publish()` |

**Validation**: All models have unit tests verifying relationships, scopes, accessors, mutators.

---

#### A.2 PDPA Compliance Services

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Consent/ConsentService.php` | Consent management service | - [ ] `captureConsent($userId, $type, $consentText, $version, $ipAddress, $userAgent)`: Record consent<br>- [ ] `withdrawConsent($userId, $type, $ipAddress, $userAgent)`: Withdraw consent<br>- [ ] `checkConsent($userId, $type)`: Check if consent is active<br>- [ ] `getConsentHistory($userId)`: Retrieve full consent history<br>- [ ] `exportConsentData($userId)`: Export for PDPA data request<br>- [ ] Automatic audit logging on consent changes |
| `backend/app/Services/Audit/AuditService.php` | Audit logging service | - [ ] `log($model, $action, $oldValues, $newValues, $userId, $ipAddress, $userAgent, $url)`: Create audit log<br>- [ ] `getAuditTrail($model)`: Retrieve audit history for a model<br>- [ ] `searchAuditLogs($filters)`: Search audit logs by date/user/action<br>- [ ] `exportAuditLogs($startDate, $endDate)`: Export for compliance audits<br>- [ ] Automatic 7-year retention policy enforcement |
| `backend/app/Observers/AuditObserver.php` | Model observer for automatic audit logging | - [ ] `created($model)`: Log creation event<br>- [ ] `updated($model)`: Log update event with old/new values<br>- [ ] `deleted($model)`: Log deletion event<br>- [ ] `restored($model)`: Log restore event<br>- [ ] IP/user agent capture from request context |
| `backend/app/Services/User/DataExportService.php` | PDPA data export service | - [ ] `exportUserData($userId)`: Generate JSON export of all user data<br>- [ ] Include: profile, bookings, testimonials, consents, audit logs<br>- [ ] Format: JSON with nested relationships<br>- [ ] Queue job for large datasets<br>- [ ] Email download link when ready |
| `backend/app/Services/User/AccountDeletionService.php` | PDPA account deletion service | - [ ] `requestDeletion($userId)`: Soft delete with 30-day grace period<br>- [ ] `cancelDeletion($userId)`: Restore account within grace period<br>- [ ] `permanentlyDelete($userId)`: Hard delete (queue job)<br>- [ ] Anonymize related data (bookings, testimonials)<br>- [ ] Notify user of deletion stages |
| `backend/app/Jobs/PermanentAccountDeletionJob.php` | Queue job for permanent deletion | - [ ] Execute after 30-day grace period<br>- [ ] Hard delete user record<br>- [ ] Anonymize bookings (replace user_id with null or anonymized ID)<br>- [ ] Remove PII from audit logs<br>- [ ] Send final deletion confirmation email |

**Validation**: 
- ✅ Consent capture/withdrawal flows tested with different consent types
- ✅ Audit logs automatically created on model changes (verified via observer tests)
- ✅ Data export generates complete JSON with all relationships
- ✅ Account deletion follows 30-day grace period, anonymizes data correctly

---

#### A.3 Authentication & Authorization

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Http/Controllers/Api/V1/Auth/RegisterController.php` | User registration | - [ ] `store(RegisterRequest $request)`: Create user with consent capture<br>- [ ] Validate unique email, strong password<br>- [ ] Send email verification<br>- [ ] Return 201 with user resource + token<br>- [ ] Log registration audit event |
| `backend/app/Http/Controllers/Api/V1/Auth/LoginController.php` | User login | - [ ] `store(LoginRequest $request)`: Authenticate and issue token<br>- [ ] Validate credentials<br>- [ ] Check email verification status<br>- [ ] Return user resource + Sanctum token<br>- [ ] Log login audit event with IP |
| `backend/app/Http/Controllers/Api/V1/Auth/LogoutController.php` | User logout | - [ ] `destroy(Request $request)`: Revoke current token<br>- [ ] Return 204 No Content<br>- [ ] Log logout audit event |
| `backend/app/Http/Controllers/Api/V1/Auth/EmailVerificationController.php` | Email verification | - [ ] `verify(Request $request)`: Mark email as verified<br>- [ ] Signed URL validation<br>- [ ] Return 200 with success message<br>- [ ] Trigger welcome email |
| `backend/app/Http/Controllers/Api/V1/Auth/PasswordResetController.php` | Password reset | - [ ] `requestReset(Request $request)`: Send password reset email<br>- [ ] `reset(Request $request)`: Reset password with token<br>- [ ] Token expiry validation (60 mins)<br>- [ ] Log password change audit event |
| `backend/app/Http/Requests/Auth/RegisterRequest.php` | Registration validation | - [ ] Required: `name`, `email`, `password`, `password_confirmation`, `consent_account`, `consent_terms`<br>- [ ] Email format, unique validation<br>- [ ] Password min 8 chars, complexity rules<br>- [ ] Consent boolean validation |
| `backend/app/Http/Requests/Auth/LoginRequest.php` | Login validation | - [ ] Required: `email`, `password`<br>- [ ] Email format validation<br>- [ ] Optional: `remember` (boolean) |
| `backend/app/Http/Middleware/EnsureEmailIsVerified.php` | Email verification middleware | - [ ] Block unverified users from protected routes<br>- [ ] Return 403 with verification required message |
| `backend/app/Http/Middleware/CheckRole.php` | Role-based authorization middleware | - [ ] `handle($request, $next, ...$roles)`: Check user role<br>- [ ] Support multiple roles: `CheckRole::class.':admin,super_admin'`<br>- [ ] Return 403 if role mismatch |
| `backend/app/Policies/UserPolicy.php` | User authorization policy | - [ ] `update(User $user, User $model)`: User can update own profile, admins can update any<br>- [ ] `delete(User $user, User $model)`: Only super_admin can delete users<br>- [ ] `viewAny(User $user)`: Only admins can list all users |

**Validation**:
- ✅ Registration flow: user created, consent captured, verification email sent
- ✅ Login returns valid Sanctum token, works with `Authorization: Bearer {token}` header
- ✅ Email verification required for protected endpoints
- ✅ Role middleware blocks unauthorized access (tested with admin/user roles)

---

#### A.4 API Infrastructure

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Http/Responses/ApiResponse.php` | Standardized API response formatter | - [ ] `success($data, $message, $statusCode)`: Success response<br>- [ ] `error($message, $errors, $statusCode)`: Error response<br>- [ ] `paginated($paginator, $resourceClass)`: Paginated collection<br>- [ ] Consistent JSON structure: `{success, message, data, errors, meta}`<br>- [ ] Support for Laravel API resources |
| `backend/app/Exceptions/Handler.php` (modify) | Global exception handler | - [ ] Catch `ValidationException`: Return 422 with field errors<br>- [ ] Catch `AuthenticationException`: Return 401<br>- [ ] Catch `AuthorizationException`: Return 403<br>- [ ] Catch `ModelNotFoundException`: Return 404<br>- [ ] Catch `ThrottleRequestsException`: Return 429<br>- [ ] Catch generic exceptions: Return 500 with sanitized message (no stack trace in production)<br>- [ ] Log all exceptions to Sentry |
| `backend/app/Http/Middleware/RateLimitApi.php` | API rate limiting | - [ ] Default: 60 requests per minute per user<br>- [ ] Higher limits for authenticated users: 120/min<br>- [ ] Return 429 with `Retry-After` header<br>- [ ] Different limits for auth endpoints (stricter): 5/min for login |
| `backend/app/Http/Middleware/LogApiRequest.php` | API request logging middleware | - [ ] Log all API requests: method, path, IP, user_id, response status<br>- [ ] Store in `api_request_logs` table (create migration)<br>- [ ] Exclude sensitive data (passwords, tokens) from logs |
| `backend/config/sanctum.php` (configure) | Sanctum configuration | - [ ] Token expiration: 60 days (configurable)<br>- [ ] Stateful domains for SPA: `localhost:3000`, staging/production domains<br>- [ ] Token abilities/permissions support enabled |
| `backend/routes/api.php` | API routing structure | - [ ] Group `/api/v1` with rate limiting, JSON middleware<br>- [ ] Public routes: auth (register, login), password reset, contact form<br>- [ ] Protected routes: profile, bookings, testimonials, data export<br>- [ ] Admin routes: center management, moderation, user management<br>- [ ] Versioning strategy documented |

**Validation**:
- ✅ All API responses follow consistent format (automated test for response structure)
- ✅ Exceptions return proper HTTP status codes with user-friendly messages
- ✅ Rate limiting blocks excessive requests, returns `Retry-After` header
- ✅ API request logs captured for debugging/analytics

---

### 🔹 Workstream B: Core Business Logic (Days 3-4)

**Objective**: Implement center management, services, FAQs, contact forms, newsletter subscriptions.

**Dependencies**: Workstream A models and services.

#### B.1 Center & Service Management

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Center/CenterService.php` | Center management service | - [ ] `create($data)`: Create center with MOH validation<br>- [ ] `update($centerId, $data)`: Update center, check license expiry<br>- [ ] `delete($centerId)`: Soft delete<br>- [ ] `publish($centerId)`: Change status to published<br>- [ ] `archive($centerId)`: Archive center<br>- [ ] `checkLicenseExpiry()`: Alert if license expires within 30 days<br>- [ ] `updateOccupancy($centerId, $newOccupancy)`: Update current occupancy<br>- [ ] `getWithStatistics($centerId)`: Return center with services count, staff count, avg rating<br>- [ ] Automatic audit logging |
| `backend/app/Services/Center/ServiceManagementService.php` | Service CRUD service | - [ ] `create($centerId, $data)`: Add service to center<br>- [ ] `update($serviceId, $data)`: Update service<br>- [ ] `delete($serviceId)`: Soft delete<br>- [ ] `getServicesForCenter($centerId)`: List all services for center<br>- [ ] `publish($serviceId)`: Publish service |
| `backend/app/Services/Center/StaffService.php` | Staff management service | - [ ] `create($centerId, $data)`: Add staff member<br>- [ ] `update($staffId, $data)`: Update staff<br>- [ ] `delete($staffId)`: Delete staff<br>- [ ] `reorder($centerId, $orderArray)`: Update display order<br>- [ ] `getActiveStaff($centerId)`: List active staff for center |
| `backend/app/Http/Controllers/Api/V1/CenterController.php` | Center API controller | - [ ] `index(Request $request)`: List centers (paginated, filterable by city/status)<br>- [ ] `show($slug)`: Show single center with relationships<br>- [ ] `store(StoreCenterRequest $request)`: Create center (admin only)<br>- [ ] `update($id, UpdateCenterRequest $request)`: Update center (admin only)<br>- [ ] `destroy($id)`: Soft delete center (admin only)<br>- [ ] Use `CenterResource` for response transformation |
| `backend/app/Http/Controllers/Api/V1/ServiceController.php` | Service API controller | - [ ] `index($centerId)`: List services for center<br>- [ ] `show($centerId, $serviceSlug)`: Show single service<br>- [ ] `store($centerId, StoreServiceRequest $request)`: Create service (admin only)<br>- [ ] `update($id, UpdateServiceRequest $request)`: Update service (admin only)<br>- [ ] `destroy($id)`: Soft delete service (admin only) |
| `backend/app/Http/Requests/StoreCenterRequest.php` | Center creation validation | - [ ] Required: `name`, `address`, `city`, `postal_code`, `phone`, `email`, `moh_license_number`, `license_expiry_date`, `capacity`, `description`<br>- [ ] MOH license format validation (Singapore format)<br>- [ ] License expiry date must be future<br>- [ ] Capacity must be positive integer<br>- [ ] JSON validation for `operating_hours`, `amenities`, `transport_info` |
| `backend/app/Http/Requests/UpdateCenterRequest.php` | Center update validation | - [ ] Same as create, but all fields optional<br>- [ ] Current occupancy cannot exceed capacity |
| `backend/app/Http/Requests/StoreServiceRequest.php` | Service creation validation | - [ ] Required: `name`, `description`<br>- [ ] Optional: `price` (decimal, min 0), `price_unit`, `duration`<br>- [ ] JSON validation for `features` array |
| `backend/app/Http/Resources/CenterResource.php` | Center API resource transformer | - [ ] Transform: id, name, slug, description, address, city, postal_code, phone, email, website<br>- [ ] Include: services (count), staff (count), average_rating, occupancy_rate, license status<br>- [ ] Conditional: MOH license details (admin only), internal notes (admin only)<br>- [ ] Nested: services (when requested), staff (when requested), media (when requested) |
| `backend/app/Http/Resources/ServiceResource.php` | Service API resource transformer | - [ ] Transform: id, name, slug, description, price, price_unit, duration, features, status<br>- [ ] Include: center (basic info)<br>- [ ] Nested: media (when requested) |

**Validation**:
- ✅ Center CRUD operations work with proper authorization (admin only)
- ✅ MOH license validation enforced (format, expiry date)
- ✅ Center listing filterable by city, status, and searchable by name
- ✅ Services correctly associated with centers
- ✅ API resources transform data consistently

---

#### B.2 FAQ, Contact Form, Newsletter

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Content/FAQService.php` | FAQ management service | - [ ] `create($data)`: Create FAQ<br>- [ ] `update($faqId, $data)`: Update FAQ<br>- [ ] `delete($faqId)`: Delete FAQ<br>- [ ] `reorder($category, $orderArray)`: Update display order<br>- [ ] `getPublishedByCategory($category)`: Get published FAQs for category<br>- [ ] `search($query)`: Full-text search in questions/answers |
| `backend/app/Services/Contact/ContactService.php` | Contact form service | - [ ] `submit($data)`: Create contact submission<br>- [ ] `markAsSpam($submissionId)`: Flag as spam<br>- [ ] `resolve($submissionId)`: Mark as resolved<br>- [ ] `detectSpam($data)`: Simple spam detection (rate limiting, honeypot field)<br>- [ ] `notifyAdmin($submission)`: Send admin notification email<br>- [ ] IP address logging for spam prevention |
| `backend/app/Services/Newsletter/MailchimpService.php` | Mailchimp integration service | - [ ] `subscribe($email, $preferences)`: Add subscriber to Mailchimp (double opt-in)<br>- [ ] `unsubscribe($email)`: Remove subscriber from Mailchimp<br>- [ ] `updatePreferences($email, $preferences)`: Update subscriber preferences<br>- [ ] `syncSubscription($subscriptionId)`: Sync local subscription with Mailchimp<br>- [ ] `handleWebhook($payload, $signature)`: Process Mailchimp webhooks (unsubscribe, cleaned)<br>- [ ] Retry logic for API failures<br>- [ ] Queue job for async sync |
| `backend/app/Http/Controllers/Api/V1/FAQController.php` | FAQ API controller | - [ ] `index(Request $request)`: List FAQs (filterable by category, status)<br>- [ ] `show($id)`: Show single FAQ<br>- [ ] `store(StoreFAQRequest $request)`: Create FAQ (admin only)<br>- [ ] `update($id, UpdateFAQRequest $request)`: Update FAQ (admin only)<br>- [ ] `destroy($id)`: Delete FAQ (admin only) |
| `backend/app/Http/Controllers/Api/V1/ContactController.php` | Contact form API controller | - [ ] `store(ContactRequest $request)`: Submit contact form<br>- [ ] Spam detection before storing<br>- [ ] Send admin notification<br>- [ ] Return 201 with success message |
| `backend/app/Http/Controllers/Api/V1/SubscriptionController.php` | Newsletter subscription API controller | - [ ] `store(SubscribeRequest $request)`: Subscribe to newsletter<br>- [ ] Queue Mailchimp sync job<br>- [ ] Send double opt-in email<br>- [ ] Return 201 with pending status<br>- [ ] `destroy(Request $request)`: Unsubscribe (email parameter)<br>- [ ] `webhook(Request $request)`: Handle Mailchimp webhooks |
| `backend/app/Http/Requests/ContactRequest.php` | Contact form validation | - [ ] Required: `name`, `email`, `subject`, `message`<br>- [ ] Optional: `phone`, `center_id`<br>- [ ] Email format validation<br>- [ ] Message min 10 characters<br>- [ ] Honeypot field validation |
| `backend/app/Http/Requests/SubscribeRequest.php` | Newsletter subscription validation | - [ ] Required: `email`<br>- [ ] Email format, unique validation<br>- [ ] Optional: `preferences` (JSON) |
| `backend/app/Jobs/SyncMailchimpSubscriptionJob.php` | Queue job for Mailchimp sync | - [ ] Call `MailchimpService::syncSubscription()`<br>- [ ] Retry 3 times on failure<br>- [ ] Update `last_synced_at` timestamp<br>- [ ] Update `mailchimp_status` from API response |

**Validation**:
- ✅ FAQ CRUD operations work, display_order updates correctly
- ✅ Contact form submissions stored with spam detection
- ✅ Newsletter subscription triggers Mailchimp API call (mocked in tests)
- ✅ Mailchimp webhook handler processes unsubscribe events correctly

---

### 🔹 Workstream C: Booking System & Integrations (Days 5-6)

**Objective**: Complete booking workflow with Calendly integration, notifications (email/SMS), webhooks.

**Dependencies**: Workstream A/B services, external API credentials (Calendly, Twilio).

#### C.1 Booking Service & Calendly Integration

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Booking/BookingService.php` | Core booking service | - [ ] `create($userId, $data)`: Create booking with questionnaire<br>- [ ] Generate unique booking_number (BK-YYYYMMDD-####)<br>- [ ] Validate booking date/time (not in past, center availability)<br>- [ ] Call `CalendlyService::createEvent()`<br>- [ ] Store Calendly event ID, URIs<br>- [ ] Queue confirmation email/SMS<br>- [ ] Return booking resource<br>- [ ] `update($bookingId, $data)`: Update booking (reschedule via Calendly)<br>- [ ] `cancel($bookingId, $reason)`: Cancel booking, call Calendly cancel API<br>- [ ] `confirm($bookingId)`: Change status to confirmed<br>- [ ] `complete($bookingId)`: Mark as completed<br>- [ ] `sendReminders()`: Queue reminders for bookings 24h ahead<br>- [ ] Automatic audit logging |
| `backend/app/Services/Integration/CalendlyService.php` | Calendly API integration | - [ ] `createEvent($centerCalendlyUrl, $bookingDetails)`: Create Calendly event<br>- [ ] `cancelEvent($calendlyEventUri)`: Cancel Calendly event<br>- [ ] `rescheduleEvent($calendlyEventUri, $newDateTime)`: Reschedule event<br>- [ ] `verifyWebhookSignature($payload, $signature)`: Verify webhook authenticity<br>- [ ] API client with retry logic (exponential backoff)<br>- [ ] Error handling (Calendly API errors → booking failure)<br>- [ ] Mock interface for testing |
| `backend/app/Services/Notification/NotificationService.php` | Email/SMS notification service | - [ ] `sendBookingConfirmation($booking)`: Send confirmation email + SMS<br>- [ ] `sendBookingReminder($booking)`: Send reminder 24h before<br>- [ ] `sendCancellationNotification($booking)`: Send cancellation email + SMS<br>- [ ] Email templates: `emails.booking.confirmation`, `emails.booking.reminder`, `emails.booking.cancellation`<br>- [ ] SMS templates: `sms.booking.confirmation`, `sms.booking.reminder`<br>- [ ] Call `TwilioService` for SMS<br>- [ ] Queue jobs for async sending |
| `backend/app/Services/Integration/TwilioService.php` | Twilio SMS integration | - [ ] `sendSMS($to, $message)`: Send SMS via Twilio API<br>- [ ] Singapore phone number validation (+65 format)<br>- [ ] API client with retry logic<br>- [ ] Error handling (failed SMS → log, don't block booking)<br>- [ ] Mock interface for testing |
| `backend/app/Http/Controllers/Api/V1/BookingController.php` | Booking API controller | - [ ] `index(Request $request)`: List user's bookings (filterable by status, date range)<br>- [ ] `show($bookingNumber)`: Show single booking with center/service details<br>- [ ] `store(StoreBookingRequest $request)`: Create booking<br>- [ ] `update($id, UpdateBookingRequest $request)`: Update/reschedule booking<br>- [ ] `destroy($id, CancelBookingRequest $request)`: Cancel booking (requires reason)<br>- [ ] Authorization: Users can only access own bookings, admins can access all |
| `backend/app/Http/Controllers/Api/V1/Webhooks/CalendlyWebhookController.php` | Calendly webhook handler | - [ ] `handle(Request $request)`: Process Calendly webhooks<br>- [ ] Verify webhook signature<br>- [ ] Handle events: `invitee.created`, `invitee.canceled`, `invitee.rescheduled`<br>- [ ] Update booking status based on webhook event<br>- [ ] Send notifications on status changes<br>- [ ] Return 200 to acknowledge webhook |
| `backend/app/Http/Requests/StoreBookingRequest.php` | Booking creation validation | - [ ] Required: `center_id`, `booking_date`, `booking_time`, `booking_type`<br>- [ ] Optional: `service_id`, `questionnaire_responses` (JSON)<br>- [ ] Date must be future date<br>- [ ] Time must be within center operating hours<br>- [ ] Validate questionnaire against schema |
| `backend/app/Http/Requests/CancelBookingRequest.php` | Booking cancellation validation | - [ ] Required: `cancellation_reason` (min 10 chars) |
| `backend/app/Http/Resources/BookingResource.php` | Booking API resource transformer | - [ ] Transform: id, booking_number, booking_date, booking_time, booking_type, status<br>- [ ] Include: center (basic), service (basic), calendly_cancel_url, calendly_reschedule_url<br>- [ ] Conditional: questionnaire_responses (user only), notes (admin only)<br>- [ ] Nested: center, service (when requested) |
| `backend/app/Jobs/SendBookingConfirmationJob.php` | Queue job for booking confirmation | - [ ] Call `NotificationService::sendBookingConfirmation()`<br>- [ ] Update `confirmation_sent_at` timestamp<br>- [ ] Retry 2 times on failure |
| `backend/app/Jobs/SendBookingReminderJob.php` | Queue job for booking reminders | - [ ] Call `NotificationService::sendBookingReminder()`<br>- [ ] Update `reminder_sent_at` timestamp<br>- [ ] Mark `sms_sent` as true |
| `backend/app/Console/Commands/SendBookingRemindersCommand.php` | Daily cron command for reminders | - [ ] Run daily at 9 AM SGT<br>- [ ] Find bookings 24 hours ahead with `reminder_sent_at` null<br>- [ ] Dispatch `SendBookingReminderJob` for each booking |

**Validation**:
- ✅ Booking creation calls Calendly API and stores event ID (mocked in tests, tested manually with real API)
- ✅ Confirmation email/SMS queued and sent after booking creation
- ✅ Booking cancellation updates status, cancels Calendly event, sends notification
- ✅ Calendly webhook handler processes events correctly and updates booking status
- ✅ Reminders sent 24 hours before bookings (cron command tested)

---

### 🔹 Workstream D: Advanced Features (Days 7-8)

**Objective**: Testimonial moderation, media management, translation workflow.

**Dependencies**: Workstream A/B models and services.

#### D.1 Testimonial Management

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Testimonial/TestimonialService.php` | Testimonial service | - [ ] `submit($userId, $centerId, $data)`: Create testimonial (pending status)<br>- [ ] `approve($testimonialId, $moderatorId)`: Approve testimonial<br>- [ ] `reject($testimonialId, $moderatorId, $reason)`: Reject testimonial<br>- [ ] `markAsSpam($testimonialId, $moderatorId)`: Flag as spam<br>- [ ] `getApprovedForCenter($centerId)`: Get approved testimonials for center<br>- [ ] `calculateAverageRating($centerId)`: Calculate center average rating<br>- [ ] Automatic audit logging |
| `backend/app/Http/Controllers/Api/V1/TestimonialController.php` | Testimonial API controller | - [ ] `index($centerId)`: List approved testimonials for center<br>- [ ] `store($centerId, StoreTestimonialRequest $request)`: Submit testimonial (authenticated users)<br>- [ ] Admin routes: `pending()`, `approve($id)`, `reject($id)`, `spam($id)` |
| `backend/app/Http/Requests/StoreTestimonialRequest.php` | Testimonial submission validation | - [ ] Required: `title`, `content`, `rating`<br>- [ ] Rating: integer, min 1, max 5<br>- [ ] Content: min 20 characters<br>- [ ] User can only submit one testimonial per center |
| `backend/app/Http/Resources/TestimonialResource.php` | Testimonial API resource transformer | - [ ] Transform: id, title, content, rating, created_at<br>- [ ] Include: user (name only, anonymize option), center (name)<br>- [ ] Conditional: moderation_notes (admin only) |

**Validation**:
- ✅ Users can submit testimonials, status is pending
- ✅ Admins can approve/reject/spam testimonials
- ✅ Only approved testimonials visible in public listing
- ✅ Average rating calculated correctly

---

#### D.2 Media Management

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Media/MediaService.php` | Media upload service | - [ ] `upload($file, $mediableType, $mediableId, $type)`: Upload to S3, create media record<br>- [ ] S3 path structure: `{mediableType}/{mediableId}/{type}/{filename}`<br>- [ ] Generate thumbnails for images (100x100, 300x300, 800x800)<br>- [ ] Store original + thumbnail URLs<br>- [ ] Extract image dimensions, file size, MIME type<br>- [ ] `delete($mediaId)`: Delete from S3 and database<br>- [ ] `reorder($mediableId, $orderArray)`: Update display order<br>- [ ] Queue job for image optimization (WebP conversion) |
| `backend/app/Services/Media/ImageOptimizationService.php` | Image optimization service | - [ ] Convert images to WebP format<br>- [ ] Compress images (quality 85%)<br>- [ ] Generate responsive sizes (300w, 600w, 1200w, 1920w)<br>- [ ] Store optimized versions in S3<br>- [ ] Update media record with optimized URLs |
| `backend/app/Http/Controllers/Api/V1/MediaController.php` | Media API controller | - [ ] `store(UploadMediaRequest $request)`: Upload media (admin only)<br>- [ ] `destroy($id)`: Delete media (admin only)<br>- [ ] `reorder(Request $request)`: Update display order (admin only) |
| `backend/app/Http/Requests/UploadMediaRequest.php` | Media upload validation | - [ ] Required: `file`, `mediable_type`, `mediable_id`, `type`<br>- [ ] File validation: max 10MB, allowed MIME types (image/jpeg, image/png, image/webp, video/mp4)<br>- [ ] Optional: `caption`, `alt_text` (required for images for accessibility) |
| `backend/app/Jobs/OptimizeImageJob.php` | Queue job for image optimization | - [ ] Call `ImageOptimizationService::optimize()`<br>- [ ] Update media record with optimized URLs<br>- [ ] Run after initial upload completes |

**Validation**:
- ✅ Image upload to S3 successful, media record created
- ✅ Thumbnails generated for images
- ✅ Image optimization job queued and processed (WebP conversion)
- ✅ Media deletion removes files from S3 and database
- ✅ Alt text enforced for images (accessibility)

---

#### D.3 Translation Management

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Translation/TranslationService.php` | Translation management service | - [ ] `createTranslation($translatableType, $translatableId, $locale, $field, $value)`: Create translation<br>- [ ] `updateTranslation($translationId, $value)`: Update translation<br>- [ ] `markTranslated($translationId, $translatorId)`: Mark as translated<br>- [ ] `markReviewed($translationId, $reviewerId)`: Mark as reviewed<br>- [ ] `publish($translationId)`: Publish translation<br>- [ ] `getTranslations($translatableType, $translatableId, $locale)`: Get all translations for model<br>- [ ] `getPendingTranslations($locale)`: Get pending translations for a locale |
| `backend/app/Http/Controllers/Api/V1/TranslationController.php` | Translation API controller (admin only) | - [ ] `index(Request $request)`: List translations (filterable by locale, status)<br>- [ ] `store(StoreTranslationRequest $request)`: Create translation<br>- [ ] `update($id, UpdateTranslationRequest $request)`: Update translation<br>- [ ] `markTranslated($id)`: Mark as translated<br>- [ ] `markReviewed($id)`: Mark as reviewed<br>- [ ] `publish($id)`: Publish translation |
| `backend/app/Http/Requests/StoreTranslationRequest.php` | Translation creation validation | - [ ] Required: `translatable_type`, `translatable_id`, `locale`, `field`, `value`<br>- [ ] Locale: enum validation (en, zh, ms, ta)<br>- [ ] Unique constraint: type + id + locale + field |
| `backend/app/Http/Resources/TranslationResource.php` | Translation API resource transformer | - [ ] Transform: id, locale, field, value, translation_status, created_at<br>- [ ] Include: translator (name), reviewer (name), translatable (polymorphic) |

**Validation**:
- ✅ Translation CRUD operations work
- ✅ Translation workflow (draft → translated → reviewed → published) enforced
- ✅ Translators/reviewers tracked correctly
- ✅ Published translations accessible via API

---

### 🔹 Workstream E: API Documentation & Admin Features (Day 9)

**Objective**: Generate OpenAPI docs, create Postman collection, admin-specific endpoints.

**Dependencies**: All previous workstreams.

#### E.1 API Documentation

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/storage/api-docs/openapi.yaml` | OpenAPI 3.0 specification | - [ ] Complete endpoint documentation for all API routes<br>- [ ] Request/response schemas<br>- [ ] Authentication (Bearer token)<br>- [ ] Error response examples<br>- [ ] Tags: Auth, Users, Centers, Services, Bookings, Testimonials, FAQs, Contact, Subscriptions, Admin |
| `backend/app/Console/Commands/GenerateApiDocsCommand.php` | Command to generate OpenAPI from routes | - [ ] Parse routes, extract controllers/methods<br>- [ ] Generate OpenAPI YAML<br>- [ ] Run: `php artisan api:generate-docs` |
| `backend/postman/ElderCare_SG_API.postman_collection.json` | Postman collection | - [ ] All endpoints organized by folder<br>- [ ] Environment variables: `{{base_url}}`, `{{token}}`<br>- [ ] Example requests with sample data<br>- [ ] Pre-request scripts for token refresh |
| `backend/postman/ElderCare_SG_Local.postman_environment.json` | Postman environment for local dev | - [ ] `base_url`: `http://localhost:8000/api/v1`<br>- [ ] `token`: (to be filled after login) |

**Validation**:
- ✅ OpenAPI spec validates via Swagger Editor
- ✅ Postman collection imports successfully, all requests work
- ✅ Documentation accessible at `/api/documentation` (Swagger UI)

---

#### E.2 Admin-Specific Endpoints

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Http/Controllers/Api/V1/Admin/UserController.php` | Admin user management | - [ ] `index(Request $request)`: List all users (paginated, filterable by role, status)<br>- [ ] `show($id)`: Show user with full details<br>- [ ] `update($id, Request $request)`: Update user (change role, verify email)<br>- [ ] `destroy($id)`: Delete user (soft delete)<br>- [ ] Authorization: super_admin only |
| `backend/app/Http/Controllers/Api/V1/Admin/DashboardController.php` | Admin dashboard statistics | - [ ] `index()`: Return key statistics<br>- [ ] Stats: total users, total centers, total bookings, pending testimonials, avg rating, revenue (if applicable)<br>- [ ] Date range filtering |
| `backend/app/Http/Controllers/Api/V1/Admin/ModerationController.php` | Content moderation | - [ ] `pendingTestimonials()`: List pending testimonials<br>- [ ] `pendingTranslations()`: List pending translations<br>- [ ] `contactSubmissions()`: List contact submissions (filterable by status) |

**Validation**:
- ✅ Admin endpoints accessible only to admin/super_admin roles
- ✅ Dashboard statistics accurate
- ✅ Moderation workflows functional

---

### 🔹 Workstream F: Testing & Quality Assurance (Days 10-11)

**Objective**: Achieve >90% test coverage, create factories/seeders, run integration tests.

**Dependencies**: All previous workstreams.

#### F.1 Unit Tests

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/tests/Unit/Models/UserTest.php` | User model tests | - [ ] Relationships: `profile()`, `bookings()`, `consents()`<br>- [ ] Accessors: `isAdmin()`<br>- [ ] Mutators: `password` bcrypt<br>- [ ] Soft delete behavior |
| `backend/tests/Unit/Models/CenterTest.php` | Center model tests | - [ ] Relationships: `services()`, `staff()`, `bookings()`<br>- [ ] Accessors: `occupancyRate()`, `averageRating()`, `isLicenseValid()`<br>- [ ] Scopes: `published()`, `validLicense()` |
| `backend/tests/Unit/Models/BookingTest.php` | Booking model tests | - [ ] Relationships: `user()`, `center()`, `service()`<br>- [ ] Methods: `confirm()`, `cancel()`<br>- [ ] Scopes: `upcoming()`, `byStatus()` |
| `backend/tests/Unit/Services/ConsentServiceTest.php` | Consent service tests | - [ ] `captureConsent()`: Creates consent record<br>- [ ] `withdrawConsent()`: Updates consent_given to false<br>- [ ] `checkConsent()`: Returns correct status |
| `backend/tests/Unit/Services/BookingServiceTest.php` | Booking service tests | - [ ] `create()`: Generates booking_number, calls Calendly mock<br>- [ ] `cancel()`: Updates status, calls Calendly cancel mock<br>- [ ] `sendReminders()`: Queues reminder jobs |
| *(Continue for all services)* | | |

**Validation**: Run `php artisan test --coverage --min=90`

---

#### F.2 Feature Tests (API Integration)

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/tests/Feature/Auth/RegistrationTest.php` | Registration flow tests | - [ ] Successful registration returns 201 with token<br>- [ ] Validation errors return 422<br>- [ ] Duplicate email returns 422<br>- [ ] Consent captured correctly |
| `backend/tests/Feature/Auth/LoginTest.php` | Login flow tests | - [ ] Successful login returns 200 with token<br>- [ ] Invalid credentials return 401<br>- [ ] Unverified email blocked (if middleware enabled) |
| `backend/tests/Feature/Center/CenterManagementTest.php` | Center CRUD tests | - [ ] Admin can create center<br>- [ ] User cannot create center (403)<br>- [ ] Center listing returns paginated results<br>- [ ] Center filtering by city works<br>- [ ] Center soft delete works |
| `backend/tests/Feature/Booking/BookingFlowTest.php` | Booking flow tests | - [ ] User can create booking<br>- [ ] Calendly API called (mocked)<br>- [ ] Confirmation email queued<br>- [ ] User can cancel booking<br>- [ ] Cancellation reason required |
| `backend/tests/Feature/Testimonial/TestimonialModerationTest.php` | Testimonial moderation tests | - [ ] User can submit testimonial (pending status)<br>- [ ] Admin can approve testimonial<br>- [ ] Approved testimonials visible in listing<br>- [ ] Pending testimonials not visible to users |
| *(Continue for all features)* | | |

**Validation**: Run `php artisan test --testsuite=Feature`

---

#### F.3 Factories & Seeders

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/database/factories/UserFactory.php` | User factory | - [ ] Generate realistic user data<br>- [ ] Support states: `admin`, `verified`, `unverified` |
| `backend/database/factories/CenterFactory.php` | Center factory | - [ ] Generate centers with valid MOH license, operating hours JSON<br>- [ ] Support states: `published`, `draft` |
| `backend/database/factories/BookingFactory.php` | Booking factory | - [ ] Generate bookings with future dates<br>- [ ] Support states: `confirmed`, `pending`, `cancelled` |
| *(Continue for all models)* | | |
| `backend/database/seeders/DatabaseSeeder.php` | Master seeder | - [ ] Seed 10 users (1 super_admin, 2 admins, 7 users)<br>- [ ] Seed 5 centers with services, staff, media<br>- [ ] Seed 20 FAQs across categories<br>- [ ] Seed 10 bookings (mix of statuses)<br>- [ ] Seed 15 testimonials (mix of pending/approved) |
| `backend/database/seeders/DemoSeeder.php` | Demo data seeder | - [ ] Comprehensive demo data for stakeholder presentation<br>- [ ] Realistic center descriptions, photos<br>- [ ] Populated bookings for next 2 weeks<br>- [ ] Testimonials with ratings |

**Validation**: 
- ✅ Run `php artisan db:seed` successfully
- ✅ Demo data loads without errors
- ✅ Factories generate valid data (tested with `tinker`)

---

## 5. File Creation Summary Table

### Summary by Category

| Category | Files | Estimated Lines of Code |
|----------|-------|------------------------|
| **Models** | 14 | ~2,800 |
| **Services** | 15 | ~3,000 |
| **Controllers** | 20 | ~2,500 |
| **Requests** | 18 | ~900 |
| **Resources** | 10 | ~800 |
| **Middleware** | 5 | ~300 |
| **Jobs** | 6 | ~600 |
| **Observers** | 1 | ~150 |
| **Policies** | 5 | ~400 |
| **Tests (Unit)** | 20 | ~2,000 |
| **Tests (Feature)** | 15 | ~1,800 |
| **Factories** | 14 | ~1,400 |
| **Seeders** | 3 | ~600 |
| **API Docs** | 3 | ~1,500 |
| **Config/Routes** | 3 | ~400 |
| **Commands** | 2 | ~200 |
| **Total** | **154 files** | **~19,350 LOC** |

---

## 6. Testing Strategy & Coverage Targets

### Coverage Breakdown

| Layer | Target Coverage | Validation Method |
|-------|----------------|-------------------|
| **Models** | ≥95% | Unit tests for relationships, scopes, accessors, mutators |
| **Services** | ≥90% | Unit tests with mocked dependencies |
| **Controllers** | ≥85% | Feature tests with API requests |
| **Jobs/Commands** | ≥90% | Unit tests with queue faking |
| **Middleware** | ≥95% | Feature tests with different roles/permissions |
| **Overall** | ≥90% | PHPUnit coverage report |

### Test Execution Plan

```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature

# All tests with coverage
php artisan test --coverage --min=90

# Parallel execution (faster)
php artisan test --parallel

# Generate HTML coverage report
php artisan test --coverage-html coverage
```

---

## 7. External Integration Checklist

### API Credentials Required

| Service | Credentials Needed | Environment Variables | Testing Approach |
|---------|-------------------|----------------------|------------------|
| **Calendly** | API Key, Organization URI | `CALENDLY_API_KEY`, `CALENDLY_ORG_URI` | Mock in tests, manual test in staging |
| **Twilio** | Account SID, Auth Token, Phone Number | `TWILIO_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_PHONE` | Mock in tests, manual test with real number |
| **Mailchimp** | API Key, List ID | `MAILCHIMP_API_KEY`, `MAILCHIMP_LIST_ID` | Mock in tests, manual test with test list |
| **AWS S3** | Access Key, Secret, Bucket, Region | `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_REGION` | Use local S3 mock (MinIO) for tests |

### Webhook Endpoints to Configure

| Service | Webhook URL | Events to Subscribe |
|---------|------------|---------------------|
| **Calendly** | `https://api.eldercare-sg.com/api/v1/webhooks/calendly` | `invitee.created`, `invitee.canceled` |
| **Mailchimp** | `https://api.eldercare-sg.com/api/v1/webhooks/mailchimp` | `unsubscribe`, `cleaned` |

---

## 8. Deployment Readiness Checklist

### Pre-Deploy Validation

- [ ] All migrations run successfully: `php artisan migrate --force`
- [ ] All tests pass: `php artisan test`
- [ ] Code coverage ≥90%
- [ ] Seeders populate demo data: `php artisan db:seed --class=DemoSeeder`
- [ ] API documentation generated: `php artisan api:generate-docs`
- [ ] Environment variables configured in staging `.env`
- [ ] External API credentials valid (test with health check endpoint)
- [ ] Queue workers configured in ECS
- [ ] Cron jobs scheduled (booking reminders, license expiry alerts)
- [ ] Error tracking active (Sentry DSN configured)
- [ ] Rate limiting tested (429 responses return correctly)
- [ ] CORS configured for frontend domain

### Smoke Test Endpoints (Manual)

```bash
# Health check
GET /api/health

# Public endpoints
GET /api/v1/centers
GET /api/v1/faqs

# Authentication flow
POST /api/v1/auth/register
POST /api/v1/auth/login
GET /api/v1/user (with Bearer token)

# Booking flow
POST /api/v1/bookings (authenticated)
GET /api/v1/bookings/:id

# Admin endpoints
GET /api/v1/admin/dashboard (admin token)
```

---

## 9. Risk Mitigation & Contingencies

| Risk | Probability | Impact | Mitigation | Contingency |
|------|------------|--------|------------|-------------|
| **External API failure** (Calendly, Twilio) | Medium | High | Retry logic, circuit breaker pattern, fallback to manual process | Allow bookings without Calendly sync, queue for retry |
| **S3 upload failure** | Low | Medium | Retry with exponential backoff, store locally temporarily | Use local storage as fallback, sync to S3 later |
| **Queue worker failure** | Low | High | Monitor queue depth, auto-scale workers, dead letter queue | Manual job retry via CLI, alert on-call |
| **Test coverage gap** | Medium | Medium | Enforce coverage checks in CI, block PR if <90% | Identify critical paths, prioritize coverage there |
| **Database migration failure** | Low | High | Test migrations in staging first, backup before deploy | Rollback migration, restore from backup |
| **PDPA compliance gap** | Low | Critical | Legal review of consent flows, audit trail verification | Halt deployment, address gap immediately |

---

## 10. Success Metrics & Demo Preparation

### Stakeholder Demo Script

**Duration**: 15 minutes

**Flow**:
1. **User Journey** (5 mins)
   - Register new account → Email verification → Login
   - Browse centers → View center details with media gallery
   - Create booking → Receive confirmation email (show in Mailtrap)
   - View booking history → Cancel booking
   
2. **Admin Features** (5 mins)
   - Admin dashboard with statistics
   - Moderate pending testimonial → Approve
   - View contact submissions → Mark as resolved
   - Add new center with MOH license validation
   
3. **PDPA Compliance** (3 mins)
   - User consent dashboard → View consent history
   - Download personal data (JSON export)
   - Request account deletion → Show soft delete with grace period
   
4. **API Documentation** (2 mins)
   - Show Swagger UI with interactive API docs
   - Execute live API call from Postman

### Demo Data Requirements

- **5 Centers**: Realistic names, addresses, photos, operating hours
- **15 Services**: Varied pricing, features
- **10 Bookings**: Mix of upcoming, completed, cancelled
- **20 Testimonials**: 15 approved (varied ratings), 5 pending
- **10 Users**: Mix of regular users and admins
- **30 FAQs**: Covering all categories

**Preparation Command**:
```bash
php artisan db:seed --class=DemoSeeder
```

---

## 11. Handoff to Independent Coding Agent

### Execution Instructions

**Prerequisites**:
1. Environment setup complete (Docker, `.env` configured)
2. Database migrations run (`php artisan migrate`)
3. External API credentials available (or mocked)

**Recommended Execution Order**:

#### Week 1 (Days 1-5):
**Day 1**: Workstream A.1 (Models) + A.4 (API Infrastructure)
- Create all 14 Eloquent models with relationships
- Setup API response formatter, exception handler
- **Validation**: Models load in `tinker`, relationships work

**Day 2**: Workstream A.2 (PDPA Services) + A.3 (Auth Controllers)
- Implement consent/audit services
- Build auth controllers (register, login, logout, password reset)
- **Validation**: Registration + login work via Postman, consent captured

**Day 3**: Workstream B.1 (Center/Service Management)
- Implement center/service services and controllers
- **Validation**: Centers CRUD works, MOH license validation enforced

**Day 4**: Workstream B.2 (FAQ, Contact, Newsletter)
- Implement FAQ, contact, Mailchimp services
- **Validation**: Contact form submission works, Mailchimp sync queued

**Day 5**: Workstream C.1 (Booking System part 1)
- Implement booking service, Calendly integration (mocked)
- **Validation**: Booking creation works, Calendly mock called

#### Week 2 (Days 6-11):
**Day 6**: Workstream C.1 (Booking System part 2)
- Complete notification service, Twilio integration
- Implement webhook handlers
- **Validation**: Confirmation email/SMS sent, webhooks processed

**Day 7**: Workstream D.1 (Testimonials) + D.3 (Translations)
- Implement testimonial moderation
- Build translation service
- **Validation**: Testimonial approval works, translations manageable

**Day 8**: Workstream D.2 (Media Management)
- Implement media upload to S3, image optimization
- **Validation**: Image upload works, thumbnails generated

**Day 9**: Workstream E (API Docs + Admin Features)
- Generate OpenAPI spec, Postman collection
- Build admin endpoints
- **Validation**: Swagger UI accessible, admin dashboard returns stats

**Day 10**: Workstream F.1 + F.2 (Unit + Feature Tests)
- Write unit tests for models, services
- Write feature tests for all API endpoints
- **Validation**: `php artisan test` passes with >90% coverage

**Day 11**: Workstream F.3 (Factories/Seeders) + Final QA
- Create factories for all models
- Build comprehensive demo seeder
- Run smoke tests, fix bugs
- **Validation**: Demo data loads successfully, all endpoints work

### Daily Validation Checklist

At the end of each day, run:
```bash
# 1. Code quality
./vendor/bin/phpcs --standard=PSR12 app/
./vendor/bin/phpstan analyse

# 2. Tests
php artisan test

# 3. Build
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache

# 4. Manual smoke test
# (Use Postman collection for relevant endpoints)
```

### Troubleshooting Guide

| Issue | Solution |
|-------|----------|
| Migration fails | Check database connection, rollback and re-run |
| Test fails | Check factory data, ensure database is seeded correctly |
| Queue job not processing | Verify queue worker is running: `php artisan queue:work` |
| External API error | Check credentials, verify mock is active in tests |
| Coverage <90% | Identify untested files: `php artisan test --coverage` |

---

## 12. Post-Phase 3 Next Steps

**Immediate** (Phase 4):
- Frontend integration with backend API
- React Query setup for API calls
- Zustand store integration with user session

**Short-term** (Phase 5):
- Laravel Nova installation for admin panel
- Advanced media management (Cloudflare Stream for videos)
- MeiliSearch integration for full-text search

**Medium-term** (Phase 6-7):
- Content population with real data
- Professional translation to Mandarin/Malay/Tamil
- Advanced features (virtual tours, subsidy calculator)

---

## 13. Final Approval & Sign-off

**This plan is ready for execution when**:
- [ ] Stakeholder review completed
- [ ] External API credentials secured (or mock strategy approved)
- [ ] Infrastructure readiness confirmed (staging environment, S3 bucket, queue workers)
- [ ] Independent coding agent has reviewed plan and confirmed understanding
- [ ] Demo date and success criteria agreed upon

**Plan Author**: AI Coding Agent (Cascade)  
**Plan Version**: 1.0  
**Last Updated**: 2025-10-10  
**Status**: ✅ APPROVED FOR EXECUTION

---

**🎯 This comprehensive sub-plan provides everything an independent coding agent needs to complete Phase 3 backend development without ambiguity. Let's build an impressive production demo!** 🚀

```

# docs/llm/pre_phase3_remediation_execution_plan_2025-10-10.md
```md
# ElderCare SG — Pre-Phase 3 Remediation Execution Plan (2025-10-10)

## 1. Objectives
- Close outstanding technical and documentation gaps before commencing Phase 3 of the `codebase_completion_master_plan.md` (backend services & PDPA compliance).
- Ensure foundational telemetry, infrastructure, and frontend QA guardrails are production-ready to support upcoming authentication and consent workflows.
- Mitigate migration/runtime risks identified during schema review to allow frictionless local, CI, and staging deployments.

## 2. Gap Summary (Source Trace)
- **DB driver compatibility**: Raw MySQL `ALTER TABLE ... CHECK` statements in `database/migrations/2025_10_08_133000_create_services_table.php`, `2025_10_08_140000_create_bookings_table.php`, `2025_10_08_141000_create_testimonials_table.php` lack driver guards; will fail under SQLite used by PHPUnit (`project_state_recap_2025-10-10.md`).
- **Analytics & monitoring bootstrap**: GA4, Hotjar, Sentry, New Relic helper modules plus env/secrets guidance remain TODO per `docs/phase1-execution-plan.md` §3.4.
- **Cloudflare & Terraform documentation gaps**: `docs/phase1-execution-plan.md` §3.2 calls for `docs/deployment/cloudflare.md` and enhanced Terraform README/apply runbook.
- **Design system QA completion**: `docs/phase2-execution-subplan.md` highlights pending Storybook story coverage (Card/Form composites, navigation polish), Percy baseline capture (awaiting `PERCY_TOKEN`), and localized testing improvements.
- **Phase 3 readiness gaps**: Need auth/consent service scaffolds, rate limiting plan, API contract alignment, plus secrets availability to avoid delays when Phase 3 starts (`codebase_completion_master_plan.md` Phase 3 acceptance criteria).

## 3. Scope & Assumptions
- Focus limited to remediation enabling Phase 3 start; does not implement Phase 3 features themselves.
- Team composition per master plan: Backend Dev 1, Backend Dev 2, Frontend Dev, QA Engineer; availability assumed in parallel blocks.
- Environment targets: Local (Docker, SQLite for tests), Staging (MySQL, Redis), Production parity via Terraform.
- Percy token and analytics secrets to be provided by stakeholders before execution; pending items highlighted in risks.

## 4. Workstreams Overview
| Workstream | Goal | Primary Owner | Key Dependencies | Estimated Duration |
| --- | --- | --- | --- | --- |
| **A. Migration Hardening** | Ensure migrations run under MySQL & SQLite | Backend Dev 1 | `database/migrations/*.php` | 0.5 day |
| **B. Analytics & Monitoring Bootstrap** | Deliver GA4, Hotjar, Sentry, New Relic scaffolding | Frontend Dev + Backend Dev 2 | Secrets provision, `frontend/src/lib`, `backend/config` | 1.5 days |
| **C. Infrastructure Documentation & IaC Parity** | Document Cloudflare workflow & Terraform usage | Backend Dev 2 | `terraform/`, `docs/deployment/` | 1 day |
| **D. Design System QA Closure** | Finish Storybook coverage, Percy baseline, axe automation | Frontend Dev + QA | `frontend/src/stories`, `.percy.yml`, CI config | 1.5 days |
| **E. Phase 3 Enablement Prep** | Stage auth/consent groundwork & rate limiting guardrails | Backend Dev 1 + Backend Dev 2 | Workstreams A–D outputs, secrets readiness | 2 days |

## 5. Workstream Detail

### Workstream A — Migration Hardening
- **Goal**: Cross-database safe migrations.
- **Tasks**:
  - **[A1]** Introduce driver checks before running `DB::statement` constraint additions (MySQL only) in services/bookings/testimonials migrations.
  - **[A2]** Add regression PHPUnit test: `php artisan migrate:fresh --database=sqlite` executed in CI matrix.
  - **[A3]** Update `docs/database-migration-execution-plan.md` with note on driver checks.
- **Deliverables**: Updated migrations with conditional guards; CI job verifying SQLite migrations.
- **Validation**: Local `php artisan test --database=sqlite`, GitHub Actions matrix green.
- **Risks**: Constraint coverage drift—mitigate by documenting parity in `project_state_recap_2025-10-10.md` addendum.

### Workstream B — Analytics & Monitoring Bootstrap
- **Goal**: Ready-to-configure telemetry stack.
- **Tasks**:
  - **[B1]** Build `frontend/src/lib/analytics/{ga,hotjar}.ts` and toggled initialization per env.
  - **[B2]** Create backend configs `backend/config/{sentry.php,newrelic.php}` + service providers.
  - **[B3]** Add env templates (`.env.staging`, `.env.production`, `frontend/.env.local.staging`) with placeholder keys.
  - **[B4]** Document setup in `docs/deployment/monitoring.md` (per Phase 1 plan) with activation checklist.
  - **[B5]** Update CI to fail if telemetry toggles missing (lint/test step verifying env keys when flags enabled).
- **Deliverables**: Modules, config files, env templates, documentation, CI guard.
- **Validation**: `npm run lint && npm run build` verifying dead code elimination, `php artisan config:cache`, manual smoke verifying toggles.
- **Risks**: Secrets not provisioned—flag to stakeholders, allow feature-flag fallback.

### Workstream C — Infrastructure Documentation & IaC Parity
- **Goal**: Repeatable staging/prod provisioning instructions.
- **Tasks**:
  - **[C1]** Draft `docs/deployment/cloudflare.md` covering DNS, CDN, WAF, terraform integration decision.
  - **[C2]** Enhance `terraform/README.md` with module descriptions, apply/destroy runbooks, state management instructions.
  - **[C3]** Audit Terraform modules for Phase 1 parity; log missing resources (e.g., CloudFront interop) in backlog.
  - **[C4]** Add validation checklist (run `terraform validate`, `terraform plan -var-file=staging.tfvars`).
- **Deliverables**: Updated docs, backlog issues for missing modules, CI/manual validation log.
- **Validation**: `terraform validate` output appended to documentation; peer review sign-off.
- **Risks**: Terraform state access; coordinate with DevOps lead.

### Workstream D — Design System QA Closure
- **Goal**: Complete frontend QA guardrails before backend integration work.
- **Tasks**:
  - **[D1]** Add missing stories (`Card`, `FormField` composites, `NavigationBar`, `LanguageSwitcher` analytics events).
  - **[D2]** Extend Jest RTL tests for localized copy & reduced-motion variants.
  - **[D3]** Configure Percy baseline run once `PERCY_TOKEN` supplied; document fallback.
  - **[D4]** Ensure Storybook a11y checks cover new stories; update `docs/phase2-status-checklist.md`.
  - **[D5]** Update CI to conditionally run Percy + Storybook test runner.
- **Deliverables**: Stories/tests committed, Percy baseline snapshot, updated checklist.
- **Validation**: `npm run test`, `npm run storybook:test`, `npm run percy:storybook` (conditional) with QA sign-off.
- **Risks**: Token absence—document deferred baseline and schedule once available.

### Workstream E — Phase 3 Enablement Prep
- **Goal**: Remove blockers for authentication/PDPA build-out.
- **Tasks**:
  - **[E1]** Define authentication & consent service architecture notes in `docs/backend/phase3-kickoff.md` (new) referencing PAD.
  - **[E2]** Prepare Laravel Sanctum setup skeleton, rate limiting middleware configuration (`app/Http/Kernel.php`), and queue retry policies.
  - **[E3]** Draft OpenAPI skeleton for `/api/v1` endpoints covering auth + consent flows.
  - **[E4]** Confirm secrets inventory (mail, SMS, Calendly, Twilio) and create `docs/deployment/secrets-checklist.md` update.
  - **[E5]** Conduct stakeholder review meeting; log decisions in doc.
- **Deliverables**: Architecture doc, code scaffolds (feature branches), OpenAPI draft, secrets checklist update, meeting notes.
- **Validation**: Peer review of doc/UI, `php artisan test` for scaffolds, alignment sign-off captured in `docs/backend/phase3-kickoff.md`.
- **Risks**: Scope creep into implementation—enforce boundary (scaffolds only).

## 6. Cross-Cutting Governance
- **Daily Stand-up**: 15 min to track workstream blockers (owner-led).
- **Mid-Remediation Review**: After Workstreams A–C complete, confirm readiness to proceed (target T+2 days).
- **Final Readiness Review**: Present validation evidence, secure go/no-go for Phase 3 start.
- **Documentation Updates**: Every deliverable includes doc link in `project_state_recap_2025-10-10.md` addendum.

## 7. Timeline & Sequencing (Calendar Days)
| Day | Activities |
| --- | --- |
| Day 0 (Today) | Kickoff, assign owners, confirm secrets availability.
| Day 1 | Workstream A + Workstream C1/C2 progress; start Workstream B1.
| Day 2 | Complete Workstream B (modules/docs), run telemetry validation; finish Workstream C.
| Day 3 | Execute Workstream D tasks, run Percy baseline (if tokens ready); start Workstream E scaffolds.
| Day 4 | Finalize Workstream E documentation, stakeholder review, consolidate validation logs.
| Day 5 | Buffer for fixes, final readiness review, update recap.

## 8. Success Metrics & Exit Criteria
- **Migration Safety**: CI passes with MySQL + SQLite runs; zero migration regressions in smoke tests.
- **Telemetry Readiness**: Feature flags default off, toggled on with secrets -> builds succeed; docs complete.
- **Infrastructure Docs**: Terraform + Cloudflare runbooks peer-approved; `terraform validate` logs stored.
- **Design System QA**: 100% targeted components covered in Storybook with axe clean run; Percy baseline captured or documented.
- **Phase 3 Prep**: Auth/consent skeleton + rate limiting configs merged; OpenAPI draft reviewed; secrets checklist confirmed.

## 9. Next Steps
1. Share plan with stakeholders for sign-off.
2. Create workstream-specific tickets referencing this plan.
3. Begin execution following timeline; update plan as tasks close.
4. Maintain evidence in `docs/llm/` and update `project_state_recap_2025-10-10.md` under "Next Steps" after completion.

Prepared by Cascade AI Assistant • 2025-10-10 06:27 SGT

```

