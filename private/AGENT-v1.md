Validation Report & Updated AGENT.md
🔍 Validation Summary
I've meticulously cross-referenced the consolidated AGENT.md against PAD v2.1. Here are the key findings:

✅ Well-Aligned Areas
Architecture pattern (service-oriented monolith)
Technology stack (Next.js 14, Laravel 12, MySQL 8.0, Redis 7, MeiliSearch)
Infrastructure (ECS Fargate, Terraform, AWS Singapore region)
Security & compliance principles
Testing strategy
Accessibility standards
⚠️ Critical Discrepancies Requiring Resolution
Issue	PAD v2.1	Current AGENT.md	Recommendation
Roadmap Phases	Only defines Phase 1 (Weeks 1-12) and Phase 2 (Weeks 13-16)	References "Phase 3" and "Pre-Phase 3 Remediation" from GEMINI.md	🚨 HIGH PRIORITY: Clarify actual current phase with stakeholders
MVP Completion Status	Presents as future deliverables (Weeks 1-12)	States Phase 1 & 2 complete	Need definitive project status update
📋 Missing Details to Add
Target Persona Age Ranges: PAD has specific ranges (Family Caregivers 25-45, Healthcare Professionals 30-60)
Performance Budget Specifics: TTFB < 300ms, FCP < 1.5s, LCP < 2.5s, TTI < 3s (PAD Section 15)
Missing Risks: "Translation delays" and "Accessibility regression" from PAD Section 21
Out of Scope Items: Explicit list from PAD 2.5
API Guidelines Detail: Rate limiting specifics, error schema examples (PAD 4.4)
Integration Security Details: OAuth specifics, webhook plans (PAD Table 8)
Constraints & Assumptions: Financial constraints, team size (PAD 2.6)
📄 Updated AGENT.md (Enhanced Version)
Below is the enhanced consolidated AGENT.md that incorporates all critical details from PAD v2.1:

AGENT.md — ElderCare SG Web Platform
Version: 1.1
Last Updated: [Current Date]
Primary Source Documents: Project_Architecture_Document.md v2.1, Project_Requirements_Document.md, codebase_completion_master_plan.md, docs/ai-coding-agent-brief.md, docs/accessibility/, docs/design-system/, docs/deployment/

Purpose: This guide is the single source of truth for any AI coding agent (and their human facilitators) to understand the ElderCare SG architecture, delivery standards, and operational guardrails before touching the codebase.

⚠️ Outstanding Clarifications Required
The following conflicts require immediate stakeholder resolution:

Topic	Conflict	Action Required	Priority
Current Project Phase	PAD v2.1 defines only Phase 1-2 (Weeks 1-16). GEMINI.md references "Pre-Phase 3 Remediation Stage" and "Phase 3: Core Backend Services"	🚨 Confirm actual current phase, completion status, and active roadmap	CRITICAL
MVP Completion	PAD frames deliverables as future (Weeks 1-12). GEMINI.md states Phase 1 & 2 complete	Clarify what has been delivered vs. planned	HIGH
Container Orchestration	PAD: "ECS Fargate" vs. ai-coding-agent-brief: "Kubernetes"	Confirmed ECS Fargate per PAD v2.1 (authoritative)	✅ Resolved
Search Infrastructure	AGENT.md: "MeiliSearch" vs. ai-coding-agent-brief: "Elasticsearch"	Confirmed MeiliSearch per PAD v2.1 Table (Section 1.4)	✅ Resolved
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
Lifecycle & Roadmap Hooks
Collaboration Protocols
Quickstart Checklist for AI Agents
Change Log & Maintenance Guidance
1. Executive Summary
1.1 Product Mission
ElderCare SG is an accessibility-first web platform connecting Singaporean families with trustworthy elderly daycare providers. The platform delivers transparent facility information, immersive engagement experiences, and seamless booking workflows while achieving Lighthouse scores >90 and complying with Singapore's regulatory landscape (PDPA, MOH, IMDA).

1.2 Platform Vision
Empower families with comprehensive, verifiable information and multimedia storytelling for confident eldercare decisions
Humanize the journey through culturally aware design, multilingual support (English, Mandarin, Malay, Tamil), and compassionate content
Operationalize trust via rigorous compliance, accessibility, and security baked into every subsystem
1.3 Target Audiences
Persona	Age Range	Characteristics	Primary Needs
Adult Children	30-55	Time-poor professionals, mobile-first, demand deep insight	Social proof, verified credentials, seamless booking
Family Caregivers	25-45	Often domestic helpers	Multilingual content, transport clarity
Healthcare Professionals	30-60	Referral decision-makers	Quick license/capability verification
Digitally Literate Seniors	55+	Direct service seekers	Larger fonts, high contrast, straightforward navigation
1.4 Core Value Pillars
Trust through transparency: Verified MOH licenses, staff credentials, authentic testimonials
Accessibility for all: WCAG 2.1 AA compliance, keyboard navigation, screen reader validation
Cultural resonance: Multicultural imagery, respectful language (honorifics), holiday-aware operations
Seamless engagement: Frictionless journey from discovery to booking with proactive reminders
1.5 North-Star Success Metrics
Metric	Target	Measurement	Owner	Cadence
Visit bookings increase	+30% in 3 months	GA4 conversions + booking DB	Marketing	Monthly
Mobile bounce rate	<40%	GA4 device segmentation	UX	Bi-weekly
Lighthouse Performance	>90	Lighthouse CI (staging & prod)	DevOps	Each deploy
Lighthouse Accessibility	>90	Lighthouse CI + axe-core	QA	Each deploy
Avg session duration	>5 minutes	GA4 engagement metrics	Marketing	Monthly
3G load time	<3 seconds	WebPageTest (Singapore)	DevOps	Weekly
Form completion	>75%	Hotjar form analytics	UX	Monthly
Video engagement (Phase 2)	>60% completion	Cloudflare Stream analytics	Marketing	Monthly
Core Web Vitals	LCP <2.5s, FID <100ms, CLS <0.1	New Relic RUM, Lighthouse CI	DevOps	Each deploy
1.6 Constraints & Assumptions (PAD 2.6)
Regulatory:

PDPA data residency (AWS ap-southeast-1 only)
MOH display compliance (license numbers, staff credentials)
WCAG 2.1 AA baseline
IMDA guidelines
Operational:

99.5% availability SLA
12-week MVP timeline (Phase 1)
Team size: 6-8 members
Technical:

Modern browser support (latest 2 versions)
Mobile-first design (70% traffic forecast)
<3s load on 3G networks
Financial:

Calendly Pro: ~US$12/user/month
Mailchimp Standard: ~US$20/month
Laravel Nova: US$199/year
Cloudflare Pro: US$20/month
Twilio SMS: ~SGD$0.07/message
Professional translation budget: pre-approved
1.7 Explicitly Out of Scope (PAD 2.5)
The following are NOT included in current release scope:

Native mobile apps (iOS/Android)
Payment processing or e-commerce
Caregiver matching algorithms
Medical record integration
Telehealth/telemedicine features
Live chat support
Provider self-service portals
Family shared accounts
Government database integrations (CPF, MOH systems)
Key Actions for Agents

Review README.md, PAD v2.1, and docs/ai-coding-agent-brief.md before any contribution
Align feature work with success metrics and value pillars
Confirm stakeholder intent via latest roadmap milestones (see Section 12 clarification)
Reject feature requests in out-of-scope list unless explicitly added to roadmap
2. System Overview
2.1 Architecture Pattern & Key Decisions
Service-oriented monolith (Laravel) with modular domain boundaries and shared MySQL schema. See PAD Section 1.4 for full decision rationale.

Decision Area	Choice	Rationale (PAD 1.4)
Architecture pattern	Service-oriented Laravel monolith	Modular boundaries without microservice overhead during early scale
Frontend	Next.js 14 with React Server Components	SEO-critical, performant, RSC minimizes JS payload
Backend	Laravel 12 (PHP 8.2)	Rapid development, mature ecosystem, strong security, internal expertise
Database	MySQL 8.0 (primary + read replica)	ACID guarantees for bookings, proven reliability
Caching & queues	Redis 7	Sessions, cache, queue driver for async workloads
Search	MeiliSearch 1.4	Excellent multilingual support, simpler ops vs. Elasticsearch
Container orchestration	AWS ECS Fargate	Managed runtime reduces DevOps overhead, scales horizontally
CDN & security	Cloudflare	Singapore edge, WAF, DDoS mitigation, caching
Admin & CMS	Laravel Nova	Accelerates curation workflows, role-based controls
Video (Phase 2)	Cloudflare Stream	Adaptive bitrate, analytics, localized delivery
2.2 Hosting & Environments
Hosting Footprint: AWS ECS Fargate (Singapore region ap-southeast-1) + Cloudflare CDN/WAF
Infrastructure Management: Terraform manages VPC, ECS services, RDS, ElastiCache, S3, IAM, CloudWatch, Secrets Manager
Environments:
Local: Docker Compose with hot-reload
Staging: ECS Fargate + RDS + Redis (auto-deploy from main branch)
Production: Multi-AZ ECS Fargate, RDS with read replica, Redis cluster (manual approval required)
Observability: Sentry (errors), New Relic (APM/RUM), CloudWatch (metrics/logs), UptimeRobot (synthetic monitoring)
2.3 Key Principles (PAD Section 3)
User-centric design: Clarity, progressive disclosure, guardrail UX, skeleton loaders, fallback paths
Accessibility first: Semantic HTML, Radix primitives, keyboard navigability, ARIA live regions, screen reader QA, motion reduction toggles
Security by design: Layered defenses, RBAC, MFA for admins, encryption (TLS 1.3 / AES-256 / bcrypt 12), CSP, rate limiting, dependency auditing
Performance optimized: Page weight budgets (<280KB), RSC defaults, image/WebP optimization, CDN caching, Redis caching, asynchronous jobs
Compliance built-in: Consent ledger, audit trails, retention automation (24-month inactivity), right-to-access/export/delete, MOH license enforcement
Scalable & maintainable: Service-layer boundaries, repository pattern, dependency injection, 90% test coverage mandate, ADR documentation
Cultural sensitivity: I18n-first architecture, locale-aware formatting, respectful honorifics, inclusive imagery, holiday scheduling awareness
2.4 Logical Components
mermaid

flowchart TB
    subgraph "Clients"
        Browser[Web & Mobile Browsers]
        Crawler[Search Engine Crawlers]
    end

    subgraph "Cloudflare Edge"
        CDN[Edge Cache & WAF]
    end

    subgraph "Presentation Layer — AWS ECS"
        Next[Next.js 14 Frontend]
        Api[Laravel API Gateway]
    end

    subgraph "Service Layer — Laravel"
        AuthSvc[AuthService]
        BookingSvc[BookingService]
        ContentSvc[ContentService]
        NotifySvc[NotificationService]
        AnalyticsSvc[AnalyticsService]
        SearchSvc[SearchService]
    end

    subgraph "Data Layer"
        MySQLPrimary[(MySQL 8.0 Primary)]
        MySQLReplica[(MySQL Read Replica)]
        Redis[(Redis 7 Cache & Queues)]
        Meili[(MeiliSearch 1.4)]
        S3[(AWS S3 Media)]
    end

    subgraph "External Services"
        Calendly[Calendly API]
        Mailchimp[Mailchimp API]
        Twilio[Twilio SMS API]
        Stream[Cloudflare Stream]
    end

    subgraph "Observability"
        Sentry[Sentry Errors]
        NewRelic[New Relic APM/RUM]
        CloudWatch[CloudWatch Logs/Metrics]
    end

    Browser --> CDN --> Next
    Crawler --> CDN
    Next --> Api
    Api --> AuthSvc
    Api --> BookingSvc
    Api --> ContentSvc
    Api --> NotifySvc
    Api --> AnalyticsSvc
    Api --> SearchSvc
    AuthSvc --> MySQLPrimary
    BookingSvc --> MySQLPrimary
    BookingSvc --> Calendly
    BookingSvc --> Redis
    ContentSvc --> MySQLPrimary
    ContentSvc --> Redis
    ContentSvc --> Meili
    ContentSvc --> S3
    NotifySvc --> Redis
    NotifySvc --> Mailchimp
    NotifySvc --> Twilio
    AnalyticsSvc --> MySQLReplica
    SearchSvc --> Meili
    Api --> Sentry
    Api --> NewRelic
    Api --> CloudWatch
2.5 Component Interaction Patterns (PAD 4.2)
Content Request Flow:

User hits /en/centers/sunshine-care
Cloudflare edge cache check (HIT = serve cached HTML)
MISS → Next.js SSR fetches GET /api/v1/centers/sunshine-care?locale=en
ContentService checks Redis cache (5 min TTL)
Redis MISS → queries MySQL read replica
Response cached in Redis, transformed via API Resource
Next.js generates HTML, caches at Cloudflare edge
Subsequent requests served from edge (stale-while-revalidate strategy)
Booking Workflow:

User submits booking form → POST /api/v1/bookings
BookingFormRequest validates input + PDPA consent
BookingService begins database transaction
Creates booking record (status: pending)
Calls Calendly API to create event
Updates booking (status: confirmed, adds calendly_event_id)
Commits transaction
Dispatches BookingConfirmed event
Event listeners queue:
SendBookingConfirmationEmail job (high priority)
SendBookingConfirmationSMS job (high priority)
TrackBookingAnalytics job (default priority)
Laravel Horizon workers execute jobs asynchronously
Cache invalidation for user's booking list
Failure Handling:

Calendly API failure → rollback transaction, set status failed, queue admin alert, surface manual contact form to user
Key Actions for Agents

Confirm environment context before running commands (.env, frontend/.env.local)
Respect architecture principles when proposing modifications
Check Terraform state/variables prior to infra-affecting changes
Never make manual AWS console edits (Terraform only)
3. Frontend Blueprint
3.1 Stack & Tooling
Framework: Next.js 14 App Router with React Server Components (RSC) default
Languages: TypeScript 5, Tailwind CSS 3
UI Components: Radix UI primitives (accessibility-first), custom design system
Animation: Framer Motion with prefers-reduced-motion support
State Management:
Server state: React Query 4 (5-min stale time, background revalidation)
Global client state: Zustand 4 with persistence
Local state: React hooks
Client Tooling: ESLint, Prettier, Jest, React Testing Library, Playwright
3.2 Directory Structure (frontend/)
text

frontend/
├── app/
│   ├── [locale]/              # Dynamic locale segment (en, zh, ms, ta)
│   │   ├── layout.tsx         # Root layout (header/footer/providers)
│   │   ├── page.tsx           # Home (Server Component)
│   │   ├── centers/
│   │   │   ├── page.tsx       # Center listing (Server Component)
│   │   │   └── [slug]/page.tsx# Center detail (Server Component)
│   │   ├── services/page.tsx  # Service catalog
│   │   ├── booking/page.tsx   # Booking flow (Client Component)
│   │   ├── about/page.tsx
│   │   ├── faq/page.tsx
│   │   └── dashboard/page.tsx # Authenticated area (auth guard)
│   ├── api/                   # Next.js route handlers (proxy, edge cases)
│   └── globals.css            # Tailwind base + design tokens
├── components/
│   ├── atoms/                 # Button, Input, Icon, Badge, Label
│   ├── molecules/             # FormField, Card, NavItem, SearchBar
│   ├── organisms/             # Header, Footer, BookingForm, CenterCard
│   ├── templates/             # Layout wrappers, page templates
│   └── providers/             # AuthProvider, ThemeProvider, I18nProvider
├── hooks/                     # useAuth, useBooking, useTranslation, useMediaQuery
├── lib/                       # API client, utils, validation (Zod schemas)
├── locales/                   # en/, zh/, ms/, ta/ JSON translation files
├── store/                     # Zustand persisted store (auth, UI state)
├── tests/                     # Jest + RTL unit tests, Playwright E2E
├── types/                     # Shared TypeScript definitions
├── middleware.ts              # Locale negotiation, auth guards
├── next.config.js             # Next.js config (i18n, images, headers)
└── tailwind.config.js         # Design tokens, theme, plugins
3.3 Rendering Strategy
Default: React Server Components for SEO-critical, data-heavy views
Client Components: Only when interactivity requires client-side state (forms, modals, booking wizard)
Justification Required: PR notes must explain CSR usage for new components
Progressive Enhancement: Forms submit via standard POST when JS unavailable; enhanced with inline validation when JS enabled
3.4 Design System Integration
Source of Truth: docs/design-system/ + Storybook component library
Design Tokens: Drive Tailwind config (colors, spacing, typography, shadows)
Accessibility Palette: All color combinations pre-validated for ≥4.5:1 contrast (≥3:1 for large text)
Component Usage: Always prefer design system components over custom implementations
Storybook: Comprehensive documentation with interactive examples, accessibility checks, visual regression baselines
3.5 Asset Optimization (PAD 5.5)
Images: Next.js <Image> for responsive WebP with JPEG fallback, LQIP placeholders, lazy loading
Fonts: Self-hosted via next/font (Inter, Noto Sans SC, Noto Sans) with subsetting and font-display: swap
CSS: Tailwind JIT + Purge reduces production payload to <20 KB
JS Code Splitting: Dynamic imports for heavy modules (map view, booking wizard) to minimize initial bundle
3.6 Progressive Enhancement (PAD 5.6)
Forms submit via standard POST when JS unavailable
Navigation functions with plain anchors; next/link provides client-side transitions opportunistically
Critical content (center details, testimonials) rendered server-side for baseline accessibility
Skeleton loaders and error boundaries ensure graceful degradation
Key Actions for Agents

Adhere to design tokens and component usage documented in Storybook
Favor React Server Components for data-heavy views; justify CSR in PR notes
Instrument new interactions with analytics data attributes (data-track-event)
Test progressive enhancement by disabling JavaScript
Validate against design system checklist before component PRs
4. Backend Blueprint
4.1 Framework & Architecture
Framework: Laravel 12 (PHP 8.2) with strict service-layer architecture
API Surface: RESTful, versioned at /api/v1/
Authentication: Laravel Sanctum (bearer tokens)
Admin CMS: Laravel Nova with role-based access
Type Safety: PHPStan level 8 enforced in CI
4.2 Service-Layer Architecture (PAD 6)
Strict Separation of Concerns:

Layer	Responsibility	Examples
Controllers (Http/Controllers/Api/V1/)	Thin HTTP handlers only	Accept requests, validate, delegate to services, return responses
Services (Services/)	Core business logic	AuthService, BookingService, ContentService, NotificationService, AnalyticsService, SearchService
Repositories (Repositories/)	Data access abstraction	Query building, caching, model hydration
Form Requests (Http/Requests/)	Validation & authorization	Per-action validation rules, PDPA consent checks
Policies (Policies/)	RBAC authorization	Resource-level permissions (view PII, approve content, manage translations)
Events & Listeners (Events/, Listeners/)	Domain event orchestration	Notifications, analytics, cache invalidation, audit logging
Jobs (Jobs/)	Async workloads	Emails, SMS, data exports, translation syncs
Observers (Observers/)	Model lifecycle hooks	AuditObserver auto-logs changes to audit_logs
4.3 Service Example: BookingService (PAD 6.3)
PHP

class BookingService
{
    public function createBooking(array $data): Booking
    {
        DB::beginTransaction();
        try {
            // 1. Create booking record (status: pending)
            $booking = $this->bookingRepository->create([
                'user_id' => $data['user_id'],
                'center_id' => $data['center_id'],
                'service_id' => $data['service_id'],
                'status' => 'pending',
                'questionnaire_data' => $data['questionnaire'],
            ]);

            // 2. Integrate with Calendly
            $calendlyEvent = $this->calendlyService->createEvent([
                'event_type_uuid' => $data['service']->calendly_event_type_id,
                'invitee_email' => $data['user']->email,
                'start_time' => $data['preferred_datetime'],
            ]);

            // 3. Update booking with external ID
            $booking->update([
                'status' => 'confirmed',
                'calendly_event_id' => $calendlyEvent['uuid'],
            ]);

            DB::commit();

            // 4. Dispatch domain events
            event(new BookingConfirmed($booking));

            return $booking;

        } catch (CalendlyApiException $e) {
            DB::rollBack();
            $booking->update(['status' => 'failed']);
            event(new BookingFailed($booking, $e->getMessage()));
            throw new BookingException('Unable to confirm booking. Please contact support.');
        }
    }
}
Features Enforced:

Transactional integrity
External API integration with compensating actions
Event-driven notifications and analytics
Error handling with user-friendly messaging
4.4 API Design Standards (PAD 4.4)
Base URL:

text

https://api.eldercare.sg/v1/
Versioning:

Version in path (/v1/, /v2/)
Minimum 6-month deprecation notice
Two concurrent versions supported maximum
Authentication:

http

GET /api/v1/bookings
Authorization: Bearer {sanctum_token}
Pagination:

http

GET /api/v1/centers?page=2&per_page=20
Response includes meta and links blocks:

JSON

{
  "data": [...],
  "meta": {
    "current_page": 2,
    "total": 145,
    "per_page": 20,
    "last_page": 8
  },
  "links": {
    "first": "https://api.eldercare.sg/v1/centers?page=1",
    "last": "https://api.eldercare.sg/v1/centers?page=8",
    "prev": "https://api.eldercare.sg/v1/centers?page=1",
    "next": "https://api.eldercare.sg/v1/centers?page=3"
  }
}
Filtering & Sorting:

http

GET /api/v1/centers?filter[city]=Singapore&filter[has_medical]=true&sort=-created_at
Error Schema:

JSON

{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "phone": ["The phone format is invalid."]
  }
}
Rate Limiting:

Public endpoints: 60 req/min per IP
Authenticated: 1000 req/hour per user
Configurable throttles for partner integrations
API Documentation:

OpenAPI 3.0 specification (planned)
Postman collection for testing
4.5 Queue & Job Processing (PAD 6.5)
Driver: Redis
Management: Laravel Horizon with worker pools sized per environment
Job Configuration:
PHP

class SendBookingConfirmationEmail implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1m, 5m, 15m
    public $queue = 'high';
}
Priority Queues:
high: SMS/email notifications
default: Transactional emails, analytics
low: Data syncs, batch operations
Failure Handling: Failed jobs logged to failed_jobs table, Sentry alerts, replayable via Nova
Delayed Jobs: Schedule reminders (72-hour, 24-hour pre-visit), post-visit follow-ups
4.6 Domain Modules (PAD 6.2)
Module	Responsibilities	Key Services
Users	Registration, authentication, profiles, consent management	AuthService, UserService, ConsentService
Centers	Facility data, MOH compliance, staff credentials	CenterService, StaffService
Bookings	Scheduling, Calendly integration, lifecycle management	BookingService, CalendlyService
Content	Multilingual content, media management, translations	ContentService, MediaService, TranslationService
Testimonials	Moderation, verification, consent tracking	TestimonialService
Newsletters	Mailchimp integration, preference center	NewsletterService, MailchimpService
Notifications	Email/SMS orchestration, queue management	NotificationService, TwilioService
Analytics	Event tracking, reporting	AnalyticsService
Search	MeiliSearch indexing, multilingual queries	SearchService
Key Actions for Agents

Consult PAD Section 6 for detailed module responsibilities
Maintain service/repository layering; avoid placing business logic in controllers
Update job/event documentation when introducing new async flows
Enforce type safety (PHPStan level 8)
Write service-layer unit tests with >90% coverage
Document API endpoints in OpenAPI spec (when available)
5. Data & Integrations
5.1 Database Schema (PAD 7)
Comprehensive 18-table MySQL 8.0 schema meticulously designed for:

Compliance: PDPA audit trails, consent versioning, soft deletes
Scalability: Indexes (composite, full-text), pre-built views, efficient joins
Multilingual Support: Polymorphic translation tables, locale metadata
Principles (PAD 7.1):

UTF8MB4 charset for multilingual content
InnoDB engine for ACID compliance
Strict foreign keys with cascading deletes
deleted_at soft deletes for PDPA retention workflows
Audit logging of personal data access/modification (7-year retention)
Entity Relationship Diagram (PAD 7.2):

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
5.2 Key Tables (PAD 7.3)
Table	Purpose	Critical Fields
users	Authentication & roles	role (user/admin/moderator/translator), mfa_enabled, deleted_at
profiles	User demographics	preferred_language, accessibility_preferences, address
centers	Facility master data	moh_license_number, accreditation_body, capacity, latitude, longitude, emergency_protocols
services	Program offerings	calendly_event_type_id, pricing, duration, staff_ratio, medical_services
bookings	Booking lifecycle	status (pending/confirmed/cancelled/completed), calendly_event_id, questionnaire_data (JSON), cancellation_reason
content_translations	Polymorphic i18n	translatable_type, translatable_id, locale, field, value, approved_at
media	S3 references	s3_key, alt_text, consent_flag, moderation_status
consents	PDPA consent ledger	type (marketing/analytics/etc.), version, consent_text, granted_at, revoked_at
audit_logs	Compliance trail	user_id, action, auditable_type, auditable_id, old_values_hash, new_values_hash, ip_address
5.3 Existing Migration Scripts
⚠️ CRITICAL: Review and use existing migration scripts before creating new ones. If in doubt, refer to database_schema.sql as source of truth.

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
5.4 Data Retention & Residency (PAD 7.4)
Data Residency: Production data confined to AWS ap-southeast-1 (Singapore)
Encryption: At-rest (RDS encryption with KMS), in-transit (TLS 1.3)
Backups: Encrypted backups stored cross-account, cross-region replication to ap-northeast-1
Retention Policy:
Inactive accounts flagged at 18 months
Auto-deletion at 24 months unless legal hold
Audit logs retained 7 years
Right to Access: Data export jobs generate ZIP bundles (JSON/CSV), secure download link expires in 30 days
Right to Deletion: Anonymization workflows for PDPA compliance
5.5 Caching & Supporting Services
Service	Purpose	Configuration
Redis 7 (ElastiCache)	Sessions, cache, queues, rate limiting	Cache TTL: 5-15 min (tag-based invalidation), Session TTL: 24h, Queue workers: 3-10 per environment
MeiliSearch 1.4	Center/service discovery, multilingual search	Replaces Elasticsearch (per PAD 1.4), Scaled vertically initially, Indexed fields: name, description, amenities, services
AWS S3 (ap-southeast-1)	Media storage (photos, videos, documents)	Lifecycle rules for archival, Versioning enabled, CloudFront CDN integration planned Phase 2
5.6 External Integrations (PAD Section 8)
Integration	Purpose	Interface	Security	SLA/Notes
Calendly	Booking orchestration	REST API v2	OAuth token via AWS Secrets Manager	Webhook sync for cancellations (Phase 2), Fallback: Manual contact form
Mailchimp	Newsletter & campaigns	REST API v3	API key via Secrets Manager	Double opt-in enforced, Preference center in-app
Twilio	SMS notifications	REST API	API key via Secrets Manager	Singapore numbers only, Throttled per PDPA consent, ~SGD$0.07/message
Cloudflare Stream	Virtual tours (Phase 2)	REST API + signed embed URLs	JWT-signed tokens	Adaptive bitrate, Captions & analytics via API
GA4	Web analytics	gtag.js + Measurement Protocol	Env config	Enhanced measurement events, Server-side fallback for cookie-blocked users
Hotjar	UX insights	JS snippet (consent-gated)	Consent ensures PDPA compliance	Session recording disabled for forms with PII
Sentry	Error tracking	SDK (frontend + backend)	DSN via env	Release tagging from CI/CD, Source maps for debugging
New Relic	APM & RUM	Agent (PHP + Browser)	License key via Secrets Manager	Core Web Vitals dashboards, Custom instrumentation
Integration Patterns:

Secrets Management: All API keys/tokens stored in AWS Secrets Manager, rotated every 90 days, IAM least privilege policies
Circuit Breakers: Graceful degradation on external API failures (e.g., Calendly outage surfaces manual contact form)
Retry Logic: Exponential backoff for transient failures
Contract Testing: Mock external APIs in automated test suites (never call live services)
Key Actions for Agents

Validate migration impact on retention/consent rules before altering schema
Mock third-party APIs in tests; never call live services from automated suites
Document new integration touchpoints in this file and relevant runbooks
Rotate secrets via AWS Secrets Manager (never hardcode)
Implement circuit breakers for new external dependencies
Log integration failures to Sentry with actionable context
6. Operational Maturity
6.1 CI/CD Pipeline (GitHub Actions)
Workflow Stages:

Lint & Test (on all branches)

ESLint (frontend)
PHPStan level 8 + PHP CodeSniffer (backend)
Jest + RTL unit tests (frontend)
PHPUnit unit + feature tests (backend)
Coverage threshold: >90%
Build (on main and release branches)

Next.js production build
Laravel optimizations (composer install --no-dev)
Docker image builds (multi-stage)
Security (on all PRs)

npm audit / Dependabot
composer audit
Docker image scanning (Trivy)
SAST tools (Snyk or SonarQube)
E2E & Accessibility (on main)

Playwright E2E smoke tests on staging
Lighthouse CI (Performance, Accessibility, SEO, Best Practices >90)
axe-core accessibility audit
Deploy

Staging: Auto-deploy on main merge (ECS Fargate)
Production: Manual approval workflow + change ticket requirement
Deployment Flow:

text

feature/* → PR → CI checks → merge to main → auto-deploy staging → manual QA → manual approval → production deploy
6.2 Infrastructure as Code (Terraform)
Managed Resources:

VPC, subnets, security groups, NAT gateways
ECS clusters, task definitions, services
RDS MySQL (primary + read replica)
ElastiCache Redis
S3 buckets with lifecycle policies
IAM roles/policies (least privilege)
CloudWatch dashboards, alarms, log groups
Secrets Manager secrets
Route 53 DNS (if applicable)
State Management:

Terraform state stored in S3 with DynamoDB locking
Separate state files per environment (staging, production)
State encryption enabled
Workflow:

Bash

# Never make manual AWS console edits
terraform plan -var-file=envs/production.tfvars
terraform apply -var-file=envs/production.tfvars
6.3 Monitoring & Observability
Tool	Purpose	Configuration	Alerts
Sentry	Error tracking	DSN in env, Release tagging, Source maps	Slack alerts for high-severity errors, Daily digest
New Relic	APM & RUM	PHP + Browser agents, Custom dashboards	Apdex < 0.8, Error rate >1%, P95 latency >500ms
CloudWatch	Logs & metrics	ECS logs, RDS metrics, Lambda logs	CPU >80%, Memory >85%, Disk >90%, Failed jobs >10/hour
UptimeRobot	Synthetic monitoring	5-min checks on critical endpoints	Downtime >2 minutes, Response time >3s
Lighthouse CI	Performance budgets	Budget config in repo	Perf/A11y score <90, Regression >5%
Dashboards:

New Relic: Core Web Vitals (LCP, FID, CLS), API latency by endpoint, Error rates, Database query performance
CloudWatch: ECS task health, RDS connections, Redis hit rate, SQS queue depth
6.4 Runbooks & Documentation
Location: docs/runbooks/

Incident Response: Escalation paths, communication templates, postmortem format
Disaster Recovery: RTO <4h, RPO <1h, Failover procedures, Backup restoration
Compliance Audits: PDPA checklist, Quarterly review process, Evidence collection
On-Call: Rotation schedule, PagerDuty integration, Escalation policies
Feature Toggles: Management via LaunchDarkly or database flags (planned)
Deployment: Rollback procedures, Blue-green strategy (planned Phase 2)
6.5 Backups & Disaster Recovery (PAD 14)
Resource	Backup Strategy	Retention	Recovery
RDS MySQL	Automated daily snapshots	35 days	Point-in-time restore (5 min granularity)
RDS Manual Snapshots	Weekly full backups	Cross-account replication to ap-northeast-1	Multi-region failover
Redis	Daily snapshots	7 days	Restore from snapshot (data loss <24h acceptable for cache)
S3 Media	Versioning enabled	Lifecycle to Glacier after 90 days	Object restoration from version history
Code	Git repository	GitHub (unlimited)	Clone from GitHub
Disaster Recovery Metrics:

RTO (Recovery Time Objective): <4 hours
RPO (Recovery Point Objective): <1 hour
DR Drills: Quarterly rehearsals with runbook validation
Key Actions for Agents

Check CI status before merges; fix failures prior to requesting review
Coordinate infra changes via Terraform modules (no manual AWS console edits)
Update runbooks when altering operational flows (deployments, incident response)
Tag releases in Sentry for error tracking correlation
Monitor Lighthouse CI budgets in PR checks
7. Security & Compliance
7.1 Regulatory Scope
PDPA (Singapore): Data protection, consent management, right to access/export/delete
MOH (Ministry of Health): Display of facility licenses, staff credentials
IMDA (Infocomm Media Development Authority): Accessibility guidelines
WCAG 2.1 AA: Web accessibility baseline
7.2 Security Architecture (PAD Section 12)
Authentication & Authorization:

Authentication: Laravel Sanctum (bearer tokens), Password strength enforcement (min 12 chars, mixed case, numbers, symbols)
MFA: Required for admin/moderator accounts (TOTP via Google Authenticator)
RBAC: Role-based policies (user, admin, moderator, translator), Granular abilities (view PII, approve content, manage translations)
Session Management: Redis-backed sessions, 24-hour TTL, Secure/HttpOnly cookies
Transport & Application Security:

TLS 1.3: Enforced on all connections
HSTS: Strict-Transport-Security header with preload
CSP (Content Security Policy): Strict directives, Nonce-based script execution
Security Headers: X-Frame-Options: DENY, X-Content-Type-Options: nosniff, Referrer-Policy: strict-origin-when-cross-origin
CSRF: Laravel middleware on all state-changing requests
SQL Injection: Parameterized queries via Eloquent ORM
XSS: Blade template escaping, DOMPurify on client-side
Secrets & Credentials Management (PAD 12):

AWS Secrets Manager for API keys, database passwords, third-party tokens
IAM roles with least privilege policies
Automatic secret rotation every 90 days
No secrets in repository (enforced via git hooks + CI checks)
Monitoring & Incident Response:

Intrusion Detection: AWS GuardDuty
Vulnerability Scanning: Dependabot, npm audit, composer audit, Docker image scanning (Trivy)
Error Tracking: Sentry with PII redaction
Incident Response SLA: 24-hour notification for security incidents, Quarterly incident drills
Data Encryption:

At Rest: RDS encryption (AWS KMS), S3 encryption (AES-256)
In Transit: TLS 1.3
Application: bcrypt hashing for passwords (cost factor 12)
7.3 Compliance Architecture (PAD Section 13)
PDPA Compliance:

Requirement	Implementation	Verification
Explicit Consent	consents table with versioned consent text, Opt-in checkboxes (never pre-checked), Double opt-in for marketing	Quarterly audit of consent records
Data Residency	Production data in AWS ap-southeast-1 only	Terraform state validation
Right to Access	User dashboard export function, ZIP bundle (JSON/CSV) generated via queue job	Test export in staging monthly
Right to Deletion	Account deletion workflow with anonymization, 30-day grace period before hard delete	Quarterly review of deletion logs
Data Retention	18-month inactivity warning, 24-month auto-deletion, 7-year audit log retention	Automated retention scripts with logs
Breach Notification	Incident response runbook, 72-hour notification SLA	Quarterly incident drills
MOH Compliance:

Mandatory display of moh_license_number on center detail pages
Staff credentials table with expiry tracking
Automated reminders for expiring certifications (sent to center admins)
WCAG 2.1 AA Compliance:

Automated checks: Lighthouse CI + axe-core in CI pipeline
Manual validation: Screen reader testing (NVDA, VoiceOver) each sprint
Transcripts/captions required for all multimedia content
Keyboard navigation and focus management tested
Legal Artifacts (Versioned in CMS with Audit Trail):

Privacy Policy
Terms of Use
PDPA Statement
Cookie Policy
Consent Forms (marketing, analytics, testimonials, media)
7.4 Audit Trail (PAD 7.3)
audit_logs Table Schema:

SQL

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(50) NOT NULL, -- 'create', 'update', 'delete', 'view'
  auditable_type VARCHAR(255) NOT NULL, -- Model class
  auditable_id BIGINT UNSIGNED NOT NULL,
  old_values_hash TEXT NULL, -- SHA256 hash for privacy
  new_values_hash TEXT NULL,
  ip_address VARCHAR(45) NOT NULL,
  user_agent TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_auditable (auditable_type, auditable_id),
  INDEX idx_created_at (created_at)
);
Auto-Logging via Observer:

PHP

// backend/app/Observers/AuditObserver.php
class AuditObserver
{
    public function updated(Model $model): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'old_values_hash' => hash('sha256', json_encode($model->getOriginal())),
            'new_values_hash' => hash('sha256', json_encode($model->getAttributes())),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
Key Actions for Agents

Surface compliance impacts in PR descriptions for relevant changes
Consult compliance officer before modifying consent flows, data retention, or audit logging
Ensure new features maintain audit logging (attach observers to new models with PII)
Test PDPA workflows (export, deletion) in staging before production deploys
Never log sensitive data (passwords, full credit card numbers, medical records) in plain text
Rotate secrets via AWS Secrets Manager on 90-day schedule
8. Performance & Scalability Playbook
8.1 Performance Budgets (PAD Section 15)
Metric	Target	Enforcement	Measurement
TTFB	<300ms	Lighthouse CI gate	New Relic RUM
FCP	<1.5s	Lighthouse CI gate	New Relic RUM, WebPageTest
LCP	<2.5s	Lighthouse CI gate	New Relic RUM, Core Web Vitals
TTI	<3s on 3G	Lighthouse CI gate	WebPageTest (Singapore, 3G profile)
FID	<100ms	Core Web Vitals monitoring	New Relic RUM
CLS	<0.1	Core Web Vitals monitoring	New Relic RUM
Page Weight	<280KB (gzipped)	CI bundle size check	Webpack Bundle Analyzer
Lighthouse Performance	>90	CI gate (blocking merge)	Lighthouse CI
API Latency (P95)	<500ms	New Relic alert	New Relic APM
8.2 Caching Strategy (PAD 15)
Multi-Layer Caching:

Layer	Technology	TTL	Invalidation Strategy
Edge Cache	Cloudflare	1 hour (static assets), 5 min (HTML)	Stale-while-revalidate, Cache-Tag purging
Application Cache	Redis	5-15 min (API responses)	Tag-based invalidation on model updates
Database Query Cache	Redis	5 min	Automatic on model changes via events
Next.js ISR	File system + CDN	60s (dynamic pages)	On-demand revalidation via API route
Cache Invalidation Example:

PHP

// backend/app/Listeners/InvalidateCenterCache.php
class InvalidateCenterCache
{
    public function handle(CenterUpdated $event): void
    {
        Cache::tags(['centers', "center:{$event->center->id}"])->flush();
        
        // Purge Cloudflare edge cache
        CloudflareFacade::purgeByTag("center:{$event->center->id}");
    }
}
8.3 Scaling Strategy (PAD 18)
Horizontal Scaling:

ECS Fargate: Auto-scaling on CPU >70% or memory >80%, Min 2 tasks (staging), Min 4 tasks (production), Max 20 tasks
Queue Workers: Scale by SQS queue depth (>100 messages triggers +1 worker), Separate worker pools for high/default/low priority
Database: Read-heavy workloads routed to read replica, Connection pooling (PgBouncer or ProxySQL), Sharding strategy documented for future regional pods
Vertical Scaling:

MeiliSearch: Initially scaled vertically (CPU/RAM), Elasticsearch migration planned beyond 5M records
Redis: ElastiCache cluster mode for >100GB datasets
Resilience Patterns:

Circuit Breakers: External API calls (Calendly, Twilio) have 3-failure threshold, 30-second open state
Retries: Exponential backoff (1s, 5s, 25s) for transient failures
Fallback UIs: Manual contact form when Calendly unavailable, Cached search results when MeiliSearch down
Health Checks: /healthz endpoint checks DB connection, Redis connection, S3 access (shallow ping), Integrated with ALB target group health
8.4 Database Optimization (PAD 15)
Indexes: Composite indexes on high-query columns (centers: (city, capacity), bookings: (user_id, status)), Full-text indexes for search-heavy fields
Connection Pooling: Max 100 connections (primary), Max 50 connections (replica)
Query Caching: Redis stores complex query results (5 min TTL)
Read/Write Splitting: Eloquent read/write connections configured, Replica lag monitoring (<1s threshold)
Slow Query Logging: Queries >500ms logged to CloudWatch, Weekly review in team meeting
8.5 Asynchronous Processing (PAD 4.5, 6.5)
Queue-Eligible Workloads:

Email notifications (via Mailchimp)
SMS notifications (via Twilio)
Data exports (ZIP generation)
Analytics event processing
Translation syncs
Media processing (future: thumbnail generation)
Queue Configuration:

PHP

// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
Horizon Pools:

PHP

// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-high' => [
            'connection' => 'redis',
            'queue' => ['high'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
        ],
        'supervisor-default' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'processes' => 5,
            'tries' => 3,
        ],
    ],
],
8.6 Media Optimization (PAD 5.5)
Responsive Images: Next.js <Image> generates multiple sizes (320w, 640w, 1024w, 1920w)
Format: WebP primary, JPEG fallback
Placeholders: LQIP (Low-Quality Image Placeholder) via blur data URL
Lazy Loading: Native loading="lazy" attribute
Video (Phase 2): Cloudflare Stream adaptive bitrate (ABR), Low-bandwidth warning for users on slow connections
Key Actions for Agents

Instrument new endpoints with New Relic custom instrumentation
Validate caching headers/TTL when introducing new pages
Include load/performance test updates when altering critical paths
Run Lighthouse CI locally before pushing (npm run lighthouse)
Monitor Core Web Vitals in New Relic after deploys
Profile slow database queries using Laravel Telescope in dev
9. Accessibility & Internationalization
9.1 Accessibility Standards (PAD Section 16)
Compliance Baseline:

WCAG 2.1 AA (minimum)
IMDA Singapore Guidelines
Target: Lighthouse Accessibility score >90
Features & Implementation:

Feature	Implementation	Validation
Keyboard Navigation	All interactive elements focusable, Skip links, Tab order logical	Manual testing, Playwright E2E
Screen Reader Support	Semantic HTML, ARIA labels/live regions, Alt text mandatory	NVDA/VoiceOver manual sweeps
Focus Management	Visible focus indicators (≥3px, high contrast), Focus trapping in modals	axe-core automated checks
Color Contrast	≥4.5:1 (normal text), ≥3:1 (large text, UI components)	Design token pre-validation, Lighthouse CI
Reduced Motion	Framer Motion respects prefers-reduced-motion, Parallax disabled if preferred	Manual testing with OS setting
Adjustable Typography	200% zoom support, Relative units (rem/em), Text not truncated	Manual testing at 200% zoom
Captions & Transcripts	Video.js captions (WebVTT), Audio descriptions (Phase 2), Transcript links	QA checklist for multimedia
Form Accessibility	ARIA live regions for errors, Associated labels, Error messages linked via aria-describedby	axe-core + manual validation
Design Tokens (Accessibility Palette):

JavaScript

// frontend/tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#1a56db', // 4.5:1 on white
          dark: '#0e3a8f',    // 7:1 on white
        },
        text: {
          DEFAULT: '#111827', // 15.9:1 on white
          muted: '#6b7280',   // 4.5:1 on white
        },
        background: {
          DEFAULT: '#ffffff',
          muted: '#f9fafb',
        },
      },
    },
  },
};
Automated Testing:

CI Pipeline: axe-core via jest-axe in component tests, Lighthouse CI accessibility audit
Component Library: Percy visual regression with accessibility assertions
Runtime: React Axe in development mode (console warnings)
Manual Validation (Per Sprint):

NVDA (Windows) or VoiceOver (macOS) testing on 2 critical user journeys
Keyboard-only navigation test
200% zoom verification (Chrome, Firefox)
Color contrast audit using browser DevTools
9.2 Internationalization (i18n) Architecture (PAD Section 10)
Supported Locales:

English (en) — default
Mandarin Chinese (zh)
Malay (ms)
Tamil (ta)
Locale Detection Priority (PAD 10):

URL path (e.g., /zh/centers)
User profile preference (authenticated users)
Cookie (NEXT_LOCALE)
Accept-Language header
Default to en
Implementation:

Content Type	Approach	Storage
UI Strings	next-intl JSON files	frontend/locales/{locale}/common.json, frontend/locales/{locale}/forms.json, etc.
Rich Content	CMS translation queue	content_translations table (polymorphic)
Dynamic Data	API responses with ?locale= param	Database locale metadata columns
Translation Workflow (PAD 11):

Content created in default locale (en)
Translation queue job assigns to translator role
Translator drafts translation in CMS (Laravel Nova)
Moderator reviews and approves
Approved content published (visible via API with ?locale=)
Typography & Formatting:

Fonts: Inter (Latin), Noto Sans SC (Simplified Chinese), Noto Sans (Malay, Tamil)
Font Subsetting: next/font loads only required character ranges
Date/Time: Intl.DateTimeFormat localized to en-SG, zh-SG, ms-SG, ta-IN
Numbers/Currency: Intl.NumberFormat with Singapore dollar (SGD) formatting
Line Height: Dynamic adjustment for CJK