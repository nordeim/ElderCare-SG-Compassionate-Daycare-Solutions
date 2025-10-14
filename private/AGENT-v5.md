AGENT.md — ElderCare SG Web Platform (v1.2)
Version: 1.2
Last Updated: 2025-01-10
Primary Source Documents: README.md (authoritative for implementation status), Project_Architecture_Document.md v2.1, Project_Requirements_Document.md, codebase_completion_master_plan.md

Purpose: This guide is the single source of truth for any AI coding agent (and their human facilitators) to understand the ElderCare SG architecture, delivery standards, and operational guardrails before touching the codebase.

⚠️ Version 1.2 Updates
Major Changes from v1.1:

✅ Phase Status Updated: Phases 1-3 completed; current status: Alpha Development
✅ Infrastructure Corrected: Container orchestration is Kubernetes (not ECS Fargate)
✅ Search Service Corrected: Using Elasticsearch 8 (not MeiliSearch)
✅ Testing Expanded: Added comprehensive testing commands, PHPUnit status, and CI integration details
✅ Phase 3 Artifacts Added: Calendly integration, audit logging, API controllers documented
✅ Developer Experience: Added Windows troubleshooting, environment setup, and smoke testing procedures
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
Change Log & Maintenance Guidance
1. Executive Summary
1.1 Product Mission
Deliver a compassionate, accessibility-first digital bridge between Singaporean families and trusted elderly daycare services.

1.2 Primary Outcomes
Empower informed decisions through transparent facility information
Build trust via verified credentials, authentic testimonials, and immersive virtual experiences
Enhance quality of life for seniors and caregivers through seamless booking and engagement
1.3 Target Personas
Persona	Age Range	Key Needs	Design Considerations
Adult Children	30-55	Time-efficient research, social proof, mobile-first	Quick comparison tools, verified reviews, mobile optimization
Family Caregivers	25-45	Multilingual support, transport clarity	i18n excellence, location/transit guidance
Healthcare Professionals	30-60	License verification, referral efficiency	Quick credential lookup, professional UI
Digitally Literate Seniors	55+	Accessibility, straightforward navigation	Large fonts, high contrast, clear CTAs
1.4 Core Value Pillars
Trust & Transparency: MOH license verification, staff credentials, authentic testimonials
Accessibility: WCAG 2.1 AA compliance, screen reader optimization, keyboard navigation
Cultural Resonance: Multicultural imagery, respectful language, holiday-aware content
Seamless Engagement: Frictionless booking, proactive reminders, preference management
Regulatory Compliance: PDPA, MOH, IMDA guidelines baked into every subsystem
1.5 North-Star Success Metrics
Metric	Target	Measurement Tool	Owner	Review Cadence
Visit bookings increase	+30% in 3 months	GA4 conversions + booking DB	Marketing	Monthly
Mobile bounce rate	<40%	GA4 device segmentation	UX	Bi-weekly
Lighthouse Performance	>90	Lighthouse CI	DevOps/QA	Each deploy
Lighthouse Accessibility	>90	Lighthouse CI + axe-core	DevOps/QA	Each deploy
Session duration	>5 minutes	GA4 engagement	Marketing	Monthly
Form completion	>75%	Hotjar form analytics	UX	Monthly
3G load time	<3 seconds	WebPageTest (Singapore)	DevOps	Weekly
Video engagement (virtual tours)	>60% completion	Cloudflare Stream analytics	Marketing	Monthly
1.6 Operational Constraints & Assumptions
Availability SLA: 99.5% uptime
Team Size: 6-8 engineers
Browser Support: Latest 2 versions of Chrome, Firefox, Safari, Edge
Mobile Traffic Forecast: 70% of total traffic
Service Costs: Calendly Pro (~US
12
/
u
s
e
r
/
m
o
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
12/user/mo),MailchimpStandard( US20/mo), Laravel Nova (US
199
/
y
r
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
199/yr),CloudflarePro(US20/mo), Twilio SMS (~SGD$0.07/msg)
1.7 Out of Scope (Current Release)
Native mobile apps
Payment processing
Caregiver matching algorithms
Medical record integration
Telehealth services
Live chat support
Provider self-service portals
Family shared accounts
Direct government database integrations
Key Actions for Agents

Review README.md and this guide before any contribution
Align feature work with success metrics and value pillars
Confirm stakeholder intent via latest roadmap milestones
Respect out-of-scope boundaries unless explicitly approved by Product
2. System Overview
2.1 Architecture Pattern
Service-oriented Laravel monolith with modular domain boundaries, shared MySQL schema, and microservices-ready abstractions for future scale.

2.2 Hosting Footprint
Cloud Provider: AWS (Singapore region ap-southeast-1)
Container Orchestration: Kubernetes (pods for frontend, backend, workers)
CDN & Security: Cloudflare (edge caching, WAF, DDoS mitigation)
Infrastructure as Code: Terraform manages all AWS resources
2.3 Environments
Environment	Purpose	Deploy Trigger	Infrastructure	Observability
Local	Development	Manual (docker-compose up)	Docker Compose	Console logs, Laravel Telescope
Staging	Pre-production testing	Auto-deploy on main merge	Kubernetes (AWS)	Sentry, New Relic, CloudWatch
Production	Live platform	Manual approval + change ticket	Kubernetes (multi-AZ), RDS read replica	Full stack (Sentry, New Relic, CloudWatch, UptimeRobot, ELK)
2.4 Key Architectural Principles
Domain Separation: Clear boundaries between Users, Centers, Bookings, Content, Integrations
API-First: All functionality exposed via versioned RESTful APIs (/api/v1)
Progressive Enhancement: Core content/forms work without JavaScript
Accessibility-First: WCAG 2.1 AA integrated from design to deployment
Security-by-Design: Layered defenses, least privilege, encryption everywhere
Automated Governance: CI/CD gates for tests, security scans, accessibility audits, performance budgets
2.5 High-Level Architecture Diagram
mermaid

flowchart TB
    subgraph "Users"
        Browser[Web & Mobile Browsers]
        Crawler[Search Crawlers]
    end

    subgraph "Cloudflare Edge"
        CDN[Edge Cache & WAF]
    end

    subgraph "Kubernetes Cluster — AWS ap-southeast-1"
        NextPod[Next.js Pods]
        LaravelPod[Laravel API Pods]
        WorkerPod[Queue Worker Pods]
    end

    subgraph "Data Layer"
        MySQLPrimary[(MySQL 8.0 Primary)]
        MySQLReplica[(MySQL Read Replica)]
        Redis[(Redis 7 Cluster)]
        Elastic[(Elasticsearch 8)]
        S3[(AWS S3 Media)]
    end

    subgraph "External Services"
        Calendly[Calendly API]
        Mailchimp[Mailchimp API]
        Twilio[Twilio SMS]
        Stream[Cloudflare Stream]
    end

    subgraph "Observability"
        Sentry[Sentry Errors]
        NewRelic[New Relic APM]
        CloudWatch[CloudWatch Logs]
        UptimeRobot[UptimeRobot]
        ELK[ELK Stack]
    end

    Browser --> CDN --> NextPod
    Crawler --> CDN
    NextPod --> LaravelPod
    LaravelPod --> MySQLPrimary
    LaravelPod --> MySQLReplica
    LaravelPod --> Redis
    LaravelPod --> Elastic
    LaravelPod --> S3
    LaravelPod --> Calendly
    LaravelPod --> Mailchimp
    LaravelPod --> Twilio
    NextPod --> Stream
    WorkerPod --> Redis
    WorkerPod --> Mailchimp
    WorkerPod --> Twilio
    LaravelPod --> Sentry
    LaravelPod --> NewRelic
    LaravelPod --> CloudWatch
    NextPod --> NewRelic
    UptimeRobot -.-> CDN
    LaravelPod --> ELK
2.6 Data Flow Patterns
Write Path (e.g., Create Booking):

User submits booking form → Next.js validates client-side
POST /api/v1/bookings → Laravel BookingRequest validation
BookingController delegates to BookingService
BookingService opens DB transaction:
Creates Booking record (status: pending)
Calls CalendlyService::createEvent()
Updates booking status: confirmed
Dispatches BookingConfirmed event
Transaction commits; events fire:
AuditObserver logs change to audit_logs
SendBookingConfirmationEmail job queued
SendBookingSMS job queued
Redis cache invalidated for user's bookings
Queue workers process jobs asynchronously
API returns 201 Created with BookingResource
Read Path (e.g., Center Detail):

User navigates to /en/centers/sunshine-care
Next.js SSR fetches GET /api/v1/centers/sunshine-care?locale=en
Laravel checks Redis cache (key: center:sunshine-care:en, TTL: 5 min)
If cache miss: CenterRepository queries MySQL read replica
CenterResource transforms data (localized fields)
Response cached in Redis with tag centers
Next.js renders RSC HTML
Cloudflare edge caches HTML (per cache rules)
Subsequent requests served from edge until invalidation
Key Actions for Agents

Confirm environment context before running commands (.env, frontend/.env.local)
Respect architecture principles when proposing modifications
Check Terraform state before infra-affecting changes
Use proper cache invalidation when mutating data
3. Frontend Blueprint
3.1 Stack
Technology	Version	Purpose
Next.js	14	React framework with App Router, RSC
React	18	UI library
TypeScript	5	Type safety
Tailwind CSS	3	Utility-first styling (JIT, < 20KB prod CSS)
Radix UI	Latest	Accessible primitives
Framer Motion	10	Animations (reduced-motion aware)
React Query	4	Server state management
Zustand	4	Global client state
3.2 Directory Structure (frontend/)
text

frontend/
├── app/
│   ├── [locale]/              # Dynamic locale routing (en, zh, ms, ta)
│   │   ├── layout.tsx         # Root layout (header, footer, providers)
│   │   ├── page.tsx           # Home (RSC)
│   │   ├── centers/
│   │   │   ├── page.tsx       # Listing (RSC)
│   │   │   └── [slug]/page.tsx# Detail (RSC)
│   │   ├── services/page.tsx  # Service catalog
│   │   ├── booking/page.tsx   # Booking flow (Client Component)
│   │   ├── dashboard/page.tsx # Authenticated user area
│   │   └── ...
│   ├── api/                   # Next.js route handlers (proxies, edge cases)
│   └── globals.css            # Tailwind imports
├── components/
│   ├── atoms/                 # Button, Input, Icon, Badge
│   ├── molecules/             # FormField, Card, NavItem
│   ├── organisms/             # Header, Footer, BookingForm
│   ├── templates/             # Page layout wrappers
│   └── providers/             # AnalyticsProvider, AuthProvider, ThemeProvider
├── hooks/                     # useAuth, useBooking, useTranslation
├── lib/                       # API client, utils, Zod schemas
├── locales/                   # en/, zh/, ms/, ta/ JSON files
├── store/                     # Zustand stores (persisted)
├── tests/                     # Jest, Testing Library, Playwright specs
├── types/                     # Shared TypeScript definitions
└── middleware.ts              # Locale detection, auth guards
3.3 State Management
Server State (@tanstack/react-query): Bookings, centers, testimonials. 5-min stale time, background refetch.
Global Client State (Zustand + persistence): Auth session, locale preference, UI toggles, feature flags.
Local State: Form inputs, modal visibility; degrades gracefully if JS disabled.
3.4 Asset Optimization
Images: Next.js <Image> component → responsive WebP with JPEG fallback, LQIP placeholders, lazy loading
Fonts: Self-hosted via next/font (Inter, Noto Sans SC, Noto Sans) with subsetting and font-display: swap
CSS: Tailwind JIT + Purge → <20KB production payload
Code Splitting: Dynamic imports for heavy modules (map view, booking wizard stepper)
3.5 Rendering Strategy
Default: React Server Components for SEO-critical pages (home, center detail, services)
Client Components: Interactive flows (booking wizard, forms with real-time validation, modals)
Progressive Enhancement: Forms submit via standard POST when JS unavailable; enhanced with inline validation and toasts when enabled
3.6 Accessibility Tooling
Automated: axe-core via jest-axe in component tests, Lighthouse CI (accessibility score >90)
Manual: NVDA/VoiceOver audits per sprint, keyboard-only navigation testing, 200% zoom verification
Design Tokens: Enforce ≥4.5:1 contrast ratios (≥3:1 for large text); high-contrast theme toggle planned
Key Actions for Agents

Adhere to design tokens in docs/design-system/ and Storybook
Favor RSC for data-heavy views; justify CSR in PR notes
Instrument new components with analytics data attributes
Use <Image> for all non-SVG images
Test keyboard navigation and screen reader experience before PR
4. Backend Blueprint
4.1 Stack
Technology	Version	Purpose
Laravel	12	PHP framework
PHP	8.2	Language runtime
MySQL	8.0	Primary database (ACID, InnoDB)
Redis	7	Caching, sessions, queues
Elasticsearch	8	Full-text search, center discovery
Laravel Sanctum	Latest	API authentication
Laravel Nova	Latest	Admin panel (US$199/yr)
4.2 Directory Structure (backend/app/)
text

backend/app/
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── Auth/
│   │   │   ├── RegisterController.php
│   │   │   ├── LoginController.php
│   │   │   └── PasswordController.php
│   │   ├── BookingController.php
│   │   ├── CenterController.php
│   │   ├── TestimonialController.php
│   │   └── ...
│   ├── Requests/Api/V1/
│   │   ├── RegisterRequest.php
│   │   ├── BookingRequest.php
│   │   └── ...
│   └── Resources/Api/V1/
│       ├── BookingResource.php
│       ├── CenterResource.php
│       └── ...
├── Services/
│   ├── AuthService.php
│   ├── BookingService.php
│   ├── CenterService.php
│   ├── ContentService.php
│   ├── UserService.php
│   ├── ConsentService.php
│   ├── AuditService.php
│   ├── NotificationService.php
│   ├── MediaService.php
│   ├── SearchService.php
│   └── Integration/
│       ├── CalendlyService.php
│       ├── MailchimpService.php
│       └── TwilioService.php
├── Repositories/
│   ├── BookingRepository.php
│   ├── CenterRepository.php
│   └── ...
├── Events/
│   ├── BookingConfirmed.php
│   ├── UserRegistered.php
│   └── ...
├── Listeners/
│   ├── SendBookingConfirmation.php
│   ├── InvalidateCenterCache.php
│   └── ...
├── Jobs/
│   ├── SendBookingConfirmationEmail.php
│   ├── SendBookingSMS.php
│   ├── SyncElasticsearch.php
│   └── ...
├── Policies/
│   ├── BookingPolicy.php
│   ├── CenterPolicy.php
│   └── ...
├── Observers/
│   └── AuditObserver.php       # Auto-logs model changes
├── Traits/
│   └── Auditable.php           # Opt-in trait for audit logging
└── Exceptions/
    ├── CalendlyNotConfiguredException.php
    └── ...
4.3 Service Layer Architecture
Strict Separation of Concerns:

Controllers (Thin): Handle HTTP, validate via Form Requests, delegate to Services
Services (Business Logic): Orchestrate workflows, enforce rules, dispatch events
Repositories (Data Access): Encapsulate queries, caching, eager loading
Observers (Cross-Cutting): AuditObserver auto-logs changes for Auditable models
Jobs (Async Work): Email, SMS, search indexing, analytics sync
Policies (Authorization): RBAC enforcement per resource
Example: BookingService

PHP

class BookingService
{
    public function createBooking(array $validated, User $user): Booking
    {
        return DB::transaction(function () use ($validated, $user) {
            // 1. Create booking (pending)
            $booking = $this->repository->create([
                'user_id' => $user->id,
                'status' => 'pending',
                ...$validated
            ]);

            // 2. Integrate with Calendly
            try {
                $calendlyEvent = $this->calendlyService->createEvent([
                    'event_type_uuid' => $validated['service']->calendly_event_type_id,
                    'invitee_email' => $user->email,
                    'start_time' => $validated['preferred_date'],
                ]);
                
                $booking->update([
                    'status' => 'confirmed',
                    'calendly_event_id' => $calendlyEvent['id'],
                ]);
            } catch (CalendlyException $e) {
                // Compensating action: mark as failed, alert admin
                $booking->update(['status' => 'failed']);
                Log::error('Calendly booking failed', ['booking_id' => $booking->id]);
                throw $e;
            }

            // 3. Dispatch events
            event(new BookingConfirmed($booking));

            return $booking;
        });
    }
}
4.4 API Design Standards
Base URL: https://api.eldercare.sg/v1/ (production), http://localhost:8000/api/v1/ (local)
Versioning: Path-based (/api/v1/); minimum 6-month deprecation for breaking changes
Authentication: Laravel Sanctum bearer tokens; MFA for admin/moderator roles
Pagination: ?page=1&per_page=20; responses include meta and links blocks
Filtering: filter[city]=Singapore&filter[status]=active
Sorting: sort=-created_at (prefix - for descending)
Error Schema:
JSON

{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "date": ["The selected date is invalid."]
  }
}
Rate Limiting: 60 req/min per IP (public), 1000 req/hour per authenticated user
Documentation: OpenAPI 3.0 spec + Postman collection (see docs/api/)
4.5 Queue & Job Processing
Driver: Redis-backed queues
Management: Laravel Horizon (dashboard at /horizon in staging/prod)
Worker Pools: Sized per environment (local: 1 worker, staging: 2, production: 5-10 auto-scaled)
Priority Queues:
high: SMS, critical emails (1-min max delay)
default: Transactional emails, notifications (5-min max delay)
low: Analytics sync, search indexing (15-min max delay)
Failure Handling: Exponential backoff (1m, 5m, 15m), logged to failed_jobs + Sentry, replayable via Nova
Delayed Jobs: Booking reminders (72h, 24h before), post-visit follow-ups (7 days after)
4.6 Static Analysis & Code Quality
PHPStan Level 8: Enforced in CI/CD (strict type checking)
PHP-CS-Fixer: PSR-12 compliance
Pest/PHPUnit: ≥90% coverage mandate for critical modules (auth, bookings, payments, consent)
Key Actions for Agents

Consult Project_Architecture_Document.md Section 6-7 for detailed module responsibilities
Maintain service/repository layering; avoid fat controllers
Update job/event documentation when introducing async flows
Ensure new models use Auditable trait if they handle personal data
Run composer dump-autoload -o && php artisan package:discover --ansi after adding new classes
Check PHPStan before committing: ./vendor/bin/phpstan analyse
5. Data & Integrations
5.1 Database Architecture
Primary Store: MySQL 8.0 (AWS RDS, multi-AZ for production)

Technical Standards:

Character Set: UTF8MB4 (full multilingual support including emoji)
Storage Engine: InnoDB (ACID compliance, foreign key constraints)
Collation: utf8mb4_unicode_ci
Audit Retention: 7 years (regulatory requirement)
Backup Strategy: Automated RDS snapshots (35-day retention), daily cross-account replication
Schema Highlights (18 tables, see database_schema.sql for full DDL):

Table	Purpose	Key Compliance Features
users	Authentication, roles	Soft deletes, MFA flags, PDPA-aware
profiles	User demographics	Preferred language, accessibility preferences
centers	Facility data	moh_license_number, accreditation_status, geolocation
services	Programs offered	calendly_event_type_id, pricing, staff ratio
bookings	Visit scheduling	Lifecycle status, questionnaire JSON, cancellation tracking
testimonials	User reviews	Moderation workflow, verified badge, media consent
consents	PDPA consent ledger	Versioned consent text, granted/revoked timestamps
audit_logs	Change tracking (polymorphic)	Actor, action, before/after hashes, IP, user agent
media	S3 references (polymorphic)	Alt text, consent flag, moderation status
content_translations	Localized content (polymorphic)	Language code, approval workflow
Existing Migration Scripts (⚠️ Review before creating new migrations):

text

backend/database/migrations/2024_01_01_000001_create_users_table.php
backend/database/migrations/2024_01_01_000002_create_password_reset_tokens_table.php
backend/database/migrations/2024_01_01_000003_create_failed_jobs_table.php
backend/database/migrations/2024_01_01_000004_create_personal_access_tokens_table.php
backend/database/migrations/2024_01_01_000005_create_jobs_table.php
backend/database/migrations/2024_01_01_100001_create_profiles_table.php
backend/database/migrations/2024_01_01_100010_create_consents_table.php
backend/database/migrations/2024_01_01_100011_create_audit_logs_table.php
backend/database/migrations/2024_01_01_200000_create_centers_table.php
backend/database/migrations/2024_01_01_200001_create_faqs_table.php
backend/database/migrations/2024_01_01_200002_create_subscriptions_table.php
backend/database/migrations/2024_01_01_200003_create_contact_submissions_table.php
backend/database/migrations/2024_01_01_300000_create_services_table.php
backend/database/migrations/2024_01_01_300001_create_staff_table.php
backend/database/migrations/2024_01_01_400000_create_bookings_table.php
backend/database/migrations/2024_01_01_400001_create_testimonials_table.php
backend/database/migrations/2024_01_01_500000_create_media_table.php
backend/database/migrations/2024_01_01_500001_create_content_translations_table.php
Entity Relationship Diagram:

mermaid

erDiagram
    USERS ||--o| PROFILES : has
    USERS ||--o{ BOOKINGS : makes
    USERS ||--o{ TESTIMONIALS : writes
    USERS ||--o{ CONSENTS : grants
    USERS ||--o{ AUDIT_LOGS : generates

    CENTERS ||--o{ SERVICES : offers
    CENTERS ||--o{ BOOKINGS : receives
    CENTERS ||--o{ TESTIMONIALS : features
    CENTERS ||--o{ MEDIA : showcases
    CENTERS ||--o{ CONTENT_TRANSLATIONS : localizes

    SERVICES ||--o{ BOOKINGS : scheduledFor
    SERVICES ||--o{ CONTENT_TRANSLATIONS : localizes

    TESTIMONIALS ||--o{ CONTENT_TRANSLATIONS : localizes
    FAQS ||--o{ CONTENT_TRANSLATIONS : localizes

    SUBSCRIPTIONS }o--|| USERS : mayBelongTo
5.2 Caching & Supporting Services
Service	Technology	Purpose	Configuration
Cache	Redis 7 (ElastiCache cluster - production)	Application cache, rate limiting	TTL: 5 min (default), tag-based invalidation
Sessions	Redis 7	User sessions	Encrypted, 2-week lifetime
Queues	Redis 7	Job queue driver	Horizon-managed, priority queues
Search	Elasticsearch 8	Full-text search, center/service discovery	Async indexing via jobs
Media Storage	AWS S3 (ap-southeast-1)	Images, videos, documents	Lifecycle rules, versioning, cross-region replication
5.3 External Integrations
Integration	Purpose	Interface	Security	Status
Calendly	Booking orchestration	REST API	OAuth token via AWS Secrets Manager, 90-day rotation	✅ Implemented (CalendlyService.php)
Mailchimp	Newsletter management	REST API	API key via Secrets Manager, 90-day rotation	✅ Implemented
Twilio	SMS notifications	REST API	API key via Secrets Manager, 90-day rotation	✅ Implemented
Cloudflare Stream	Virtual tour videos	REST + signed URLs	JWT-signed tokens	🚧 Planned (Phase 2 enhancements)
GA4	Web analytics	gtag.js + Measurement Protocol	Consent-gated, IP anonymization	✅ Implemented (AnalyticsProvider)
Hotjar	UX insights (heatmaps, recordings)	JS snippet	Consent-gated, form recording disabled	✅ Implemented
Sentry	Error tracking	SDK	DSN via env	✅ Implemented
New Relic	APM & RUM	Agent	License key via Secrets Manager	✅ Implemented
Integration Best Practices:

Circuit Breakers: Graceful degradation on external service failure (e.g., show manual contact form if Calendly down)
Webhook Verification: Calendly/Mailchimp webhooks verify signatures before processing
Secrets Rotation: 90-day automated rotation via AWS Secrets Manager
Contract Testing: Mock all external APIs in automated test suites
Monitoring: Sentry alerts on integration failures; New Relic tracks external service latency
5.4 Data Governance & PDPA Compliance
Data Residency: All production data confined to AWS ap-southeast-1 (Singapore)
Retention Policies:
Active accounts: Indefinite (with ongoing consent)
Inactive accounts: Flagged at 18 months, auto-deleted at 24 months (unless legal hold)
Audit logs: 7-year retention
Media with consent: Retained per consent terms
Right-to-be-Forgotten: Automated data export (JSON + CSV ZIP), anonymization workflows, cascading deletes
Encryption:
At rest: RDS encryption (AES-256), S3 encryption (SSE-S3)
In transit: TLS 1.3 for all connections
Application: bcrypt 12 rounds for passwords
Key Actions for Agents

Validate migration impact on retention/consent rules before altering schema
Mock third-party APIs in tests; never call live services from automated suites
Use existing migrations; only create new ones if feature truly doesn't exist (check database_schema.sql)
Ensure new personal data fields trigger AuditObserver logging
Document new integration touchpoints in this guide and relevant runbooks
6. Operational Maturity
6.1 CI/CD Pipeline (GitHub Actions)
Workflow Stages:

Lint & Format: ESLint (frontend), PHP-CS-Fixer (backend), PHPStan level 8
Unit Tests: Jest (npm test), PHPUnit (composer test)
Integration Tests: React Testing Library, Laravel HTTP tests
E2E Tests: Playwright smoke tests on critical flows
Security Scans: npm audit, composer audit, Dependabot alerts, container image scanning
Accessibility Checks: axe-core via jest-axe, Lighthouse CI (score >90 gate)
Performance Checks: Lighthouse CI (performance >90, budget enforcement)
Build Artifacts: Next.js production build, Laravel optimize
Deploy to Staging: Auto-deploy on main merge (Kubernetes rolling update)
Production Deploy: Manual approval + change ticket required (Kubernetes blue-green)
Branch Protection:

main branch requires:
All CI checks pass
1+ peer review approval
Up-to-date with base branch
No merge commits (rebase workflow preferred)
6.2 Infrastructure as Code (Terraform)
Managed Resources:

VPC, subnets, security groups
Kubernetes cluster (EKS) + node groups
RDS (MySQL 8.0, multi-AZ for production)
ElastiCache (Redis cluster for production)
S3 buckets (media, backups, logs)
IAM roles & policies (least privilege)
AWS Secrets Manager (external service credentials)
CloudWatch alarms & dashboards
Route 53 DNS records
Terraform State:

Stored in S3 with DynamoDB locking
Separate workspaces for staging/production
No manual console edits allowed (enforce via CloudTrail alarms)
6.3 Monitoring & Observability
Tool	Purpose	Key Dashboards	Alerting
Sentry	Error tracking, release tracking	Error frequency, affected users, stack traces	Slack #eldercare-alerts on critical errors
New Relic	APM, RUM, Core Web Vitals	Transaction traces, Apdex, frontend performance	PagerDuty on Apdex <0.85
CloudWatch	Infrastructure metrics, logs	CPU/memory utilization, RDS connections, Lambda errors	SNS → PagerDuty on threshold breaches
UptimeRobot	Synthetic uptime monitoring	Availability %, response times	Email + SMS on downtime >2 min
ELK Stack	Log aggregation, search	Centralized logs, error patterns, audit trails	Kibana saved searches → Slack
Lighthouse CI	Performance budgets	Lighthouse scores, Core Web Vitals trends	CI failure on score <90
On-Call Rotation:

24/7 coverage via PagerDuty
Escalation: On-call engineer → Lead → CTO (15 min → 30 min → 1 hour)
Incident response runbook: docs/runbooks/incident-response.md
6.4 Backup & Disaster Recovery
RTO (Recovery Time Objective): <4 hours
RPO (Recovery Point Objective): <1 hour

Asset	Backup Strategy	Retention	Recovery Procedure
MySQL	RDS automated backups + daily snapshots	35 days	Restore from snapshot → update DNS → validate
Redis	Daily snapshots	7 days	Rebuild cache (warm-up scripts)
S3 Media	Versioning enabled, cross-region replication (ap-northeast-1)	Indefinite (lifecycle rules archive to Glacier after 1 year)	S3 restore from version or replica region
Application Config	Git repo + Terraform state in S3	Indefinite	git clone + terraform apply
DR Drill Schedule: Quarterly (next: Q2 2025)

6.5 Runbooks
Located in docs/runbooks/:

incident-response.md — On-call procedures, escalation paths
disaster-recovery.md — Full system recovery playbook
compliance-audit.md — PDPA audit evidence collection
feature-toggles.md — Feature flag management (LaunchDarkly/OSS)
deployment.md — Step-by-step deployment procedures
troubleshooting.md — Common issues and resolutions
Key Actions for Agents

Check CI status before requesting review; fix failures locally first
Coordinate infra changes via Terraform PRs; no manual AWS console edits
Update runbooks when altering operational flows
Reference monitoring dashboards when debugging production issues
Follow on-call escalation procedures for P0/P1 incidents
7. Security & Compliance
7.1 Regulatory Scope
PDPA (Singapore): Personal Data Protection Act — data residency, consent, access/export/delete rights
MOH Guidelines: Ministry of Health eldercare service display requirements (license verification, staff credentials)
IMDA: Infocomm Media Development Authority accessibility guidelines
WCAG 2.1 AA: Web Content Accessibility Guidelines baseline
7.2 Security Architecture
Authentication & Authorization:

User Authentication: Laravel Sanctum (token-based), bcrypt 12 rounds for passwords
Admin/Moderator: MFA required (TOTP via Google Authenticator)
Session Management: Redis-backed, secure cookies (HttpOnly, SameSite=Strict, Secure flag)
Authorization: RBAC via Laravel Policies + Gates, granular abilities (view PII, approve content, manage translations)
Transport & Application Security:

Layer	Implementation	Standard
TLS	TLS 1.3 (Cloudflare → origin, origin → services)	IETF RFC 8446
HSTS	Preload list submission	2-year max-age
CSP	Strict Content-Security-Policy header	script-src 'self' 'nonce-*'; object-src 'none'
CSRF	Laravel CSRF middleware (all state-changing requests)	Synchronizer token pattern
XSS Prevention	Auto-escaping (Blade, React), DOMPurify for rich text	OWASP XSS Prevention
SQLi Prevention	Eloquent ORM, parameterized queries	OWASP SQL Injection Prevention
Clickjacking	X-Frame-Options: DENY	OWASP Clickjacking Defense
MIME Sniffing	X-Content-Type-Options: nosniff	Chromium security guidelines
Secrets & Credentials:

Storage: AWS Secrets Manager (RDS, Redis, external APIs)
Rotation: 90-day automated rotation (Lambda-triggered)
Access: IAM least privilege, assumed roles for pods
Auditing: CloudTrail logs all secret access
Infrastructure Security:

AWS GuardDuty: Threat detection, malicious IP blocking
VPC: Private subnets for databases, NAT gateway for egress
Security Groups: Least privilege, deny-by-default
Dependabot: Automated dependency PRs, weekly review
Container Scanning: Trivy in CI/CD (fail on HIGH/CRITICAL vulns)
Monitoring & Response:

Incident Notification SLA: 24 hours for security incidents (Sentry → Slack → PagerDuty)
Quarterly PDPA Drills: Simulated data breach, audit log review, right-to-be-forgotten testing
Penetration Testing: Annual third-party pentest (next: Q3 2025)
7.3 PDPA Compliance Implementation
Consent Management:

Consent Ledger: consents table stores versioned consent text, granted/revoked timestamps, channel (web, email)
Opt-In: Explicit consent required for newsletter, analytics cookies, testimonial media use
Granular Preferences: Users control marketing, analytics, media sharing independently
Withdrawal: One-click unsubscribe, immediate effect
Data Subject Rights:

Right to Access: User dashboard shows all stored data
Right to Export: ExportUserDataJob generates ZIP (JSON + CSV), secure download link (30-day expiry)
Right to Delete: DeleteUserAccountJob anonymizes records, cascades deletes, updates audit log
Right to Object: Preference center allows opting out of processing (e.g., analytics)
Audit Trail:

Automatic Logging: AuditObserver on all models with Auditable trait
Log Fields: actor_id, action, auditable_type, auditable_id, old_values (hashed), new_values (hashed), ip_address, user_agent, created_at
Retention: 7 years
Access Controls: Admin-only, API endpoint for compliance officer
MOH Compliance:

License Verification: centers.moh_license_number required, expiry date tracked
Auto-Alerts: CheckMOHLicenseExpiryJob (daily cron) emails admins 30 days before expiry
Staff Credentials: staff.qualifications JSON column, verified badges on profiles
7.4 Compliance Artifacts
Privacy Policy: Versioned in CMS, change log in audit_logs
Terms of Use: Versioned in CMS
PDPA Statement: Dedicated page, last updated 2024-10-15
Accessibility Statement: WCAG 2.1 AA conformance claim, audit report linked
Key Actions for Agents

Surface compliance impacts in PR descriptions (e.g., "New field stores phone number → PII → add to export job")
Consult compliance officer before modifying consent/audit flows
Ensure new features with personal data use Auditable trait and consent checks
Review security headers in responses (use SecurityHeadersMiddleware)
Never commit secrets to repo (pre-commit hook enforced)
8. Performance & Scalability Playbook
8.1 Performance Budgets
Metric	Target	Measurement Tool	Enforcement
TTFB	<300ms	Lighthouse CI, New Relic	CI gate fails if >400ms (3 consecutive runs)
FCP	<1.5s	Lighthouse CI	CI gate fails if >2s
LCP	<2.5s	Lighthouse CI, New Relic RUM	CI gate fails if >3s
TTI	<3s on 3G	WebPageTest (Singapore, 3G Fast)	Weekly monitoring, alert if >4s
CLS	<0.1	Lighthouse CI	CI gate fails if >0.25
Page Weight	<280KB (compressed)	Lighthouse CI	CI gate fails if >350KB
CSS Payload	<20KB (production)	Tailwind build output	Manual review in PR
JS Payload (initial)	<150KB (compressed)	Next.js build analyzer	Manual review in PR
8.2 Caching Strategy
Multi-Layer Caching:

Layer	Technology	TTL	Invalidation
CDN Edge	Cloudflare	Varies by route (static: 7 days, HTML: 5 min)	Purge on deploy, tag-based via API
Application	Redis (ElastiCache cluster)	5 min (default), 1 hour (center details), 24 hours (static content)	Tag-based (centers, services, testimonials)
Database Query	Redis	5 min	Automatic on model updates (via observers)
Next.js ISR	Filesystem + CDN	Incremental revalidation (per page config)	revalidatePath() on mutations
Cache Tags Example:

PHP

// In CenterRepository
Cache::tags(['centers', "center:{$center->id}"])
    ->remember("center:{$center->slug}:en", 3600, function () {
        return $this->query()->with('services', 'media')->find($id);
    });

// Invalidate on update
Cache::tags(['centers', "center:{$center->id}"])->flush();
8.3 Scaling Strategy
Horizontal Scaling:

Frontend Pods: Kubernetes HPA (CPU >70% → scale up to 10 replicas)
Backend Pods: Kubernetes HPA (CPU >70%, memory >80% → scale up to 15 replicas)
Queue Workers: Kubernetes HPA based on SQS queue depth (>100 jobs → scale up to 10 workers)
Database: Read replicas for heavy read workloads; sharding strategy documented for future regional pods (>1M centers)
Vertical Scaling:

Redis: Upgrade to larger ElastiCache node if memory >80% sustained
Elasticsearch: Scale vertically initially; evaluate cluster expansion beyond 5M documents
Load Testing Benchmarks:

Baseline: 1000 concurrent users, <500ms p95 response time
Stress Test: 2x baseline (2000 concurrent), <1s p95 response time
Tools: k6 scripts in tests/load/, executed pre-launch and quarterly
8.4 Database Optimization
Indexes: Composite indexes on common query patterns (see database_schema.sql)
Full-Text Search: Offloaded to Elasticsearch (async indexing via jobs)
Connection Pooling: PgBouncer-equivalent for MySQL (ProxySQL considered)
Read Replicas: Read-heavy queries routed to replicas via Laravel DB::connection('mysql_read')
Query Monitoring: New Relic DB analysis, slow query log (>1s threshold)
8.5 Media Optimization
Images: Responsive srcsets via Next.js <Image>, WebP with JPEG fallback, LQIP placeholders
Videos (Phase 2): Cloudflare Stream adaptive bitrate (ABR), low-bandwidth warning for <2 Mbps connections
Lazy Loading: Intersection Observer for images/videos below fold
8.6 Profiling & Debugging Tools
Backend: Laravel Telescope (local/staging), New Relic APM (production), php artisan tinker
Frontend: React Profiler, Next.js build analyzer, Chrome DevTools Performance tab
Network: Cloudflare Analytics, New Relic Browser (RUM)
Key Actions for Agents

Instrument new endpoints with performance metrics (New Relic custom transactions)
Validate caching headers/TTL when introducing new pages
Run Lighthouse CI locally before PR (npm run lighthouse)
Include load test updates when altering critical paths (booking, search)
Monitor Core Web Vitals dashboard after deploy (New Relic → "Web Vitals")
9. Accessibility & Internationalization
9.1 Accessibility Standards (WCAG 2.1 AA)
Core Features:

Keyboard Navigation: Full site navigable without mouse (Tab, Enter, Esc, Arrow keys)
Screen Reader Support: Semantic HTML5, ARIA labels/roles/live regions, tested with NVDA (Windows), VoiceOver (macOS/iOS)
Focus Management: Visible focus indicators (≥3px outline, 4.5:1 contrast), focus trapping in modals, skip links
Color Contrast: ≥4.5:1 for normal text, ≥3:1 for large text (18pt+/14pt+ bold), verified via axe-core
Adjustable Typography: User preference for font size (browser zoom support, no breakage at 200%)
Motion Control: prefers-reduced-motion respected (Framer Motion disabled for users with preference)
Multimedia: Video captions (WebVTT), audio descriptions (Phase 2), transcript links
Forms: Label association, error identification (ARIA live regions), help text, multi-step progress indicators
Accessibility Testing:

Type	Tool	Cadence	Owner
Automated	axe-core via jest-axe	Every component test	Developers
Automated	Lighthouse CI	Every deploy	CI/CD pipeline
Manual	NVDA screen reader	Sprint review (bi-weekly)	QA
Manual	VoiceOver	Sprint review (bi-weekly)	QA
Manual	Keyboard-only navigation	Sprint review (bi-weekly)	QA
Manual	200% zoom test	Sprint review (bi-weekly)	QA
Manual	Color contrast audit	Design review	UX Designer
Third-party audit	External accessibility firm	Annually	Compliance Officer
9.2 Internationalization (i18n)
Supported Languages:

English (en): Default, 100% coverage
Mandarin (zh): Simplified Chinese, 100% coverage (UI + content)
Malay (ms): 90% coverage (Phase 2 completion target: Q2 2025)
Tamil (ta): 85% coverage (Phase 2 completion target: Q2 2025)
Locale Detection Priority:

URL path segment (/zh/centers/...)
User preference (stored in profile)
Cookie (NEXT_LOCALE)
Accept-Language header
Default: en
Translation Workflow:

Developer adds string to frontend/locales/en/*.json or backend/lang/en/*.php
Translation job created in Laravel Nova (status: pending)
Translator reviews context + translates (status: translated)
Native speaker reviews (status: reviewed)
Admin approves (status: approved)
Next deploy pulls latest translations
Content Localization:

Database: content_translations polymorphic table (center descriptions, service details, FAQs, testimonials)
Fallback: If translation missing, display English + indicator ("Translation pending")
Rich Text: CKEditor with locale-aware toolbar, RTL support planned for future languages
Cultural Considerations:

Honorifics: Respectful titles (Mr./Mrs./Mdm.) enforced in forms, culturally appropriate greetings
Date/Number Formats: Intl.DateTimeFormat, Intl.NumberFormat with locale codes (en-SG, zh-SG, ms-SG, ta-IN)
Imagery: Multicultural representation, vetted for cultural sensitivity
Holidays: Content scheduling aware of major holidays (CNY, Hari Raya, Deepavali, Christmas)
Key Actions for Agents

Use docs/accessibility/accessibility-checklist.md before completing features
Mark translatable strings with i18n keys (t('booking.confirm_button'))
Update all locale JSON/PHP files when adding strings
Request accessibility review for UI-heavy changes
Test with screen reader (at least NVDA) before PR
Verify keyboard navigation works end-to-end
10. Testing & Quality Assurance
10.1 Testing Strategy & Coverage Mandate
Coverage Requirement: ≥90% for critical modules (auth, bookings, payments, consent management)

10.2 Frontend Testing
Type	Framework	Command	Coverage Target
Unit	Jest + React Testing Library	npm test	≥90% (components, hooks, utils)
Integration	Testing Library	npm test	≥80% (user flows within pages)
E2E	Playwright	npm run test:e2e	Critical paths (booking, auth, search)
Accessibility	jest-axe	Integrated in component tests	100% (all components)
Visual Regression	Percy (Storybook)	npm run percy:storybook	Critical templates (home, center detail, booking wizard)
Performance	Lighthouse CI	npm run lighthouse	Score >90 (all pages)
Test Execution Cadence:

Local: Pre-commit (unit tests via Husky hook)
CI: On every PR (full suite)
Nightly: E2E tests + visual regression (main branch)
Release Candidate: Full manual QA + accessibility audit
Example Test (React Testing Library + jest-axe):

React

import { render, screen } from '@testing-library/react';
import { axe, toHaveNoViolations } from 'jest-axe';
import BookingButton from './BookingButton';

expect.extend(toHaveNoViolations);

describe('BookingButton', () => {
  it('renders with correct label', () => {
    render(<BookingButton>Book Visit</BookingButton>);
    expect(screen.getByRole('button', { name: /book visit/i })).toBeInTheDocument();
  });

  it('has no accessibility violations', async () => {
    const { container } = render(<BookingButton>Book Visit</BookingButton>);
    const results = await axe(container);
    expect(results).toHaveNoViolations();
  });
});
10.3 Backend Testing
Type	Framework	Command	Coverage Target
Unit	PHPUnit/Pest	./vendor/bin/phpunit --testdox	≥90% (services, repositories)
Feature	PHPUnit/Pest	php artisan test	≥85% (API endpoints, policies)
Integration	PHPUnit (gated by env vars)	CALENDLY_API_TOKEN=xyz composer test	External service integrations
Static Analysis	PHPStan Level 8	./vendor/bin/phpstan analyse	Zero errors (enforced in CI)
Current Status (as of last run):

Tests: 90 tests, 216 assertions
Result: ✅ All passing
Deprecations: 59 PHPUnit deprecations (to be addressed in Q1 2025)
Test Database:

Local/CI: In-memory SQLite (RefreshDatabase trait)
Integration Tests: Isolated MySQL database (docker-compose service)
Example Test (Pest + Laravel HTTP):

PHP

use App\Models\User;
use App\Models\Center;

it('allows authenticated user to create booking', function () {
    $user = User::factory()->create();
    $center = Center::factory()->create();
    
    $response = $this->actingAs($user)->postJson('/api/v1/bookings', [
        'center_id' => $center->id,
        'service_id' => $center->services()->first()->id,
        'preferred_date' => now()->addDays(7)->toISOString(),
        'notes' => 'First visit',
    ]);
    
    $response->assertStatus(201)
             ->assertJsonStructure(['data' => ['id', 'status', 'calendly_event_id']]);
             
    $this->assertDatabaseHas('bookings', [
        'user_id' => $user->id,
        'center_id' => $center->id,
        'status' => 'confirmed',
    ]);
});
Gated Integration Tests:

External API tests (Calendly, Mailchimp, Twilio) skip if env vars not set
Add to CI secrets for full integration test runs in staging pipeline
10.4 Load & Performance Testing
Tool	Purpose	Command	Baseline Threshold
k6	Backend load testing	k6 run tests/load/booking-flow.js	1000 concurrent users, p95 <500ms
Lighthouse CI	Frontend performance	npm run lighthouse (via GitHub Actions)	Score >90, budgets enforced
WebPageTest	Real-world 3G testing	Manual (weekly)	<3s load time on 3G Fast (Singapore)
Load Test Execution:

Pre-launch: Full stress test (2x baseline = 2000 concurrent users)
Quarterly: Regression testing
Before Major Releases: Stress test + soak test (sustained load for 1 hour)
10.5 Manual QA Checklist
Per Sprint (before release to staging):

 Cross-browser testing: Chrome, Firefox, Safari, Edge (latest 2 versions) via BrowserStack
 Mobile device testing: iOS Safari, Android Chrome (latest OS versions)
 Screen reader testing: NVDA (Windows), VoiceOver (macOS, iOS)
 Keyboard-only navigation (Tab, Enter, Esc, Arrow keys)
 200% zoom test (no horizontal scroll, all content accessible)
 Form validation and error messaging
 Booking flow end-to-end (happy path + error scenarios)
 i18n: Verify all 4 languages render correctly
 Analytics instrumentation: Verify GA4 events fire (GA4 DebugView)
Per Major Release (before production):

 Stakeholder UAT (Product Manager, Compliance Officer, UX Designer)
 Security scan: OWASP ZAP, Dependabot review
 Performance audit: Lighthouse, WebPageTest
 Accessibility audit: External firm (annually) or internal deep dive
 PDPA compliance review: Consent flows, audit logs, data export/delete
 Disaster recovery drill: Restore from backup (quarterly)
10.6 Definition of Done
A feature is done when:

 ✅ Peer review approved (1+ reviewer)
 ✅ All automated tests pass (unit, integration, E2E, accessibility)
 ✅ 100% coverage for new code (≥90% overall for critical modules)
 ✅ Manual QA sign-off (checklist above)
 ✅ Accessibility targets met (Lighthouse >90, axe-core pass, manual screen reader test)
 ✅ Performance targets met (Lighthouse >90, budgets enforced)
 ✅ Documentation updated (docs/, README.md, ADRs, runbooks)
 ✅ Monitoring hooks configured (Sentry error tracking, New Relic custom events)
 ✅ Stakeholder approval obtained (for user-facing changes)
 ✅ i18n: All languages updated (or translation jobs created)
 ✅ Security review (for auth/payment/PII changes)
Key Actions for Agents

Extend test suites when modifying behavior (don't just update existing tests)
Run full test suite locally before PR (npm test && composer test)
Attach Lighthouse/axe reports for significant UI changes
Update QA checklists with new scenarios if introducing new flows
Request accessibility review for any new UI components
Verify analytics instrumentation (GA4 DebugView) for new user actions
11. Risk Register & Mitigations
Refer to Project_Architecture_Document.md Section 21 for full risk matrix. Key risks:

Risk	Impact	Probability	Mitigation	Status
Vendor API changes (Calendly/Twilio)	High	Medium	Abstraction layer (CalendlyService), contract tests, webhook monitoring, fallback to manual contact	✅ Mitigated
Performance degradation on media-heavy pages	High	Medium	Cloudflare Stream ABR, lazy loading, performance budgets in CI, CDN tuning	✅ Mitigated
Compliance breach (PDPA)	High	Low	Consent ledger, AuditObserver, quarterly drills, legal review cadence, penetration testing	✅ Mitigated
Data migration errors	Medium	Low	Automated migration tests (RefreshDatabase), rehearsal rollbacks in staging, blue-green deploys	✅ Planned
Staffing bandwidth	Medium	Medium	Sprint prioritization, cross-training, partner support, detailed documentation	🟡 Active monitoring
Translation delays	Medium	Medium	Translation workflow tooling (Nova), buffer in roadmap, fallback to English with indicator	🟡 Active monitoring
Accessibility regression	High	Low	Automated axe checks in CI, manual audits per sprint, design token governance	✅ Mitigated
Kubernetes cluster failure	High	Low	Multi-AZ deployment, health checks, auto-restart policies, runbook for manual failover	✅ Mitigated
Elasticsearch outage	Medium	Low	Graceful degradation (fallback to SQL LIKE queries), monitoring with auto-alerts	✅ Mitigated
Key Actions for Agents

Log new risks or mitigation updates in PAD Section 21 and this guide's Changelog
Notify stakeholders when a mitigation depends on pending development
Include risk assessment in major architectural proposal PRs
Update runbooks when introducing new failure modes
12. Lifecycle & Roadmap
12.1 Current Status: Alpha Development
Completed Phases:

✅ Phase 1 (Weeks 1-12): Foundation, Infrastructure & Analytics
Docker environment, CI/CD pipeline, database schema complete
Terraform infrastructure (Kubernetes, RDS, Redis, S3)
Monitoring stack (Sentry, New Relic, CloudWatch)
✅ Phase 2 (Weeks 13-16): Design System, UI Components & i18n
Accessible component library in Storybook
i18n framework complete (English + Mandarin)
Visual regression testing (Percy)
✅ Phase 3: Core Backend Services & PDPA Compliance
Laravel API implementation (auth, bookings, centers)
Calendly integration (CalendlyService.php)
Audit logging infrastructure (AuditObserver, Auditable trait)
PDPA consent management
PHPUnit test suite (90 tests, 216 assertions, all passing)
12.2
Som