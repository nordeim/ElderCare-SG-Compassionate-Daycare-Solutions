# Consolidated AGENT.md — ElderCare SG Web Platform

**Version:** 1.0  
**Last Updated:** 2025-10-08 19:01 SGT  
**Primary Source Documents:** `Project_Architecture_Document.md`, `Project_Requirements_Document.md`, `codebase_completion_master_plan.md`, `docs/ai-coding-agent-brief.md`, `docs/accessibility/`, `docs/design-system/`, `docs/deployment/`

> **Purpose**: This guide is the single source of truth for any AI coding agent (and their human facilitators) to understand the ElderCare SG architecture, delivery standards, and operational guardrails before touching the codebase.

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [System Overview](#2-system-overview)
3. [Frontend Blueprint](#3-frontend-blueprint)
4. [Backend Blueprint](#4-backend-blueprint)
5. [Data & Integrations](#5-data--integrations)
6. [Operational Maturity](#6-operational-maturity)
7. [Security & Compliance](#7-security--compliance)
8. [Performance & Scalability Playbook](#8-performance--scalability-playbook)
9. [Accessibility & Internationalization](#9-accessibility--internationalization)
10. [Testing & Quality Assurance](#10-testing--quality-assurance)
11. [Risk Register & Mitigations](#11-risk-register--mitigations)
12. [Lifecycle & Roadmap Hooks](#12-lifecycle--roadmap-hooks)
13. [Collaboration Protocols](#13-collaboration-protocols)
14. [Quickstart Checklist for AI Agents](#14-quickstart-checklist-for-ai-agents)
15. [Change Log & Maintenance Guidance](#15-change-log--maintenance-guidance)

---

## 1. Executive Summary

- **Product Mission:** Deliver a compassionate, accessibility-first digital bridge between Singaporean families and trusted elderly daycare services.
- **Primary Outcomes**: Empower informed decisions, build trust, and enhance quality of life for seniors and caregivers.
- **Target Audiences:** Adult children (30–55, primary), family caregivers, healthcare professionals, digitally literate seniors, and support agencies across Singapore's multicultural landscape.
- **Core Value Pillars:** Trust & transparency, accessibility, cultural resonance (multicultural imagery, respectful language, holiday-aware content), seamless booking and engagement, compliance with Singapore regulations.
- **North-Star Success Metrics:** +30% visit bookings in 3 months, Lighthouse scores >90, <40% mobile bounce, >5-minute session duration, >75% form completion, >60% video completion, <3s 3G load time (see `Project_Requirements_Document.md`).

**Key Actions for Agents**
- **Review** `README.md` and `docs/ai-coding-agent-brief.md` before any contribution.
- **Align** feature work with success metrics and value pillars.
- **Confirm** stakeholder intent via latest roadmap milestones.

---

## 2. System Overview

- **Architecture Pattern:** Service-oriented monolith (Laravel) with modular domain boundaries and shared MySQL schema (`Project_Architecture_Document.md`).
- **Hosting Footprint:** AWS ECS Fargate (Singapore region ap-southeast-1) + Cloudflare CDN/WAF. Terraform manages infrastructure (`docs/deployment/`).
- **Environments:** Local (Docker Compose), Staging (ECS - auto-deploy from `main`), Production (ECS - manual approval). Observability across all tiers.
- **Key Principles:** Domain separation, API-first, progressive enhancement, accessibility-first, security-by-design, automated governance.
- **Logical Components:** Next.js frontend, Laravel backend, supporting services (MySQL, Redis, MeiliSearch, S3), third-party APIs (Calendly, Mailchimp, Twilio, Cloudflare Stream).

```mermaid
flowchart LR
    User((Users)) -->|HTTPS| Cloudflare --> NextJS
    NextJS -->|API Calls| LaravelAPI
    LaravelAPI --> MySQL[(MySQL 8.0)]
    LaravelAPI --> Redis[(Redis 7)]
    LaravelAPI --> MeiliSearch
    LaravelAPI --> S3[(AWS S3)]
    LaravelAPI -->|Queues| SQS[(SQS/Fargate workers)]
    LaravelAPI --> Calendly{{Calendly}}
    LaravelAPI --> Mailchimp{{Mailchimp}}
    LaravelAPI --> Twilio{{Twilio}}
    Cloudflare -.-> Observability[(Sentry/New Relic/CloudWatch)]
```

**Key Actions for Agents**
- **Confirm** environment context before running commands (`.env`, `frontend/.env.local`).
- **Respect** architecture principles when proposing modifications.
- **Check** Terraform state/variables prior to infra-affecting changes.

---

## 3. Frontend Blueprint

- **Stack:** Next.js 14 (App Router), React 18, TypeScript 5, Tailwind CSS, Radix UI primitives, Framer Motion (reduced-motion aware), Zustand (client state), React Query (server state).
- **Directory Guardrails:** `frontend/app/` (RSC pages/layouts), `frontend/components/` (shared UI), `frontend/lib/` (utilities), `frontend/hooks/`, `frontend/tests/`.
- **Rendering Strategy:** Mix of React Server Components and edge caching; fallback to CSR only where interactivity demands.
- **Design System:** Defined in `docs/design-system/`; tokens drive Tailwind config; follow accessibility color palette. Comprehensive, accessible component library documented in Storybook.
- **Client Tooling:** ESLint, Prettier, Jest, Testing Library, Playwright.

**Key Actions for Agents**
- **Adhere** to design tokens and component usage documented in `docs/design-system/` and Storybook.
- **Favor** server components for data-heavy views; justify CSR usage in PR notes.
- **Instrument** interactions with analytics data attributes when introducing new components.

---

## 4. Backend Blueprint

- **Framework:** Laravel 12 (PHP 8.2) with domain-focused service classes (`backend/app/Domain/*`).
- **API Surface:** RESTful, versioned at `/api/v1/`; JSON:API-inspired responses, consistent error envelopes, global exception handler for predictable error codes (4xx/5xx). Sanctum for auth. Will be documented using OpenAPI 3.0 specification with Postman collection.
- **Domain Modules:** Users, Centers, Bookings, Testimonials, Content, Newsletters, Subsidies, Integrations (Calendly/Mailchimp/Twilio).
- **Service Layer Architecture:** Strict separation of concerns:
  - **Controllers:** Thin, handle HTTP requests only
  - **Services:** Core business logic (e.g., `AuthService`, `ConsentService`, `AuditService`, `UserService`, `CenterService`, `BookingService`, `CalendlyService`, `TwilioService`, `NotificationService`, `MediaService`)
  - **Repositories:** Data access layer
  - **Observers:** `AuditObserver` automatically logs model changes for PDPA audit trail
- **Async Workloads:** Laravel Queues backed by SQS/Fargate for email, SMS, data sync. Event-Listener patterns for audit trails.
- **Policies & Validation:** Form requests for validation, Policies/Guard for RBAC enforcement, role-based middleware.
- **Testing:** PHPUnit + Pest. Factories/seeders maintain sample data for test baselines.

**Key Actions for Agents**
- **Consult** `Project_Architecture_Document.md` section 7 for module responsibilities.
- **Maintain** service/repository layering; avoid placing logic in controllers.
- **Update** job/event documentation when introducing new async flows.

---

## 5. Data & Integrations

### Database Schema
- **Primary Store:** MySQL 8.0 (RDS). Comprehensive **18-table schema** meticulously designed for compliance, scalability, and multilingual support.
- **Compliance-First Design:**
  - Polymorphic `audit_logs` table for tracking all data changes
  - `consents` table for versioned user consent
  - Soft deletes on critical tables
  - MOH compliance via specific fields in `centers` and `staff` tables (e.g., `moh_license_number`)
- **Core Entities:** `users`, `profiles`, `centers`, `services`, `bookings`, `testimonials` with well-defined relationships and constraints.
- **Advanced Features:**
  - **Polymorphic relationships** for reusable `media` (S3-backed) and `content_translations` tables
  - **JSON columns** for semi-structured data (operating hours, amenities, questionnaire responses)
  - Full suite of **indexes** (composite and full-text)
  - Pre-built **VIEWS** for complex queries (e.g., center summaries)
  - Integration-ready columns for external service IDs (Calendly, Mailchimp, Twilio)

### Existing Migration Scripts
**⚠️ Important**: Review and use existing migration scripts. Only create new migrations if features are not already included. If in doubt, refer to `database_schema.sql` as source of truth.

```
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
backend/database/migrations/2025_10_14_000001_add_updated_at_to_audit_logs_table.php
```

### Caching & Supporting Services
- **Caching & Sessions:** Redis 7 (ElastiCache). Used for caching, queues, rate limiting, and session storage.
- **Search:** MeiliSearch (hosted) for center/service discovery (replaces older Elasticsearch plan).
- **Object Storage:** AWS S3 (ap-southeast-1) with lifecycle rules, Cloudflare R2 considered for future offloading.

### External APIs
- **Calendly:** Booking orchestration and scheduling workflows
- **Mailchimp:** Newsletter management (double opt-in)
- **Twilio:** SMS notifications, confirmations, and reminders
- **Cloudflare Stream:** Phase 2 video hosting with adaptive streaming

### Data Governance
PDPA-compliant retention policies, anonymization workflows, consent tracking, data residency in Singapore, right-to-be-forgotten workflows.

**Key Actions for Agents**
- **Validate** migration impact on retention/consent rules before altering schema.
- **Mock** third-party APIs in tests; never call live services from automated suites.
- **Document** new integration touchpoints in `docs/AGENT.md` and relevant runbooks.

---

## 6. Operational Maturity

- **CI/CD:** GitHub Actions with pipelines for lint/test, build, security checks, and environment deploy (staging automatic on `main`). Manual approval for production.
- **Infrastructure as Code:** Terraform modules for ECS, RDS, ElastiCache, S3, IAM, CloudWatch, secrets.
- **Monitoring:** Sentry (errors), New Relic (APM), CloudWatch metrics/logs, UptimeRobot (synthetic), Lighthouse CI (performance budgets), GA4 (analytics), Hotjar (user behavior).
- **Runbooks:** Stored in `docs/runbooks/` (incident response, DR, compliance audit, on-call).
- **Backups:** RDS snapshots + S3 archival, cross-region replication to ap-northeast-1.

**Key Actions for Agents**
- **Check** CI status before merges; fix failures prior to requesting review.
- **Coordinate** infra changes with Terraform modules (no manual console edits).
- **Update** or reference runbooks when altering operational flows.

---

## 7. Security & Compliance

- **Regulatory Scope:** PDPA (Singapore), MOH eldercare guidelines, IMDA accessibility, WCAG 2.1 AA.
- **Security Posture:**
  - OAuth 2.0 + Sanctum authentication with secure session cookies
  - MFA for admin access
  - Role-based access control (RBAC)
  - Strict CSP, CSRF, SQLi/XSS safeguards
  - Secrets via AWS Secrets Manager/SSM; rotated per schedule
  - Audit logs for user actions
  - Consent ledger for PDPA compliance
  - Rate limiting to prevent abuse
- **Privacy:** Data residency in Singapore, anonymization protocols, right-to-be-forgotten workflows, media consent tracking for testimonials.
- **Compliance Documentation:** `docs/accessibility/`, `Project_Requirements_Document.md` sections 2–4, `docs/deployment/security.md` (if present).

**Key Actions for Agents**
- **Surface** compliance impacts in PR descriptions for relevant changes.
- **Consult** compliance officer/stakeholders before modifying regulated flows.
- **Ensure** new features maintain audit and consent logging.

---

## 8. Performance & Scalability Playbook

- **Performance Budgets:** Lighthouse >90 (all categories), 3G load <3s, Core Web Vitals thresholds met.
- **Caching Strategy:** 
  - HTTP caching via Cloudflare
  - Application caching via Redis
  - Next.js ISR for dynamic pages
- **Scaling:** 
  - ECS Fargate autoscaling (CPU/memory)
  - Read replicas for MySQL (planned)
  - Queue worker scaling by SQS depth
- **Profiling Tools:** New Relic, Laravel Telescope (dev), React Profiler.
- **Load Testing:** k6 (backend), Lighthouse CI & WebPageTest (frontend).

**Key Actions for Agents**
- **Instrument** new endpoints with performance metrics.
- **Validate** caching headers/TTL when introducing new pages.
- **Include** load/perf test updates when altering critical paths.

---

## 9. Accessibility & Internationalization

### Accessibility
- **Standards:** WCAG 2.1 AA baseline, IMDA guidelines.
- **Features:** 
  - Keyboard-only navigation
  - ARIA-rich components (Radix UI)
  - Adjustable typography
  - Video captions/audio descriptions (Video.js-powered tours)
  - Color contrast >= 4.5:1
  - Screen reader validation (NVDA/VoiceOver)
  - Reduced-motion support (Framer Motion)
- **Testing:** axe-core automation via `jest-axe`, NVDA/VoiceOver manual sweeps, screen magnification tests.

### Internationalization (i18n)
- **Languages:** English, Mandarin, Malay, Tamil
- **Implementation:** 
  - Content stored with locale metadata
  - Translation pipeline uses CMS and translation memory
  - Respectful tone and cultural sensitivity
  - Holiday-aware content
  - Transit and parking guidance aligned with Singapore norms
- **Framework:** Complete i18n framework for all four languages

**Key Actions for Agents**
- **Use** `docs/accessibility/accessibility-checklist.md` before completing features.
- **Mark** translatable strings and update translation files/stubs.
- **Request** accessibility review for UI-heavy changes.

---

## 10. Testing & Quality Assurance

### Automated Coverage (>90% required)
- **Frontend:**
  - **Unit/Integration:** Jest + React Testing Library
  - **E2E:** Playwright for critical user journeys
  - **Accessibility:** `jest-axe` in every component test
  - **Visual Regression:** Percy on Storybook component library (CI-gated via `PERCY_TOKEN`, `.percy.yml` baselines)
  - **Performance:** Lighthouse CI with budget enforcement
- **Backend:**
  - **Unit/Feature:** PHPUnit + Pest
  - **Integration:** Laravel Dusk (as needed)
  - **Contract Tests:** For external API abstractions
- **Cross-Cutting:**
  - axe-core accessibility audits
  - Security penetration tests

### Manual QA
- BrowserStack multi-browser/device testing
- Assistive technology validation (screen readers, magnification)
- Stakeholder UAT for major releases
- Usability feedback loops

### Definition of Done
- Peer review approved
- 100% automated coverage for new code (>90% overall)
- Manual QA sign-off
- Accessibility targets met (Lighthouse >90, axe-core pass)
- Performance targets met (Lighthouse >90, <3s 3G load)
- Documentation updates complete
- Monitoring hooks configured
- Stakeholder approval obtained

**Key Actions for Agents**
- **Extend** test suites when modifying behavior.
- **Update** QA checklists with new scenarios.
- **Attach** Lighthouse/axe reports for significant UI changes.
- **Run** Percy visual regression tests for component changes.

---

## 11. Risk Register & Mitigations

Refer to `Project_Architecture_Document.md` section 20 for full matrix. Highlights:

| Risk | Impact | Probability | Mitigation | Status |
|------|--------|-------------|------------|--------|
| Vendor API changes (Calendly/Twilio) | High | Medium | Abstraction layer, contract tests, fallbacks | Active |
| Performance degradation on media-heavy pages | High | Medium | Adaptive bitrate (Cloudflare Stream), lazy loading, perf budgets | Mitigated |
| Compliance breach (PDPA) | High | Low | Consent ledger, audit trails, legal review cadence | Mitigated |
| Data migration errors | Medium | Low | Automated migration tests, rehearsal rollbacks | Planned |
| Staffing bandwidth | Medium | Medium | Sprint prioritization, cross-training, partner support | Active |

**Key Actions for Agents**
- **Log** new risks or mitigation updates in the PAD risk table and Changelog.
- **Notify** stakeholders when a mitigation depends on pending development.
- **Include** risk assessment in major architectural proposals.

---

## 12. Lifecycle & Roadmap Hooks

### Current Phase
**Foundation Hardening** (also referred to as "Pre-Phase 3 Remediation Stage" in some docs) — addressing gaps from initial phases before Phase 3 commencement.

### Completed Phases
- **Phase 1:** Foundation, Infrastructure & Analytics — Docker environment, CI/CD pipeline, database schema complete
- **Phase 2:** Design System, UI Components & i18n — Accessible component library in Storybook, i18n framework complete for English and Mandarin

### Upcoming Milestones
- **Phase 3:** Core Backend Services & PDPA Compliance — Complete Laravel API implementation, authentication, all core business logic, booking system integration, robust PDPA/MOH compliance features
- **v1.0:** Authentication, content management, base booking, analytics instrumentation
- **v1.1:** Multilingual refinement, subsidy calculator, advanced testimonials
- **v2.0:** Cloudflare Stream integration, AI recommendations, provider portal, WebXR virtual tours

### Feature Toggles
Documented in `docs/runbooks/feature-toggles.md` (create/update as needed).

**Key Actions for Agents**
- **Align** contributions with roadmap phase priorities (currently Phase 3).
- **Document** new toggles and update rollout plans.
- **Flag** dependencies that could affect milestone schedules.
- **Reference** `codebase_completion_master_plan.md` for detailed phased delivery plan and acceptance criteria.

---

## 13. Collaboration Protocols

- **Branching:** `feature/*`, `bugfix/*`, `chore/*`. Rebase-based workflow preferred. Work merged into `main` via Pull Requests.
- **PR Expectations:** 
  - Architecture alignment summary
  - Tests included (>90% coverage)
  - Screenshots/demo for UI changes
  - Risk assessment
  - Documentation updates
  - Lighthouse/axe reports for UI changes
  - Reference to relevant ADRs
- **ADR Process:** Use `docs/adr/ADR-###.md` template for significant architectural decisions. Reference ADRs in PRs.
- **Communication:** Slack channels `#eldercare-dev`, `#eldercare-docs`, weekly architecture sync, incident bridge hotline (see runbook).
- **Code Review:** All PRs must pass full CI suite before review. Peer approval required before merge.
- **Documentation:** Update `docs/` and runbooks alongside code changes.

**Key Actions for Agents**
- **Submit** ADRs before implementing high-impact changes.
- **Maintain** transparent PR narratives referencing this guide.
- **Sync** with on-call/lead engineer for operationally sensitive work.

---

## 14. Quickstart Checklist for AI Agents

### 1. Context Intake
- [ ] Read `docs/AGENT.md` (this file) end-to-end
- [ ] Review `Project_Architecture_Document.md` sections relevant to task
- [ ] Review `Project_Requirements_Document.md` for detailed requirements
- [ ] Confirm roadmap priorities in `codebase_completion_master_plan.md`
- [ ] Review `README.md` for project overview and setup

### 2. Environment Prep
- [ ] Clone repository: `git clone https://github.com/eldercare-sg/web-platform.git && cd web-platform`
- [ ] Copy environment files:
  ```bash
  cp .env.example .env
  cp frontend/.env.local.example frontend/.env.local
  ```
- [ ] Edit `.env` files with appropriate credentials if needed
- [ ] Start Docker services: `docker-compose up -d`
- [ ] Install dependencies (if working outside Docker):
  ```bash
  cd backend && composer install
  cd ../frontend && npm install
  ```

### 3. Verification
- [ ] Run database migrations: `docker-compose exec backend php artisan migrate`
- [ ] Verify frontend access: [http://localhost:3000](http://localhost:3000)
- [ ] Verify backend API access: [http://localhost:8000](http://localhost:8000)
- [ ] Run backend test suite: `docker-compose exec backend composer test`
- [ ] Run frontend test suite: `docker-compose exec frontend npm test`
- [ ] Run E2E tests: `docker-compose exec frontend npm run test:e2e`
- [ ] Start Storybook (optional): `cd frontend && npm run storybook`

### 4. Change Implementation
- [ ] Draft implementation plan aligned with architecture principles
- [ ] Create feature branch: `git checkout -b feature/TASK-XXX-description`
- [ ] Implement changes following service-layer architecture
- [ ] Write/update tests (>90% coverage)
- [ ] Update documentation (`docs/`, runbooks, ADRs as needed)

### 5. Pre-PR Checks
- [ ] All linting passes: `npm run lint` / `composer lint`
- [ ] All tests pass: `npm test` / `composer test`
- [ ] E2E tests pass (if applicable): `npm run test:e2e`
- [ ] Lighthouse/axe checks for UI changes (>90 scores)
- [ ] Percy visual regression tests (if component changes)
- [ ] Review existing migration scripts before creating new ones
- [ ] Update runbooks/ADRs if applicable
- [ ] Verify no manual console edits required (Terraform only)

### 6. Delivery
- [ ] Prepare thorough PR description:
  - Context and motivation
  - Approach and alternatives considered
  - Risks and trade-offs
  - Testing strategy
  - Documentation updates
  - Screenshots/demos for UI changes
  - Reference to ADRs, issues, or roadmap items
- [ ] Tag reviewers per ownership matrix
- [ ] Link to CI run results
- [ ] Attach Lighthouse/axe/Percy reports if applicable

**Key Actions for Agents**
- **Archive** completed checklist with PR for traceability.
- **Raise** blockers early via designated communication channels.
- **Ensure** local environment mirrors target environment before debugging.

---

## 15. Change Log & Maintenance Guidance

| Date | Author | Change Summary | Linked Docs |
|------|--------|----------------|-------------|
| 2025-10-08 | Cascade AI Agent | Initial creation of `docs/AGENT.md` consolidating architecture insights | `Project_Architecture_Document.md` v2.1 |
| 2025-10-08 | Cascade AI Agent | Updated Phase 1 documentation references (env templates, CI/CD, monitoring) | `docs/phase1-execution-plan.md`, `docs/ci-cd-overview.md`, `docs/deployment/monitoring.md`, `docs/git-workflow.md` |
| 2025-10-09 | Cascade AI Agent | Documented Phase 2 testing enhancements (jest-axe, Storybook runner, Percy workflow) | `docs/phase2-execution-subplan.md`, `.github/workflows/ci.yml`, `.percy.yml` |
| [Current] | Cascade AI Agent | Consolidated AGENT.md, GEMINI.md, and ai-coding-agent-brief.md into single source of truth; highlighted conflicts requiring resolution | This document |

### Update Protocol

**When to Update:**
- Any architectural change
- New integration or external dependency
- Workflow modification
- Risk/mitigation update
- Process adjustment affecting onboarding
- Conflict resolution or clarification

**How to Update:**
1. Modify relevant sections in `docs/AGENT.md`
2. Append new entry to change log table above
3. Cross-reference supporting docs (PAD, ADRs, runbooks)
4. Notify team via `#eldercare-docs` Slack channel and tag lead architect
5. Update version number if major changes

**Validation:**
- Re-run Quickstart Checklist to ensure instructions remain accurate
- Perform markdown lint/spellcheck
- Verify all referenced documents still exist and are current

**Key Actions for Agents**
- **Treat** this document as living; keep synchronized with PAD, ADRs, and runbooks.
- **Record** rationale for major updates in ADRs or PAD sections.
- **Audit** this guide quarterly to maintain relevance.
- **Resolve** highlighted conflicts in section 0 with stakeholder input.

---

## Analytics & Success Metrics (Detailed)

| Metric | Target | Review Cadence | Owner | Tracking Tool |
|--------|--------|----------------|-------|---------------|
| Visit bookings increase | +30% in 3 months | Monthly | Marketing | GA4, Backend Analytics |
| Mobile bounce rate | <40% | Bi-weekly | UX | GA4, Hotjar |
| Lighthouse Performance | >90 | Each deploy | DevOps/QA | Lighthouse CI |
| Lighthouse Accessibility | >90 | Each deploy | DevOps/QA | Lighthouse CI |
| Average session duration | >5 minutes | Monthly | Marketing | GA4 |
| Form completion rate | >75% | Monthly | UX | GA4, Hotjar |
| 3G load time | <3 seconds | Weekly | DevOps | WebPageTest, Lighthouse |
| Video engagement | >60% completion | Monthly | Marketing | Video.js analytics, GA4 |
| Core Web Vitals (LCP/FID/CLS) | Meet thresholds | Each deploy | DevOps | Lighthouse CI, New Relic |

---

> **Operational Reminder:** Uphold the elevated operating framework—deep analysis, systematic planning, technical excellence, strategic partnership, and transparent communication—on every engagement.
