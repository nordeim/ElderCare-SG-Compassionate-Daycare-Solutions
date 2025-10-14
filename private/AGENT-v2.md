AGENT.md v1.2 — ElderCare SG Web Platform
Version: 1.2
Last Updated: 2025-01-XX (Current)
Status: Phases 1-3 Backend Complete | Alpha Integration Phase
Primary Source Documents: Project_Architecture_Document.md v2.1, Project_Requirements_Document.md, codebase_completion_master_plan.md, README.md, docs/accessibility/, docs/design-system/, docs/deployment/

Purpose: This guide is the single source of truth for any AI coding agent (and their human facilitators) to understand the ElderCare SG architecture, delivery standards, and operational guardrails before touching the codebase.

✅ CURRENT PROJECT STATUS
Phase	Status	Deliverables
Phase 1: Foundation	✅ COMPLETE	Infrastructure, Docker environment, CI/CD pipeline, database schema, core migrations
Phase 2: Design System	✅ COMPLETE	Component library (Storybook), i18n framework (EN/ZH), design tokens, accessibility baseline
Phase 3: Backend Services	✅ COMPLETE	Authentication APIs, Booking APIs, Center APIs, Calendly integration, Audit logging, PDPA compliance infrastructure
Current: Alpha Integration	🚧 IN PROGRESS	Frontend ↔ Backend integration, content management workflows, virtual tours, production deployment preparation
Test Baseline (Recorded): 90 backend tests, 216 assertions, 59 PHPUnit deprecation warnings (technical debt tracked)

⚠️ RESOLVED CONFLICTS & CLARIFICATIONS
Previous conflicts from source documents have been resolved using the authoritative README.md:

Topic	Previous Conflict	RESOLVED (v1.2)	Authority
Container Orchestration	"ECS Fargate" vs "Kubernetes"	✅ Kubernetes for production; Docker Compose for local dev	README.md Tech Stack
Search Infrastructure	"MeiliSearch" vs "Elasticsearch"	✅ Elasticsearch 8	README.md Tech Stack
Project Phase	"Foundation hardening" vs "Pre-Phase 3"	✅ Phases 1-3 Complete; Alpha Integration ongoing	README.md + user confirmation
Database	"MySQL" vs "MariaDB/MySQL"	✅ MySQL 8.0	README.md + PAD (unanimous)
Table of Contents
Executive Summary
System Overview
Frontend Blueprint
Backend Blueprint
Data & Integrations
Operational Maturity
Security & Compliance
Performance & Scalability Playbook
Accessibility & Internationalization
Testing & Quality Assurance
Risk Register & Mitigations
Lifecycle & Roadmap
Development Workflows
Platform-Specific Guidance
Quickstart Checklist for AI Agents
Change Log & Maintenance
1. Executive Summary
1.1 Product Mission
Deliver a compassionate, accessibility-first digital bridge between Singaporean families and trusted elderly daycare services.

1.2 Primary Outcomes
Empower informed decisions through transparent facility information and authentic testimonials
Build trust via verified MOH licenses, staff credentials, and rigorous compliance
Enhance quality of life for seniors and caregivers across Singapore's multicultural landscape
1.3 Target Personas
Persona	Age Range	Key Needs	Design Considerations
Adult Children	30-55	Time-poor professionals; mobile-first; demand deep insight and social proof	Concise information architecture, quick booking flows
Family Caregivers	25-45	Often domestic helpers; require multilingual content and transport clarity	Multilingual UI/content, transit guidance, simplified navigation
Healthcare Professionals	30-60	Need quick license/capability verification for referrals	Advanced search filters, credential badges, exportable data
Digitally Literate Seniors	55+	Prefer larger fonts, high contrast, straightforward navigation	Adjustable typography, high-contrast theme (Phase 2), reduced motion
1.4 Core Value Pillars
Trust & Transparency: Verified MOH licenses, staff credentials, authentic reviews
Accessibility: WCAG 2.1 AA compliance, keyboard navigation, screen reader optimization
Cultural Resonance: Multicultural imagery, respectful language, holiday-aware content, honorific templates
Seamless Engagement: Frictionless journey from discovery to booking with proactive reminders
Compliance: PDPA data residency, MOH display requirements, IMDA accessibility guidelines
1.5 Success Metrics (North Star)
Metric	Target	Measurement	Owner	Review Cadence
Visit bookings increase	+30% in 3 months	GA4 conversions + booking DB	Marketing	Monthly
Mobile bounce rate	<40%	GA4 device segmentation	UX	Bi-weekly
Lighthouse Performance	>90	Lighthouse CI (staging & prod)	DevOps	Each deploy
Lighthouse Accessibility	>90	Lighthouse CI + axe-core	QA	Each deploy
Session duration	>5 minutes	GA4 engagement metrics	Marketing	Monthly
Form completion	>75%	Hotjar form analytics	UX	Monthly
3G load time	<3 seconds	WebPageTest (Singapore)	DevOps	Weekly
Video engagement	>60% completion	Cloudflare Stream analytics	Marketing	Monthly
Core Web Vitals	Pass thresholds	TTFB <300ms, FCP <1.5s, LCP <2.5s, TTI <3s	DevOps	Each deploy
1.6 Operational Constraints
Availability SLA: 99.5% uptime
Team Size: 6-8 (development, QA, DevOps)
Browser Support: Latest 2 major versions (Chrome, Firefox, Safari, Edge)
Mobile Traffic Forecast: 70% (mobile-first design mandatory)
Regulatory Compliance: PDPA (data residency in Singapore), MOH eldercare guidelines, WCAG 2.1 AA
1.7 Out of Scope (Current Release)
To maintain focus, the following are explicitly excluded from the current roadmap:

Native mobile apps (iOS/Android)
Payment processing (future Phase 4)
Caregiver matching algorithms
Medical record integration
Telehealth/virtual consultations
Live chat support
Provider self-service portals
Family shared accounts
Government database integrations
2. System Overview
2.1 Architecture Pattern
Service-oriented monolith (Laravel) with modular domain boundaries, designed for eventual microservices extraction when traffic exceeds 100K MAU.

2.2 Hosting Footprint
Environment	Infrastructure	Database	Caching	Orchestration
Local	Docker Compose	MySQL 8.0 (container)	Redis 7 (container)	Docker Compose
Staging	AWS (Singapore ap-southeast-1)	RDS MySQL 8.0	ElastiCache Redis 7	Kubernetes
Production	AWS (Singapore ap-southeast-1, multi-AZ)	RDS MySQL 8.0 (primary + read replica)	ElastiCache Redis 7 (cluster mode)	Kubernetes
CDN & Security: Cloudflare (Singapore edge, WAF, DDoS mitigation)
Infrastructure as Code: Terraform modules manage VPC, Kubernetes clusters, RDS, ElastiCache, S3, IAM, Secrets Manager
Observability: Sentry (errors), New Relic (APM), CloudWatch (metrics/logs), UptimeRobot (synthetic monitoring)
2.3 Key Architectural Principles
User-centric design: Progressive disclosure, skeleton loaders, fallback paths (e.g., manual contact when Calendly fails)
Accessibility first: Semantic HTML, Radix UI primitives, ARIA live regions, keyboard navigability, screen reader QA
Security by design: Layered defenses, RBAC, MFA for admins, TLS 1.3, encryption at rest (AES-256), bcrypt cost 12
Performance optimized: Page weight budgets (<280KB), React Server Components default, CDN caching, Redis application cache, async job processing
Compliance built-in: Consent ledger, audit trails, 7-year retention, right-to-access/export/delete, MOH license enforcement
Scalable & maintainable: Service-layer architecture, repository pattern, dependency injection, ≥90% test coverage mandate, ADR documentation
Cultural sensitivity: I18n-first architecture, locale-aware formatting, respectful honorifics, inclusive imagery
2.4 Logical Components
mermaid

flowchart LR
    User((Users)) -->|HTTPS| Cloudflare[Cloudflare CDN/WAF]
    Cloudflare --> NextJS[Next.js 14 Frontend]
    NextJS -->|/api/v1/*| LaravelAPI[Laravel 12 API]
    
    LaravelAPI --> MySQL[(MySQL 8.0<br/>Primary + Replica)]
    LaravelAPI --> Redis[(Redis 7<br/>Cache & Queues)]
    LaravelAPI --> Elasticsearch[(Elasticsearch 8<br/>Search Index)]
    LaravelAPI --> S3[(AWS S3<br/>Media Storage)]
    LaravelAPI -->|Jobs/Events| SQS[(SQS<br/>Queue Backend)]
    
    LaravelAPI --> Calendly{{Calendly API}}
    LaravelAPI --> Mailchimp{{Mailchimp API}}
    LaravelAPI --> Twilio{{Twilio SMS}}
    NextJS --> Stream{{Cloudflare Stream}}
    
    Cloudflare -.-> Observability[(Sentry / New Relic<br/>CloudWatch / UptimeRobot)]
    LaravelAPI -.-> Observability
2.5 Data Flow Examples
Read Path (Content Request):

User requests /en/centers/sunshine-care
Cloudflare edge checks cache → HIT: return cached HTML (5-min TTL)
MISS: Forward to Next.js
Next.js Server Component calls GET /api/v1/centers/sunshine-care?locale=en
Laravel CenterController → CenterService checks Redis cache
Redis MISS: CenterRepository queries MySQL read replica
Response cached in Redis (5-min TTL), returned to Next.js
Next.js renders HTML, caches at Cloudflare edge
Subsequent requests served from edge until TTL expires
Write Path (Booking Creation):

User submits booking form → POST /api/v1/bookings
Laravel BookingRequest validates + checks PDPA consent
BookingController delegates to BookingService
Database transaction begins:
Create bookings record (status: pending)
CalendlyService creates event via API
Update booking (status: confirmed, calendly_event_id)
Commit transaction
Domain events dispatched: BookingConfirmed
Event listeners queue jobs:
SendBookingConfirmationEmail (high priority)
SendBookingConfirmationSMS (high priority)
UpdateAnalyticsDashboard (default priority)
AuditObserver logs changes to audit_logs table
Cache invalidation: Redis tags user:{id}:bookings, center:{id}:bookings purged
Response returned to client (201 Created)
Laravel Horizon workers process queued jobs asynchronously
Key Actions for Agents

Confirm environment context before running commands (check .env, frontend/.env.local, .env.staging, .env.production.template)
Respect architecture principles when proposing modifications
Check Terraform state/variables prior to infrastructure-affecting changes
Never make manual AWS console edits; all changes via Terraform
3. Frontend Blueprint
3.1 Stack
Technology	Version	Purpose
Next.js	14 (App Router)	React framework with SSR/RSC
React	18	UI library
TypeScript	5	Type safety
Tailwind CSS	3	Styling framework (JIT, <20KB prod CSS)
Radix UI	Latest	Accessible primitives
Framer Motion	10	Animations (reduced-motion aware)
React Query	4	Server state management
Zustand	4	Global client state
next-intl	Latest	I18n framework
3.2 Directory Structure (frontend/)
text

frontend/
├── app/
│   ├── [locale]/              # Dynamic locale segment (en/zh/ms/ta)
│   │   ├── layout.tsx         # Root layout (header/footer)
│   │   ├── page.tsx           # Home (Server Component)
│   │   ├── centers/
│   │   │   ├── page.tsx       # Listing (Server Component)
│   │   │   └── [slug]/page.tsx# Detail (Server Component)
│   │   ├── services/page.tsx
│   │   ├── booking/page.tsx   # Booking flow (Client Component)
│   │   ├── about/page.tsx
│   │   ├── faq/page.tsx
│   │   └── dashboard/page.tsx # Authenticated area
│   ├── api/                   # Next.js route handlers (edge cases, proxies)
│   └── globals.css            # Tailwind base + custom utilities
├── components/
│   ├── atoms/                 # Button, Input, Icon, Badge, etc.
│   ├── molecules/             # FormField, Card, NavItem, SearchBar
│   ├── organisms/             # Header, Footer, BookingForm, ServiceCard
│   ├── templates/             # Layout wrappers
│   └── providers/             # AnalyticsProvider, AuthProvider, ThemeProvider
├── hooks/                     # useAuth, useBooking, useTranslation, useAnalytics
├── lib/                       # API client, utils, Zod schemas
├── locales/                   # en/, zh/, ms/, ta/ JSON resources
├── store/                     # Zustand persisted stores
├── tests/                     # Jest + Testing Library + Playwright
├── types/                     # Shared TypeScript definitions
├── .storybook/                # Storybook configuration
└── middleware.ts              # Locale negotiation, auth guards
3.3 Rendering Strategy
Default: React Server Components (RSC) for all pages unless interactivity requires client-side state
CSR Opt-In: Mark with 'use client' directive (booking wizard, map view, modals)
ISR: Incremental Static Regeneration for semi-static pages (center listings, FAQs) with 5-minute revalidation
Edge Caching: Cloudflare caches SSR output per locale/path (Vary: Accept-Language, Cookie)
3.4 State Management
Server State (@tanstack/react-query): Bookings, centers, testimonials, user profiles. 5-min stale time, background revalidation.
Global Client State (Zustand + persistence): Auth session, locale preference, UI toggles (theme, font size), feature flags.
Local State: Form inputs, modal visibility; degrade gracefully if JS disabled.
3.5 Asset Optimization
Images: Next.js <Image> component generates responsive WebP with JPEG fallback, LQIP (Low-Quality Image Placeholder), lazy loading.
Fonts: Self-hosted via next/font with subsetting and font-display: swap:
Inter (Latin scripts)
Noto Sans SC (Simplified Chinese)
Noto Sans (Malay, Tamil)
CSS: Tailwind JIT + PurgeCSS reduces production payload to <20KB.
JavaScript: Dynamic imports for heavy modules (map view: @react-google-maps/api, booking wizard stepper: react-hook-form).
3.6 Progressive Enhancement
Forms submit via standard POST when JS unavailable; enhanced with inline validation (Zod) and toast notifications when JS enabled.
Navigation works with plain <a> tags; Next.js <Link> adds client-side transitions opportunistically.
Critical content (center details, testimonials) always server-rendered to guarantee baseline accessibility.
3.7 Analytics & Instrumentation
Provider: AnalyticsProvider (wraps GA4, Hotjar)
Events: Custom data layer tracks booking_started, booking_completed, virtual_tour_started, newsletter_subscribed, testimonial_submitted, language_switched
Privacy: Consent banner gates non-essential analytics; IP anonymization enforced
Performance: Lighthouse CI runs on every deploy; New Relic RUM tracks Core Web Vitals
Key Actions for Agents

Adhere to design tokens documented in docs/design-system/ and Storybook
Favor Server Components; justify CSR usage in PR descriptions
Instrument new interactions with data-event attributes for analytics
Validate responsive images use Next.js <Image> with sizes attribute
Test reduced-motion support when adding Framer Motion animations
4. Backend Blueprint
4.1 Framework & Standards
Technology	Version	Standards
Laravel	12	PHP 8.2, service-layer architecture
Static Analysis	PHPStan Level 8	Enforced in CI pipeline
Testing	PHPUnit + Pest	≥90% coverage mandate for critical modules
Authentication	Laravel Sanctum	Stateful SPA auth + optional token auth
Admin Panel	Laravel Nova	US$199/year license
4.2 Directory Highlights (backend/app/)
text

backend/app/
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── Auth/
│   │   │   ├── RegisterController.php       # POST /api/v1/auth/register
│   │   │   ├── LoginController.php          # POST /api/v1/auth/login
│   │   │   └── LogoutController.php         # POST /api/v1/auth/logout
│   │   ├── BookingController.php            # CRUD /api/v1/bookings
│   │   ├── CenterController.php             # Index/Show /api/v1/centers
│   │   ├── TestimonialController.php
│   │   └── UserController.php               # Profile, data export, deletion
│   ├── Requests/Api/V1/
│   │   ├── RegisterRequest.php              # Validation + PDPA consent checks
│   │   ├── BookingRequest.php
│   │   └── TestimonialRequest.php
│   └── Resources/Api/V1/
│       ├── BookingResource.php              # JSON:API-inspired serialization
│       ├── CenterResource.php
│       └── UserResource.php
├── Services/
│   ├── AuthService.php                      # Registration, login, password resets
│   ├── BookingService.php                   # Booking lifecycle, Calendly orchestration
│   ├── CenterService.php                    # Center CRUD, MOH compliance checks
│   ├── ConsentService.php                   # PDPA consent management
│   ├── AuditService.php                     # Manual audit log creation
│   ├── NotificationService.php              # Email/SMS orchestration
│   ├── MediaService.php                     # S3 uploads, media associations
│   └── Integration/
│       ├── CalendlyService.php              # Calendly API adapter
│       ├── MailchimpService.php             # Newsletter management
│       └── TwilioService.php                # SMS notifications
├── Repositories/
│   ├── BookingRepository.php                # Data access with Redis caching
│   ├── CenterRepository.php
│   └── UserRepository.php
├── Events/
│   ├── BookingConfirmed.php
│   ├── UserRegistered.php
│   └── ConsentGiven.php
├── Listeners/
│   ├── SendBookingConfirmationEmail.php
│   ├── SendBookingConfirmationSMS.php
│   └── UpdateAnalyticsDashboard.php
├── Jobs/
│   ├── SendBookingReminderEmail.php         # Delayed jobs (72h, 24h pre-visit)
│   ├── GenerateDataExportZip.php            # PDPA right-to-access
│   └── SyncSearchIndex.php                  # Elasticsearch index updates
├── Observers/
│   └── AuditObserver.php                    # Automatic audit logging for models
├── Policies/
│   ├── BookingPolicy.php                    # Authorization (view, update, cancel)
│   ├── CenterPolicy.php
│   └── TestimonialPolicy.php
├── Traits/
│   └── Auditable.php                        # Opt-in audit logging for models
└── Exceptions/
    ├── CalendlyNotConfiguredException.php
    └── ConsentRequiredException.php
4.3 API Design
Base URL: https://api.eldercare.sg/v1/ (production), http://localhost:8000/api/v1/ (local)
Versioning: Path-based (/v1/); minimum 6-month deprecation notice; two concurrent versions supported
Authentication: Sanctum bearer tokens (stateful cookies for SPA, tokens for future mobile apps)
Pagination: ?page=1&per_page=20, response includes meta (total, per_page, current_page) and links (first, last, prev, next)
Filtering: filter[city]=Singapore&filter[has_moh_license]=true
Sorting: sort=-created_at,name (minus prefix = descending)
Rate Limiting: 60 req/min per IP (public), 1000/hour per authenticated user
Error Schema:
JSON

{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "phone": ["The phone format is invalid."]
  }
}
Documentation: OpenAPI 3.0 specification + Postman collection (see docs/api/)
4.4 Service Layer Example: BookingService
Responsibilities:

Validate business rules (24-hour cancellation policy, center capacity)
Orchestrate Calendly integration (CalendlyService)
Manage booking lifecycle (pending → confirmed → completed/cancelled)
Dispatch domain events (BookingConfirmed, BookingCancelled)
Handle failures with compensating actions (status rollback, admin alert)
Transaction Guarantees:

Database writes and Calendly API calls wrapped in transactions
On Calendly failure: rollback booking creation, throw exception with user-friendly message
Circuit breaker (future): After 3 consecutive Calendly failures, switch to manual fallback mode (email notification to center)
4.5 Queue & Job Processing
Driver: Redis (managed via Laravel Horizon)
Worker Pools: Sized per environment (2 workers local, 5 staging, 10 production)
Priority Queues:
high: SMS, critical emails (booking confirmations)
default: Transactional emails, notifications
low: Analytics sync, search index updates
Failure Handling: Exponential backoff (1m → 5m → 15m), max 3 attempts, failed jobs logged to Sentry + failed_jobs table (replayable via Nova)
Delayed Jobs: Booking reminders scheduled at booking creation (72h and 24h before visit)
4.6 Audit Logging Infrastructure (Phase 3 Completion)
Trait: Auditable (opt-in for models: User, Center, Booking, Consent, Testimonial)
Observer: AuditObserver automatically logs created, updated, deleted events to audit_logs table
Schema: Actor (user_id), action, before/after JSON hashes, IP address, user agent
Retention: 7 years (regulatory requirement for PDPA compliance)
Manual Logging: AuditService::log($action, $model, $metadata) for non-model events
Key Actions for Agents

Consult PAD Section 6 for full service responsibilities matrix
Maintain thin controllers; delegate all business logic to services
Add Auditable trait to new models handling personal data
Write feature tests for new API endpoints (PHPUnit + Laravel HTTP testing)
Update OpenAPI spec when adding/modifying endpoints
Never call external APIs directly from controllers; use service abstractions
5. Data & Integrations
5.1 Database Architecture
Specification	Value	Rationale
RDBMS	MySQL 8.0	ACID guarantees for bookings, proven reliability
Character Set	UTF8MB4	Full Unicode support (emoji, CJK characters)
Storage Engine	InnoDB	ACID compliance, foreign key support
Collation	utf8mb4_unicode_ci	Case-insensitive, multilingual sorting
Primary Environment	RDS (ap-southeast-1, multi-AZ)	High availability, automated backups
Read Scaling	Read replica (same AZ)	Offload analytics and reporting queries
Backup Retention	35 days (automated snapshots)	Compliance + disaster recovery
Cross-Region Replication	Daily snapshots → ap-northeast-1	Geographic redundancy
5.2 Schema Overview (18 Tables)
Compliance-First Design:

Polymorphic audit_logs: Tracks all changes to personal data (7-year retention)
consents table: Versioned consent text snapshots with granted/revoked timestamps
Soft deletes: deleted_at columns on users, profiles, bookings, testimonials (24-month retention before purge)
MOH compliance: centers.moh_license_number, staff.qualification_certifications (JSON), expiry date tracking
Core Entities:

users (roles: user, admin, moderator, translator; MFA flags)
profiles (address, preferred_language, accessibility_preferences JSON)
centers (MOH license, accreditation, geolocation, emergency_protocols JSON)
services (pricing, duration, staff_ratio, calendly_event_type_id)
bookings (lifecycle status, Calendly IDs, questionnaire JSON, cancellation metadata)
content_translations (polymorphic: centers, services, testimonials, FAQs)
media (S3 object references, alt text, consent flag, moderation status)
consents (user_id, consent_type, version, granted_at, revoked_at)
audit_logs (polymorphic: auditable_type/id, actor, action, before/after hashes, IP, user_agent)
Advanced Features:

Polymorphic relationships: media (morphs to centers, testimonials), content_translations (morphs to translatable models)
JSON columns: centers.operating_hours, centers.amenities, bookings.questionnaire_responses, profiles.accessibility_preferences
Indexes: Composite (center_id + status), full-text (center name, description), geospatial (latitude/longitude)
Views: center_summary_view (pre-joins services, media counts, avg ratings for fast queries)
5.3 Existing Migration Scripts
⚠️ CRITICAL: Review existing migrations before creating new ones. If in doubt, refer to docs/database_schema.sql as source of truth.

text

backend/database/migrations/
├── 2024_01_01_000001_create_users_table.php
├── 2024_01_01_000002_create_password_reset_tokens_table.php
├── 2024_01_01_000003_create_failed_jobs_table.php
├── 2024_01_01_000004_create_personal_access_tokens_table.php
├── 2024_01_01_000005_create_jobs_table.php
├── 2024_01_01_100001_create_profiles_table.php
├── 2024_01_01_100010_create_consents_table.php
├── 2024_01_01_100011_create_audit_logs_table.php
├── 2024_01_01_200000_create_centers_table.php
├── 2024_01_01_200001_create_faqs_table.php
├── 2024_01_01_200002_create_subscriptions_table.php
├── 2024_01_01_200003_create_contact_submissions_table.php
├── 2024_01_01_300000_create_services_table.php
├── 2024_01_01_300001_create_staff_table.php
├── 2024_01_01_400000_create_bookings_table.php
├── 2024_01_01_400001_create_testimonials_table.php
├── 2024_01_01_500000_create_media_table.php
└── 2024_01_01_500001_create_content_translations_table.php
5.4 Caching & Supporting Services
Service	Technology	Purpose	Configuration
Application Cache	Redis 7 (ElastiCache cluster)	API responses (5-min TTL), session storage, rate limiting	Tag-based invalidation (e.g., user:{id}:bookings)
Queue Backend	Redis 7 (same cluster)	Laravel queue driver for async jobs	Horizon dashboard monitors queue depth
Search Index	Elasticsearch 8	Center/service discovery, multilingual full-text search	Indexes updated via SyncSearchIndex job
Object Storage	AWS S3 (ap-southeast-1)	Media files (images, PDFs), data export ZIPs	Lifecycle rules: archive to Glacier after 90 days
Cache Strategy:

Edge (Cloudflare): Static assets (max-age=31536000), HTML pages (max-age=300, stale-while-revalidate=600)
Application (Redis): API responses (TTL=5min), computed aggregates (center ratings, booking counts)
Database (MySQL): Query cache disabled (read replica handles read load)
5.5 External Integrations
Service	Purpose	Interface	Security	Phase	Notes
Calendly	Booking scheduling	REST API	OAuth token (Secrets Manager), webhook signature verification	Phase 3 ✅	Webhook sync for cancellations implemented
Mailchimp	Newsletter & campaigns	REST API	API key (Secrets Manager)	Phase 1 ✅	Double opt-in enforced, preference center in-app
Twilio	SMS notifications	REST API	API key (Secrets Manager)	Phase 1 ✅	Singapore numbers only, throttled per PDPA
Cloudflare Stream	Virtual tours	REST API + signed embed URLs	JWT-signed tokens	Alpha (current)	Captions, analytics, adaptive bitrate
GA4	Web analytics	gtag.js + Measurement Protocol	Environment config	Phase 1 ✅	Enhanced events, server-side fallback
Hotjar	UX insights	JS snippet (consent-gated)	Consent ensures PDPA compliance	Phase 2 ✅	Session recording disabled for forms
Sentry	Error tracking	SDK	DSN (environment variable)	Phase 1 ✅	Release tagging from CI/CD
New Relic	APM & RUM	Agent	License key (Secrets Manager)	Phase 1 ✅	Core Web Vitals dashboards
Integration Standards:

Secrets Rotation: 90-day cycle (automated via AWS Secrets Manager rotation Lambda)
Circuit Breakers: After 3 consecutive failures, switch to manual fallback (email admin)
Contract Testing: Mock external APIs in tests; integration tests gated by environment variables (CALENDLY_API_TOKEN, etc.)
Abstraction Layer: All external API calls via service classes (e.g., CalendlyService), never direct HTTP calls from controllers
5.6 Data Governance & PDPA Compliance
Data Residency: All production data stored in AWS ap-southeast-1 (Singapore)
Retention Policy:
Active accounts: Indefinite (with consent renewal every 24 months)
Inactive accounts (no login 18 months): Flagged for review
Inactive accounts (24 months): Automated anonymization (unless legal hold)
Audit logs: 7 years (regulatory requirement)
Right-to-Access: GenerateDataExportZip job creates JSON/CSV bundle, secure download link expires in 30 days
Right-to-Delete: AnonymizeUserData job replaces PII with hashed placeholders, retains booking/consent records for compliance
Consent Management: consents table tracks versioned consent (terms, privacy policy, marketing); renewal prompts on policy updates
Key Actions for Agents

Validate schema changes against retention/consent rules before altering migrations
Mock external APIs in automated tests; never call live Calendly/Twilio/Mailchimp
Document new integration touchpoints in this section and docs/api/integrations.md
Tag Redis cache keys appropriately for granular invalidation (e.g., center:{slug}, user:{id}:profile)
Add integration tests to backend/tests/Integration/ with environment variable gates
6. Operational Maturity
6.1 CI/CD Pipeline (GitHub Actions)
Workflow: .github/workflows/ci.yml

Stage	Actions	Success Criteria
Lint	ESLint (frontend), PHPStan Level 8 (backend), Prettier	Zero errors
Unit Tests	Jest (frontend), PHPUnit/Pest (backend)	≥90% coverage, all tests pass
Integration Tests	Laravel HTTP tests, external API contract tests (gated)	All tests pass
E2E Tests	Playwright smoke tests (login, booking happy path, locale switching)	All critical paths pass
Accessibility	axe-core (via jest-axe), pa11y CLI	Zero violations (WCAG 2.1 AA)
Performance	Lighthouse CI (performance, accessibility, best practices, SEO >90)	All budgets met
Security	npm audit, composer audit, Dependabot, container image scanning (Trivy)	No high/critical vulnerabilities
Build	Next.js production build, Laravel asset compilation	Build succeeds, artifact <50MB
Deploy (Staging)	Kubernetes apply to staging namespace (auto-trigger on main merge)	Pods healthy, smoke tests pass
Deploy (Production)	Manual approval → Kubernetes apply to production namespace, requires change ticket	Pods healthy, zero-downtime rollout
Branch Protection:

main: Require PR approval, CI passing, no force-push
Production deploys: Require signed commits, manual approval from DevOps lead
6.2 Infrastructure as Code (Terraform)
Modules (terraform/modules/):

vpc/: Network configuration (subnets, security groups, NAT gateways)
kubernetes/: EKS cluster, node groups, autoscaling policies
rds/: MySQL primary + read replica, parameter groups, backup schedules
elasticache/: Redis cluster, replication groups
s3/: Media bucket, lifecycle rules, CORS configuration
iam/: Service accounts, policies, roles (least privilege)
secrets/: AWS Secrets Manager resources for API keys
cloudwatch/: Alarms, dashboards, log groups
Workflow:

Changes made in terraform/environments/{local,staging,production}
terraform plan run locally or in CI
PR review by infrastructure team
terraform apply executed with approval
State stored in S3 backend with DynamoDB locking
⚠️ CRITICAL: Never make manual AWS console edits. All changes via Terraform to maintain drift-free state.

6.3 Monitoring & Observability
Tool	Purpose	Integration	Alerts
Sentry	Error tracking	SDK in Laravel + Next.js	Slack #alerts on new issues (>10 events/min)
New Relic	APM, distributed tracing, RUM	Agent in Laravel, browser snippet in Next.js	PagerDuty on Apdex <0.7, response time >2s
CloudWatch	Metrics (CPU, memory, disk), logs	Kubernetes DaemonSet, RDS/ElastiCache built-in	SNS → email on threshold breaches
UptimeRobot	Synthetic monitoring	HTTP checks every 5 minutes (Singapore region)	SMS + email on downtime >5min
ELK Stack	Centralized logging (optional)	Filebeat → Logstash → Elasticsearch → Kibana	Custom Kibana alerts on error rate spikes
AWS GuardDuty	Threat detection	Enabled on AWS account	SNS → security team email on threats
Dashboards:

New Relic: Core Web Vitals, API response times, database query performance, error rates
CloudWatch: Kubernetes pod health, RDS connections, ElastiCache hit rate, S3 request metrics
GA4 + Looker Studio: User journeys, conversion funnels, content performance
6.4 Runbooks & Incident Response
Location: docs/runbooks/

Runbook	Scenario	RTO	RPO
incident-response.md	Service outage, data breach	4 hours	1 hour
disaster-recovery.md	AWS region failure	4 hours	1 hour
database-restore.md	Data corruption, accidental deletion	2 hours	1 hour (from snapshot)
scaling-up.md	Traffic spike (>10x normal)	30 minutes	N/A
security-incident.md	DDoS, unauthorized access	1 hour (containment)	N/A
Incident SLA: 24-hour notification to stakeholders for security incidents, 4-hour RTO for critical service restoration.

6.5 Disaster Recovery (DR)
Backup Strategy:
RDS automated snapshots (daily, 35-day retention)
Manual snapshots before major releases
Daily snapshots replicated to ap-northeast-1 (cross-region)
Redis snapshots (daily, 7-day retention)
S3 versioning enabled (30-day retention for deleted objects)
Recovery Scenarios:
Database corruption: Restore from snapshot (<2 hours)
Region failure: Promote ap-northeast-1 replica to primary, update DNS (<4 hours)
Application bug: Rollback Kubernetes deployment (<15 minutes)
DR Testing: Quarterly drills (restore staging from production snapshot, validate data integrity)
Key Actions for Agents

Check CI status before merging PRs; fix all failures locally first
Coordinate infrastructure changes via Terraform; never manual AWS edits
Update runbooks when adding new failure modes or operational procedures
Tag Docker images with Git SHA + semantic version for rollback traceability
Monitor Sentry/New Relic after deploying changes; set up alerts for new endpoints
7. Security & Compliance
7.1 Security Architecture
Layer	Measures	Standards
Transport	TLS 1.3, HSTS preload, certificate pinning	OWASP TLS Cheat Sheet
Application	OAuth 2.0 (Sanctum), MFA for admin/moderator, RBAC (policies/gates)	OWASP ASVS Level 2
Data at Rest	AES-256 (RDS encryption), S3 default encryption, bcrypt cost 12 (passwords)	NIST SP 800-175B
Headers	CSP, X-Frame-Options: DENY, X-Content-Type-Options: nosniff, Referrer-Policy: strict-origin-when-cross-origin	OWASP Secure Headers Project
Secrets	AWS Secrets Manager, IAM least privilege, 90-day rotation	AWS Well-Architected Framework
Input Validation	Laravel Form Requests (Zod on frontend), SQLi/XSS safeguards via Eloquent ORM	OWASP Input Validation Cheat Sheet
CSRF	Laravel @csrf tokens, SameSite=Strict cookies	OWASP CSRF Prevention
Rate Limiting	60 req/min (public), 1000/hour (authenticated), Cloudflare WAF	OWASP API Security Top 10
7.2 Authentication & Authorization
User Authentication: Sanctum stateful cookies (SPA), MFA via TOTP (Google Authenticator) for admin/moderator accounts
Admin Authentication: Mandatory MFA, session timeout 30 minutes (idle), password complexity enforcement (min 12 chars, uppercase, lowercase, number, symbol)
RBAC: Four roles (user, admin, moderator, translator), granular permissions via Laravel policies
user: View centers, book visits, manage own profile/consents
moderator: Approve testimonials, moderate content
translator: Manage content translations
admin: Full access + user management, system configuration
7.3 Dependency Security
Automation: Dependabot PRs for npm/Composer dependencies (weekly scans)
Audits: npm audit / composer audit in CI pipeline (fail on high/critical)
Container Scanning: Trivy scans Docker images for vulnerabilities before push to registry
License Compliance: Check licenses for GPL/AGPL (incompatible with MIT)
7.4 Monitoring & Incident Response
AWS GuardDuty: Enabled for threat detection (unusual API calls, port scans, compromised credentials)
Security Alerts: Sentry alerts on suspicious patterns (repeated 401s, SQL error messages, CSRF failures)
Incident SLA: 24-hour notification to stakeholders, 1-hour containment for active breaches
Quarterly Drills: PDPA data breach simulation, penetration test review, security awareness training
7.5 Compliance Architecture
PDPA (Personal Data Protection Act - Singapore)
Requirement	Implementation	Evidence
Consent	Versioned consent records in consents table, opt-in checkboxes with clear language	ConsentService, consent banner UI
Data Residency	All production data in AWS ap-southeast-1 (Singapore)	Terraform VPC module
Retention	24-month inactivity → auto-anonymization (unless legal hold)	Scheduled job AnonymizeInactiveUsers
Right-to-Access	User data export (JSON/CSV ZIP) via dashboard, 30-day secure link	GenerateDataExportZip job
Right-to-Delete	Anonymization workflow (PII replaced with hashes, retain compliance records)	AnonymizeUserData job
Audit Logging	7-year retention of personal data access/modification	audit_logs table, AuditObserver
Breach Notification	24-hour SLA to affected users + PDPC (Personal Data Protection Commission)	docs/runbooks/security-incident.md
MOH (Ministry of Health) Eldercare Guidelines
Requirement	Implementation
License Display	centers.moh_license_number prominently displayed on center detail pages
Staff Credentials	staff.qualification_certifications (JSON), expiry dates tracked
Automated Reminders	Scheduled job NotifyExpiringCertifications alerts center admins 30 days before expiry
Emergency Protocols	centers.emergency_protocols (JSON) displayed, updated quarterly
WCAG 2.1 AA (Web Content Accessibility Guidelines)
Automated Testing: axe-core (CI), pa11y CLI (staging smoke tests)
Manual Testing: NVDA (Windows), VoiceOver (macOS/iOS) QA sweeps per sprint
Color Contrast: Design tokens enforce ≥4.5:1 (≥3:1 for large text)
Keyboard Navigation: All interactive elements focusable, skip links, focus traps in modals
Screen Reader: ARIA labels, live regions for dynamic content, semantic HTML5
IMDA (Infocomm Media Development Authority) Accessibility
Standards: Singapore Standard SS 632:2019 (aligned with WCAG 2.1 AA)
Certification: Annual accessibility audit by certified auditor (planned post-MVP)
Key Actions for Agents

Surface compliance impacts in PR descriptions (e.g., "Modifies consent flow - PDPA review required")
Consult compliance officer before altering consents, audit_logs, or data retention workflows
Ensure new features maintain audit logging (add Auditable trait to models handling PII)
Request security review for authentication/authorization changes
Run npm audit / composer audit locally before pushing; fix high/critical vulnerabilities immediately
8. Performance & Scalability Playbook
8.1 Performance Budgets
Metric	Target	Measurement	Enforcement
TTFB (Time to First Byte)	<300ms	New Relic, WebPageTest	Lighthouse CI gate
FCP (First Contentful Paint)	<1.5s	Lighthouse, New Relic RUM	Lighthouse CI gate
LCP (Largest Contentful Paint)	<2.5s	Lighthouse, Core Web Vitals	Lighthouse CI gate
TTI (Time to Interactive)	<3s on 3G	Lighthouse (Moto G4 throttling)	Lighthouse CI gate
CLS (Cumulative Layout Shift)	<0.1	New Relic RUM, Lighthouse	Monitoring alert
Page Weight	<280KB (initial load)	Webpack Bundle Analyzer, Lighthouse	CI warning at 300KB
CSS Payload	<20KB (production)	Tailwind JIT + PurgeCSS	Build step verification
JS Payload (initial bundle)	<150KB gzipped	Next.js bundle analyzer	CI warning at 180KB
8.2 Caching Strategy
Layer	Mechanism	TTL	Invalidation
Edge (Cloudflare)	HTTP cache (Vary: Accept-Language, Cookie)	Static: 1 year, HTML: 5 min	Purge via API on content updates
Application (Redis)	Tag-based cache (center:{slug}, user:{id}:bookings)	5 minutes	Invalidate tags on model events
Database (MySQL)	Query cache disabled (rely on read replica)	N/A	N/A
Browser (Service Worker)	Workbox caching strategies	Static: 1 week, API: network-first	SW update on deployment
8.3 Database Optimization
Connection Pooling: PgBouncer for MySQL (max 100 connections, pool mode: transaction)
Indexed Queries: All API filters/sorts use indexed columns (monitored via EXPLAIN logs)
Read Replica: Analytics queries, content listing → read replica; writes → primary
Prepared Statements: Eloquent ORM uses prepared statements (SQLi prevention + performance)
N+1 Prevention: Eager loading via with() relationships (enforced in code review)
8.4 Asynchronous Processing
Email/SMS: All notifications queued (high priority), never block HTTP response
Search Indexing: SyncSearchIndex job runs on default queue after content updates
Analytics: Events batched and sent to GA4 Measurement Protocol via low priority queue
Data Exports: GenerateDataExportZip runs in background, user notified via email when ready
8.5 Scaling Thresholds & Strategies
Threshold	Trigger	Action
Horizontal Scaling (Kubernetes)	CPU >70% (5-min avg)	Add pods (HPA: min 3, max 20)
Database Scaling	Connections >80 pool capacity	Add read replicas (max 5)
Redis Scaling	Memory >80%	Increase instance class or add cluster nodes
Elasticsearch Scaling	>5M documents, query latency >500ms	Vertical scaling (larger instances) or evaluate sharding
Microservices Extraction	>100K MAU, monolith deployment >30min	Extract NotificationService, SearchService, BookingService
8.6 Load Testing & Profiling
Tools: k6 (backend), Lighthouse CI (frontend), New Relic Transaction Traces (profiling)
Baseline: 1000 concurrent users, 50 req/s sustained
Stress Test: 2x baseline (2000 concurrent, 100 req/s) for 10 minutes
Timing: Pre-launch (staging), quarterly (production during low-traffic hours)
Success Criteria: Response time <2s (p95), error rate <1%, no database deadlocks
8.7 Media Optimization
Images: Next.js <Image> generates WebP + JPEG fallback, responsive srcset, lazy loading
Video (Cloudflare Stream): Adaptive bitrate (ABR), low-bandwidth warning (<2 Mbps), chapter markers for seeking
Fonts: Subsets per locale (Latin: 50KB, CJK: 150KB), font-display: swap, preload critical fonts
Key Actions for Agents

Instrument new API endpoints with New Relic custom transactions
Validate caching headers/TTL when introducing new pages or API routes
Include load/perf test updates when altering critical paths (booking, search)
Run Lighthouse CI locally before submitting PRs with UI changes
Monitor New Relic for N+1 queries after deploying Eloquent relationship changes
9. Accessibility & Internationalization
9.1 Accessibility Standards
Baseline: WCAG 2.1 AA compliance (Singapore SS 632:2019)
Testing Pyramid:
Automated (80%): axe-core via jest-axe (component tests), pa11y CLI (integration)
Manual (20%): NVDA (Windows), VoiceOver (macOS/iOS), keyboard-only navigation, 200% zoom
9.2 Accessibility Features
Feature	Implementation	Testing
Keyboard Navigation	All interactive elements focusable, skip links, focus traps in modals, no keyboard traps	Keyboard-only QA
Screen Reader	ARIA labels, live regions (aria-live="polite" for notifications), semantic HTML5	NVDA, VoiceOver
Color Contrast	Design tokens enforce ≥4.5:1 (text), ≥3:1 (large text/UI)	axe-core, manual audit
Focus Management	Visible focus indicators (2px outline, high contrast), focus restoration after modal close	Visual inspection
Adjustable Typography	User preference (base font: 16px, 18px, 20px), responsive line-height	Manual testing
Reduced Motion	prefers-reduced-motion media query disables Framer Motion animations	Browser DevTools
Alt Text	Mandatory for all <Image> components, CMS field validation	Lighthouse, manual review
Captions/Transcripts	Cloudflare Stream videos require captions, audio descriptions for complex visuals	Manual review
Form Errors	ARIA live regions announce errors, visible inline messages, focus management	Screen reader testing
9.3 Internationalization (i18n)
Language	Code	Completion	Native Speaker Review	Font Stack
English	en	100%	✅ (Native team member)	Inter
Mandarin (Simplified)	zh	100%	✅ (Professional translator)	Noto Sans SC
Malay	ms	80% (Alpha phase)	⏳ Planned	Noto Sans
Tamil	ta	60% (Alpha phase)	⏳ Planned	Noto Sans
9.4 Localization Framework
UI Strings: next-intl with JSON message files (frontend/locales/{locale}/)
Rich Content: content_translations table (polymorphic) with CMS approval workflow
Locale Detection Priority: URL segment (/{locale}/) → user preference (cookie) → Accept-Language → default (en)
Formatting:
Dates: Intl.DateTimeFormat (e.g., en-SG: "8 Jan 2025", zh-SG: "2025年1月8日")
Numbers: Intl.NumberFormat (e.g., currency: "SGD 1,200.00")
Relative time: Intl.RelativeTimeFormat (e.g., "2 days ago")
9.5 Cultural Considerations
Imagery: Diverse representation (Chinese, Malay, Indian, Eurasian seniors), authentic care settings
Language Tone: Respectful, warm, non-patronizing; honorifics for seniors (e.g., "Mdm Tan", "Mr Lee")
Holiday Awareness: Content calendar accounts for CNY, Hari Raya, Deepavali, Christmas (no major campaigns during festivals)
Honorific Templates: Database stores title field (Mr, Mrs, Ms, Mdm, Dr), UI concatenates respectfully
9.6 Translation Workflow
Content creator drafts in English (default locale)
Translator role receives notification in Nova admin panel
Translator adds content_translations records for zh, ms, ta
Moderator reviews translations (native speaker QA)
Moderator approves → content visible on frontend for respective locale
Machine translation (Google Translate API) used for initial drafts, human refinement mandatory
Key Actions for Agents

Use docs/accessibility/accessibility-checklist.md before marking features complete
Mark translatable strings with next-intl t() function (never hardcode user-facing text)
Request accessibility review for UI-heavy changes (tag @accessibility-team in PRs)
Test keyboard navigation and screen reader compatibility for new interactive components
Validate color contrast with axe DevTools extension before submitting PR
Add jest-axe assertions to all new component tests
10. Testing & Quality Assurance
10.1 Testing Standards & Mandates
Coverage Mandate: ≥90% for critical modules (authentication, booking, consent management, audit logging)
Overall Coverage Target: ≥80% (tracked via Coveralls badge in README)
Pre-PR Requirement: All tests passing locally before requesting review
CI Gate: PRs cannot merge if tests fail or coverage drops below threshold
10.2 Test Baseline (Recorded - Phase 3)
Metric	Value	Notes
Backend Tests	90 tests	PHPUnit + Pest
Assertions	216	Unit + feature tests
Deprecation Warnings	59	Technical debt tracked in backlog
Frontend Tests	TBD (Alpha phase)	Jest + RTL + Playwright
10.3 Testing Pyramid
Backend (PHPUnit + Pest)
Level	Scope	Tools	Execution
Unit Tests	Service classes, helpers, policies	PHPUnit, Mockery	composer test:unit (fast, <5s)
Feature Tests	API endpoints, authentication flows	Laravel HTTP testing, RefreshDatabase	composer test:feature (~30s)
Integration Tests	External APIs (Calendly, Twilio)	HTTP::fake(), environment gates	composer test:integration (gated by env vars)
Example Commands:

Bash

# Run all backend tests
cd backend && ./vendor/bin/phpunit --testdox

# Run specific test class
./vendor/bin/phpunit --filter BookingServiceTest

# Run with coverage report
./vendor/bin/phpunit --coverage-html coverage/
Environment Gating (Integration Tests):

Bash

# Enable Calendly integration tests
export CALENDLY_API_TOKEN=your_token
export CALENDLY_ORGANIZATION_URI=https://api.calendly.com/organizations/ORG_ID

# Tests will skip if these are not set
Frontend (Jest + Testing Library + Playwright)
Level	Scope	Tools	Execution
Unit Tests	Hooks, utilities, API client	Jest	npm test (fast, <10s)
Component Tests	UI components, accessibility	RTL, jest-axe	npm test (includes a11y checks)
Integration Tests	Page flows, API mocking	RTL, MSW (Mock Service Worker)	npm test
E2E Tests	Critical user journeys	Playwright	npm run test:e2e (nightly + pre-release)
Visual Regression	Storybook components	Percy	npm run percy:storybook (CI-gated)
E2E Test Schedule:

Nightly: Full suite (~50 tests, 15 minutes)
Pre-Release: Full suite + smoke tests on staging
CI: Smoke tests only (login, booking happy path, locale switching) (~5 tests, 3 minutes)
10.4 Load & Performance Testing
Test Type	Tool	Baseline	Stress Threshold	Timing
Backend Load	k6	1000 concurrent users, 50 req/s sustained	2x baseline (2000 concurrent, 100 req/s)	Pre-launch + quarterly
Frontend Performance	Lighthouse CI	Perf/A11y/Best Practices/SEO >90	Any score <90 fails CI	Every deploy
Real User Monitoring	New Relic RUM	LCP <2.5s, FID <100ms, CLS <0.1	Alert if p95 exceeds thresholds	Continuous
10.5 Accessibility Testing
Test Type	Tool	Scope	Frequency
Automated (CI)	axe-core via jest-axe	All components	Every commit
Automated (Staging)	pa11y CLI	Critical pages (home, center detail, booking)	Every deploy
Manual (Screen Reader)	NVDA (Windows), VoiceOver (macOS/iOS)	New features + critical flows	Per sprint
Manual (Keyboard)	Keyboard-only navigation	All interactive elements	Per sprint
Manual (Zoom)	200%		
S