AGENT.md — ElderCare SG Web Platform (v1.2)
Version: 1.2
Last Updated: 2025-01-XX (Current)
Status: Phases 1-3 Completed | Alpha Development
Primary Source Documents: Project_Architecture_Document.md v2.1, Project_Requirements_Document.md, codebase_completion_master_plan.md, README.md, docs/ai-coding-agent-brief.md, docs/accessibility/, docs/design-system/, docs/deployment/

Purpose: This guide is the single source of truth for any AI coding agent (and their human facilitators) to understand the ElderCare SG architecture, delivery standards, and operational guardrails before touching the codebase.

⚠️ Resolved Conflicts & Authoritative Sources
All conflicts from v1.0/v1.1 have been resolved using latest authoritative sources:

Topic	Previous Conflict	Resolution (v1.2)	Source
Search Infrastructure	MeiliSearch vs Elasticsearch	Elasticsearch 8 for production search	README.md (authoritative)
Container Orchestration	Kubernetes vs ECS Fargate	Both: Kubernetes (local/dev), ECS Fargate (staging/prod AWS)	README.md + PAD v2.1
Project Phase	Foundation/Phase 2 vs Pre-Phase 3	Phases 1-3 COMPLETED (Alpha Development stage)	README.md (authoritative)
Primary Database	MySQL vs MariaDB	MySQL 8.0 (RDS)	All sources aligned ✅
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
Collaboration Protocols
Quickstart Checklist for AI Agents
Troubleshooting & Platform-Specific Guidance
Change Log & Maintenance Guidance
1. Executive Summary
1.1 Project Mission & Vision
Mission: Deliver a compassionate, accessibility-first digital bridge between Singaporean families and trusted elderly daycare services.

Vision: Create a digital ecosystem that empowers families to make informed decisions about elderly care, while ensuring dignity, accessibility, and quality of life for Singapore's senior population.

1.2 Target Personas
Persona	Age Range	Key Needs	Technical Profile
Adult Children	30-55	Time-poor professionals; demand deep facility insights, social proof, mobile-first UX	High digital literacy; mobile-first (70% traffic)
Family Caregivers	25-45	Often domestic helpers; require multilingual content, clear transport/pricing info	Variable literacy; multilingual support critical
Healthcare Professionals	30-60	Quick license/capability verification for referrals	Desktop-first; value speed and accuracy
Digitally Literate Seniors	55+	Prefer larger fonts, high contrast, straightforward navigation	Moderate literacy; accessibility essential
1.3 Core Value Pillars
Trust & Transparency: Verified MOH licenses, staff credentials, authentic testimonials with booking verification badges
Accessibility First: WCAG 2.1 AA compliance, multilingual support (EN/ZH/MS/TA), reduced-motion experiences
Cultural Resonance: Respectful honorifics, diverse imagery, holiday-aware content, Singapore-specific transit guidance
Seamless Engagement: Frictionless journey from discovery → booking with proactive reminders and fallback paths
1.4 Current Status (v1.2)
Development Phase: Alpha Development
Completed Phases: Phase 1 (MVP), Phase 2 (Enhancements), Phase 3 (Backend Services & PDPA Compliance)

Phase 1-3 Key Deliverables (Completed):

✅ Project architecture, Docker environment, CI/CD pipeline
✅ Core UI components with Storybook documentation
✅ Internationalization framework (EN/ZH with MS/TA scaffolding)
✅ Database schema (18 tables, PDPA-compliant, audit logs)
✅ Authentication system (Laravel Sanctum, registration/login APIs)
✅ Calendly integration adapter with webhook support
✅ Audit logging infrastructure (Auditable trait, AuditObserver)
✅ API controllers: Auth (RegisterController), Bookings (BookingController), Centers (CenterController)
✅ Feature tests: 90 tests, 216 assertions passing
In Progress:

Content management workflows
Virtual tours (Cloudflare Stream integration)
Production deployment hardening
Advanced testimonial moderation
Malay & Tamil translation completion
1.5 North-Star Success Metrics
Metric	Target	Measurement Tool	Owner	Cadence
Visit bookings increase	+30% within 3 months	GA4 conversions + booking DB	Marketing	Monthly
Mobile bounce rate	<40%	GA4 device segmentation	UX	Bi-weekly
Lighthouse Performance	>90	Lighthouse CI (staging & prod)	DevOps	Each deploy
Lighthouse Accessibility	>90	Lighthouse CI + axe-core	QA	Each deploy
Session duration	>5 minutes	GA4 engagement metrics	Marketing	Monthly
3G load time	<3 seconds	WebPageTest (Singapore)	DevOps	Weekly
Form completion	>75%	Hotjar form analytics	UX	Monthly
Video engagement (Phase 2+)	>60% completion	Cloudflare Stream analytics	Marketing	Monthly
TTFB	<300ms	New Relic RUM	DevOps	Per deploy
FCP	<1.5s	Lighthouse CI	DevOps	Per deploy
LCP	<2.5s	Lighthouse CI + New Relic	DevOps	Per deploy
TTI	<3s on 3G	Lighthouse CI	DevOps	Per deploy
1.6 Operational Constraints & Assumptions
SLA: 99.5% availability (production), RTO <4h, RPO <1h
Timeline: 12-week MVP (completed), 4-week enhancements (completed), ongoing iterations
Team Size: 6-8 engineers + product/design/QA
Browser Support: Latest 2 versions of Chrome, Firefox, Safari, Edge
Mobile Traffic: 70% forecast (mobile-first design mandatory)
Regulatory: PDPA data residency (AWS ap-southeast-1), MOH display compliance, WCAG 2.1 AA, IMDA guidelines
Budget: Calendly Pro (~USD
12
/
u
s
e
r
/
m
o
n
t
h
)
,
M
a
i
l
c
h
i
m
p
S
t
a
n
d
a
r
d
(
 
U
S
D
12/user/month),MailchimpStandard( USD20/month), Laravel Nova (USD
199
/
y
e
a
r
/
s
e
a
t
)
,
C
l
o
u
d
f
l
a
r
e
P
r
o
(
U
S
D
199/year/seat),CloudflarePro(USD20/month), Twilio SMS (~SGD$0.07/message)
1.7 Out of Scope (Current Release)
To maintain focus, the following features are explicitly not included in the current roadmap:

Native mobile apps (iOS/Android)
Payment processing / billing systems
Caregiver-to-family matching algorithms
Medical record integration (EHR/EMR)
Telehealth / virtual consultation platform
Live chat / real-time support widget
Provider self-service portals
Family shared accounts (multi-user households)
Government database integrations (CPF, SingPass deep integration)
2. System Overview
2.1 Architecture Pattern
Service-Oriented Monolith (Laravel) with modular domain boundaries and shared MySQL schema, designed for future microservices extraction when scale demands (>100K MAU).

2.2 Hosting & Orchestration
Environment	Orchestration	Hosting	Notes
Local Development	Docker Compose	Developer workstations	SQLite or MySQL container
Development/Testing	Kubernetes (local cluster)	Minikube/Kind/Docker Desktop	Mirrors prod architecture
Staging	AWS ECS Fargate	ap-southeast-1	Auto-deploy from main branch
Production	AWS ECS Fargate (multi-AZ)	ap-southeast-1	Manual approval + change ticket
Key Infrastructure:

CDN & Security: Cloudflare (Singapore edge, WAF, DDoS mitigation)
Infrastructure as Code: Terraform modules (terraform/ directory)
CI/CD: GitHub Actions (.github/workflows/)
Observability: Sentry (errors), New Relic (APM/RUM), CloudWatch (logs/metrics), UptimeRobot (synthetic monitoring)
2.3 Logical Components
mermaid

flowchart LR
    User((Users)) -->|HTTPS| Cloudflare[Cloudflare CDN/WAF]
    Cloudflare --> NextJS[Next.js 14 Frontend]
    NextJS -->|/api/v1/*| LaravelAPI[Laravel 12 API]
    
    LaravelAPI --> MySQL[(MySQL 8.0 RDS)]
    LaravelAPI --> Redis[(Redis 7 ElastiCache)]
    LaravelAPI --> Elasticsearch[(Elasticsearch 8)]
    LaravelAPI --> S3[(AWS S3 Media)]
    LaravelAPI -->|Jobs/Queues| SQS[(SQS + Fargate Workers)]
    
    LaravelAPI --> Calendly{{Calendly API}}
    LaravelAPI --> Mailchimp{{Mailchimp API}}
    LaravelAPI --> Twilio{{Twilio SMS}}
    LaravelAPI --> Stream{{Cloudflare Stream}}
    
    NextJS -.Analytics.-> GA4{{Google Analytics 4}}
    NextJS -.Heatmaps.-> Hotjar{{Hotjar}}
    LaravelAPI -.Errors.-> Sentry{{Sentry}}
    LaravelAPI -.APM.-> NewRelic{{New Relic}}
    LaravelAPI -.Logs.-> CloudWatch{{CloudWatch}}
2.4 Key Principles
User-Centric Design: Progressive disclosure, skeleton loaders, fallback paths (manual contact when Calendly fails)
Accessibility First: Semantic HTML, Radix UI primitives, keyboard navigation, ARIA live regions, screen reader QA
Security by Design: Layered defenses, RBAC, MFA for admins, TLS 1.3, encryption at rest (AES-256) and in transit
Performance Optimized: Page weight budgets (<280KB), React Server Components default, CDN + Redis caching
Compliance Built-In: Consent ledger, 7-year audit trails, automated retention (24-month inactivity deletion)
Scalable & Maintainable: Service-layer boundaries, repository pattern, ≥90% test coverage mandate, ADR documentation
Cultural Sensitivity: I18n-first, locale-aware formatting, respectful honorifics, holiday scheduling awareness
2.5 API Guidelines
Base URL: https://api.eldercare.sg/v1/ (production), http://localhost:8000/api/v1/ (local)
Versioning: Version in path (/v1/), minimum 6-month deprecation notice, support 2 concurrent versions
Authentication: Laravel Sanctum bearer tokens (Authorization: Bearer {token})
Pagination: ?page=1&per_page=20, response includes meta.total, meta.last_page, links.next
Filtering & Sorting: filter[city]=Singapore, sort=-created_at conventions
Rate Limits: 60 req/min per IP (public), 1000/hour per authenticated user
Error Schema:
JSON

{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
Key Actions for Agents

Confirm environment context before running commands (.env, frontend/.env.local, .env.staging, .env.production.template)
Respect architecture principles when proposing modifications
Check Terraform state/variables prior to infrastructure-affecting changes (terraform/)
Reference API documentation in docs/api/README.md for endpoint contracts
3. Frontend Blueprint
3.1 Technology Stack
Technology	Version	Purpose
Next.js	14 (App Router)	React framework with SSR/RSC
React	18	UI library
TypeScript	5	Type safety
Tailwind CSS	3	Utility-first styling (JIT, <20KB purged CSS)
Radix UI	Latest	Accessible component primitives
Framer Motion	10	Animations (with prefers-reduced-motion support)
React Query	4	Server state management (5-min stale time, background revalidation)
Zustand	4	Global client state (persisted auth/locale/UI toggles)
3.2 Directory Structure (frontend/)
text

frontend/
├── app/
│   ├── [locale]/              # Dynamic locale segment (en/zh/ms/ta)
│   │   ├── layout.tsx         # Root layout (Header/Footer)
│   │   ├── page.tsx           # Home (Server Component)
│   │   ├── centers/
│   │   │   ├── page.tsx       # Listing (Server Component)
│   │   │   └── [slug]/page.tsx# Detail (Server Component)
│   │   ├── services/page.tsx
│   │   ├── booking/page.tsx   # Booking flow (Client Component)
│   │   ├── dashboard/page.tsx # Authenticated area
│   │   └── ...
│   ├── api/                   # Next.js route handlers (proxy/edge cases)
│   └── globals.css
├── components/
│   ├── atoms/                 # Button, Input, Icon, Badge
│   ├── molecules/             # FormField, Card, NavItem, SearchBar
│   ├── organisms/             # Header, Footer, BookingForm, ServiceCard
│   ├── templates/             # Layout wrappers
│   └── providers/             # AnalyticsProvider, AuthProvider, ThemeProvider
├── hooks/                     # useAuth, useBooking, useTranslation
├── lib/                       # API client, utils, Zod schemas
├── locales/                   # en/, zh/, ms/, ta/ JSON resources
├── store/                     # Zustand stores (authStore, uiStore)
├── tests/                     # Jest + Testing Library + Playwright
├── types/                     # Shared TypeScript definitions
└── middleware.ts              # Locale negotiation, auth guards
3.3 Rendering Strategy
Default: React Server Components (RSC) for data-heavy views (center listings, detail pages, testimonials)
Client Components: Only where interactivity demands (booking wizard, search filters, modals, carousels)
Progressive Enhancement: Forms submit via standard POST when JS disabled; enhanced with inline validation when enabled
Navigation: Plain anchors for baseline functionality; Next.js Link for client-side transitions
3.4 Asset Optimization
Images: Next.js <Image> with responsive WebP + JPEG fallback, LQIP (Low-Quality Image Placeholder), lazy loading
Fonts: Self-hosted via next/font with subsetting and font-display: swap
English: Inter (Latin subset)
Mandarin: Noto Sans SC (CJK subset)
Malay: Inter (Latin-Ext subset)
Tamil: Noto Sans Tamil (Tamil subset)
CSS: Tailwind JIT + Purge → <20KB production payload
JS: Dynamic imports for heavy modules (map view, video player, booking wizard stepper)
Page Weight Budget: <280KB total (enforced via Lighthouse CI)
3.5 State Management
Server State (@tanstack/react-query): Booking data, center listings, testimonials
5-minute stale time
Background revalidation
Retry logic with exponential backoff
Global Client State (Zustand + localStorage persistence):
Auth session (token, user profile)
Locale preference
UI toggles (sidebar, contrast mode)
Feature flags
Local State: Form inputs, modal visibility, accordion state
3.6 Analytics & Monitoring
GA4: Core events via AnalyticsProvider — booking_started, booking_completed, virtual_tour_started, newsletter_subscribed, testimonial_submitted, language_switched
Hotjar: UX insights (consent-gated, session recording disabled for forms per PDPA)
Sentry: Frontend error tracking with release tagging from CI/CD
Configuration: Environment variables documented in docs/deployment/monitoring.md
Key Actions for Agents

Adhere to design tokens and component patterns in Storybook (npm run storybook)
Favor Server Components; justify Client Component usage in PR descriptions
Instrument new interactions with GA4 data attributes (data-event="...")
Test accessibility with jest-axe in every component spec
Validate page weight budgets via npm run lighthouse before PR submission
4. Backend Blueprint
4.1 Technology Stack
Technology	Version	Purpose
Laravel	12	PHP framework with service-layer architecture
PHP	8.2	Programming language
Laravel Sanctum	Latest	API token authentication
Laravel Nova	Latest	Admin panel (accelerates curation, RBAC controls)
PHPStan	Level 8	Static analysis (enforced in CI)
4.2 Service-Layer Architecture
Strict separation of concerns:

text

┌─────────────┐
│ Controllers │ ← Thin HTTP layer (validation, response formatting)
└──────┬──────┘
       │
┌──────▼─────────┐
│ Form Requests  │ ← Validation rules, authorization checks
└────────────────┘
       │
┌──────▼──────┐
│  Services   │ ← Business logic (transactions, orchestration)
└──────┬──────┘
       │
┌──────▼────────┐
│ Repositories  │ ← Data access abstraction (caching, queries)
└───────────────┘
       │
┌──────▼──────┐
│   Models    │ ← Eloquent ORM, relationships, scopes
└─────────────┘
4.3 Key Services (Implemented)
Service	Responsibilities	Status
AuthService	User registration, login, password resets, MFA	✅ Phase 3
BookingService	Booking lifecycle (create, confirm, cancel, reschedule), Calendly orchestration	✅ Phase 3
CalendlyService	Calendly API adapter (events, cancellations, webhooks, signature verification)	✅ Phase 3
ConsentService	PDPA consent capture, versioning, revocation	✅ Phase 3
AuditService	Audit log generation (via AuditObserver on Auditable models)	✅ Phase 3
UserService	Profile management, data export, account deletion	🔄 In progress
CenterService	Center CRUD, MOH license validation	✅ Phase 3
NotificationService	Email/SMS queue orchestration (Mailchimp, Twilio)	🔄 In progress
MediaService	S3 uploads (signed URLs), media associations	🔄 In progress
SearchService	Elasticsearch indexing/querying	🔄 In progress
4.4 API Controllers (Phase 3 Delivered)
backend/app/Http/Controllers/Api/V1/Auth/RegisterController.php — Returns token + user payload
backend/app/Http/Controllers/Api/V1/BookingController.php — CRUD endpoints (store/index/show/destroy)
backend/app/Http/Controllers/Api/V1/CenterController.php — Listing (index) and detail (show)
4.5 Audit Logging Infrastructure
Trait: app/Traits/Auditable.php — Opt-in for models requiring audit trails
Observer: app/Observers/AuditObserver.php — Automatically logs created, updated, deleted events to audit_logs table
Audited Models: User, Center, Booking, Consent, Testimonial
Retention: 7 years (regulatory requirement)
4.6 Queue & Job Processing
Driver: Redis (backend/config/queue.php)
Manager: Laravel Horizon (web UI for monitoring)
Priority Queues:
high: SMS/urgent emails (Twilio, booking confirmations)
default: Transactional emails (password resets, welcome)
low: Analytics sync, background data exports
Failure Handling: Exponential backoff (1m, 5m, 15m), failed_jobs table for manual replay via Nova
4.7 Testing Standards
Framework: PHPUnit + Pest
Coverage Mandate: ≥90% for critical modules (services, policies, repositories)
Current Status: 90 tests, 216 assertions (all passing as of Phase 3 completion)
Integration Tests: Gated by environment variables (e.g., CALENDLY_API_TOKEN for live Calendly tests)
Database: In-memory SQLite for unit tests (RefreshDatabase trait)
Key Actions for Agents

Consult Project_Architecture_Document.md Section 6 for service responsibilities
Maintain service/repository layering; never place business logic in controllers
Extend Auditable trait to new models requiring PDPA compliance
Run composer dump-autoload -o and php artisan package:discover --ansi after adding new classes
Document new jobs/events in docs/ and update runbooks
5. Data & Integrations
5.1 Database Architecture
Primary Store: MySQL 8.0 (AWS RDS, multi-AZ production)

Technical Standards:

Character Set: UTF8MB4 (full Unicode support for multilingual content)
Storage Engine: InnoDB (ACID compliance, transaction support)
Collation: utf8mb4_unicode_ci
Foreign Keys: Explicit cascade rules for PDPA-compliant deletes
Soft Deletes: deleted_at on users, centers, bookings, testimonials
Audit Retention: 7 years for audit_logs (regulatory requirement)
5.2 18-Table Schema Overview
Core Entities:

users (roles: user, admin, moderator, translator; MFA support)
profiles (address, preferred language, accessibility preferences)
centers (MOH license, accreditation, geolocation, capacity, emergency protocols)
services (program metadata, pricing, duration, staff ratios, calendly_event_type_id)
bookings (lifecycle status, Calendly IDs, questionnaire JSON, cancellation reasons, audit timestamps)
testimonials (moderation queue, verified badge, consent tracking)
Compliance & Audit:

consents (polymorphic, versioned consent text, granted/revoked timestamps)
audit_logs (actor, action, before/after hashes, IP, user agent; 7-year retention)
Content & Localization:

content_translations (polymorphic, locale codes, approval workflow metadata)
media (S3 object references, alt text, consent flags, moderation status)
faqs (with translations)
Communications:

subscriptions (Mailchimp double opt-in, preference center)
contact_submissions (support requests)
Infrastructure:

Laravel defaults: password_reset_tokens, failed_jobs, personal_access_tokens (Sanctum), jobs
staff (credentials, certifications, center assignments)
5.3 Existing Migration Scripts
⚠️ CRITICAL: Review and reuse existing migrations. Only create new scripts if features are genuinely missing. When in doubt, consult database_schema.sql.

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
5.4 Caching & Search
Redis 7 (AWS ElastiCache, cluster mode for production):
Application cache (5-min TTL default, tag-based invalidation)
Session storage
Queue driver (SQS integration for workers)
Rate limiting
Elasticsearch 8 (AWS OpenSearch Service):
Center/service discovery (multilingual tokenization)
Faceted search (location, staff ratio, medical services, languages)
Autocomplete suggestions
Vertical scaling initially; evaluate sharding beyond 5M records
5.5 Object Storage
AWS S3 (ap-southeast-1):
Media uploads (images, videos, documents)
Lifecycle rules (archive to Glacier after 90 days for inactive content)
Versioning enabled
Cross-region replication to ap-northeast-1 (DR)
Cloudflare R2: Considered for future offloading (cost optimization)
5.6 External Integrations
Integration	Purpose	Interface	Auth Method	Secret Rotation	Notes
Calendly	Booking scheduling	REST API	OAuth token	90 days	Webhook sync for cancellations (Phase 2+). CalendlyService validates config via isConfigured()
Mailchimp	Newsletter campaigns	REST API	API key	90 days	Double opt-in enforced; preference center in-app
Twilio	SMS notifications	REST API	API key	90 days	Singapore numbers only; throttled per PDPA limits
Cloudflare Stream	Virtual tours (Phase 2+)	REST + signed URLs	JWT tokens	90 days	Captions, chapters, analytics; ABR (Adaptive Bit Rate)
GA4	Web analytics	gtag.js + Measurement Protocol	API secret	N/A (rotate on breach)	Enhanced measurement; server-side fallback
Hotjar	UX insights	JS snippet	Site ID	N/A	Consent-gated; session recording disabled for forms
Sentry	Error tracking	SDK	DSN	90 days	Release tagging from CI/CD
New Relic	APM/RUM	Agent	License key	90 days	Core Web Vitals dashboards
Secret Management:

AWS Secrets Manager stores all API keys/tokens
IAM Policies: Least privilege, scoped per service
Terraform manages secret rotations (terraform/modules/secrets/)
Circuit Breakers: Graceful degradation on third-party failures (e.g., manual contact form fallback when Calendly is down)
5.7 Data Residency & Retention
Production Data: Confined to AWS ap-southeast-1 (Singapore)
Backups: RDS automated snapshots (35-day retention), cross-account replication, encrypted with KMS
Inactive Account Lifecycle:
18 months: Flag for deletion warning
24 months: Auto-deletion (unless legal hold)
Right-to-Export: Data export jobs generate ZIP bundles (JSON/CSV), download link expires in 30 days
Right-to-Delete: Hard delete from primary + backups within 30 days of request (audit trail retained separately per PDPA)
Key Actions for Agents

Validate migration impact on retention/consent rules before schema changes
Mock third-party APIs in tests; never call live services from automated suites
Document new integration touchpoints in this file and docs/runbooks/integrations.md
Check CalendlyService::isConfigured() before Calendly operations (graceful degradation)
Verify secret rotation schedules in Terraform (terraform/modules/secrets/rotation.tf)
6. Operational Maturity
6.1 CI/CD Pipeline (GitHub Actions)
Workflow: .github/workflows/ci.yml

Stages:

Lint & Format: ESLint, Prettier (frontend), PHP-CS-Fixer, PHPStan Level 8 (backend)
Unit Tests: Jest (frontend), PHPUnit (backend) — require ≥90% coverage
Integration Tests: API feature tests (gated Calendly tests skip if CALENDLY_API_TOKEN unset)
E2E Tests: Playwright smoke tests (critical user journeys)
Accessibility Audits: npm run lighthouse (integrates axe-core + pa11y)
Security Scans: Dependabot, npm audit, composer audit, container image scanning
Build Artifacts: Next.js production build, Laravel optimized autoload
Deploy to Staging: Automatic on main branch merges
Deploy to Production: Manual approval + change ticket required
Deployment Cadence:

Staging: Auto-deploy on every main merge (blue-green deployment)
Production: Weekly release train (Thursdays 10:00 SGT) or emergency hotfixes
6.2 Infrastructure as Code (Terraform)
Directory: terraform/

Modules:

vpc/: Multi-AZ VPC, public/private subnets, NAT gateways
ecs/: Fargate clusters, task definitions, auto-scaling policies
rds/: MySQL primary + read replica, parameter groups, backup schedules
elasticache/: Redis cluster mode, node configurations
opensearch/: Elasticsearch domain, access policies
s3/: Buckets with lifecycle rules, versioning, replication
iam/: Service roles, least-privilege policies
secrets/: AWS Secrets Manager + rotation lambdas
cloudwatch/: Log groups, metric filters, alarms
State Management:

Backend: S3 (eldercare-terraform-state)
Locking: DynamoDB table (eldercare-terraform-locks)
⚠️ Golden Rule: No manual console edits. All infrastructure changes must go through Terraform + peer review.

6.3 Environment Configuration
Environment	Branch	Deploy Method	Database	Monitoring
Local	Feature branches	Docker Compose	MySQL container or SQLite	Telescope, local logs
Staging	main	GitHub Actions → ECS Fargate	RDS (single-AZ, t3.medium)	Sentry, New Relic, CloudWatch
Production	release/* tags	Manual approval → ECS Fargate	RDS (multi-AZ, r5.xlarge + read replica)	Full observability stack
Environment Files:

.env.example (template for local)
.env.staging (staging-specific overrides)
.env.production.template (production template, secrets injected via AWS Secrets Manager)
6.4 Observability Stack
Tool	Purpose	Alerts	Retention
Sentry	Error tracking (frontend + backend)	Critical: Slack #alerts, PagerDuty	90 days
New Relic	APM (traces, DB queries, ext. calls), RUM (Core Web Vitals)	Apdex <0.7: On-call escalation	8 days (NRDB retention)
CloudWatch	Logs, metrics (ECS, RDS, Lambda), custom metrics	CPU >80%, disk >85%, 5xx >5%: Email + Slack	30 days (logs), 15 months (metrics)
UptimeRobot	Synthetic monitoring (5-min checks)	Downtime >2 min: SMS + email	90 days
Lighthouse CI	Performance budgets (per deploy)	Score drop >5 points: Block deploy	30 runs
6.5 Disaster Recovery & SLAs
RTO (Recovery Time Objective): <4 hours
RPO (Recovery Point Objective): <1 hour
Availability SLA: 99.5% (production)
Backup Strategy:
RDS: Automated snapshots (35-day retention), cross-account replication
S3: Versioning + cross-region replication (ap-northeast-1)
Redis: Daily snapshots (7-day retention)
DR Runbook: docs/runbooks/disaster-recovery.md
Key Actions for Agents

Check CI status in GitHub Actions before merging; fix failures immediately
Coordinate infrastructure changes via Terraform PRs (tag DevOps lead)
Update runbooks when altering operational flows (deployments, rollbacks, incident response)
Verify Terraform plan output before applying (terraform plan -out=tfplan)
Tag releases with semantic versioning (v1.2.3) for production deploys
7. Security & Compliance
7.1 Security Architecture
Authentication & Authorization:

Primary: Laravel Sanctum (stateful SPA auth with httpOnly cookies)
MFA: Required for admin and moderator roles (TOTP via Google Authenticator)
Password Policy: Minimum 12 characters, complexity requirements (uppercase, lowercase, number, symbol), bcrypt rounds: 12
Session Management: Redis-backed, 2-hour idle timeout (extends on activity), secure/httpOnly/SameSite=Strict cookies
Transport Security:

TLS: 1.3 minimum (1.2 fallback deprecated)
HSTS: max-age=31536000; includeSubDomains; preload
Certificate Management: AWS ACM with auto-renewal
Application Security:

CSP (Content Security Policy): default-src 'self'; script-src 'self' 'unsafe-inline' *.googletagmanager.com; ...
Security Headers:
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), camera=(), microphone=()
CSRF: Laravel middleware on all state-changing routes
SQL Injection: Eloquent ORM parameterized queries (raw queries forbidden without explicit approval)
XSS: Blade template auto-escaping, CSP enforcement
Infrastructure Security:

VPC: Private subnets for RDS/ElastiCache, NAT gateways for outbound, Security Groups with least privilege
IAM: Role-based access, no long-lived credentials, MFA for AWS Console access
Secrets: AWS Secrets Manager (90-day rotation), no secrets in code/logs
WAF: Cloudflare WAF rules (SQL injection, XSS, rate limiting by IP/endpoint)
DDoS: Cloudflare DDoS mitigation (automatic + custom rules)
Security Monitoring:

AWS GuardDuty: Threat detection (enabled across all accounts)
Dependency Scanning: Dependabot PRs (auto-merge patch versions), npm audit / composer audit in CI
Container Security: Trivy/Grype image scanning in CI (fail on HIGH+ vulnerabilities)
Penetration Testing: Quarterly external audits (scheduled with security firm)
Incident Response:

SLA: 24-hour notification for security incidents (stakeholders + affected users)
Runbook: docs/runbooks/security-incident-response.md
Drills: Quarterly tabletop exercises
7.2 PDPA Compliance (Singapore)
Data Protection Principles:

Consent: Explicit, informed, opt-in (stored in consents table with versioned text snapshots)
Purpose Limitation: Data collected only for stated purposes
Notification: Privacy policy accessible from all pages, updated notifications on changes
Access & Correction: User dashboard for data export (JSON/CSV), profile updates
Accuracy: Automated data validation, user-initiated updates
Protection: Encryption (AES-256 at rest, TLS 1.3 in transit), access controls
Retention: 24-month inactivity limit (automated deletion workflows)
Transfer: No international transfers outside Singapore (data residency in ap-southeast-1)
Openness: Transparent privacy policy, data processing disclosures
Accountability: DPO designated, regular compliance audits
Technical Implementation:

Consent Ledger: consents table (polymorphic, version tracking, revocation timestamps)
Audit Logs: 7-year retention (actor, action, before/after state, IP, timestamp) via AuditObserver
Right-to-Access: Automated data export (ZIP with JSON + CSV), 30-day download link expiry
Right-to-Delete: Hard delete from primary DB + backups within 30 days (audit trail retained separately)
Data Minimization: Forms collect only essential fields, optional fields clearly marked
Breach Notification: 72-hour regulatory notification per PDPA, user notifications per impact assessment
Compliance Artifacts:

Privacy Policy: content_translations table (versioned, audited changes)
Terms of Use: Versioned, user acceptance tracked in user_agreements (future Phase 4)
Cookie Consent: Banner with granular preferences (analytics, marketing, functional)
PDPA Statement: Accessible from footer, plain-language explanations
Quarterly Compliance Drills:

Data breach tabletop exercises (Q1, Q3)
Access request simulations (Q2, Q4)
Retention automation audits (ongoing)
7.3 MOH & IMDA Compliance
MOH (Ministry of Health) Requirements:

License Display: Mandatory moh_license_number on all center detail pages
Staff Credentials: Certifications stored in staff table, expiry date tracking
Automated Reminders: Laravel job sends alerts 30 days before license/certification expiry
Emergency Protocols: Required field in centers table, displayed prominently
IMDA (Infocomm Media Development Authority) Accessibility:

WCAG 2.1 AA: Baseline compliance (see Section 9)
Testing: Automated (axe-core, pa11y) + manual (NVDA, VoiceOver) each sprint
Remediation SLA: Critical issues (A11y blocking) fixed within 1 sprint
Key Actions for Agents

Surface compliance impacts in PR descriptions when modifying user data flows
Consult DPO/legal before altering consent, audit, or retention logic
Ensure new features maintain audit logging (extend Auditable trait to new models)
Test PDPA workflows (data export, deletion) in staging before production deploy
Update privacy policy version when data processing changes (coordinate with legal)
8. Performance & Scalability Playbook
8.1 Performance Budgets (Enforced via Lighthouse CI)
Metric	Target	Baseline Measurement	Tool	Enforcement
TTFB	<300ms	180ms (staging avg)	New Relic RUM	Alert on >400ms (P95)
FCP	<1.5s	1.1s (staging avg)	Lighthouse CI	Fail deploy if >2s
LCP	<2.5s	2.1s (staging avg)	Lighthouse CI + New Relic	Fail deploy if >3.5s
TTI	<3s on 3G	2.7s (throttled)	Lighthouse CI	Fail deploy if >4s
CLS	<0.1	0.05 (current)	Lighthouse CI	Warn on >0.15
Page Weight	<280KB	245KB (home), 310KB (center detail ⚠️)	Webpack Bundle Analyzer	Manual review if >300KB
CSS Payload	<20KB	18KB (purged)	PostCSS stats	Fail build if >25KB
JS (initial)	<120KB (gzipped)	98KB (current)	Next.js Build Analyzer	Manual review if >150KB
8.2 Caching Strategy
Multi-Layer Caching:

Layer	TTL	Invalidation Strategy	Use Cases
Cloudflare Edge	1 hour (static), 5 min (dynamic)	Cache-Control headers + API purge	Images, CSS/JS bundles, center listings
Next.js ISR	5 minutes (revalidate)	On-demand revalidation via API route	Center detail pages, testimonials
Redis Application Cache	5 minutes (default), tag-based	Event-driven invalidation (e.g., CenterUpdated → purge center:{id})	API responses, computed aggregates
MySQL Query Cache	Disabled (deprecated in 8.0)	N/A	Use Redis instead
Browser Cache	1 year (immutable assets)	Filename hashing (Next.js)	Fonts, images, compiled JS/CSS
Cache Invalidation Events:

CenterUpdated → Purge center:{id}, center-list:*, Cloudflare /centers/*
BookingConfirmed → Invalidate user's booking list cache
TestimonialApproved → Purge testimonial cache, regenerate center averages
8.3 Database Optimization
Connection Pooling: PgBouncer-style pooling (max 100 connections, min 10 idle)
Read Replicas: Route analytics queries to read replica (current: 1 replica, plan: 2 for production)
Indexes: Full coverage on foreign keys, WHERE clause columns, ORDER BY fields
Slow Query Log: Enabled (>1s queries logged), reviewed weekly, indexed if recurring
Query Optimization: Use EXPLAIN ANALYZE, avoid N+1 (Eloquent eager loading mandatory), prefer whereHas over manual joins
8.4 Asynchronous Processing
Queue-Driven Workflows:

Emails: All transactional/marketing emails queued (high priority for confirmations, default for newsletters)
SMS: Twilio notifications queued (high priority)
Search Indexing: Elasticsearch updates queued (low priority, batch every 5 minutes)
Data Exports: Large exports (>1000 records) queued, download link emailed
Job Performance:

Target: <30s per job (median), <2min (P95)
Monitor: Laravel Horizon dashboard, New Relic custom events
Failure Rate: <1% (alerting threshold)
8.5 Scalability Thresholds & Migration Triggers
Component	Current Capacity	Scaling Trigger	Scaling Strategy
ECS Tasks	4 (staging), 12 (prod)	CPU >70% (5-min avg)	Horizontal auto-scaling (max 50 tasks)
MySQL (Primary)	r5.xlarge (4 vCPU, 32 GB)	Connections >80, CPU >70%	Vertical scale to r5.2xlarge, add read replicas
MySQL (Read Replicas)	1 (prod), 0 (staging)	Read latency >100ms	Add 2nd replica, implement query routing logic
Redis Cluster	3-node (16 GB each)	Memory >80%, evictions >100/min	Add nodes, enable cluster mode resharding
Elasticsearch	3-node (r5.large)	Indexing lag >5 min, query P95 >500ms	Vertical scale initially; evaluate sharding >5M records
S3	Unlimited (AWS limit)	N/A	Lifecycle rules to Glacier (90-day inactive)
Cloudflare	Pro plan (unlimited bandwidth)	N/A	Upgrade to Business if custom WAF rules exceed limits
Microservices Extraction (>100K MAU):

Candidates: NotificationService, SearchService, BookingService
Strategy: Strangler pattern, API Gateway (Kong/Tyk), shared auth via JWT, feature flags for gradual rollout
8.6 Resilience Patterns
Circuit Breakers: Calendly/Twilio/Mailchimp calls wrapped in circuit breakers (5 failures → open, 30s half-open retry)
Retries: Exponential backoff (1s, 5s, 15s) for transient failures
Fallbacks: Manual contact form if Calendly unavailable, degrade to email if SMS fails
Health Checks: /health endpoint (DB connectivity, Redis ping, queue depth) integrated with ALB target group
Rate Limiting: API endpoints throttled (60/min public, 1000/hour authenticated), Redis-backed
Key Actions for Agents

Instrument new endpoints with New Relic custom metrics (newrelic.recordMetric(...))
Validate caching headers (Cache-Control, ETag) when introducing new pages
Add cache invalidation logic when mutating data (dispatch events in services)
Include load testing updates (k6 scripts in tests/load/) when altering critical paths
Profile slow queries via EXPLAIN before committing optimizations
Document scaling triggers when adding resource-intensive features
9. Accessibility & Internationalization
9.1 Accessibility Standards (WCAG 2.1 AA + IMDA)
Automated Testing:

axe-core: Integrated in Jest component tests via jest-axe (enforced in CI)
pa11y: CLI scans via npm run lighthouse (integrates axe + pa11y)
Lighthouse CI: Accessibility score >90 (deployment gate)
Manual Validation (Per Sprint):

Screen Readers: NVDA (Windows), VoiceOver (macOS/iOS), TalkBack (Android)
Keyboard Navigation: Tab order, skip links, focus indicators, no keyboard traps
Zoom/Magnification: 200% zoom test (layout integrity, no horizontal scrolling)
Color Contrast: ≥4.5:1 (normal text), ≥3:1 (large text 18pt+, UI components)
Design System Guarantees:

Radix UI Primitives: Pre-built ARIA patterns (Dialog, Dropdown, Tabs, Accordion)
Focus Management: Visible focus rings (2px solid, theme-based color), focus trapping in modals
Semantic HTML: <nav>, <main>, <article>, <aside>, <header>, <footer> for landmarks
ARIA Live Regions: Form validation errors, toast notifications (aria-live="polite")
Alt Text: Mandatory for all images (enforced via ESLint rule, MediaService validation)
Captions/Transcripts: Required for all video content (Cloudflare Stream auto-captioning + manual review)
Accessibility Features:

Keyboard Shortcuts: Customizable (user preferences in profile)
Reduced Motion: Respects prefers-reduced-motion, disables Framer Motion animations
Adjustable Typography: User preference for font size (100%, 125%, 150%, 200%)
High Contrast Mode (Phase 4 roadmap): Toggle for enhanced contrast ratios
Accessibility Checklist:

Reference: docs/accessibility/accessibility-checklist.md (mandatory sign-off before feature completion)
9.2 Internationalization (i18n)
Supported Locales:

English (en): Default, 100% coverage
Mandarin (zh): 100% coverage (UI strings + content)
Malay (ms): 60% coverage (UI complete, content in progress)
Tamil (ta): 40% coverage (UI in progress, content planned)
Locale Detection Priority:

URL path segment (/{locale}/...)
User profile preference (authenticated users)
NEXT_LOCALE cookie
Accept-Language header
Default: en
Translation Workflow:

UI Strings: JSON files in frontend/locales/{locale}/, managed via next-intl
Rich Content: CMS translation queue (Laravel Nova), translator role approval workflow
Quality Assurance: Native speaker review (outsourced), context screenshots provided
Versioning: Translation memory in database (content_translations table with approval metadata)
Cultural Localization:

Honorifics: Respectful titles (e.g., "Mdm", "Mr", "Ms" in English; "女士", "先生" in Mandarin)
Date/Number Formatting: Intl.DateTimeFormat, Intl.NumberFormat (locale-aware)
English: dd/mm/yyyy, commas for thousands
Mandarin: yyyy年mm月dd日, Chinese numerals option
Malay: dd/mm/yyyy, periods for thousands
Tamil: dd/mm/yyyy, Indian numbering (lakhs/crores)
Currency: SGD (S$) across all locales
Typography: Dynamic line-height for CJK scripts (1.8 vs 1.5 for Latin), font subsetting per locale
Holiday Calendar: Content scheduling respects CNY, Hari Raya, Deepavali, Christmas (Singapore public holidays)
Key Actions for Agents

Use docs/accessibility/accessibility-checklist.md before marking features complete
Mark new translatable strings in frontend/locales/en/*.json, create PRs for translations
Request accessibility review for UI-heavy changes (tag UX/Accessibility lead)
Test with screen readers (NVDA/VoiceOver) for critical user flows
Run npm run lighthouse locally before PR submission (accessibility gate)
Validate color contrast with browser DevTools (target ≥4.5:1)
10. Testing & Quality Assurance
10.1 Automated Testing Standards
Coverage Mandate: ≥90% for critical modules (services, policies, repositories, business logic)

Frontend Testing:

Test Type	Framework	Command	Scope	CI Gate
Unit/Integration	Jest + Testing Library	npm test	Components, hooks, utils	✅ Required
Accessibility	jest-axe	npm test (integrated)	All components	✅ Required
E2E	Playwright	npm run test:e2e	Critical user journeys (booking, registration, search)	✅ Required (smoke tests)
Visual Regression	Percy	npm run percy:storybook	Storybook components	⚠️ Manual review
Performance	Lighthouse CI	npm run lighthouse	Key pages (home, centers, booking)	✅ Score >90 required
Backend Testing:

Test Type	Framework	Command	Scope	CI Gate
Unit	PHPUnit/Pest	./vendor/bin/phpunit --testdox	Services, repositories, helpers	✅ Required
Feature	PHPUnit/Pest	Same	API endpoints, policies, jobs	✅ Required
Integration	PHPUnit (gated)	Same	External APIs (Calendly, Twilio) - skipped if env vars unset	⚠️ Optional (CI skips)
Current Test Status (Phase 3):

Backend: 90 tests, 216 assertions (all passing)
Frontend: TBD (component library complete, E2E in progress)
Coverage: Backend services >85%, target 90% by Phase 4
10.2 Testing Cadences & Thresholds
Test Category	Execution Cadence	Performance Threshold	Failure Action
Unit/Integration	Every commit (pre-commit hook + CI)	<2 min (frontend), <3 min (backend)	Block commit/PR
E2E (Smoke)	Every PR + staging deploy	<10 min (critical paths only)	Block merge
E2E (Full Suite)	Nightly (3:00 SGT)	<30 min	Alert on-call, fix next day
Accessibility	Every PR	100% pass (axe violations)	Block merge
Visual Regression	Weekly (Storybook updates)	Manual review (Percy diffs)	Review before release
Performance (Lighthouse)	Every staging deploy	Score >90 (all categories)	Block production promotion
Load Testing	Pre-launch + quarterly	k6: 1000 concurrent (baseline), 2x stress test	Performance tuning if P95 >3s
10.3 Load & Stress Testing
Baseline Requirements (Pre-Launch):

Tool: k6 (scripts in tests/load/)
Baseline: 1000 concurrent users, 10-minute duration, <3s P95 response time
Stress Test: 2x baseline (2000 concurrent), identify breaking point
Scenarios:
Homepage load (authenticated + anonymous)
Center search (filters, pagination)
Booking creation (happy path)
API endpoints (CRUD operations)
Acceptance Criteria:

P50 <1s, P95 <3s, P99 <5s
Error rate <1%
No database connection pool exhaustion
No memory leaks (stable over 10-min duration)
10.4 Manual QA Protocols
Cross-Browser/Device Testing (BrowserStack):

Browsers: Chrome, Firefox, Safari, Edge (latest 2 versions)
Mobile: iOS Safari (latest), Android Chrome (latest)
Devices: iPhone 12/13, Samsung Galaxy S21, iPad Pro
Cadence: Before each production release
Assistive Technology Validation:

Screen Readers: NVDA (Windows), VoiceOver (macOS/iOS), JAWS (enterprise users)
Magnification: ZoomText, Windows Magnifier (200% zoom)
Voice Control: Dragon NaturallySpeaking (future roadmap)
Cadence: Sprint-based for new features, regression testing before release
Stakeholder UAT:

Participants: Product owner, UX lead, 2-3 target persona representatives
Scope: Major features (booking wizard, virtual tours, testimonials)
Acceptance: Sign-off via checklist (usability, accessibility, content accuracy)
10.5 Definition of Done
A feature is done when:

 Code: Peer-reviewed PR merged to main
 Tests: ≥90% coverage (new code), all tests passing
 Accessibility: axe/pa11y pass, manual screen reader validation
 Performance: Lighthouse >90 (all categories), no performance budget violations
 Security: No new vulnerabilities (Dependabot, image scanning)
 Documentation: docs/ updated, ADR created (if architectural change), runbooks updated (if operational impact)
 Translation: Translatable strings marked, translation PRs created (if user-facing)
 Monitoring: Sentry/New Relic instrumentation added (if new endpoint/feature)
 QA: Manual QA checklist completed, UAT sign-off (if major feature)
 Stakeholder Approval: Product owner sign-off
Key Actions for Agents

Extend test suites when modifying behavior (never reduce coverage)
Run full test suite locally before PR (npm test && composer test && npm run test:e2e)
Attach Lighthouse/axe reports to PRs for UI changes (screenshots required)
Update k6 load test scenarios when adding high-traffic endpoints
Document new test utilities/fixtures for team reuse
Flag flaky tests immediately (create issue, skip if blocking, fix within 1 sprint)
11. Risk Register & Mitigations
Refer to Project_Architecture_Document.md Section 21 for full risk matrix. Highlights:

Risk	Impact	Probability	Mitigation	Status	Owner
External API downtime (Calendly/Twilio)	High	Medium	Circuit breakers, manual fallback UI, status page monitoring, contract SLA	✅ Mitigated	DevOps + Product
Translation delays	Medium	Medium	Translator tooling (Nova), buffer in roadmap, fallback to English, partial launch	🔄 Active	Product
Accessibility regression	High	Low	Automated axe checks (CI gate), quarterly manual audits, design token governance	✅ Mitigated	QA + UX
Performance degradation (media-heavy pages)	High	Medium	ABR video (Cloudflare Stream), lazy loading, perf budgets, CDN tuning, image optimization		
S